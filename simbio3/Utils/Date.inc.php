<?php
/**
 * Simbio Date class
 * A Collection of static function for doing date arithmatic related operation
 *
 * Copyright (C) 2009  Arie Nugraha (dicarve@yahoo.com)
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301  USA
 *
 */

class Date
{
    /* ALL METHODS DATE ARGUMENT(s) IS ASSUMED USING YYYY-MM-DD format */

    /**
     * Static Method to get next date
     *
     * @param   integer $int_day_num
     * @param   string  $str_start_date
     * @param   string  $str_start_format
     * @return  string
     */
    public static function getNextDate($int_day_num = 1, $str_start_date = '', $str_date_format = 'Y-m-d')
    {
        if ($int_day_num < 1) { return $str_start_date; }
        if (!$str_start_date) {
            return date($str_date_format, mktime(0, 0, 0, intval(date('n')), (intval(date('j'))+$int_day_num), intval(date('Y')) ) );
        } else if ($_parsed_date = @date_parse($str_start_date)) {
            return date($str_date_format, mktime(0, 0, 0, (integer)$_parsed_date['month'], ((integer)$_parsed_date['day'])+$int_day_num, (integer)$_parsed_date['year'] ) );
        } else {
            return null;
        }
    }


    /**
     * Static Method to get previous date
     *
     * @param   integer $int_day_num
     * @param   string  $str_start_date
     * @param   string  $str_date_format
     * @return  string
     */
    public static function getPrevDate($int_day_num = 1, $str_start_date = '', $str_date_format = 'Y-m-d')
    {
        if ($int_day_num < 1) { return $str_start_date; }
        if (!$str_start_date) {
            return date($str_date_format, mktime(0, 0, 0, intval(date('n')), (intval(date('j'))-$int_day_num), intval(date('Y')) ) );
        } else if ($_parsed_date = @date_parse($str_start_date)) {
            return date($str_date_format, mktime(0, 0, 0, (integer)$_parsed_date['month'], ((integer)$_parsed_date['day'])-$int_day_num, (integer)$_parsed_date['year'] ) );
        } else {
            return null;
        }
    }


    /**
     * Static Method to get number of day between dates
     *
     * @param   string  $str_start_date
     * @param   string  $str_end_date
     * @return  integer
     */
    public static function calcDay($str_start_date, $str_end_date)
    {
        $_parsed_start_date = @date_parse($str_start_date);
        $_parsed_end_date = @date_parse($str_end_date);
        $_start_mktime = mktime(0, 0, 0, $_parsed_start_date['month'], $_parsed_start_date['day'], $_parsed_start_date['year']);
        $_end_mktime = mktime(0, 0, 0, $_parsed_end_date['month'], $_parsed_end_date['day'], $_parsed_end_date['year']);
        $_mksec = $_end_mktime-$_start_mktime;
        return abs(intval($_mksec/(3600*24)));
    }


    /**
     * Static Method to get number of holiday between dates
     *
     * @param   string  $str_start_date
     * @param   string  $str_end_date
     * @param   array   $array_holiday_name
     * @param   array   $array_holiday_date
     * @return  integer
     */
    public static function countHolidayBetween($str_start_date, $str_end_date, $array_holiday_dayname = array('Sun'), $array_holiday_date = array())
    {
        $_holiday_count = 0;
        $_one_day = 3600*24;
        $_parsed_start_date = @date_parse($str_start_date);
        $_parsed_end_date = @date_parse($str_end_date);
        $_start_mktime = mktime(0, 0, 0, $_parsed_start_date['month'], $_parsed_start_date['day'], $_parsed_start_date['year']);
        $_end_mktime = mktime(0, 0, 0, $_parsed_end_date['month'], $_parsed_end_date['day'], $_parsed_end_date['year']);
        while ($_start_mktime <= $_end_mktime) {
            if (in_array(date('D', $_start_mktime), $array_holiday_dayname) OR in_array(date('Y-m-d', $_start_mktime), $array_holiday_date)) {
                $_holiday_count += 1;
            }
            $_start_mktime += $_one_day;
        }

        return $_holiday_count;
    }


    /**
     * Static Method to compare dates and return the latest date
     *
     * @param   string  $str_date_to_compares
     * @return  string
     */
    public static function compareDates()
    {
        if (func_num_args() < 1) {
            return null;
        } else if (func_num_args() == 2) {
            // get value of method arguments
            $date1 = func_get_arg(0);
            $date2 = func_get_arg(1);
            // check if $date1 and $date2 is same
            if ($date1 == $date2) {
                return null;
            }
            // get the UNIX timestamp of date
            $_parsed_date1 = date_parse($date1);
            $_parsed_date2 = date_parse($date2);
            $timestamp1 = mktime(0, 0, 0, $_parsed_date1['month'], $_parsed_date1['day'], $_parsed_date1['year']);
            $timestamp2 = mktime(0, 0, 0, $_parsed_date2['month'], $_parsed_date2['day'], $_parsed_date2['year']);
            if ($timestamp1 > $timestamp2) {
                return $date1;
            } else {
                return $date2;
            }
        }

        $func_args = func_get_args();
        $latest = func_get_arg(0);
        foreach ($func_args as $args) {
            $latest = self::compareDates($latest, $args);
        }

        return $latest;
    }


    /**
     * Static Method to get next date that are not holidays
     *
     * @param   string  $str_date
     * @param   array   $array_holiday_dayname
     * @param   array   $array_holiday_date
     * @return  string
     */
    public static function getNextDateNotHoliday($str_date, $array_holiday_dayname = array(), $array_holiday_date = array())
    {
        // if array dayname and date is empty
        if (!$array_holiday_dayname AND !$array_holiday_date) {
            return $str_date;
        }
        // parse date
        list($_year, $_month, $_daym) = explode('-', $str_date);
        // get dayname of $str_date
        $dayname = date('D', mktime(0, 0, 0, $_month, $_daym, $_year));
        // check date array first
        if ($array_holiday_date) {
            $d = false;
            foreach ($array_holiday_date as $_idx=>$_each_date) {
                if (substr($str_date, -5) == substr($_each_date, -5)) {
                    $d = true;
                    unset($array_holiday_date[$_idx]);
                }
            }
            if ($d) {
                $_str_date_next = self::getNextDate(1, $str_date);
                return self::getNextDateNotHoliday($_str_date_next, $array_holiday_dayname, $array_holiday_date);
            } else {
                // check dayname
                if (!in_array($dayname, $array_holiday_dayname)) {
                    return $str_date;
                } else {
                    $_str_date_next = self::getNextDate(1, $str_date);
                    return self::getNextDateNotHoliday($_str_date_next, $array_holiday_dayname, $array_holiday_date);
                }
            }
        } else {
            // check dayname
            if (!in_array($dayname, $array_holiday_dayname)) {
                return $str_date;
            } else {
                $_str_date_next = self::getNextDate(1, $str_date);
                return self::getNextDateNotHoliday($_str_date_next, $array_holiday_dayname);
            }
        }
    }
}
?>
