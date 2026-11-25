<?php

use PHPUnit\Framework\TestCase;

require_once dirname(__DIR__) . '/application/libraries/Jadwal_import.php';

final class ExamScheduleSlotsTest extends TestCase
{
    private function service(): Exam_schedule_slots
    {
        return (new ReflectionClass(Exam_schedule_slots::class))->newInstanceWithoutConstructor();
    }

    private function slot(array $changes = []): array
    {
        return $changes + [
            'id_jadwal' => 21, 'id_jenis' => '1', 'bank_level' => '10',
            'tanggal' => '2026-12-08', 'id_sesi' => '1', 'jenis_sasaran' => 'rombel',
            'sasaran' => '4', 'urutan' => '2', 'ubah_sesi' => '0',
            'jam_buka' => '07:45:00', 'waktu_akhir' => '08:30:00',
            'status' => '1', 'aktif' => '1', 'wajib_mapel_pertama' => '0',
            'tgl_mulai' => '2026-12-08', 'tgl_selesai' => '2026-12-09',
            'bank_aktif' => '1', 'status_soal' => '1', 'soal_agama' => '-', 'nama_mapel' => 'Matematika',
        ];
    }

    public function testImportRetainsMultipleSlotsForOneBankAndReadsExcelTime(): void
    {
        $exam = ['BANK-1', 'PAS', '2026-12-08', '2026-12-08', 90, 30, 1, 1, 1, 0, 0, 1];
        $result = (new Jadwal_import())->parseRows([
            Jadwal_import::headers(),
            array_merge($exam, ['2026-12-08', 'S1', 'rombel', 'X-A', 2, 465 / 1440, 1, 0]),
            array_merge($exam, ['2026-12-08', 'S3', 'rombel', 'X-B', 2, '11:45', 0, 1]),
        ]);

        self::assertSame([], $result['errors']);
        self::assertCount(2, $result['rows']);
        self::assertSame('07:45', $result['rows'][0]['jam_buka_wib']);
        self::assertSame('1', $result['rows'][1]['ubah_sesi']);
    }

    public function testDailyOverrideUsesMostSpecificMatchingGroupAndExpires(): void
    {
        $service = $this->service();
        $slots = [
            $this->slot(),
            $this->slot(['id_sesi' => '3', 'ubah_sesi' => '1']),
            $this->slot(['id_sesi' => '2', 'ubah_sesi' => '1', 'jenis_sasaran' => 'peminatan', 'sasaran' => '9']),
            $this->slot(['tanggal' => '2026-12-09']),
        ];
        $firstClass = $service->assignedSlots($slots, ['groups' => ['rombel' => '4', 'peminatan' => '9'], 'session' => 1]);
        $otherClass = $service->assignedSlots($slots, ['groups' => ['rombel' => '5', 'peminatan' => '9'], 'session' => 1]);

        self::assertSame('3', $firstClass['2026-12-08'][21]['id_sesi']);
        self::assertSame('1', $firstClass['2026-12-09'][21]['id_sesi']);
        self::assertSame('2', $otherClass['2026-12-08'][21]['id_sesi']);
    }

    public function testMakSlotTargetsOnlyTheMatchingProgramAndSession(): void
    {
        $service = $this->service();
        $slots = [$this->slot(['jenis_sasaran' => 'program_mak', 'sasaran' => 'TAHFIDZ'])];
        self::assertArrayHasKey(21, $service->assignedSlots($slots, ['groups' => ['program_mak' => 'TAHFIDZ'], 'session' => 1])['2026-12-08']);
        self::assertSame([], $service->assignedSlots($slots, ['groups' => ['program_mak' => 'KITAB'], 'session' => 1]));
        self::assertSame([], $service->assignedSlots($slots, ['groups' => ['program_mak' => 'TAHFIDZ'], 'session' => 2]));
    }

    public function testOpeningUsesWibAndAllowsStartingExactlyAtConfiguredMinute(): void
    {
        $service = $this->service();
        $slot = $this->slot();
        $student = (object) ['id_siswa' => 7];
        self::assertSame('Ujian dibuka pukul 07:45 WIB.', $service->startBlock($slot, [], $student, new DateTimeImmutable('2026-12-08T00:44:59Z')));
        self::assertNull($service->startBlock($slot, [], $student, new DateTimeImmutable('2026-12-08T00:45:00Z')));
        self::assertSame('Sesi ujian telah berakhir.', $service->startBlock($slot, [], $student, new DateTimeImmutable('2026-12-08T01:30:01Z')));
    }

    public function testConflictingOverridesForOneGroupAreRejected(): void
    {
        $service = $this->service();
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('dipindah ke satu sesi');
        $service->validatePlan([$this->slot(['id_sesi' => '1', 'ubah_sesi' => '1'])], [
            $this->slot(['id_sesi' => '3', 'ubah_sesi' => '1']),
        ]);
    }

    public function testPrerequisiteRequiresFinishedStatusForFirstScheduleOfThisStudent(): void
    {
        $student = (object) ['id_siswa' => 7, 'level_id' => 10, 'agama' => 'Islam'];
        $database = new class {
            public $completed = FALSE;
            public $filters;

            public function where($filters)
            {
                $this->filters = $filters;
                return $this;
            }

            public function count_all_results($table)
            {
                return $table === 'cbt_durasi_siswa' && $this->completed ? 1 : 0;
            }
        };
        $service = $this->service();
        (new ReflectionProperty(Exam_schedule_slots::class, 'ci'))->setValue($service, (object) [
            'load' => new class {
                public function library($name) {}
            },
            'db' => $database,
            'student_exam_eligibility' => new class {
                public function isEligible($studentId, $scheduleId)
                {
                    return $studentId === 7 && $scheduleId === 20;
                }
            },
        ]);
        $second = $this->slot(['wajib_mapel_pertama' => '1']);
        $first = $this->slot(['id_jadwal' => 20, 'urutan' => '1', 'priority' => 3]);
        $now = new DateTimeImmutable('2026-12-08T07:45:00+07:00');

        self::assertSame('Jadwal mapel pertama untuk kelompok Anda belum tersedia. Hubungi pengawas.', $service->startBlock($second, [], $student, $now));
        self::assertSame('Selesaikan Matematika terlebih dahulu.', $service->startBlock($second, [$first], $student, $now));
        self::assertSame(['id_siswa' => 7, 'id_jadwal' => 20, 'status' => 2], $database->filters);
        $database->completed = TRUE;
        self::assertNull($service->startBlock($second, [$first], $student, $now));
    }
}
