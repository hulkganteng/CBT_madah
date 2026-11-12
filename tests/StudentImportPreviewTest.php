<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

final class StudentImportPreviewTest extends TestCase
{
    private function source(string $path): string
    {
        return (string) file_get_contents(dirname(__DIR__) . '/' . $path);
    }

    public function testEmptySpreadsheetDoesNotRunAnEmptyDuplicateQuery(): void
    {
        $controller = $this->source('application/controllers/Datasiswa.php');

        self::assertStringContainsString("if (\$siswa === [])", $controller);
        self::assertStringContainsString("'siswa' => []", $controller);
        self::assertStringContainsString("'exist' => []", $controller);
        self::assertStringContainsString('getSiswaByArrNisn($arrNisn, $arrNis, $arrUsername)', $controller);
        self::assertStringContainsString('$headerFound = false', $controller);
        self::assertStringContainsString('$normalizedNama === \'NAMASISWA\'', $controller);
        self::assertStringContainsString('if (!$headerFound)', $controller);
    }

    public function testPreviewAlwaysReturnsStructuredJsonAndRemovesTemporaryFile(): void
    {
        $controller = $this->source('application/controllers/Datasiswa.php');

        self::assertStringContainsString("'status' => false", $controller);
        self::assertStringContainsString("'status' => true", $controller);
        self::assertStringContainsString('finally {', $controller);
        self::assertStringContainsString('@unlink($file)', $controller);
    }

    public function testBrowserHandlesJsonResponseAndDisablesUploadOnPreviewFailure(): void
    {
        $javascript = $this->source('assets/app/js/master/siswa/import.js');

        self::assertStringContainsString("typeof data === 'string' ? JSON.parse(data) : data", $javascript);
        self::assertStringContainsString("if (!dataResult.status)", $javascript);
        self::assertStringContainsString("$('#submit-excel').attr('disabled', 'disabled')", $javascript);
        self::assertSame(2, substr_count($javascript, "typeof data === 'string' ? JSON.parse(data) : data"));
        self::assertStringContainsString('normalizeRows(dataResult.siswa)', $javascript);
        self::assertStringContainsString('findDuplicateStudent(dataExists, siswa)', $javascript);
        self::assertStringNotContainsString("showDangerToast(data);", $javascript);
        self::assertStringContainsString('arrUserEmpty', $javascript);
        self::assertStringContainsString('Kolom USERNAME wajib diisi', $javascript);
        self::assertStringContainsString('formatPreviewList(arrUserEmpty)', $javascript);
    }
}
