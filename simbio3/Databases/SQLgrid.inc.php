<?php
/**
 * Simbio SQLGrid
 * SQL datagrid creator
 *
 * Copyright (C) 2009,2010  Arie Nugraha (dicarve@yahoo.com)
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

abstract class SQLgrid
{
    /**
     * Private properties
     */
    private $objDB = false;
    private $gridRealQuery = false;
    private $sqlString = false;

    /**
     * Protected properties
     */
    protected $gridResultFields = array();
    protected $fieldNumericName = array();
    protected $gridResultRows = array();
    protected $sqlTable = '';
    protected $sqlColumn = '';
    protected $sqlCriteria = '';
    protected $sqlOrder = '';
    protected $primaryKeys = array();
    protected $noSortColumn = array();
    protected $modifiedContent = array();
    protected $pagingList = false;

    /**
     * Public properties
     */
    public $debug = false;
    public $num_rows = 0;
    public $numToShow = 10;
    public $sortColumn = array();
    public $sqlGroupBy = '';
    public $selectFlag = '';
    public $currentPage = 1;
    public $totalPage = 1;
    public $hiddenFields = array();
    public $error = '';
    public $pagingHandler = 'Paging';


    /**
     * Class constructor
     *
     * @param   object  $obj_db
     * @return  void
     */
    public function __construct($obj_db) {
        $this->objDB = $obj_db;
    }


    /**
     * Method to proccess/create datagrid
     *
     * @param   object  $obj_db
     * @param   string  $str_db_table
     * @param   integer $int_num2show
     * @return  string
     */
    public function create($str_db_table = '') {
        // check database connection
        if (!$this->objDB) {
            $this->error = 'No database connection or database connection error!';
            return;
        }

        $this->sqlTable = $str_db_table;

        if (!$this->sqlTable && !$this->sqlString) {
            $this->error = 'No database table specified yet!';
            return;
        }

        // get page number from http get var
        if (isset($_GET['page']) AND $_GET['page'] > 1) {
            $this->currentPage = (integer)$_GET['page'];
        }

        // count the row offset
        $_offset = 0;
        if ($this->currentPage > 1) {
            $_offset = ($this->currentPage*$this->numToShow) - $this->numToShow;
        }

        // change the record sorting if there fld var in URL
        $_dir = 'ASC';
        $_next_dir = 'DESC';
        $_sort_dir_info = 'ascendingly';
        if (isset($_GET['fld']) AND !empty($_GET['fld'])) {
            $this->sqlOrder = 'ORDER BY `'.urldecode($_GET['fld']).'` ';
        }

        // record order direction
        if (isset($_GET['dir']) AND !empty($_GET['dir'])) {
            $_dir = trim($_GET['dir']);
            if ($_dir == 'DESC') {
                $_next_dir = 'ASC';
            } else {
                $_next_dir = 'DESC';
                $_sort_dir_info = 'descendingly';
            }
            // append sort direction
            $this->sqlOrder .= $_dir;
        }

        // check group by
        if ($this->sqlGroupBy) {
            $this->sqlGroupBy = ' GROUP BY '.$this->sqlGroupBy;
        }

        // if sql string already set
        if ($this->sqlString) {
            $this->sqlString .= $this->sqlGroupBy.' '.$this->sqlCriteria.' '.$this->sqlOrder." LIMIT ".$this->numToShow." OFFSET $_offset";
            $_sql_count_str = preg_replace('@^SELECT\s+.+\s+FROM@i', 'SELECT '.$this->selectFlag.' COUNT(*) FROM', $this->sqlString);
            $_sql_count_str = preg_replace('@\s+GROUP\s+BY\s+.+|\s+ORDER\s+BY\s+.+|\s+LIMIT\s+.+$@i', '', $_sql_count_str);
        } else {
            // build sql string
            $this->sqlString = 'SELECT '.$this->selectFlag.' '.$this->sqlColumn.
                ' FROM '.$this->sqlTable.' '.$this->sqlCriteria.
                ' '.$this->sqlGroupBy.' '.$this->sqlOrder." LIMIT ".$this->numToShow." OFFSET $_offset";
            $_sql_count_str = 'SELECT '.$this->selectFlag.' COUNT(*) FROM '.$this->sqlTable.' '.$this->sqlCriteria.' '.$this->sqlGroupBy;
        }

        // for debugging purpose only
        // die($this->sqlString);
        // die($_sql_count_str);

        // real query
        $this->gridRealQuery = $this->objDB->query($this->sqlString);
        // if the query error
        $_last_error = $this->objDB->error;
        if ($_last_error) {
            $this->error .= 'ERROR<br />';
            $this->error .= 'Database Server said : '.$_last_error.'';
            if ($this->debug) {
                $this->error .= '<br />With SQL Query : '.$this->sqlString;
            }
            return false;
        }

        // total rows query
        $_num_q = $this->objDB->query($_sql_count_str);
        $_num_d = $_num_q->fetch_row();
        $this->num_rows = $_num_d[0];

        // check if there is no rows returned
        if ($this->num_rows < 1) {
            return;
        }

        // get total page
        $this->totalPage = ceil($this->num_rows/$this->numToShow);

        // check the query string and rebuild with urlencoded value
        $_url_query_str = Utility::rebuildURLQuery('', array('fld', 'dir'));

        // make all field name link for sorting
        $this->gridResultFields = array();

        $_row = 1;
        $_result_row = 0;
        // loop the record
        while ($_data = $this->gridRealQuery->fetch_assoc()) {
            $this->gridResultRows[] = $_data;
            // fetch column field info
            if ($_row == 1) {
                $_field_num = 0;
                // adding record order links to field name header
                foreach ($_data AS $_fld_name => $_fld_val) {
                    // check if the column is not listed in noSortColumn array properties
                    if (!in_array($_fld_name, $this->noSortColumn)) {
                        $_order_by = 'fld='.urlencode($_fld_name).'&dir='.$_next_dir;
                        $this->gridResultFields[$_fld_name] = array('link' => $_SERVER['PHP_SELF'].'?'.$_url_query_str.$_order_by, 'title' => 'order list by '.$_fld_name.' '.$_sort_dir_info, 'name' => $_fld_name);
                    } else {
                        $this->gridResultFields[$_fld_name] = array('name' => $_fld_name);
                    }
                    $this->fieldNumericName[$_field_num] = $_fld_name;
                    $_field_num++;
                }
            }

            // modified content
            foreach ($this->modifiedContent as $_mod_field_name => $_new_content) {
                // change the value of modified column
                if (isset($this->gridResultRows[$_result_row][$_mod_field_name])) {
                    // run callback function php script if the string is embraced by "callback{*}"
                    if (preg_match('@^callback\{.+?\}@i', $_new_content)) {
                        // strip the "callback{" and "}" string to empty string
                        $_callback_func = str_replace(array('callback{', '}'), '', $_new_content);
                        // check if the callback is a static class method
                        if (stripos($_callback_func, ',', 1) !== false) {
                            $_arr_callback = explode(',', $_callback_func, 2);
                            $this->gridResultRows[$_result_row][$_mod_field_name] = call_user_func($_arr_callback, $this->objDB, $this->gridResultRows[$_result_row]);
                        } else {
                            // else it is ordinary user function
                            if (function_exists($_callback_func)) {
                                // call the function
                                $this->gridResultRows[$_result_row][$_mod_field_name] = call_user_func($_callback_func, $this->objDB, $this->gridResultRows[$_result_row]);
                            } else { $this->gridResultRows[$_result_row][$_mod_field_name] = $_callback_func; }
                        }
                    } else {
                        // replace the "{column_value}" marker with real column value
                        $this->gridResultRows[$_result_row][$_mod_field_name] = str_replace('{column_value}', $this->gridResultRows[$_result_row][$_mod_field_name], trim($_new_content));
                    }
                }
            }

            $_row++;
            $_result_row++;
        }
    }


    /**
     * Method to disable sorting link of certain fields in datagrid
     *
     * @param   array   $arr_field_number
     * @return  void
     */
    public function disableSort($arr_field_number)
    {
        if (count($arr_field_number) > 0) {
            $this->noSortColumn = $arr_field_number;
        }
    }


    /**
     * Method to modify column content of field in datagrid
     *
     * @param   string  $str_column
     * @param   string  $str_new_value
     * @return  void
     */
    public function modifyColumnContent($str_column, $str_new_value)
    {
        $this->modifiedContent[$str_column] = $str_new_value;
    }


    /**
     * Method to remove hidden field in datagrid
     *
     * @return  void
     */
    protected function removeHiddenField()
    {
        if (!$this->hiddenFields OR !$this->gridResultRows) return;
        $_result_rows_buffer = array();
        foreach ($this->gridResultRows as $_data) {
            foreach ($this->hiddenFields as $_inv_fld) {
                unset($_data[$_inv_fld]);
                // remove header field to
                unset($this->gridResultFields[$_inv_fld]);
            }
            $_result_rows_buffer[] = $_data;
        }
        $this->gridResultRows = $_result_rows_buffer;
    }


    /**
     * Method to set SQL string
     *
     * @param   string  $sql_string
     * @return  void
     */
    public function setSQLQuery($str_sql_string)
    {
        // remove limit and offset keyword
        $str_sql_string = preg_replace('@\s+(ORDER\sBY\s.+|LIMIT\s.+)$@i', '', $str_sql_string);
        $this->sqlString = $str_sql_string;
    }


    /**
     * Method to set datagrid fields
     *
     * @param   mixed   $mix_field_list
     * @return  void
     */
    public function setSQLColumn($mix_field_list)
    {
        if (is_array($mix_field_list)) {
            // iterate all arguments
            foreach ($mix_field_list as $_alias => $_field) {
                $_alias = is_string($_alias)?trim(str_replace('`', '', $_alias)):$_alias;
                // store to class properties
                $this->sqlColumn .= '`'.$_field.'`'.( is_int($_alias)?'':' AS `'.$_alias.'`' ).', ';
                // $this->sortColumn[trim($_column_alias)] = trim($_real_column);
                $this->sortColumn[$_alias] = $_field;
            }
            // remove the last comma
            $this->sqlColumn = substr_replace($this->sqlColumn, ' ', -2);
        } else {
            // force to string
            $mix_field_list = (string)$mix_field_list;
            $this->sqlColumn = $mix_field_list;
        }
    }


    /**
     * Method to set SQL criteria (WHERE definition) of datagrid
     *
     * @param   string  $str_where_clause
     * @return  void
     */
    public function setSQLCriteria($str_where_clause)
    {
        if ($str_where_clause) {
            // remove WHERE word if exist
            $str_where_clause = preg_replace('@^\s*WHERE\s@i', '', $str_where_clause);
            $this->sqlCriteria = 'WHERE '.$str_where_clause;
        }
    }


    /**
     * Method to set ordering of datagrid
     *
     * @param   string  $str_order_column
     */
    public function setSQLOrder($str_order_column)
    {
        if ($str_order_column) {
            // remove WHERE word if exist
            $this->sqlOrder = 'ORDER BY '.$str_order_column;
        }
    }


    /**
     * Method to define primary keys of datagrid row
     *
     * @param   array  $arr_primary_keys
     */
    public function setPrimaryKeys($arr_primary_keys)
    {
        $this->primaryKeys = $arr_primary_keys;
    }
}

?>
