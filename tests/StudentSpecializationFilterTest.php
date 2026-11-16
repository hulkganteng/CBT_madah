<?php
declare(strict_types=1);

use PHPUnit\Framework\TestCase;

#[\PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses]
final class StudentSpecializationFilterTest extends TestCase
{
    public function testFiltersAreCombinedForActivePeriod(): void
    {
        if (!class_exists('CI_Controller', false)) {
            eval('class CI_Controller { public $db; }');
        }
        require_once dirname(__DIR__) . '/application/controllers/Peminatansiswa.php';
        $db = new class {
            public $calls = array();
            public function __call($name, $arguments) { $this->calls[] = array($name, $arguments); return $this; }
        };
        $controller = (new ReflectionClass('Peminatansiswa'))->newInstanceWithoutConstructor();
        $controller->db = $db;
        $method = new ReflectionMethod('Peminatansiswa', 'studentQuery');
        $method->setAccessible(true);
        $method->invoke($controller, array('search' => 'Ahmad', 'major' => '4', 'class' => '12',
            'level' => '11', 'program' => 'TAHFIDZ'), 3, 2);
        self::assertContains(array('where', array('s.peminatan_id', 4)), $db->calls);
        self::assertContains(array('where', array('ks.id_kelas', 12)), $db->calls);
        self::assertContains(array('where', array('k.level_id', 11)), $db->calls);
        self::assertContains(array('where', array('s.program_mak', 'TAHFIDZ')), $db->calls);
        self::assertContains(array('like', array('s.nama', 'Ahmad')), $db->calls);
        self::assertContains(array('join', array('kelas_siswa ks', 'ks.id_siswa = s.id_siswa AND ks.id_tp = 3 AND ks.id_smt = 2', 'left')), $db->calls);
    }

    public function testUnassignedFiltersUseNullChecks(): void
    {
        if (!class_exists('CI_Controller', false)) {
            eval('class CI_Controller { public $db; }');
        }
        require_once dirname(__DIR__) . '/application/controllers/Peminatansiswa.php';
        $db = new class {
            public $calls = array();
            public function __call($name, $arguments) { $this->calls[] = array($name, $arguments); return $this; }
        };
        $controller = (new ReflectionClass('Peminatansiswa'))->newInstanceWithoutConstructor();
        $controller->db = $db;
        $method = new ReflectionMethod('Peminatansiswa', 'studentQuery');
        $method->setAccessible(true);
        $method->invoke($controller, array('search' => '', 'major' => 'none', 'class' => 'none', 'level' => '', 'program' => ''), 3, 2);
        self::assertContains(array('where', array('s.peminatan_id IS NULL', null, false)), $db->calls);
        self::assertContains(array('where', array('ks.id_siswa IS NULL', null, false)), $db->calls);
    }
}
