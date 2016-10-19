<?php
class Holidays
{
    var $names = array();
 
    function getHolidayNames($year, $month)
    {
        $_this = new Holidays();
        $_this->setHolidays($year, $month);
 
        if(!empty($_this->names))
        {
            for($day = 1; $day < date('t',mktime(0,0,0,$month,1,$year)); $day++)
            {
                $time = mktime(0,0,0,$month,$day,$year);
                if (date('w', $time) == 0 )
                {
                    // 日曜以外は振替休日判定不要
                    if(!empty($_this->names[$day]) && empty($_this->names[$day+1]))
                    {
                        // 振替休日施行
                        if ($time >= mktime(0,0,0,4,12,1973))
                        { 
                            $_this->names[$day+1] = "振替休日";
                        }
                    }
                }
            }
        }
        return $_this->names;
    }
 
 
    private function setHolidays($year, $month)
    {
        $mon2 = $this->secondMonday($year, $month);
        $mon3 = $this->thirdMonday($year, $month);
 
        if(($year <= 1948) && ($month <= 7))
        {
            return $this->names;   // 祝日法施行(1948/7/20 )以前
        }
        else
        {
 
            switch ( $month )
            {
                case 1:
                    $this->names[1] = "元日";
                    if ( $year >= 2000 ) {
                        $this->names[$mon2] = "成人の日";
                    } else {
                        $this->names[15] = "成人の日";
                    }
                    break;
 
                case 2:
                    if ( $year >= 1967 ) {
                        $this->names[11] = "建国記念の日";
                    }
                    if ( $year == 1989 ) {
                        $this->names[24] = "昭和天皇の大喪の礼";
                    }
                    break;
 
                case 3:
                    $day = $this->prvDayOfSpringEquinox( $year );
                    if ( $day ) {
                        $this->names[$day] = "春分の日";
                    }
                    break;
 
                case 4:
                    if ($year >= 2007) {
                        $this->names[29] = "昭和の日";
                    } else {
                        if ( $year >= 1989 ) {
                            $this->names[29] = "みどりの日";
                        } else {
                            $this->names[29] = "天皇誕生日";
                        }
                    }
                    if ( $year == 1959 ) {
                        $this->names[10] = "皇太子明仁親王の結婚の儀";  // ( =1959/4/10 )
                    }
                    break;
 
                case 5:
                    $this->names[3] = "憲法記念日";
                    if ($year >= 2007) {
                        $this->names[4] = "みどりの日";
                    } else {
                        if ($year >= 1986) {
                            if (date('w', mktime(0,0,0,5,4,$year)) > 1) {
                                // 5/4が日曜日は『只の日曜』､月曜日は『憲法記念日の振替休日』(～2006年)
                                $this->names[4] = "国民の休日";
                            }
                        }
                    }
                    $this->names[5] = "こどもの日";
                    if ($year >= 2007) {
                        if (date('w',mktime(0,0,0,5,6,$year)) == 2
                            || date('w',mktime(0,0,0,5,6,$year)) == 3) {
                            $this->names[6] = "振替休日";    // [5/3,5/4が日曜]ケースのみ、ここで判定
                        }
                    }
                    break;
 
                case 6:
                    if ( $year == 1993 ) {
                        $this->names[9] = "皇太子徳仁親王の結婚の儀";
                    }
                    break;
 
                case 7:
                    if ( $year >= 2003 ) {
                        $this->names[$mon3] = "海の日";
                    } else {
                        if ( $year >= 1996 ) {
                                $this->names[20] = "海の日";
                        }
                    }
                    break;
 
                case 9:
                    //第３月曜日( 15～21 )と秋分日(22～24 )が重なる事はない
                    $day = $this->prvDayOfAutumnEquinox( $year );
                    if ( $day ) {
                        $this->names[$day] = "秋分の日";
                    }
                    if ( $year >= 2003 ) {
                        $this->names[$mon3] = "敬老の日";
                        if (date('w',mktime(0,0,0,9,$day,$year)) == 3) {
                            $this->names[$day-1] = "国民の休日";
                        }
                    } else {
                        if ( $year >= 1966 ) {
                            $this->names[15] = "敬老の日";
                        }
                    }
                    break;
 
                case 10:
                    if ( $year >= 2000 ) {
                        $this->names[$mon2] = "体育の日";
                    } else {
                        if ( $year >= 1966 ) {
                            $this->names[10] = "体育の日";
                        }
                    }
                    break;
 
                case 11:
                    $this->names[3] = "文化の日";
                    $this->names[23] = "勤労感謝の日";
                    if ( $year == 1990 ) {
                        $this->names[12] = "即位礼正殿の儀";
                    }
                    break;
 
                case 12:
                    if ( $year >= 1989 ) {
                        $this->names[23] = "天皇誕生日";
                    }
                    break;
            }
            return $this->names;
        }
    }
 
 
    private function secondMonday($year, $month)
    {
        $w = date('N', mktime(0,0,0,$month,1,$year));
        switch($w)
        {
            case 1 :
                return 8;
            default :
                return 14 - ($w - 2);  // 9～14
        }
    }
 
 
    private function thirdMonday($year, $month)
    {
        $w = date('N', mktime(0,0,0,$month,1,$year));
        switch($w)
        {
            case 1 :
                return 15;
            default :
                return 21 - ($w - 2);  // 16～21
        }
    }
 
 
    // 春分/秋分日の略算式は
    // 『海上保安庁水路部 暦計算研究会編 新こよみ便利帳』
    // で紹介されている式です。
    private function prvDayOfSpringEquinox($year)
    {
        $springEquinox_ret = false;
        if ($year <= 1947)
        {
            $springEquinox_ret = false; // 祝日法施行前
        }
        else
        {
            if ($year <= 1979)
            {
                $springEquinox_ret = intval(20.8357 + (0.242194 * ($year - 1980)) - intval(($year - 1983) / 4));
            }
            else
            {
                if ($year <= 2099)
                {
                    $springEquinox_ret = intval(20.8431 + (0.242194 * ($year - 1980)) - intval(($year - 1980) / 4));
                }
                else
                {
                    if ($year <= 2150)
                    {
                        $springEquinox_ret = intval(21.851 + (0.242194 * ($year - 1980)) - intval(($year - 1980) / 4));
                    }
                    else
                    {
                        $springEquinox_ret = false; // 2151年以降は略算式が無いので不明
                    }
                }
            }
        }
        return $springEquinox_ret;
    }
 
    private function prvDayOfAutumnEquinox($year)
    {
        $autumnEquinox_ret = false;
        if ($year <= 1947)
        {
            $autumnEquinox_ret = false; // 祝日法施行前
        }
        else
        {
            if ($year <= 1979)
            {
                $autumnEquinox_ret = intval(23.2588 + (0.242194 * ($year - 1980)) - intval(($year - 1983) / 4));
            }
            else
            {
                if ($year <= 2099)
                {
                    $autumnEquinox_ret = intval(23.2488 + (0.242194 * ($year - 1980)) - intval(($year - 1980) / 4));
                }
                else
                {
                    if ($year <= 2150)
                    {
                        $autumnEquinox_ret = intval(24.2488 + (0.242194 * ($year - 1980)) - (int) (($year - 1980) / 4));
                    }
                    else
                    {
                        $autumnEquinox_ret = false; // 2151年以降は略算式が無いので不明
                    }
                }
            }
        }
        return $autumnEquinox_ret;
    }
}

?>
