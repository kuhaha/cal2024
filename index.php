<?php 
include "vendor\autoload.php";

use \Symfony\Component\Yaml\Yaml;
use kcal\Day;
use kcal\Month;
use kcal\Calendar;
use kcal\Holiday;

date_default_timezone_set('Asia/Tokyo');
function eol($n = 1)
{
    $eols = array_map(fn($v)=>PHP_EOL, range(1,$n)); 
    return implode('', $eols);
}

header('Content-Type: text/plain; charset=UTF-8');


echo '=== class Day ========', eol(2);

$today = new Day(2024,10,12);
$next = $today->next();
$other = $today->next(2);
$another = $today->next(-5);
$fmt = 'Y-m-d (D)';
echo eol();
echo $today->format($fmt), $today->wday(), eol();
echo $today->leq($next) ? '<=' : '>', eol();
echo $next->format($fmt), $next->wday(), eol();
echo $next->leq($other) ? '<=' : '>', eol();
echo $other->format($fmt), $other->wday(), eol();
echo $other->leq($another) ? '<=' : '>', eol();
echo $another->format($fmt), $another->wday(), eol();

echo eol();
echo $today->format($fmt), eol();
echo $today->sandwich($other) ? 'sandwich' : 'non-sandwich', eol();
echo $other->format($fmt), eol();

echo eol();
echo $today->format($fmt), eol();
echo $today->sandwich($another) ? 'sandwich' : 'non-sandwich', eol();
echo $another->format($fmt), eol();

echo eol();
echo '=== class Month ========', eol(2);

echo eol();
$month = new Month(2024, 9);
print_r($month->day(15)->setAttr('Holiday', '敬老の日'));

echo $month->d2w(15), '<- dow number of 15th day, Sep.', eol();
echo $month->w2d(2, 4), '<- 2nd Thursday of Sep.', eol();

echo eol();
echo '=== class Calendar ========', eol(2);

echo eol();
$year = 2024;

$cal2024 = new Calendar($year, 4);
foreach ($cal2024->months() as $m=>$month){
    echo $month,  eol();
}

echo eol();
$input = file_get_contents("lib\holiday_defs.yaml");
$holiday_defs = Yaml::parse($input);
// print_r($holiday_defs); 

echo '=== class Holiday ========', eol(2);

$cal = new Calendar(1965, 4);
$holiday = new Holiday($cal);
$holiday->parse($holiday_defs);
print_r($holiday->holidays);

$cal = new Calendar(2019, 4);
$holiday = new Holiday($cal);
$holiday->parse($holiday_defs);
print_r($holiday->holidays);

$cal2024 = new Calendar($year, 4);
$holiday2024 = new Holiday($cal2024);
$holiday2024->parse($holiday_defs);
print_r($holiday2024->holidays);

// 祝日休日をカレンダーに追加
$cal2024->setDays('Holiday', $holiday2024->holidays);

$w_offday = 4; //定休日を指定
// $cal2024->setCloseday($w_offday, '定休日');
$date_to_names = [];
foreach ($cal2024->months as $month){
    foreach ($month->w2days($w_offday,[1,2]) as $d){
        $date = sprintf('%d-%02d-%02d', $month->year, $month->month, $d);
        $date_to_names[$date] = '定休日';
    } 
}
print_r($date_to_names);
$cal2024->setDays('Closeday', $date_to_names);


// 臨時休業
$cal2024->month(3)->day(21)->setAttr('Closeday', '臨時休業A');
$cal2024->month(3)->day(23)->setAttr('Closeday', '臨時休業B');
// 指定営業日＞指定定休日＞指定なし（デフォルト＝「営業日」
$cal2024->month(3)->day(20)->setAttr('Openday', '営業日');

// echo '=== class Calendar:: 2025-03 営業日========', eol(2);
// print_r($cal2024->month(3)->openDays());
// 
echo '=== class Calendar:: 2025-03 休業日========', eol(2);
print_r($cal2024->month(3)->closeDays());

echo $cal2024->today(new Day(2025, 3, 20)),'***本日***', eol();
echo $cal2024->nextOpenDay(),' 第1営業日', eol();
echo $cal2024->nextOpenDay(2),' 第2営業日', eol();
echo $cal2024->nextOpenDay(3),' 第3営業日', eol(2);

$other = new Calendar($year, 4);
$other->month(3)->day(24)->setAttr('Closeday', '臨時休業C');
$cal_and = $cal2024->and($other);

// echo '=== class Calendar:: 2025-03 営業日========', eol(2);
// print_r($cal2024->month(3)->openDays());
echo '=== class Calendar:: 2025-03 休業日========', eol(2);
print_r($cal2024->month(3)->closeDays());

echo $cal2024->today(new Day(2025, 3, 20)),'***本日***', eol();
echo $cal_and->nextOpenDay(),' 第1営業日', eol();
echo $cal_and->nextOpenDay(2),' 第2営業日', eol();
echo $cal_and->nextOpenDay(3),' 第3営業日', eol(2);

// echo '=== serialize / cache ========', eol(2);

// $serializedData = serialize($cal2024);
// file_put_contents('cache/cal2024.cache', $serializedData);
// echo $cal2024::class . " Cached!", eol();

// $cachedData = file_get_contents('cache/cal2024.cache');
// $cache_filetime = filemtime('cache/cal2024.cache');
// echo date('Y/m/d H:i:s',$cache_filetime), ' cached', eol();
// // Unserialize the data
// $unserializedData = unserialize($cachedData);
// print_r($unserializedData->month(2));

// echo $cal2024::class . " Loaded!", eol();


