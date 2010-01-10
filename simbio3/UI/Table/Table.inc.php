<?php
/**
 * Simbio Table class
 * Class for creating HTML table
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

require 'TableField.inc.php';
require 'TableRow.inc.php';

class Table
{
    public $tableName = 'datagrid';
    public $tableAttr = '';
    public $tableHeaderAttr = 'class="datagrid-head"';
    public $tableContentAttr = 'class="datagrid-content"';
    public $tableRow = array();
    public $cellAttr = array();
    public $highlightRow = false;
    public $columnWidth = array();
    public $alternateRow = true;

    /**
     * Class Constructor
     *
     * @param   string  $str_tableAttr
     */
    public function __construct($str_table_attr = '')
    {
        $this->tableAttr = $str_table_attr;
    }


    /**
     * Method to set table headers
     *
     * @param   array   $arr_column_value
     * @return  void
     */
    public function setHeader($arr_column_content)
    {
        if (!is_array($arr_column_content)) {
            // do nothing
            return;
        } else {
            $this->tableRow[0] = new TableRow($arr_column_content);
        }
    }


    /**
     * Method to append row/record to table
     *
     * @param   array   $arr_column_content
     * @return  void
     */
    public function appendTableRow($arr_column_content)
    {
        // row content must be an array
        if (!is_array($arr_column_content)) {
            // do nothing
            return;
        } else {
            // records row must start with index 1 not 0
            // index 0 is reserved for table header row
            $_row_cnt = count($this->tableRow);
            // create instance of simbio_tableRow
            $_row_obj = new TableRow($arr_column_content);
            if ($_row_cnt < 1) {
                $this->tableRow[1] = $_row_obj;
            } else {
                // if header row exists
                if (isset($this->tableRow[0])) {
                    $this->tableRow[$_row_cnt] = $_row_obj;
                } else {
                    $this->tableRow[$_row_cnt+1] = $_row_obj;
                }
            }
        }
    }


    /**
     * Method to set content of specific column
     *
     * @param   integer $int_row
     * @param   integer $int_column
     * @param   string  $str_column_content
     * @return  void
     */
    public function setColumnContent($int_row, $int_column, $str_column_content)
    {
        if (!isset($this->tableRow[$int_row]->fields[$int_column])) {
           // do nothing
           return;
        } else {
           $this->tableRow[$int_row]->fields[$int_column]->value = $str_column_content;
        }
    }



    /**
     * Method to get content of specific column
     *
     * @param   integer $int_row
     * @param   integer $int_column
     * @param   string  $str_column_content
     * @return  mixed
     */
    public function getColumnContent($int_row, $int_column, $str_column_content)
    {
        if (isset($this->tableRow[$int_row]->fields[$int_column])) {
            return $this->tableRow[$int_row]->fields[$int_column]->value;
        } else {
            return null;
        }
    }


    /**
     * Method to set specific column attribute
     *
     * @param   integer $int_row
     * @param   integer $int_column
     * @param   string  $str_column_attr
     * @return  void
     */
    public function setCellAttr($int_row = 0, $int_column = null, $str_column_attr)
    {
        if (is_null($int_column)) {
            $this->tableRow[$int_row]->allCellAttr = $str_column_attr;
        } else {
            $this->cellAttr[$int_row][$int_column] = $str_column_attr;
        }
    }


    /**
     * Method to print out table
     *
     * @return string
     */
    public function printTable()
    {
        $this->tableName = str_replace(' ', '-', strtolower(trim($this->tableName)));
        $_buffer = '<table class="'.$this->tableName.'" id="'.$this->tableName.'" '.$this->tableAttr.'>'."\n";

        // check if the array have a records
        if (count($this->tableRow) < 1) {
            $_buffer = '<table style="width: 100%;" cellpadding="5">'."\n";
            $_buffer .= '<tr><td align="center" style="color: red; background-color: #CCCCCC; font-weight: bold;">No Data</td></tr>'."\n";
        } else {
            // records
            $_record_row = 0;
            foreach ($this->tableRow as $_row_idx => $_row) {
                if (!$_row instanceof TableRow) {
                    continue;
                }
                // alternate cell class
                $_alter = '';
                if ($this->alternateRow) {
                    $_alter = (($_record_row+1)%2 == 0)?' row-even':' row-odd';
                }
                // print out the row objects
                $_buffer .= '<tr class="'.$this->tableName.'-row'.$_alter.'" '.( $_row->attr?' '.$_row->attr:'' ).'>'."\n";
                foreach ($_row->fields as $_field_idx => $_field) {
                    // header field
                    if ($_record_row == 0) {
                        $_field->attr .= 'class="'.$this->tableName.'-head" '.$this->tableHeaderAttr;
                    } else {
                        $_field->attr .= 'class="'.$this->tableName.'-content" ';
                    }
                    // set column width
                    if (isset($this->columnWidth[$_field_idx]) && $_record_row == 0) {
                        $_field->attr .= ' style="width: '.$this->columnWidth[$_field_idx].';"';
                    }
                    // all column attribute
                    if ($_row->allCellAttr) {
                        $_field->attr = $_row->allCellAttr;
                    }
                    if (isset($this->cellAttr[$_row_idx][$_field_idx])) {
                        $_field->attr = $this->cellAttr[$_row_idx][$_field_idx];
                    }
                    $_buffer .= '<td valign="top" '.( $_field->attr?' '.$_field->attr:'' ).'>'.$_field->value.'</td>';
                }
                $_buffer .= '</tr>'."\n";
                $_record_row++;
            }
        }

        $_buffer .= '</table>'."\n";
        return $_buffer;
    }
}
?>
