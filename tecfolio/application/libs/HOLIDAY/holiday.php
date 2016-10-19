<?php

require_once('holidays/ja/holidays.php');	// 日本語


//祝日名取得ルーチン
class HolidayUtil
{
    function getHolidayNames($from, $to)
    {
        $holidays = array();
 
        $time = $from;
        while (date('Ym',$time) <= date('Ym',$to)) {
            $year = date('Y', $time);
            $month = date('n', $time);
            $holidays[$month] = Holidays::getHolidayNames($year, $month);
            $time = mktime(0,0,0,$month+1,1,$year);
        }

        return $holidays;
    }
}
?>
