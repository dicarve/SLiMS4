<?php
/**
 * Paging Generator class
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

class Paging
{
    /**
     * Static method to create paging list
     *
     * @param   integer $int_all_recs_num
     * @param   integer $int_recs_each_page
     * @param   integer $int_pages_each_set
     * @param   string  $str_fragment
     * @return  array
     */
    public static function build($int_all_recs_num, $int_recs_each_page, $int_pages_each_set = 10, $str_fragment = '#')
    {
        // check for wrong arguments
        if ($int_recs_each_page > $int_all_recs_num) {
            return;
        }
        // total number of pages
        $_num_page_total = ceil($int_all_recs_num/$int_recs_each_page);
        if ($_num_page_total < 2) {
            return;
        }

        // total number of pager set
        $_pager_set_num = ceil($_num_page_total/$int_pages_each_set);
        // check the current page number
        if (isset($_GET['page']) AND $_GET['page'] > 1) {
            $_page = (integer)$_GET['page'];
        } else {$_page = 1;}

        // check the query string
        if (isset($_SERVER['QUERY_STRING']) AND !empty($_SERVER['QUERY_STRING'])) {
            parse_str($_SERVER['QUERY_STRING'], $arr_query_var);
            // rebuild query str without "page" var
            $_query_str_page = '';
            foreach ($arr_query_var as $varname => $varvalue) {
                $varvalue = urlencode($varvalue);
                if ($varname != 'page') {
                    $_query_str_page .= $varname.'='.$varvalue.'&';
                }
            }
            // append "page" var at the end
            $_query_str_page .= 'page=';
            // create full URL
            $_current_page = $_SERVER['PHP_SELF'].'?'.$_query_str_page;
        } else {
            $_current_page = $_SERVER['PHP_SELF'].'?page=';
        }

        // init the return string
        $_stopper = 1;
        // count the offset of paging
        if (($_page > 5) AND ($_page%5 == 1)) {
            $_lowest = $_page-5;
            if ($_page == $_lowest) {
                $_pager_offset = $_lowest;
            } else {
                $_pager_offset = $_page;
            }
        } else if (($_page > 5) AND (($_page*2)%5 == 0)) {
            $_lowest = $_page-5;
            $_pager_offset = $_lowest+1;
        } else if (($_page > 5) AND ($_page%5 > 1)) {
            $_rest = $_page%5;
            $_pager_offset = $_page-($_rest-1);
        } else {
            $_pager_offset = 1;
        }

        // init return array
        $_pager = array();

        // First page link
        $_first = __('First Page');
        // Previous page link
        $_prev = __('Previous Page');

        if ($_page > 1) {
            $_pager[$_first] = $_current_page.'1'.$str_fragment;
            $_pager[$_prev] = $_current_page.($_page-1).$str_fragment;
        }
        for ($p = $_pager_offset; ($p <= $_num_page_total) AND ($_stopper < $int_pages_each_set+1); $p++) {
            if ($p == $_page) {
                $_pager[$p] = 0;
            } else {
                $_pager[$p] = $_current_page.$p.$str_fragment;
            }
            $_stopper++;
        }

        // Next page link
        $_next = __('Next');
        // if (($_pager_offset != $_num_page_total-4) AND ($_page  $_num_page_total)) {
        if ($_page < $_num_page_total) {
            $_pager[$_next] = $_current_page.($_page+1).$str_fragment;
        }

        // Last page link
        $_last = __('Last Page');
        if ($_page < $_num_page_total) {
            $_pager[$_last] = $_current_page.($_num_page_total).$str_fragment;
        }

        return $_pager;
    }
}

?>
