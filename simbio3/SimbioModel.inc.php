<?php
/**
 * SimbioModel abstract class
 * Simbio Framework's module template/abstract class
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

abstract class SimbioModel {
    protected $dbc = null;
    protected $global = null;
    /**
     * An array of database table fields
     */
    protected $dbTable = 'table';
    protected $tableRelation = null;
    protected $primaryFields = array();
    protected $dbFields = array();


    /**
     * Method that must be defined by all child module
     * used by framework to get module information
     *
     * @param   object      $simbio: Simbio framework object
     * @return  array       an array of module information containing
     */
    abstract public function moduleInfo(&$simbio);


    /**
     * Method that must be defined by all child module
     * used by framework to get module privileges type
     *
     * @param   object      $simbio: Simbio framework object
     * @return  array       an array of privileges for this module
     */
    abstract public function modulePrivileges(&$simbio);


    /**
     * Method to add module data
     *
     * @param   object      $simbio: Simbio framework object
     * @param   string      $str_args: method main argument
     * @return  void
     */
    public function add(&$simbio, $str_args) {

    }


    /**
     * Method to auto generate
     */
    protected function autoGenerateFields(&$simbio) {
        $_q = $simbio->dbQuery('DESCRIBE {'.$this->dbTable.'}');
        $_e = $simbio->dbError();
        if (!$_e['errno']) {
            while ($_d = $_q->fetch_assoc()) {
                // get field length
                $_m = array();
                $_fld_size = 128;
                // check for primary field
                if ($_d['Key'] == 'PRI') {
                    $this->primaryFields[$_d['Field']] = $_d['Field'];
                    $this->dbFields[$_d['Field']]['isPrimary'] = true;
                    if ($_d['Extra'] == 'auto_increment') {
                        $this->dbFields[$_d['Field']]['autoID'] = true;
                    }
                }
                if (preg_match('@^.+\(([0-9]+)\)@i', $_d['Type'], $_m)) {
                    $_fld_size = (integer)$_m[1];
                }
                // form field
                $this->dbFields[$_d['Field']]['dataType'] = strtolower(preg_replace('@\([0-9]+\)@i', '', $_d['Type']));
                $this->dbFields[$_d['Field']]['default'] = ($_d['Default'] == 'NULL')?'NULL':$_d['Default'];
                $this->dbFields[$_d['Field']]['required'] = ($_d['Null'] == 'NO')?true:false;
                $this->dbFields[$_d['Field']]['maxSize'] = $_fld_size;
                $this->dbFields[$_d['Field']]['value'] = ($_d['Default'] == 'NULL')?'':$_d['Default'];
            }
        }
    }


    /**
     * Method to generate form element array
     *
     * @param   object  $simbio: Simbio framework instance
     * @param   object  $obj_form_output: Instance of Simbio FormOutput class
     * @return  mixed
     */
    protected function autoGenerateForm(&$simbio, &$obj_form_output = null)
    {
        // init form element array
        $_forms = array();
        foreach ($this->dbFields as $_field_id => $_field_info) {
            // pass on auto ID field
            if (isset($_field_info['autoID']) && $_field_info['autoID']) {
                continue;
            }
            $_element = array();
            $_element['id'] = $_field_id;
            $_element['label'] = ucwords(str_replace(array('_', '-'), ' ', $_field_id));
            $_element['maxSize'] = $_field_info['maxSize'];
            $_element['required'] = $_field_info['required'];
            $_element['value'] = $_field_info['default'];
            $_element['type'] = 'text';
            if (in_array($_field_info['dataType'], array('varchar', 'char', 'int', 'bigint', 'smallint'))) {
                if ($_field_info['maxSize'] > 200) {
                    $_element['type'] = 'textarea';
                }
            } else if (in_array($_field_info['dataType'], array('text', 'bigtext', 'mediumtext', 'smalltext'))) {
                $_element['type'] = 'textarea';
            } else if (in_array($_field_info['dataType'], array('date', 'datetime', 'timestamp'))) {
                // skip input_date and last_update field
                if (in_array($_field_id, array('input_date', 'last_update'))) {
                    continue;
                }
                $_element['type'] = 'date';
            } else if (in_array($_field_info['dataType'], array('enum', 'set'))) {
                $_element['type'] = 'select';
            } else {
                foreach ($this->tableRelation as $_table => $_rel) {
                    if ($_rel[$_field_id]) {
                        $_element['type'] = 'select';
                        // get list of options from database
                        $_options = array();
                        $_opt_q = $simbio->dbQuery("SELECT * FROM {$_table} LIMIT 100");
                        if ($_opt_q->num_rows > 0) {
                            while ($_opt_d = $_opt_q->fetch_row()) {
                                $_opt_label = $_opt_d[0];
                                if (isset($_opt_d[1])) {
                                    $_opt_label = $_opt_d[1];
                                }
                                $_options[] = array($_opt_d[0], $_opt_label);
                            }
                        }
                        $_element['option'] = $_options;
                    }
                }
            }

            // add to form array
            $_forms[$_field_id] = $_element;
        }

        if ($obj_form_output instanceof FormOutput) {
            foreach($_forms as $_element) {
                $obj_form_output->add($_element);
            }
        }
        return $_forms;
    }


    /**
     * Method to generate complete SQL with join
     *
     * @param       array       $arr_field : an array containing SQL fields definition
     * @return      string
     */
    protected function autoGenerateSQL($arr_field = '')
    {
        $_fields = '*';
        // field join
        if ($arr_field && is_array($arr_field))  {
            $_fields = '';
            foreach ($arr_field as $_alias => $_fld) {
                $_fields .= $_fld.' AS '.$_alias.',';
            }
            $_fields = substr_replace($_fields, '', -1);
        }
        // table join
        $_str_sql = "SELECT $_fields FROM ".$this->dbTable.' ';
        $_join = '';
        if ($this->tableRelation) {
            foreach ($this->tableRelation as $_table => $_join_field) {
                $_join .= " LEFT JOIN $_table ON ".$this->dbTable.".$_join_field=$_table.$_join_field";
            }
        }
        $_str_sql .= $_join;

        return $_str_sql;
    }


    /**
     * Method to get block content
     *
     * @param   object      $simbio: Simbio framework instance/object
     * @param   string      $str_block_type: string of block type to load
     * @return  string
     */
    public static function getBlock($simbio, $str_block_type) {

    }


    /**
     * Method to get records form table
     *
     * @param   object      $simbio: Simbio framework instance/object
     * @param   array       $arr_search_criteria: an array containing field => value pair
     * @param   array       $arr_fields: an array of fields to retrieve
     * @return  array       array of records
     */
    public function getRecords($simbio, $arr_search_criteria, $arr_fields = array())
    {
        $_records = array();
        // criteria
        $_criteria = '';
        if ($arr_search_criteria) {
            foreach ($arr_search_criteria as $_field => $_value) {
                if (is_integer($_value) || is_double($_value)) {
                    $_criteria .= $_field."=$_value AND ";
                } else {
                    $_criteria .= $_field." LIKE '$_value' AND ";
                }
            }
            $_criteria = substr_replace($_criteria, '', -4);
        }
        if ($_criteria) {
            $_criteria = "WHERE ".$_criteria;
        }
        // fields
        $_fields = '';
        if ($arr_fields) {
            foreach ($arr_fields as $_field) {
                $_fields .= $_field.',';
            }
            $_fields = substr_replace($_fields, '', -1);
        }
        if (!$_fields) {
            $_fields = "*";
        }
        // query
        $_sql_str = "SELECT $_fields FROM {".$this->dbTable."} $_criteria LIMIT 100";
        $_q = $simbio->dbQuery($_sql_str);
        if ($_q->num_rows > 0) {
            while ($_rec = $_q->fetch_assoc()) {
                $_records[] = $_rec;
            }
        }
        return $_records;
    }


    /**
     * Default module page method
     * All module must have this method
     *
     * @param   object      $simbio: Simbio framework object
     * @param   string      $str_args: method main argument
     * @return  void
     */
    public function index(&$simbio, $str_args) {

    }


    /**
     * Module initialization method
     * All preparation for module such as loading library should be doing here
     *
     * @param   object  $simbio: Simbio framework object
     * @param   string  $str_current_module: current module called by framework
     * @param   string  $str_current_method: current method of current module called by framework
     * @param   string  $str_args: method main argument
     * @return  void
     */
    public function init(&$simbio, $str_current_module, $str_current_method, $str_args) {

    }


    /**
     * Method run at the end of framework's flow before all output sent
     * This method useful to manipulize other modules output or add last action
     * after module main method run
     *
     * @param   object  $simbio: Simbio framework object
     * @param   string  $str_current_module: current module called by framework
     * @param   string  $str_current_method: current method of current module called by framework
     * @param   string  $str_args: method main argument
     * @return  void
     */
    public function manipulate(&$simbio, $str_current_module, $str_current_method, $str_args) {

    }


    /**
     * Method returning an array of application main menu and navigation menu
     *
     * @param   object  $simbio: Simbio framework object
     * @param   string  $str_menu_type: value can be 'main' or 'navigation'
     * @return  array
     */
    public function menu(&$simbio, $str_menu_type = 'navigation') {
        return array();
    }


    /**
     * Method to update module data
     *
     * @param   object      $simbio: Simbio framework object
     * @param   string      $str_args: method main argument
     * @return  void
     */
    public function update(&$simbio, $str_args) {

    }


    /**
     * Method to remove module data
     *
     * @param   object      $simbio: Simbio framework object
     * @param   string      $str_args: method main argument
     * @return  void
     */
    public function remove(&$simbio, $str_args) {

    }


    /**
     * Method to save/update module data
     *
     * @param   object      $simbio: Simbio framework object
     * @param   string      $str_args: method main argument
     * @return  array       an array of status flag and messages
     */
    public function save(&$simbio, $str_args) {

    }



    /**
     * Method to validate processed module data
     *
     * @param   object      $simbio: Simbio framework object
     * @param   string      $str_current_module: current module called by framework
     * @param   string      $str_current_method: current method of current module called by framework
     * @param   string      $str_args: method main argument
     * @return  boolean/array       boolean true if validation success OR an array of status flag and messages if validation failed
     */
    public function validate(&$simbio, $str_current_module, $str_current_method, $str_args) {
        return true;
    }
}
?>
