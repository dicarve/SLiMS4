<?php
/**
 * simbio_mysql class
 * Simbio MySQL connection object class
 * Simbio MySQL try to emulates mysqli object behaviour
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

require 'MysqlResult.inc.php';

class Mysql
{
    private $host = '127.0.0.1';
    private $port = 3306;
    private $socket = '';
    private $dbName = '';
    private $dbUsername = '';
    private $dbPassword = '';
    private $resourceConn = false;
    public $affected_rows = 0;
    public $insert_id = 0;
    public $error = null;
    public $errno = 0;

    /**
     * Simbio MySQL Class Constructor
     *
     * @param   string  $str_host
     * @param   string  $str_username
     * @param   string  $str_passwd
     * @param   string  $str_dbname
     * @param   integer $int_port
     * @param   string  $str_socket
     * @return  void
     */
    public function __construct($str_host, $str_username, $str_passwd, $str_dbname, $int_port = 3306, $str_socket = '')
    {
        $this->host = $str_host;
        $this->port = $int_port;
        $this->socket = $str_socket;
        $this->dbName = $str_dbname;
        $this->dbUsername = $str_username;
        $this->dbPassword = $str_passwd;
        // execute connection
        $this->connect();
    }


    /**
     * Method to invoke connection to RDBMS
     *
     * @return  void
     */
    private function connect()
    {
        if ($this->socket) {
            $this->resourceConn = @mysql_connect($this->host.":".$this->socket, $this->dbUsername, $this->dbPassword);
        } else {
            $this->resourceConn = @mysql_connect($this->host.":".$this->port, $this->dbUsername, $this->dbPassword);
        }
        // check the connection status
        if (!$this->resourceConn) {
            $this->error = 'Error Connecting to Database. Please check your configuration';
            $this->errno = mysql_errno();
        } else {
            // select the database
            $db = @mysql_select_db($this->dbName, $this->resourceConn);
            if (!$db) {
                $this->error = 'Error Opening Database';
                $this->errno = mysql_errno();
            }
        }
    }


    /**
     * Method to create/send query to RDBMS
     *
     * @param   string  $str_query : the SQL query to execute
     * @return  object
     */
    public function query($str_query = '')
    {
        if (empty($str_query)) {
            $this->error = "Error on Mysql::query() method : query empty";
            return false;
        } else {
            // create MysqlResult object
            $result = new MysqlResult($str_query, $this->resourceConn);
            // get any properties from result object
            $this->affected_rows = $result->affected_rows;
            $this->errno = $result->errno;
            $this->error = $result->error;
            $this->insert_id = $result->insert_id;
            // return the result object
            if ($this->error) {
                return false;
            } else {
                return $result;
            }
        }
    }


    /**
     * Method to escape SQL string
     *
     * @param   string  $str_data : value to be escaped
     * @return  string
     */
    public function escape_string($str_data)
    {
        return mysql_real_escape_string($str_data, $this->resourceConn);
    }


    /**
     * Method to close RDBMS connection
     *
     * @return  void
     */
    public function close()
    {
        mysql_close($this->resourceConn);
    }
}
?>
