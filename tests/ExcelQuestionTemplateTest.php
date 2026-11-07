<?php

use PHPUnit\Framework\TestCase;

if (!class_exists('CI_Controller')) {
    class CI_Controller {}
}
require_once dirname(__DIR__) . '/application/controllers/Excelsoal.php';

final class ExcelQuestionTemplateTest extends TestCase
{
    private Excelsoal $controller;

    protected function setUp(): void
    {
        $this->controller = (new ReflectionClass(Excelsoal::class))->newInstanceWithoutConstructor();
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

    private function method(string $name): ReflectionMethod
    {
        return (new ReflectionClass(Excelsoal::class))->getMethod($name);
    }
}
