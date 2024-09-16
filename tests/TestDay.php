<?php
namespace kcal;

include 'vendor/autoload.php';

use PHPUnit\Framework\TestCase;

class TestDay extends TestCase
{
    public function testFormat() {
        $today = new Day(2024, 10, 9);
        $day1 = new Day(2024, 10, 8);
        $day2 = new Day(2024, 10, 10);

        $this->assertEquals('2024-10-09', $today->format('Y-m-d')); 
        $this->assertEquals('2024-10-09(Wed)', $today->format('Y-m-d(D)')); 

        $this->assertEquals('水', $today->wday());
        $this->assertEquals('火', $day1->wday());
        $this->assertEquals('木', $day2->wday());
 
    }

    public function testComparison(){
        $today = new Day(2024, 10, 12);
        $day1 = new Day(2024, 10, 11);
        $day2 = new Day(2024, 10, 12);
        $day3 = new Day(2024, 10, 13);

        $this->assertTrue($today->eq($day2));
        $this->assertTrue($day2->eq($today));
        $this->assertTrue($today->next(-1)->eq($day1));
        $this->assertTrue($today->next()->eq($day3));
        $this->assertTrue($day1->next(2)->eq($day3));

        $this->assertTrue($today->leq($day2));
        $this->assertTrue($day2->leq($today));
        $this->assertTrue($day1->leq($day2));
        $this->assertTrue($day2->leq($day3));
        $this->assertTrue($today->leq($today->next()));

        $this->assertTrue($day2->between($day1, $day3));
        $this->assertTrue($day1->sandwich($day3)->eq($day2));

    }

}

