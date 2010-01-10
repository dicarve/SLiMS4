<?php
/**
 * Simbio datagrid class
 * SQL datagrid with checkbox and other edit action
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

class Datagrid extends SQLgrid
{
    private $actionURI = 'index.php';
    private $actionOptions = array();
    private $table = '';
    protected $rowActions = array();
    protected $rowActionsHeader = array();
    public $withPaging = true;

    /**
     * Class constructor
     *
     * @param   object  $obj_db: Simbio framework connection object
     * @param   string  $str_grid_name: name of datagrid
     * @return  void
     */
    public function __construct($obj_db, $str_grid_name = 'datagrid')
    {
        parent::__construct($obj_db);
        // create table object
        $this->table = new Table();
    }


    /**
     * Method to make header field in simbio table
     *
     * @return  void
     */
    protected function makeHeaderField()
    {
        $_buffer = array();
        foreach ($this->gridResultFields AS $_fld_info) {
            if (is_string($_fld_info)) {
                $_buffer[] = '<span class="datagrid-head-plain">'.$_fld_info.'</span>';
            } else {
                if (isset($_fld_info['link'])) {
                    $_buffer[] = '<a href="'.$_fld_info['link'].'" title="'.$_fld_info['title'].'">'.$_fld_info['name'].'</a>';
                } else {
                    $_buffer[] = '<span class="datagrid-head-plain">'.$_fld_info['name'].'</span>';
                }
            }
        }

        return $_buffer;
    }


    /**
     * Method to format an output of datagrid
     *
     * @return  string
     */
    public function build()
    {
        if ($this->num_rows < 1) {
            return $this->table->printTable();
        }

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
                        $_ids .= $_data[$_key_field].'/';
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

        // row action header
        foreach ($this->rowActions as $_head => $_val) {
            $this->rowActionsHeader[] = $_head;
            $this->table->columnWidth[] = '20px';
        }

        // row action header
        $this->gridResultFields = array_merge($this->rowActionsHeader, $this->gridResultFields);

        // set table grid header
        $this->table->setHeader(self::makeHeaderField());

        // remove hidden fields if any
        $this->removeHiddenField();
        // data loop
        foreach ($this->gridResultRows as $_data) {
            // append array to table
            $this->table->appendTableRow($_data);
        }

        // datagrid output
        $_datagrid = $this->table->printTable();

        // paging
        $_paging = '';
        // create paging
        if ($this->num_rows > $this->numToShow AND $this->withPaging) {
            $_paging = Utility::makePaging(Paging::build($this->num_rows, $this->numToShow, 10));
        }

        // defaults
        $_action_bar = '';
        $_action_bar_top = '';
        $_buttons = '';

        if ($this->actionOptions || $_paging) {
            // datagrid action and buttons bar
            $_action_bar = '<div class="datagrid-action">';
            $_action_bar_top = $_action_bar;
            $_buttons = '';
            // action options
            if ($this->actionOptions) {
                // action buttons
                $_buttons = '<div class="datagrid-buttons">';
                $_buttons .= 'Action: <select name="datagrid-option" class="datagrid-option">';
                foreach ($this->actionOptions AS $_opt) {
                    $_buttons .= '<option class="datagrid-option-item" value="'.$_opt[0].'">'.$_opt[1].'</option>';
                }
                $_buttons .= '</select>';
                $_buttons .= ' <input type="submit" class="datagrid-submit" value="Submit" /> ';
                // action buttons
                $_buttons .= '<input type="button" value="Check All" class="button check-all" /> '
                    .'<input type="button" value="Uncheck All" class="button uncheck-all" />';
                $_buttons .= '</div>';
            }
            // action bars add
            $_action_bar_top .= $_paging.$_buttons."</div>\n";
            $_action_bar .= $_paging.preg_replace('@Action.*<select.+<\/select>\s*@i', '', $_buttons)."</div>\n";
        }

        // final output
        $_output = '<form id="'.$this->table->tableName.'-form" name="'.$this->table->tableName.'-form" action="'.$this->actionURI.'" method="post">';
        $_output .= $_action_bar_top;
        $_output .= $_datagrid;
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
     * Method to set an array of row actions in datagrid
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
    public function setActionOptions($arr_options)
    {
        $this->actionOptions = $arr_options;
    }
}
