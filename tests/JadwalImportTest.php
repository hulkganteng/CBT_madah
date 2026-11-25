<?php

use PHPUnit\Framework\TestCase;

require_once dirname(__DIR__) . '/application/libraries/Jadwal_import.php';

final class JadwalImportTest extends TestCase
{
    public function testValidRowsAreNormalized(): void
    {
        $result = (new Jadwal_import())->parseRows(array(
            Jadwal_import::HEADERS,
            array(' mtk-xii-01 ', ' pas ', '2026-12-01', '2026-12-02', '90', '30', '1', '1', '1', '0', '0', '1'),
        ));

        self::assertSame(array(), $result['errors']);
        self::assertSame('MTK-XII-01', $result['rows'][0]['kode_bank']);
        self::assertSame(90, $result['rows'][0]['durasi']);
    }

    public function testInvalidAndDuplicateRowsAreRejected(): void
    {
        $row = array('BANK-1', 'PAS', '2026-12-03', '2026-12-01', '0', '10', '2', '0', '0', '0', '0', '1');
        $result = (new Jadwal_import())->parseRows(array(Jadwal_import::HEADERS, $row, $row));

        self::assertCount(2, $result['errors']);
        self::assertStringContainsString('tanggal_selesai', $result['errors'][0]);
        self::assertStringContainsString('duplikat', $result['errors'][1]);
    }

    public function testControllerKeepsCommitAtomicAndAdminOnly(): void
    {
        $source = file_get_contents(dirname(__DIR__) . '/application/controllers/Cbtjadwalimport.php');

        self::assertStringContainsString('ion_auth->is_admin()', $source);
        self::assertStringContainsString('trans_begin()', $source);
        self::assertStringContainsString('trans_rollback()', $source);
        self::assertStringContainsString("insert_batch('cbt_jadwal'", $source);
    }

    public function testMigrationEnforcesTheSameBankTypeInvariant(): void
    {
        $migration = file_get_contents(dirname(__DIR__) . '/application/migrations/20261118000100_unique_exam_schedule_bank_type.php');

        self::assertStringContainsString('uq_jadwal_bank_jenis', $migration);
        self::assertStringContainsString('GROUP BY id_bank, id_jenis HAVING COUNT(*) > 1', $migration);
    }
}
