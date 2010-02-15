<?php
/**
 * Membership module class
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

class Membership extends SimbioModel {
    /**
     * Class contructor
     *
     * @param   object  $simbio: Simbio framework object
     * @return  void
     */
    public function __construct(&$simbio) {
        $this->dbTable = 'member';
        // auto generate fields from database
        $this->autoGenerateFields($simbio);
        // get global config from framework
        $this->global = $simbio->getGlobalConfig();
        // get database connection
        $this->dbc = $simbio->getDBC();
    }


    /**
     * Method that must be defined by all child module
     * used by framework to get module information
     *
     * @param   object      $simbio: Simbio framework object
     * @return  array       an array of module information containing
     */
    public static function moduleInfo(&$simbio) {
        return array('module-name' => 'Membership',
            'module-desc' => 'Enable library membership management',
            'module-depends' => array());
    }


    /**
     * Method that must be defined by all child module
     * used by framework to get module privileges type
     *
     * @param   object      $simbio: Simbio framework object
     * @return  array       an array of privileges for this module
     */
    public static function modulePrivileges(&$simbio) {

    }


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
     * Method to get block content
     *
     * @param   object      $simbio: Simbio framework instance/object
     * @param   string      $str_block_type: string of block type to load
     * @return  string
     */
    public static function getBlock($simbio, $str_block_type) {

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
        if (!User::isUserLogin()) {
            $simbio->addError('MEMBER_NOT_LOGIN', __('Please login to view membership related information!'));
        } else {
            // include datagrid library
            $simbio->loadLibrary('Datagrid', SIMBIO_BASE.'Databases'.DSEP.'Datagrid.inc.php');
            // create datagrid instance
            $_datagrid = new Datagrid($this->dbc);
            // set column to view in datagrid
            $_datagrid->setSQLColumn(array('ID' => 'member_id', 'Member Name' => 'expr{IF(last_name IS NOT NULL, CONCAT(first_name, \' \', last_name), first_name)}',
                'Join Date' => 'join_date', 'Register Date' => 'register_date'));
            // set primary key for detail view
            $_datagrid->setPrimaryKeys(array('ID'));
            // set record actions
            $_action['Del.'] = '<input type="checkbox" name="record[]" value="{rowIDs}" />';
            $_action['Edit'] = '<a class="datagrid-links" href="index.php?p=member/update/{rowIDs}">&nbsp;</a>';
            $_datagrid->setRowActions($_action);
            // set multiple record action options
            $_action_options[] = array('0', 'Select action');
            $_action_options[] = array('member/remove', 'Remove selected member');
            $_action_options[] = array('member/extend', 'Extend membership of selected member');
            $_action_options[] = array('member/pending', 'Pending membership of selected member');
            $_action_options[] = array('member/blacklist', 'Blacklist membership of selected member');
            $_datagrid->setActionOptions($_action_options);
            // set result ordering
            $_datagrid->setSQLOrder('register_date DESC');
            // search criteria
            if (isset($_GET['keywords'])) {
                $_search = $simbio->filterizeSQLString($_GET['keywords'], true);
                $_criteria = "first_name LIKE '%$_search%' OR last_name LIKE '%$_search%')";
                $_datagrid->setSQLCriteria($_criteria);
            }
            // built the datagrid
            $_datagrid->create($this->global['db_prefix'].'member');

            // set header
            $simbio->headerBlockTitle = 'Membership';
            $simbio->headerBlockMenu = array(
                    array('class' => 'add', 'link' => 'member/add', 'title' => __('Add Member'), 'desc' => __('Add new Member')),
                    array('class' => 'list', 'link' => 'member', 'title' => __('Member List'), 'desc' => __('View list of Library Member')),
                    array('class' => 'expired', 'link' => 'member/expired', 'title' => __('Expired Member List'), 'desc' => __('View list of Expired Library Member'))
                );
            // build search form
            $_quick_search = new FormOutput('search', 'index.php', 'get');
            $_quick_search->submitName = 'search';
            $_quick_search->submitValue = __('Search');
            // define form elements
            $_form_items[] = array('id' => 'keywords', 'label' => __('Search '), 'type' => 'text', 'maxSize' => '200');
            $_form_items[] = array('id' => 'p', 'type' => 'hidden', 'value' => 'member');
            foreach ($_form_items as $_item) {
                $_quick_search->add($_item);
            }
            $simbio->headerBlockContent = $_quick_search;

            // add to main member
            $simbio->loadView($_datagrid, 'CONTENT_LIST');
        }
    }


    /**
     * Method returning an array of application main menu and navigation menu
     *
     * @param   object  $simbio: Simbio framework object
     * @param   string  $str_menu_type: value can be 'main' or 'navigation'
     * @param   string  $str_current_module: current module called by framework
     * @param   string  $str_current_method: current method of current module called by framework
     * @return  array
     */
    public function menu(&$simbio, $str_menu_type = 'navigation', $str_current_module = '', $str_current_method = '') {
        $_menu = array();
        if ($str_menu_type == 'main') {
            $_menu[] = array('link' => 'admin/membership', 'name' => __('Membership'), 'description' => __('Manage library membership data'));
        } else {
            if ($str_current_module == 'admin' && $str_current_method == 'membership') {
                $_menu['Membership'][] = array('link' => 'membership/add', 'name' => __('Add Member'), 'description' => __('Add new member data'));
                $_menu['Membership'][] = array('link' => 'membership', 'name' => __('Member List'), 'description' => __('List of existing member'));
                $_menu['Membership'][] = array('link' => 'membership/fields', 'name' => __('Member Data Fields'), 'description' => __('Manage additional fields of data of member'));
                $_menu['Membership'][] = array('link' => 'membership/type', 'name' => __('Membership Types'), 'description' => __('Manage membership types'));
                $_menu['Tools'][] = array('link' => 'membership/config', 'name' => __('Configuration'), 'description' => __('Module configuration'));
                $_menu['Tools'][] = array('link' => 'membership/export', 'name' => __('Export Member Data'), 'description' => __('Export member data'));
                $_menu['Tools'][] = array('link' => 'membership/import', 'name' => __('Import Member Data'), 'description' => __('Import member records'));
            }
        }
        return $_menu;
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
}
?>
