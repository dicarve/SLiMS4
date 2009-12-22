<?php
/**
 * Simbio datalist class
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

require 'SQLgrid.inc.php';

class Datalist extends SQLgrid
{
    private $actionURI = 'index.php';
    private $actionOptions = array();
    protected $rowActions = array();
    protected $rowActionsHeader = array();

    /**
     * Class constructor
     *
     * @param   object  $obj_db : Simbio framework connection object
     * @return  void
     */
    public function __construct($obj_db)
    {
        parent::__construct($obj_db);
        // check primary keys
        if (!$this->primaryKeys) {
            $this->primaryKeys = array(0);
        }
    }


    /**
     * Method to format an output of datalist
     *
     * @return  string
     */
    protected function output()
    {
        // remove hidden fields if any
        $this->removeHiddenField();

        $_row = 1;
        // row action modification
        foreach ($this->gridResultRows as $_data) {
            $_ids = '';
            $_row_actions = $this->rowActions;
            if ($this->primaryKeys) {
                foreach ($this->primaryKeys as $_key) {
                    $_key_field = $_key;
                    if (is_int($_key)) {
                        $_key_field = $this->fieldNumericName[$_key];
                    }
                    if (isset($_data[$_key_field])) {
                        $_ids .= $_data[$_key_field].':';
                    }
                }
                $_ids = substr_replace($_ids, '', -1);
                // replace row action pattern with real ID
                foreach ($this->rowActions as $_head => $_action) {
                    $_row_actions[$_head] = str_ireplace('{rowIDs}', $_ids, $_action);
                }
                // remove primary fields
                unset($this->gridResultFields[$_key_field]);
                unset($_data[$_key_field]);
            }
            // append row action to data array
            $this->gridResultRows[$_row-1] = array_merge($_row_actions, $_data);
            $_row++;
        }


        // datalist output
        $_datalist = parent::output(true);

        // datalist action and buttons bar
        $_action_bar = '<div class="datalist-action">';
        $_action_bar_top = $_action_bar;
        // action buttons
        $_buttons = '<div class="datalist-action-buttons">';
        // action options
        if ($this->actionOptions) {
            $_buttons .= 'Action : <select name="batchOp" class="datalist-action-select">';
            foreach ($this->actionOptions AS $_opt) {
                $_buttons .= '<option value="?req='.$_opt[0].'&'.Simbio::rebuildURLQuery('', array('p')).'">'.$_opt[1].'</option>';
            }
            $_buttons .= '</select>';
            $_buttons .= ' <input type="button" value="Submit" />';
        }
        // paging
        $_paging = '';
        if ($this->paging_list) {
            $_paging = '<div class="datalist-paging">'.Simbio::makePaging($this->paging_list).'</div><hr size="1" />';
        }

        // action buttons
        $_buttons .= '<input type="button" value="Check All" class="button check-all" />'
            .'&nbsp;<input type="button" value="Uncheck All" class="button uncheck-all" />';
        $_buttons .= '</div>';
        // action bars add
        $_action_bar_top .= $_paging.$_buttons."</div>\n";
        $_action_bar .= $_paging.preg_replace('@Action.*<select.+<\/select>\s*@i', '', $_buttons)."</div>\n";
        // final output
        $_output = '<form id="'.$this->table_name.'Form" name="'.$this->table_name.'Form" action="'.$this->actionURI.'" method="post">';
        $_output .= $_action_bar_top;
        $_output .= $_datalist;
        $_output .= $_action_bar;
        $_output .= '</form>'."\n";
        return $_output;
    }


    /**
     * Method to set batch action URL
     *
     * @param   string  $str_action_URL
     * @return  void
     */
    public function setActionURL($str_action_URL)
    {
        $this->actionURI = $str_action_URL;
    }


    /**
     * Method to set an array of row actions in datalist
     *
     * @param   array   $arr_row_actions
     * @return  void
     */
    public function setRowActions($arr_row_actions)
    {
        $this->rowActions = $arr_row_actions;
    }


    /**
     * Method to set checkboxes action options
     *
     * @param   array   $arr_options
     * @return  void
     */
    public function batchActionOptions($arr_options)
    {
        $this->actionOptions = $arr_options;
    }
}
