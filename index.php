<?php 
include "vendor\autoload.php";

use \Symfony\Component\Yaml\Yaml;
use kcal\Day;
use kcal\Month;
use kcal\Calendar;
use kcal\Holiday;

header('Content-Type: text/plain; charset=UTF-8');

echo '=== class Day ========', PHP_EOL;

$today = new Day(2024,10,12);
$next = $today->next();
$other = $today->next(2);
$another = $today->next(-5);
$fmt = 'Y-m-d (D)';
echo PHP_EOL;
echo $today->format($fmt), $today->wday(), PHP_EOL;
echo $today->leq($next) ? '<=' : '>', PHP_EOL;
echo $next->format($fmt), $next->wday(), PHP_EOL;
echo $next->leq($other) ? '<=' : '>', PHP_EOL;
echo $other->format($fmt), $other->wday(), PHP_EOL;
echo $other->leq($another) ? '<=' : '>', PHP_EOL;
echo $another->format($fmt), $another->wday(), PHP_EOL;

echo PHP_EOL;
echo $today->format($fmt), PHP_EOL;
echo $today->sandwich($other) ? 'sandwich' : 'non-sandwich', PHP_EOL;
echo $other->format($fmt), PHP_EOL;

echo PHP_EOL;
echo $today->format($fmt), PHP_EOL;
echo $today->sandwich($another) ? 'sandwich' : 'non-sandwich', PHP_EOL;
echo $another->format($fmt), PHP_EOL;

echo PHP_EOL;
echo '=== class Month ========', PHP_EOL;

echo PHP_EOL;
$month = new Month(2024, 9);
print_r($month->day(15)->setAttr('Holiday', '敬老の日'));

echo $month->d2w(15), '<- dow number of 15th day, Sep.', PHP_EOL;
echo $month->w2d(2, 4), '<- 2nd Thursday of Sep.', PHP_EOL;

echo PHP_EOL;
echo '=== class Calendar ========', PHP_EOL;

echo PHP_EOL;
$year = 2024;

$cal2024 = new Calendar($year, 4);
foreach ($cal2024->months() as $m=>$month){
    echo $month,  PHP_EOL;
}

echo PHP_EOL;
$input = file_get_contents("lib\holiday_defs.yaml");
$holiday_defs = Yaml::parse($input);
// print_r($holiday_defs); 

echo '=== class Holiday ========', PHP_EOL;

$cal = new Holiday(1965, 4);
$cal->parse($holiday_defs);
print_r($cal->holidays);

$cal = new Holiday(2019, 4);
$cal->parse($holiday_defs);
print_r($cal->holidays);

$hcal2024 = new Holiday($year, 4);
$hcal2024->parse($holiday_defs);
print_r($hcal2024->holidays);
// print_r($hcal2024->month(5));

$w_offday = 2; //定休日：4-毎週木曜日
$cal2024->setCloseday($w_offday, '店休日');
// 臨時休業
$cal2024->month(3)->day(21)->setAttr('Closeday', '臨時休業A');
$cal2024->month(3)->day(23)->setAttr('Closeday', '臨時休業B');
// 指定営業日＞指定定休日＞指定なし（デフォルト＝「営業日」
$cal2024->month(3)->day(20)->setAttr('Openday', '営業日');

print_r($cal2024->month(3));

echo $cal2024->today(new Day(2025, 3, 19)), PHP_EOL;

echo $cal2024->nextOpenDay(), PHP_EOL;
echo $cal2024->nextOpenDay(2), PHP_EOL;
echo $cal2024->nextOpenDay(3), PHP_EOL;

echo '=== class Holiday ::and()========', PHP_EOL;

$other = new Calendar($year, 4);
$other->month(3)->day(24)->setAttr('Closeday', '臨時休業C');

$cal_and = $cal2024->and($other);
// print_r($cal_and->month(3));

echo $cal2024->today(new Day(2025, 3, 19)), PHP_EOL;
echo $cal_and->nextOpenDay(), PHP_EOL;
echo $cal_and->nextOpenDay(2), PHP_EOL;
echo $cal_and->nextOpenDay(3), PHP_EOL;