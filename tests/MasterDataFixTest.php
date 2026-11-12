<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

final class MasterDataFixTest extends TestCase
{
    private function source(string $path): string
    {
        return (string) file_get_contents(dirname(__DIR__) . '/' . $path);
    }

    public function testMasterCreateEndpointsValidateRequiredAndDuplicateValues(): void
    {
        foreach (array('Datatahun', 'Datamapel', 'Datajurusan', 'Dataekstra') as $controller) {
            $source = $this->source("application/controllers/{$controller}.php");
            self::assertStringContainsString("['status' => false, 'message' =>", $source, $controller);
            self::assertStringContainsString('count_all_results(', $source, $controller);
        }
    }

    public function testClassSaveIsValidatedAndTransactional(): void
    {
        $source = $this->source('application/controllers/Datakelas.php');
        self::assertStringContainsString("foreach (['nama_kelas', 'kode_kelas', 'level_id']", $source);
        self::assertStringContainsString('$this->db->trans_start();', $source);
        self::assertStringContainsString('$this->db->trans_complete();', $source);
    }

    public function testMajorCreateReturnsTheDatabaseResult(): void
    {
        self::assertStringContainsString(
            "\$data['status'] = (bool) \$created",
            $this->source('application/controllers/Datajurusan.php')
        );
    }

    public function testSubjectImportSuppliesRequiredFieldsAndUsesStableFileEvents(): void
    {
        $controller = $this->source('application/controllers/Datamapel.php');
        $javascript = $this->source('assets/app/js/master/mapel/import.js');

        self::assertStringContainsString("'urutan' => \$maxUrutan", $controller);
        self::assertStringContainsString("'jenjang' => \$jenjang", $controller);
        self::assertStringContainsString("insert_batch('master_mapel', \$mapel)", $controller);
        self::assertStringContainsString('$ext = strtolower($this->upload->data(', $controller);
        self::assertStringContainsString(".on('change'", $javascript);
        self::assertStringNotContainsString('DOMSubtreeModified', $javascript);
    }

    public function testBrokenExtraImportFallsBackToTheWorkingPage(): void
    {
        self::assertStringContainsString(
            "redirect('dataekstra');",
            $this->source('application/controllers/Dataekstra.php')
        );
    }

    public function testTeacherPasswordsAreNotRenderedOrReturned(): void
    {
        $add = $this->source('application/views/master/guru/add.php');
        $edit = $this->source('application/views/users/guru/edit.php');
        $controller = $this->source('application/controllers/Userguru.php');

        self::assertStringContainsString('id="password" type="password"', $add);
        self::assertStringNotContainsString('$guru->password', $edit);
        self::assertStringContainsString("unset(\$data['pass'])", $controller);
    }
}
