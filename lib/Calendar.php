<?php
namespace kcal;

class Calendar
{
    public int $year;
    public int $firstmonth;
    public Day $firstday;
    public Day $lastday; 
    public Day $today;
    public array $months;

    public function __construct(int $year, int $firstmonth=1)
    {
        $this->year = $year;
        $this->firstmonth = $firstmonth;
        $this->firstday = new Day($year, $firstmonth, 1);
        $this->lastday = new Day($year, $firstmonth+12, 31);
        $this->today = new Day($year, $firstmonth, 1);
        
        foreach (range($firstmonth, $firstmonth + 11) as $m){
            $n = $m > 12 ? $m - 12 : $m; // 13 => 1
            $this->months[$n] = new Month($year, $m);
        }      
    }

    public function months(): array
    {
        return $this->months;
    }

    public function month($m): Month
    {
        return $this->months[$m] ?? null;
    }

    /**
     * set or return $today if argument is not given
     */
    public function today(Day $day = null): Day
    {
        $this->today = $day ?? $this->today;
        return $this->today;
    }

    public function setCloseday(int $w, string $name="定休日")
    {
        foreach ($this->months() as $month){
            foreach ($month->w2days($w) as $d){
                $month->day($d)->setAttr('Closeday', $name);
            }
        }
    }

    public function setDays(string $key, array $date_to_names): self
    {
        foreach ($date_to_names as $date=>$name){
            $day = Day::createFromString($date);
            if ($this->validate($day)){
                [$m, $d] = [$day->month(), $day->day()];
                $this->month($m)->day($d)->setAttr($key, $name);
            }            
        }
        return $this;
    }


    /**
     * return the n'th open day after today 
     * @param int $n
     * @return Day
     */
    public function nextOpenDay($n = 1): Day
    {
        $this_day = $this->today();
        for ($i = 0; $i < $n; $i++){
            do {      
                $next_day = $this_day->next();
                if ($this->validate($next_day) == false){
                    throw new \Exception('年度範囲を超えています。');
                }
                $m = $next_day->month();
                $d = $next_day->day();    
                $this_day = $this->month($m)->day($d);                
            } while (!$this_day->isOpenday() and $this_day->isCloseday());
        }
        return $this_day;       
    } 

    /**
     * return a new calendar object `$this && other` if calendars are compatible,
     *  where  `&&` is defined as follows:
     * - opendays are non-closed days in both $this and $other calendar
     * - closedays are closed days in either $this or $other calendar
     * return false otherwise
     */
    public function and(Calendar $other): Calendar | bool
    {
        if ($this->year != $other->year) return false;
        if ($this->firstmonth != $other->firstmonth) return false;
        $cal = clone $this;
        foreach ($cal->months() as $month){
            foreach ($month->days as $day){
                [$m, $d] = [$day->month(), $day->day()];
                $other_day = $other->month($m)->day($d);
                $dayname = $other_day->isCloseday();
                if ($dayname) $day->isCloseday($dayname);
            }
        }
        return $cal;
    }

    /**
     * validate if the day is in this year
     */
    private function validate(Day $day) : bool
    {
        return $day->between($this->firstday, $this->lastday);
    }
}
