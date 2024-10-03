<?php 
include "vendor\autoload.php";

use \Symfony\Component\Yaml\Yaml;
use kcal\Day;
use kcal\Month;
use kcal\Calendar;
use kcal\Holiday;
use kcal\DerivedHoliday;

date_default_timezone_set('Asia/Tokyo');
function eol($n = 1)
{
    $eols = array_map(fn($v)=>PHP_EOL, range(1,$n)); 
    return implode('', $eols);
}
$input = file_get_contents("lib\holiday_defs.yaml");
$holiday_defs = Yaml::parse($input);

header('Content-Type: text/plain; charset=UTF-8');
for ($year = 2018; $year < 2032; $year++){
    echo "===={$year}====", eol(2);
    $cachefile = "cache/{$year}.cache";
    $holidays = DerivedHoliday::get($year);
    if ($holidays){
        echo "====Derived====", eol();
        print_r($holidays);
    }else if (is_file($cachefile)){
        $cached = file_get_contents($cachefile);
        $holidays = unserialize($cached);
        echo "====Cached====", eol();
        print_r($holidays);
    }
    else{

        $cal = new Calendar($year, 4);
        $holiday = new Holiday($cal);
        $holiday->parse($holiday_defs);
        echo "====Computed====", eol();
        print_r($holiday->holidays);
        $serialized = serialize($holiday->holidays);
        file_put_contents($cachefile, $serialized);
    }
}
