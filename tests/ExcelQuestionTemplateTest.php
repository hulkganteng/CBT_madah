<?php

use PHPUnit\Framework\TestCase;

if (!class_exists('CI_Controller')) {
    class CI_Controller {}
}
require_once dirname(__DIR__) . '/application/controllers/Excelsoal.php';

class ExcelsoalForPlanTest extends Excelsoal
{
    public $question_import;
}

final class ExcelQuestionTemplateTest extends TestCase
{
    private Excelsoal $controller;

    protected function setUp(): void
    {
        $this->controller = (new ReflectionClass(ExcelsoalForPlanTest::class))->newInstanceWithoutConstructor();
        $this->controller->question_import = new Question_import();
    }

    public function testBankTemplateRowsFollowEachQuestionQuota(): void
    {
        $bank = (object) array('tampil_pg' => 2, 'tampil_kompleks' => 1, 'tampil_bs' => 2);
        $rows = $this->method('templateRows')->invoke($this->controller, $bank);

        self::assertCount(5, $rows);
        self::assertSame(array('Pilihan Ganda', 'Pilihan Ganda', 'Pilihan Ganda Kompleks', 'Benar/Salah', 'Benar/Salah'), array_column($rows, 1));
    }

    public function testComplexAnswerDropdownContainsAllCombinations(): void
    {
        $answers = $this->method('complexAnswerCombinations')->invoke($this->controller, array('A', 'B', 'C', 'D', 'E'));

        self::assertCount(31, $answers);
        self::assertContains('A,C', $answers);
        self::assertContains('A,B,C,D,E', $answers);
    }

    public function testAppendSkipsIdenticalQuestionsAndPreservesDistinctAnswers(): void
    {
        $first = $this->question('Choose the right answer.');
        $another = $this->question('Choose the right answer.', 'B');
        $stored = $this->controller->question_import->toDatabaseRow($first, 4) + ['id_soal' => 20];
        $plan = $this->plan([$first, $another, $another], [$stored], [1 => 1, 2 => 0, 6 => 0], false);

        self::assertSame([1, 2, 3], $plan['itemIds']);
        self::assertSame(2, $plan['duplicates']);
        self::assertSame(0, $plan['activated']);
        self::assertCount(1, $plan['rows']);
        self::assertSame(5, $plan['rows'][0]['nomor_soal']);
        self::assertSame('b', $plan['rows'][0]['jawaban']);
        self::assertSame('0', $plan['rows'][0]['tampilkan']);
    }

    public function testReplacingThePackageCanReuseAnIdenticalReserveQuestion(): void
    {
        $old = $this->question('Old question.');
        $reserve = $this->question('Question from the replacement file.');
        $existing = [
            $this->controller->question_import->toDatabaseRow($old, 1) + ['id_soal' => 20],
            $this->controller->question_import->toDatabaseRow($reserve, 2, false) + ['id_soal' => 21],
        ];
        $plan = $this->plan([$reserve, $reserve], $existing, [1 => 1, 2 => 0, 6 => 0], true);

        self::assertSame([], $plan['rows']);
        self::assertSame([21], $plan['reusedIds']);
        self::assertSame([1 => 1, 2 => 0, 6 => 0], $plan['selectedCounts']);
        self::assertSame(1, $plan['activated']);
    }

    private function question(string $text, string $answer = 'A'): array
    {
        return $this->controller->question_import->normalizeQuestion([
            'jenis' => 1, 'soal' => $text, 'a' => 'First option', 'b' => 'Second option', 'jawaban' => $answer,
        ]);
    }

    private function plan(array $questions, array $existing, array $quota, bool $activate): array
    {
        $items = [];
        foreach ($questions as $index => $question) {
            $items[] = (object) ['id' => $index + 1, 'normalized_data' => json_encode($question)];
        }
        return $this->method('planImport')->invoke($this->controller, $items, $existing, $quota, $activate);
    }

    private function method(string $name): ReflectionMethod
    {
        return (new ReflectionClass(Excelsoal::class))->getMethod($name);
    }
}
