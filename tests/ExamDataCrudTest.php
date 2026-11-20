<?php

use PHPUnit\Framework\TestCase;

if (!class_exists('CI_Model')) {
    #[AllowDynamicProperties]
    class CI_Model
    {
    }
}
require_once dirname(__DIR__) . '/application/models/Cbt_model.php';

final class ExamDataCrudTest extends TestCase
{
    public function testUsedScheduleStopsBulkDeleteBeforeAnyWrite(): void
    {
        $db = new ExamDataCrudDb();
        $db->used = 'cbt_nilai';
        $model = new Cbt_model();
        $model->db = $db;

        self::assertSame('Data sudah digunakan dan tidak dapat dihapus.', $model->deleteUnusedSchedules([10, 11]));
        self::assertSame([10, 11], $db->lockedIds);
        self::assertSame([], $db->deleted);
        self::assertTrue($db->rolledBack);
    }

    public function testUnusedScheduleDeletesOnlyItsOwnDependentRows(): void
    {
        $db = new ExamDataCrudDb();
        $model = new Cbt_model();
        $model->db = $db;

        self::assertNull($model->deleteUnusedSchedules([10, 11]));
        self::assertSame([
            ['cbt_pengawas', 'id_jadwal', [10, 11]],
            ['cbt_jadwal', 'id_jadwal', [10, 11]],
        ], $db->deleted);
        self::assertTrue($db->committed);
    }

    public function testBulkAssignmentRollsBackWhenSecondWriteFails(): void
    {
        $db = new ExamDataCrudDb();
        $model = new Cbt_model();
        $model->db = $db;

        self::assertFalse($model->replaceExamRows(['cbt_sesi_siswa' => [['siswa_id' => 1], ['siswa_id' => 2]]]));
        self::assertSame(2, $db->writes);
        self::assertTrue($db->rolledBack);
        self::assertFalse($db->committed);
    }
}

final class ExamDataCrudDb
{
    public $used;
    public $deleted = [];
    public $lockedIds = [];
    public $writes = 0;
    public $rolledBack = false;
    public $committed = false;
    private $where;

    public function trans_begin() { return true; }
    public function trans_status() { return true; }
    public function trans_rollback() { return $this->rolledBack = true; }
    public function trans_commit() { return $this->committed = true; }
    public function table_exists($table) { return true; }

    public function query($sql, $parameters)
    {
        $this->lockedIds = $parameters[0];
        return $this;
    }

    public function num_rows() { return count($this->lockedIds); }

    public function where_in($column, $ids)
    {
        $this->where = [$column, $ids];
        return $this;
    }

    public function count_all_results($table) { return (int) ($table === $this->used); }

    public function delete($table)
    {
        $this->deleted[] = array_merge([$table], $this->where);
        return true;
    }

    public function replace($table, $row) { return ++$this->writes < 2; }
}
