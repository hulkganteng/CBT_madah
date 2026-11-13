<?php

class Student_exam_filter
{
    public static function matches($studentMajor, $studentProgram, $bankMajor, $bankProgram)
    {
        $major = (int) $bankMajor;
        if ($major > 0 && (!$studentMajor || (int) $studentMajor !== $major)) {
            return array(FALSE, 'Peminatan siswa tidak sesuai dengan ujian.');
        }
        $program = strtoupper(trim((string) $bankProgram));
        if ($program !== '' && strtoupper(trim((string) $studentProgram)) !== $program) {
            return array(FALSE, 'Program MAK siswa tidak sesuai dengan ujian.');
        }
        return array(TRUE, '');
    }
}
