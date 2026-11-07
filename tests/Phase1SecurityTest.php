<?php

use PHPUnit\Framework\TestCase;

if (!class_exists('CI_Model')) {
    #[AllowDynamicProperties]
    class CI_Model
    {
    }
}
require_once dirname(__DIR__) . '/application/models/Cbt_model.php';

final class Phase1SecurityTest extends TestCase
{
    public function testValidImageIsAcceptedAndReencoded(): void
    {
        $directory = $this->temporaryDirectory();
        try {
            $png = $this->pngBytes();
            $filename = Question_image::storeEncoded(
                'data:image/png;base64,' . base64_encode($png . '<?php echo "pwn"; ?>'),
                'test',
                $directory
            );

            self::assertNotNull($filename);
            self::assertMatchesRegularExpression('/^test_[a-f0-9]{24}\.png$/', $filename);
            $saved = file_get_contents($directory . DIRECTORY_SEPARATOR . $filename);
            self::assertStringNotContainsString('<?php', $saved);
            self::assertSame(IMAGETYPE_PNG, getimagesizefromstring($saved)[2]);
        } finally {
            $this->removeDirectory($directory);
        }
    }

    public function testPhpMimeAndNonImagePayloadsAreRejected(): void
    {
        $directory = $this->temporaryDirectory();
        try {
            self::assertNull(Question_image::storeEncoded(
                'data:image/php;base64,' . base64_encode('<?php echo "pwn"; ?>'),
                'test',
                $directory
            ));
            self::assertNull(Question_image::storeEncoded(
                'data:image/png;base64,' . base64_encode('not an image'),
                'test',
                $directory
            ));
            self::assertNull(Question_image::storeEncoded(
                'data:text/html;base64,' . base64_encode('<script>alert(1)</script>'),
                'test',
                $directory
            ));
            self::assertSame(array(), glob($directory . DIRECTORY_SEPARATOR . '*') ?: array());
        } finally {
            $this->removeDirectory($directory);
        }
    }

    public function testDurationLookupRejectsInjectionAndUsesBoundArray(): void
    {
        $db = new Phase1FakeDb();
        $model = new Cbt_model();
        $model->db = $db;

        self::assertNull($model->getDurasiSiswa('1 OR 1=1'));
        self::assertNull($model->getDurasiSiswa('123abc'));
        self::assertNull($model->getDurasiSiswa(''));

        $row = $model->getDurasiSiswa('123');
        self::assertSame(123, $row->id_durasi);
        self::assertSame(
            array('cbt_durasi_siswa', array('id_durasi' => 123)),
            $db->lastCall
        );
    }

    public function testSubmitRequiresSuccessfulGrading(): void
    {
        $source = file_get_contents(dirname(__DIR__) . '/application/controllers/Siswa.php');
        self::assertStringContainsString('FOR UPDATE', $source);
        self::assertStringContainsString('trans_rollback()', $source);
        self::assertStringContainsString("'status_nilai'", $source);
        self::assertStringContainsString('!$nilai || !$update', $source);

        $hook = file_get_contents(dirname(__DIR__) . '/application/hooks/Exam_security_hook.php');
        self::assertStringContainsString('empty($payload->status_nilai)', $hook);
    }

    public function testUploadDirectoriesNeverExecuteScripts(): void
    {
        $htaccess = dirname(__DIR__) . '/uploads/.htaccess';
        self::assertFileExists($htaccess);
        $rules = file_get_contents($htaccess);
        self::assertStringContainsString('Require all denied', $rules);
        self::assertStringContainsString('RemoveHandler', $rules);

        $source = file_get_contents(dirname(__DIR__) . '/application/controllers/Cbtbanksoal.php');
        self::assertStringNotContainsString(
            'file_put_contents("./uploads/bank_soal/" . $output_file, base64_decode($data))',
            $source
        );
        self::assertStringNotContainsString(
            "file_put_contents('./uploads/bank_soal/' . \$name, base64_decode(\$src))",
            $source
        );
    }

    private function pngBytes(): string
    {
        $image = imagecreatetruecolor(2, 2);
        imagefill($image, 0, 0, imagecolorallocate($image, 10, 120, 200));
        ob_start();
        imagepng($image);
        $bytes = ob_get_clean();
        imagedestroy($image);
        return $bytes;
    }

    private function temporaryDirectory(): string
    {
        $directory = sys_get_temp_dir() . DIRECTORY_SEPARATOR . uniqid('garuda_image_', true);
        mkdir($directory, 0777, true);
        return $directory;
    }

    private function removeDirectory(string $directory): void
    {
        foreach (glob($directory . DIRECTORY_SEPARATOR . '*') ?: array() as $file) {
            @unlink($file);
        }
        @rmdir($directory);
    }
}

class Phase1FakeDb
{
    public $lastCall;

    public function get_where($table, $where)
    {
        $this->lastCall = array($table, $where);
        return new Phase1FakeResult();
    }
}

class Phase1FakeResult
{
    public function row()
    {
        return (object) array('id_durasi' => 123);
    }
}