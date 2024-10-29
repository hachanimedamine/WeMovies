<?php
namespace App\Tests\Unit;

use PHPUnit\Framework\TestCase;

class CalculatorTest extends TestCase
{
public function testAdd()
{
$a = 2;
$b = 3;
$result = $a + $b;

$this->assertEquals(5, $result);
}
}
