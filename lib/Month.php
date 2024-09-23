<?php
namespace kcal;

class Month
{
    public int $year;
    public int $month;
    public int $lastday;
    public int $firstday_ofweek;
    public array $days = [];

    public function __construct(int $year, int $month)
    {
        $time = mktime(0, 0, 0, $month, 1, $year);
        $this->year = (int) date('Y', $time);
        $this->month= (int) date('n', $time);
        $this->lastday = date('t', $time);
        $this->firstday_ofweek = date('w', $time);
        for ($d=1; $d <= $this->lastday; $d++){
            $this->days[$d] = new Day($this->year, $this->month, $d);
        }
    }
    /**
     * day(), return n's day of this month
     */
    public function day(int $n) : Day
    {
        return $this->days[$n] ?? null;
    }

    /**
     * d2w(), transform a day of the month to the corresponding day of week  
     * @param int $day,
     * @return int the day of week for the `$day`
     */
    public function d2w(int $day): int
    {
        return ($day -1 + $this->firstday_ofweek) % 7 ;
    }
  
    /**
     * w2d(), transform the n'th day of week to the corresponding day of month
     * @param int $n, number, 1=first, 2=second, ... 
     * @param int $dow, day of week
     * @return int the corresponding day of month
     */
    public function w2d(int $n, int $dow): int
    {
        $n = $dow >= $this->firstday_ofweek ? $n - 1 : $n; 
        $d = $n * 7 + $dow - $this->firstday_ofweek + 1;
        return ($d <= $this->lastday) ? $d : -1; 
    }
    
    /**
     * w2days(): compute days of the same $dow in the month 
     * @param int $dow, day of week
     * @param array[int] $ns, a list of n'th, e.g.,[2,4] for 2nd, 4th 
     * @return array[int], all days of the same $dow in the month 
     */
    public function w2days(int $dow, array $ns=null): array
    {
        $days = [];
        foreach($ns??range(1,5) as $n){
            $day = $this->w2d($n, $dow);
            if ($day > 0) array_push($days, $day);
        }
        return $days;
    }

    public function closeDays(): array
    {
        return array_filter($this->days, fn($x)=>$x->isClose());
    }

    public function openDays(): array
    {
        return array_filter($this->days, fn($x)=>$x->isOpen());
    }


    function __toString(): string
    {
        return "{$this->year}-{$this->month}";
    }
}