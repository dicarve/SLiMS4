<?php
/**
 * MysqlResult class
 * This class emulates mysqli mysqli_result object behaviour
 *
 * Copyright (C) 2007,2008  Arie Nugraha (dicarve@yahoo.com)
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

class MysqlResult
{
    /**
     * Private properties
     */
    private $resourceResult = false;
    private $sqlString = '';

    /**
     * Public properties
     */
    public $num_rows = 0;
    public $fieldCount = 0;
    public $affected_rows = 0;
    public $insert_id = 0;
    public $errno = false;


    /**
     * Class Constructor
     *
     * @param   string      $str_query
     * @param   resource    $res_conn
     */
    public function __construct($str_query, $res_conn)
    {
        $this->sqlString = trim($str_query);
        $this->sendQuery($res_conn);
    }


    /**
     * Method to send SQL query
     *
     * @param   resource    $res_conn
     * @return  void
     */
    private function sendQuery($res_conn)
    {
        // checking query type
        // if the query return recordset or not
        if (preg_match("/^SELECT|DESCRIBE|SHOW|EXPLAIN\s/i", $this->sqlString)) {
            $this->resourceResult = @mysql_query($this->sqlString, $res_conn);
            // error checking
            if (!$this->resourceResult) {
                $this->error = 'Query ('.$this->sqlString.") failed to executed. Please check your query again \n".mysql_error($res_conn);
                $this->errno = mysql_errno($res_conn);
            } else {
                // count number of rows
                $this->num_rows = @mysql_num_rows($this->resourceResult);
                $this->fieldCount = @mysql_num_fields($this->resourceResult);
            }
        } else {
            $query = @mysql_query($this->sqlString, $res_conn);
            $this->insert_id = @mysql_insert_id($res_conn);
            // error checking
            if (!$query) {
                $this->error = 'Query ('.$this->sqlString.") failed to executed. Please check your query again \n".mysql_error($res_conn);
                $this->errno = mysql_errno($res_conn);
            } else {
                // get number of affected row
                $this->affected_rows = @mysql_affected_rows($res_conn);
            }
            // nullify query
            $query = null;
        }
    }


    /**
     * Method to fetch record in associative  array
     *
     * @return  array
     */
    public function fetch_assoc()
    {
        return @mysql_fetch_assoc($this->resourceResult);
    }


    /**
     * Method to fetch record in numeric array indexes
     *
     * @return  array
     */
    public function fetch_row()
    {
        return @mysql_fetch_row($this->resourceResult);
    }


    /**
     * Method to fetch fields information of resultset
     *
     * @return  array
     */
    public function fetch_fields()
    {
        $_fields_info = array();
        $_f = 0;
        $_field_num = mysql_num_fields($this->resourceResult);
        while ($_f < $_field_num) {
            $_fields_info[] = mysql_fetch_field($this->resourceResult, $_f);
            $_f++;
        }

        return $_fields_info;
    }


    /**
     * Method to free resultset memory
     *
     * @return  void
     */
    public function free_result()
    {
        if ($this->resourceResult) {
            @mysql_free_result($this->resourceResult);
        }
    }
}
?>
