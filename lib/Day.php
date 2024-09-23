<?php
namespace kcal;

class Day
{
    public int $year;
    public int $month;
    public int $day; // day of month 日付
    public int $dow; // dow: day of week 曜日 [0..6]
    public array $attrs = [];
    const DAY = [ // 指定営業日 ＞ 指定休業日 ＞ 指定なし（デフォルト「営業日」）※曜日・平日・祝日に関係なく）
        'Weekday' => '平日',
        'Weekend' => '週末', 
        'Holiday' => '祝日', 
        'Openday' => '営業日', 
        'Closeday'=> '休業日',
    ];
    const DOW_JP = ['日', '月', '火', '水', '木', '金', '土',];
    const DOW_EN = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat',];

    public function __construct($year, $month, $day)
    {
        $time = mktime(0, 0, 0, $month, $day, $year);
        $this->year = (int) date('Y', $time);
        $this->month = (int) date('n', $time);
        $this->day = (int) date('d', $time);
        $this->dow = (int) date('w', $time);
        if ($this->dow > 0 and $this->dow < 6){
            $this->attrs['Weekday'] = self::DOW_JP[$this->dow];
        }else{
            $this->attrs['Weekend'] = self::DOW_JP[$this->dow];
        }
    }

    public static function createFromString(string $date): Day
    {
        [$y, $m, $d] = self::ymd($date);
        return new Day($y, $m, $d);
    } 
    
    public static function createFromDatetime(\DateTime $date): Day
    {
        return self::createFromString($date->format('Y-m-d'));
    } 

    public static function ymd(string $date): array
    {
        return array_map(fn($v)=>(int)$v, explode('-', $date));
    }

    public function wday($lang='jp'): string
    {
        if ($lang=='en')
            return self::DOW_EN[$this->dow];

        return self::DOW_JP[$this->dow];
    }

    public function year(): int
    {
        return $this->year;
    }

    public function month(): int
    {
        return  $this->month;
    }

    public function day(): int
    {
        return $this->day;
    }

    public function setAttr($key, $value) : Day
    {
        $this->attrs[$key] = $value;
        return $this;
    }

    /**
     * return the n'th day after $this day
     */
    public function next(int $n = 1): Day
    {
        return new Day($this->year, $this->month, $this->day + $n);
    }

    /**
     * check if $this day is equal to $other day
     */
    public function eq (Day $other): bool
    {
        return ($this->year == $other->year and $this->month == $other->month and $this->day == $other->day);
    }

    /**
     * check if $this day is less than or equal to $other day
     */
    public function leq (Day $other): bool
    {
        if ($this->year < $other->year) return true;
        if ($this->year == $other->year and $this->month < $other->month) return true;
        if ($this->year == $other->year and $this->month == $other->month and $this->day <= $other->day) return true;
        return false;
    }

    /**
     * check if $this day is between $day1 and $day2
     */
    public function between(Day $day1, Day $day2): bool
    {
        return $day1->leq($this) and $this->leq($day2);
    }

    /**
     * check if there is exactly one day between $this and $other day
     */
    public function sandwich(Day $other): mixed
    {
        if  ($other->eq($this->next(2))) return $this->next();
        return false;
    }

    public function format(string $format): string
    {
        $time = $this->d2time();
        return date($format, $time);
    }

    /**
     * Short-hands for `isWeekday()`, `isWeekend()`, `isHoliday()`, ...
     * return or set day attribute if possible, otherwise return false.
     */ 
    public function __call($isname,  $args = array()): mixed
    {
        $name = substr($isname, 0, 2)==='is' ? substr($isname, 2) : '';
        $valid = self::DAY[$name] ?? false;
        if ($valid) {
            if (count($args) > 0) $this->setAttr($name, $args[0]);
            return $this->attrs[$name] ?? false;
        }
        return false;
    } 

    public function isOpen()
    {
        return $this->isOpenday() or !$this->isCloseday();
    }

    public function isClose()
    {
        return !$this->isOpen();
    }


    public function __toString(): string
    {
        return "{$this->year}-{$this->month}-{$this->day}";
    }

    private function d2time(int $year=null, int $month=null, int $day=null)
    {
        $year = $year ?? $this->year;
        $month = $month ?? $this->month;
        $day = $day ?? $this->day;
        return mktime(0, 0, 0, $month, $day, $year);
    }
}
