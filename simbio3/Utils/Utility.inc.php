<?php
/**
 * Application wide utility function
 *
 * Copyright (C) 2009,2010  Arie Nugraha (dicarve@yahoo.com), Hendro Wicaksono (hendrowicaksono@yahoo.com)
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

class Utility {
    /*
     * Static Method to create block
     *
     * @param   string  $str_block_content: block content
     * @param   string  $str_block_title: block title/header
     * @param   string  $str_block_ID: the HTML ID of block
     * @return  string
     */
    public static function createBlock($str_block_content, $str_block_title = null, $str_block_ID = null)
    {
        $_block;
        ob_start();
    echo '<div class="block"'.( $str_block_ID?' id="'.$str_block_ID.'"':'' ).'>'."\n";
        if ($str_block_title) { echo '<div class="block-title">'.$str_block_title.'</div>'."\n"; }
        echo '<div class="block-content">'."\n";
        echo $str_block_content;
    echo '</div>'."\n";
    echo '</div>'."\n";
        $_block = ob_get_clean();
        return $_block;
    }


    /*
     * Static Method to create block
     *
     * @param   string  $arr_attribute: an array containing pair attributeName => attributeValue
     * @return  string
     */
    public static function createHTMLAttribute($arr_attribute)
    {
        $_attrs = '';
        if (is_array($arr_attribute) && count($arr_attribute) > 0) {
            foreach ($arr_attribute as $_name => $_value) {
                $_attrs .= ' '.$_name.'="'.$_value.'"';
            }
        }
        return $_attrs;
    }


    /**
     * Static Method to redirect page to https equivalent
     *
     * @param   integer $int_https_port
     * @return  void
     */
    public static function checkHttps($int_https_port)
    {
        $server_https_port = $_SERVER['SERVER_PORT'];
        if ($server_https_port != $int_https_port) {
            $host =  $_SERVER['SERVER_NAME'];
            $https_url = 'https://'.$host.$_SERVER['PHP_SELF'];
            // send HTTP header
            header("Location: $https_url");
        }
    }


    /**
     * Destroy session and its cookies
     *
     * @param   string  $str_session_name: name of session to remove
     * @param   string  $str_cookie_path: path of session cookie
     * @return  void
     */
    public static function destroySessionCookie($str_session_name = '', $str_cookie_path = '/')
    {
        if (!$str_session_name) { $str_session_name = session_name(); }
        // deleting session browser cookie
        @setcookie($str_session_name, '', time()-86400, $str_cookie_path);
        // reset session
        $_SESSION = array();
        // remove server session file
        session_destroy();
    }


    /*
     * Static Method to make paging list
     *
     * @param       array       $arr_paging_data : an array of paging list
     * return       string
     */
    public static function makePaging($arr_paging_data, $str_link_pattern = '')
    {
        if (!is_array($arr_paging_data) OR !$arr_paging_data) {
            return;
        }
        $_buffer = '<span class="paging">';
        foreach ($arr_paging_data AS $_label => $_links) {
            if (empty($_links)) {
                $_buffer .= '<strong class="paging-item paging-current">'.$_label.'</strong> &nbsp;';
            } else {
                $_buffer .= '<a href="'.( $str_link_pattern?str_ireplace('#link', $_links, $str_link_pattern):$_links ).'" class="paging-item">'.$_label.'</a> &nbsp;';
            }
        }
        $_buffer .= '</span>';
        return $_buffer;
    }


    /**
     * Rebuild URL Query String and encode each value
     *
     * @param       string      $str_query_string : URL query to rebuild
     * @param       array       $arr_excluded_param : excluded param
     * @return      string
     */
    public static function rebuildURLQuery($str_query_string = '', $arr_excluded_param = '')
    {
        $_query_string = $str_query_string;
        if (!$_query_string) {
            if (isset($_SERVER['QUERY_STRING']) AND $_SERVER['QUERY_STRING']) {
                $_query_string = $_SERVER['QUERY_STRING'];
            } else {
                return null;
            }
        }
        // parse query string
        parse_str($_query_string, $_params);
        // emptying query string
        $_query_string = '';
        foreach ($_params AS $_param_name => $_param_value) {
            if (in_array($_param_name, $arr_excluded_param)) {
                continue;
            }
            if (is_array($_param_value)) {
                foreach ($_param_value as $_each_val) {
                    $_query_string .= $_param_name.'[]='.urlencode($_each_val).'&';
                }
            } else {
                $_query_string .= $_param_name.'='.urlencode($_param_value).'&';
            }
        }

        return $_query_string;
    }
}
?>
