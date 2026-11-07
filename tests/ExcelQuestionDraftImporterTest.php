<?php

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PHPUnit\Framework\TestCase;

final class ExcelQuestionDraftImporterTest extends TestCase
{
    public function testNewAndLegacyColumnsProduceTheSameDraftQuestion(): void
    {
        $new = $this->parse(array(
            array('NO', 'JENIS', 'PERTANYAAN', 'A', 'B', 'JAWABAN'),
            array('CONTOH', 'Pilihan Ganda', 'Baris contoh', 'A', 'B', 'A'),
            array(1, 'Pilihan Ganda', 'Ibukota Indonesia adalah...', 'Jakarta', 'Bandung', 'a.'),
        ));
        $legacy = $this->parse(array(
            array('NO', 'JENIS', 'SOAL', 'OPSI_A', 'OPSI_B', 'KUNCI', 'KESULITAN', 'TAMPILKAN'),
            array(1, 1, 'Ibukota Indonesia adalah...', 'Jakarta', 'Bandung', 'A', 1, 1),
        ));

        self::assertCount(1, $new['items']);
        self::assertSame(array(), $new['items'][0]['errors']);
        $normalizer = new Question_import();
        self::assertSame(
            $normalizer->toDatabaseRow($new['items'][0]['normalized_data'], 1),
            $normalizer->toDatabaseRow($legacy['items'][0]['normalized_data'], 1)
        );
    }

    public function testUnfilledQuotaRowsAreSkipped(): void
    {
        $parsed = $this->parse(array(
            array('NO', 'JENIS', 'PERTANYAAN', 'A', 'B', 'JAWABAN'),
            array(1, 1, '', '', '', ''),
            array(2, 1, 'Dua ditambah dua adalah...', '4', '5', 'A'),
            array(3, 1, '', '', '', ''),
        ));

        self::assertSame(array(), $parsed['file_errors']);
        self::assertCount(1, $parsed['items']);
        self::assertSame(3, $parsed['items'][0]['row_number']);
    }

    private function parse(array $rows): array
    {
        $spreadsheet = new Spreadsheet();
        $spreadsheet->getActiveSheet()->setTitle('SOAL')->fromArray($rows);
        $path = sys_get_temp_dir() . DIRECTORY_SEPARATOR . uniqid('garuda_import_', true) . '.xlsx';
        (new Xlsx($spreadsheet))->save($path);
        $spreadsheet->disconnectWorksheets();

        try {
            $reflection = new ReflectionClass(Excel_question_draft_importer::class);
            $importer = $reflection->newInstanceWithoutConstructor();
            $property = $reflection->getProperty('normalizer');
            $property->setValue($importer, new Question_import());
            return $importer->parse($path);
        } finally {
            @unlink($path);
        }
    }
}
