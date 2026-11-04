<?php

use PHPUnit\Framework\TestCase;

final class QuestionImportTest extends TestCase
{
    private Question_import $import;

    protected function setUp(): void
    {
        $this->import = new Question_import();
    }

    public function testValidMultipleChoice(): void
    {
        $question = $this->question('Pilihan Ganda', 'A');
        self::assertSame(array(), $this->import->validateQuestion($question));
        self::assertSame(array('A'), $question['correct_answers']);
    }

    public function testAnswerVariantsAreNormalized(): void
    {
        self::assertSame(array('A'), $this->question('PG', 'a')['correct_answers']);
        self::assertSame(array('A'), $this->question('PG', 'A.')['correct_answers']);
    }

    public function testComplexAnswersAreNormalized(): void
    {
        self::assertSame(array('A', 'C'), $this->question('PG Kompleks', 'A,C')['correct_answers']);
    }

    public function testLegacyColumnsAndTypeCodeRemainSupported(): void
    {
        $question = $this->import->normalizeQuestion(array(
            'jenis' => '1',
            'soal' => 'Dua ditambah dua adalah...',
            'opsi_a' => '4',
            'opsi_b' => '5',
            'kunci' => 'a',
        ));

        self::assertSame('pg', $question['type']);
        self::assertSame(array('A'), $question['correct_answers']);
        self::assertSame(array(), $this->import->validateQuestion($question));
    }

    public function testTrueFalseUsesExistingStorageFormat(): void
    {
        $question = $this->import->normalizeQuestion(array(
            'jenis' => 'Benar/Salah',
            'pertanyaan' => 'Air membeku pada suhu nol derajat Celsius.',
            'jawaban' => 'Benar',
        ));

        self::assertSame(array(), $this->import->validateQuestion($question));
        self::assertSame('b', $this->import->toDatabaseRow($question, 1)['jawaban']);
    }

    public function testUnavailableAnswerIsInvalid(): void
    {
        $errors = $this->import->validateQuestion($this->question('PG', 'E'));
        self::assertContains('Jawaban benar tidak ditemukan pada pilihan yang tersedia.', $errors);
    }

    public function testEmptyQuestionIsInvalid(): void
    {
        $question = $this->question('PG', 'A');
        $question['question'] = '';
        self::assertContains('Pertanyaan belum diisi.', $this->import->validateQuestion($question));
    }

    public function testUnknownTypeIsInvalid(): void
    {
        $question = $this->question('Essay', 'A');
        self::assertStringContainsString('Jenis soal tidak dikenali', implode(' ', $this->import->validateQuestion($question)));
    }

    public function testPartialSetKeepsEightValidItems(): void
    {
        $questions = array();
        for ($i = 0; $i < 8; $i++) {
            $questions[] = $this->question('PG', 'A');
        }
        $questions[] = $this->question('PG', 'E');
        $invalid = $this->question('PG', 'A');
        $invalid['question'] = '';
        $questions[] = $invalid;

        $valid = array_filter($questions, fn ($question) => !$this->import->validateQuestion($question));
        self::assertCount(8, $valid);
        self::assertCount(2, array_diff_key($questions, $valid));
    }

    private function question(string $type, string $answer): array
    {
        return $this->import->normalizeQuestion(array(
            'jenis' => $type,
            'pertanyaan' => 'Ibukota Indonesia adalah...',
            'a' => 'Jakarta',
            'b' => 'Bandung',
            'c' => 'Surabaya',
            'd' => 'Semarang',
            'jawaban' => $answer,
        ));
    }
}
