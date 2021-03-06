<?php
/**
 * Taxonomy module class
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

class Taxonomy extends SimbioModel {
    /**
     * Method that must be defined by all child module
     * used by framework to get module information
     *
     * @param   object  $simbio: Simbio framework object
     * @return  array   an array of module information containing
     */
    public static function moduleInfo(&$simbio) {
        return array('module-name' => 'Taxonomy',
            'module-desc' => 'Create and manage master and hierarchical data to be used with other modules',
            'module-depends' => array());
    }


    /**
     * Method that must be defined by all child module
     * used by framework to get module privileges type
     *
     * @param   object  $simbio: Simbio framework object
     * @return  array   an array of privileges for this module
     */
    public static function modulePrivileges(&$simbio) {

    }


    /**
     * Class Constructor
     *
     * @param   object  $simbio: Simbio framework object
     */
    public function __construct(&$simbio) {
        $this->dbTable = 'taxonomy';
        // auto generate fields from database
        $this->autoGenerateFields($simbio);
        // get global config from framework
        $this->global = $simbio->getGlobalConfig();
        // get database connection
        $this->dbc = $simbio->getDBC();
    }


    /**
     * Method to add module data
     *
     * @param   object  $simbio: Simbio framework object
     * @param   string  $str_args: method main argument
     * @return  void
     */
    public function add(&$simbio, $str_args) {
        if (stripos($str_args, 'term/') !== false) {
            $this->dbTable = 'taxonomy_term';
            $this->dbFields = array(); $this->autoGenerateFields($simbio);
            // get taxonomy data
            $_taxonomy_id = (integer)str_replace('term/', '', $str_args);
            $_taxonomy = self::getTaxonomyByID($simbio, $_taxonomy_id);
            $simbio->addInfo('TAXONOMY_TERM_ADD', __('Add taxonomy term for ').$_taxonomy['taxonomy_desc']);
            // create form
            $_form = new FormOutput('form', 'index.php?p=taxonomy/save/term', 'post');
            $_form->submitName = 'add';
            $_form->submitValue = __('Save');
            $_form->submitAjax = true;
            $_form->formInfo = __('Insert Taxonomy.');
            // auto generate form
            $this->autoGenerateForm($simbio, $_form);
            // remove taxonomy element and re-add it as hidden element
            $_form->removeElement('taxonomy_id');
            $_form->add(array('type' => 'hidden', 'id' => 'taxonomy_id', 'value' => $_taxonomy_id));
            $simbio->loadView($_form, 'TAXONOMY_TERM_FORM');
        } else {
            // create form
            $_form = new FormOutput('form', 'index.php?p=taxonomy/save', 'post');
            $_form->submitName = 'add';
            $_form->submitValue = __('Save');
            $_form->submitAjax = true;
            $_form->formInfo = __('Insert Taxonomy Name and Taxonomy Description field.');
            // auto generate form
            $this->autoGenerateForm($simbio, $_form);
            $simbio->loadView($_form, 'TAXONOMY_FORM');
        }
    }


    /**
     * get taxonomy data based on ID
     *
     * @param   object  $simbio: Simbio framework object
     * @param   integer $int_taxonomy_id: an ID of taxonomy
     * @return  array   an array of taxonomy data
     */
    public static function getTaxonomyByID($simbio, $int_taxonomy_id) {
        $_q = $simbio->dbQuery('SELECT * FROM {taxonomy} WHERE taxonomy_id='.$int_taxonomy_id);
        $_d = $_q->fetch_assoc();
        return $_d;
    }


    /**
     * get taxonomy data based on name
     *
     * @param   object  $simbio: Simbio framework object
     * @param   integer $str_taxonomy_name: name identifier of taxonomy
     * @return  array   an array of taxonomy data
     */
    public static function getTaxonomyByName($simbio, $str_taxonomy_name) {
        $_q = $simbio->dbQuery('SELECT * FROM {taxonomy} WHERE taxonomy_name=\''.$str_taxonomy_name.'\'');
        $_d = $_q->fetch_assoc();
        return $_d;
    }


    /**
     * get taxonomy term list
     *
     * @param   object  $simbio: Simbio framework object
     * @param   string  $str_args: method main argument
     * @return  void
     */
    public function getTerms($simbio, $str_args) {
        $_term = '%';
        $_taxonomy = null;
        // get taxonomy name
        if ($str_args) {
            $_taxonomy = $simbio->filterizeSQLstring($str_args, true);
        }
        // get search term
        if (isset($_GET['search'])) {
            $_search_text = $simbio->filterizeSQLstring($_GET['search'], true);
            $_term = "%$_search_text%";
        }

        $_sql_str = 'SELECT `term_id` AS `listvalue`, `term` AS `listtext` FROM {taxonomy_term} AS t
            LEFT JOIN {taxonomy} AS tx ON t.taxonomy_id=tx.taxonomy_id
            WHERE `term` LIKE \''.$_term.'\''
            .( $_taxonomy?' AND taxonomy_name=\''.$_taxonomy.'\'':'' ).' ORDER BY `term` ASC LIMIT 10';

        $_q = $simbio->dbQuery($_sql_str);
        $_terms = array();
        while ($_d = $_q->fetch_assoc()) {
            $_terms[] = $_d;
        }
        $simbio->setViewConfig('load_type', 'notemplate');
        $simbio->setViewConfig('content_type', 'text/x-json');
        // emptying closure
        $simbio->loadView(null, 'CLOSURE');
        $simbio->loadView(json_encode($_terms), 'Content');
    }


    /**
     * Default module page method
     * All module must have this method
     *
     * @param   object  $simbio: Simbio framework object
     * @param   string  $str_args: method main argument
     * @return  void
     */
    public function index(&$simbio, $str_args) {
        if (User::isUserLogin()) {
            // include datagrid library
            $simbio->loadLibrary('Datagrid', SIMBIO_BASE.'Databases'.DSEP.'Datagrid.inc.php');
            // create datagrid instance
            $_datagrid = new Datagrid($this->dbc);
            // set column to view in datagrid
            $_datagrid->setSQLColumn(array('ID' => 'taxonomy_id', 'Taxonomy Name' => 'taxonomy_name',
                'Taxonomy Description' => 'taxonomy_desc'));
            // set primary key for detail view
            $_datagrid->setPrimaryKeys(array('ID'));
            // set record actions
            $_action['Del.'] = '<input type="checkbox" name="record[]" value="{rowIDs}" />';
            $_action['Edit'] = '<a class="datagrid-links" href="index.php?p=taxonomy/update/{rowIDs}">&nbsp;</a>';
            $_datagrid->setRowActions($_action);
            // set multiple record action options
            $_action_options[] = array('0', 'Select action');
            $_action_options[] = array('taxonomy/delete', 'Remove selected record');
            $_datagrid->setActionOptions($_action_options);
            // set result ordering
            $_datagrid->setSQLOrder('taxonomy_name ASC');
            // search criteria
            if (isset($_GET['keywords'])) {
                $_search = $simbio->filterizeSQLString($_GET['keywords'], true);
                $_criteria = "taxonomy_name LIKE '$_search%' OR taxonomy_desc LIKE '$_search%'";
                $_datagrid->setSQLCriteria($_criteria);
            }
            // built the datagrid
            $_datagrid->create($this->global['db_prefix'].'taxonomy');

            // set header
            $simbio->headerBlockTitle = 'Taxonomy';
            $simbio->headerBlockMenu = array(
                    array('class' => 'add', 'link' => 'taxonomy/add', 'title' => 'Add Taxonomy', 'desc' => 'Add new Taxonomy'),
                    array('class' => 'list', 'link' => 'taxonomy', 'title' => 'Taxonomy List', 'desc' => 'View list of existing Taxonomy')
                );
            // build search form
            $_quick_search = new FormOutput('search', 'index.php', 'get');
            $_quick_search->submitName = 'search';
            $_quick_search->submitValue = __('Search');
            // define form elements
            $_form_items[] = array('id' => 'keywords', 'label' => __('Search '), 'type' => 'text', 'maxSize' => '200');
            $_form_items[] = array('id' => 'p', 'type' => 'hidden', 'value' => 'taxonomy');
            foreach ($_form_items as $_item) {
                $_quick_search->add($_item);
            }
            $simbio->headerBlockContent = $_quick_search;

            // add to main content
            $simbio->loadView($_datagrid, 'TAXONOMY_LIST');
        }
    }


    /**
     * Method to manage each taxonomy
     *
     * @param   object  $simbio: Simbio framework object
     * @param   string  $str_args: method main argument
     * @return  array
     */
    public function manage(&$simbio, $str_args) {
        $_taxonomy_id = (integer)$str_args;
        // get taxonomy data from database
        $_taxonomy_q = $simbio->dbQuery('SELECT * FROM {taxonomy} WHERE taxonomy_id='.$_taxonomy_id);
        $_taxonomy_d = $_taxonomy_q->fetch_assoc();

        // include datagrid library
        $simbio->loadLibrary('Datagrid', SIMBIO_BASE.'Databases'.DSEP.'Datagrid.inc.php');
        // create datagrid instance
        $_datagrid = new Datagrid($this->dbc);
        // set column to view in datagrid
        $_datagrid->setSQLColumn(array('ID' => 'term_id', 'Term' => 'term',
            'Notes' => 'term_notes'));
        // set primary key for detail view
        $_datagrid->setPrimaryKeys(array('ID'));
        // set record actions
        $_action['Del.'] = '<input type="checkbox" name="record[]" value="{rowIDs}" />';
        $_action['Edit'] = '<a class="datagrid-links" href="index.php?p=taxonomy/update/term/{rowIDs}">&nbsp;</a>';
        $_datagrid->setRowActions($_action);
        // set multiple record action options
        $_action_options[] = array('0', 'Select action');
        $_action_options[] = array('taxonomy/delete/term', 'Remove selected terms');
        $_datagrid->setActionOptions($_action_options);
        // set result ordering
        $_datagrid->setSQLOrder('term ASC');
        // search criteria
        $_criteria = 'taxonomy_id='.$_taxonomy_id;
        if (isset($_GET['keywords'])) {
            $_search = $simbio->filterizeSQLString($_GET['keywords'], true);
            $_criteria .= " AND term LIKE '%$_search%'";
        }
        $_datagrid->setSQLCriteria($_criteria);
        // built the datagrid
        $_datagrid->create($this->global['db_prefix'].'taxonomy_term');

        if (!isset($_GET['nohead'])) {
            // set header
            $simbio->headerBlockTitle = $_taxonomy_d['taxonomy_desc'].' Taxonomy';
            $simbio->headerBlockMenu = array(
                    array('class' => 'add', 'link' => 'taxonomy/add/term/'.$_taxonomy_id, 'title' => 'Add Taxonomy Term', 'desc' => 'Add new Taxonomy term'),
                    array('class' => 'list', 'link' => 'taxonomy/manage/'.$_taxonomy_id, 'title' => 'Taxonomy Term List', 'desc' => 'View list of existing Taxonomy terms')
                );
            // build search form
            $_quick_search = new FormOutput('search', 'index.php', 'get');
            $_quick_search->submitName = 'search';
            $_quick_search->submitValue = __('Search');
            // define form elements
            $_form_items[] = array('id' => 'keywords', 'label' => __('Search '), 'type' => 'text', 'maxSize' => '200');
            $_form_items[] = array('id' => 'p', 'type' => 'hidden', 'value' => 'taxonomy/manage/'.$_taxonomy_id);
            foreach ($_form_items as $_item) {
                $_quick_search->add($_item);
            }
            $simbio->headerBlockContent = $_quick_search;
        }

        // add to main content
        $simbio->loadView($_datagrid, 'TAXONOMY_LIST');
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
            $_menu[] = array('link' => 'admin/taxonomy', 'name' => 'Taxonomy', 'description' => __('Manage hierarchical referencial data such as types, classification etc.'));
        } else {
            if ($str_current_module == 'admin' && $str_current_method == 'taxonomy') {
                $_menu['Manage'][] = array('link' => 'taxonomy/add', 'name' => __('Add Taxonomy'), 'description' => __('Add new taxonomy schema'));
                $_menu['Manage'][] = array('link' => 'taxonomy', 'name' => __('Taxonomy List'), 'description' => __('List available taxonomy schema'));
                // get taxonomy list from database
                $_taxonomy_q = $simbio->dbQuery('SELECT * FROM {taxonomy} ORDER BY taxonomy_desc ASC');
                while ($_taxonomy_d = $_taxonomy_q->fetch_assoc()) {
                    $_menu['Taxonomies'][] = array('link' => 'taxonomy/manage/'.$_taxonomy_d['taxonomy_id'],
                        'name' => ucwords($_taxonomy_d['taxonomy_desc']), 'description' => $_taxonomy_d['taxonomy_desc']);
                }
            }
        }
        return $_menu;
    }


    /**
     * Method to update module data
     *
     * @param   object  $simbio: Simbio framework object
     * @param   string  $str_args: method main argument
     * @return  void
     */
    public function update(&$simbio, $str_args) {
        if (stripos($str_args, 'term/') !== false) {
            $_term_id = (integer)str_replace('term/', '', $str_args);
            // get record data
            $this->dbTable = 'taxonomy_term';
            $this->dbFields = array(); $this->autoGenerateFields($simbio);
            $_rec = $this->getRecords($simbio, array('term_id' => $_term_id));
            if (!$_rec) {
                $simbio->addError('RECORD_NOT_FOUND', __("Taxonomy record not found!"));
                return;
            }
            // create form
            $_form = new FormOutput('form', 'index.php?p=taxonomy/save/term', 'post');
            $_form->submitName = 'update';
            $_form->submitValue = __('Update');
            $_form->submitAjax = true;
            $_form->includeReset = true;
            $_form->disabled = true;
            $_form->formInfo = '<div class="form-update-buttons"><a href="#" class="form-unlock">'.__('Unlock Form').'</a>'
                .' <a href="#" class="form-cancel">'.__('Cancel').'</a>'
                .'</div><hr size="1" />';
            // auto generate form
            $_elms = $this->autoGenerateForm($simbio);
            // add form and set form field value
            foreach ($_elms as $_elm) {
                foreach ($_rec[0] as $_field => $_value) {
                    if ($_elm['id'] == $_field) {
                        $_elm['value'] = $_value;
                        $_form->add($_elm);
                    }
                }
            }
            // add update ID
            $_form->removeElement('taxonomy_id');
            $_form->add(array('id' => 'updateID', 'type' => 'hidden', 'value' => $_term_id));
            $simbio->addInfo('UPDATE_RECORD_INFO', __('You are going to update Taxonomy Term'));
        } else {
            $_taxonomy_id = (integer)$str_args;
            // get record data
            $_rec = $this->getRecords($simbio, array('taxonomy_id' => $_taxonomy_id));
            if (!$_rec) {
                $simbio->addError('RECORD_NOT_FOUND', __("Taxonomy record not found!"));
                return;
            }
            // create form
            $_form = new FormOutput('form', 'index.php?p=taxonomy/save', 'post');
            $_form->submitName = 'update';
            $_form->submitValue = __('Update');
            $_form->submitAjax = true;
            $_form->includeReset = true;
            $_form->disabled = true;
            $_form->formInfo = '<div class="form-update-buttons"><a href="#" class="form-unlock">'.__('Unlock Form').'</a>'
                .' <a href="#" class="form-cancel">'.__('Cancel').'</a>'
                .'</div><hr size="1" />';
            // auto generate form
            $_elms = $this->autoGenerateForm($simbio);
            // add form and set form field value
            foreach ($_elms as $_elm) {
                foreach ($_rec[0] as $_field => $_value) {
                    if ($_elm['id'] == $_field) {
                        $_elm['value'] = $_value;
                        $_form->add($_elm);
                    }
                }
            }
            // add update ID
            $_form->add(array('id' => 'updateID', 'type' => 'hidden', 'value' => $_taxonomy_id));
            $simbio->addInfo('UPDATE_RECORD_INFO', __('You are going to update Taxonomy record'));
        }
        $simbio->loadView($_form, 'TAXONOMY_FORM');
    }


    /**
     * Method to remove module data
     *
     * @param   object  $simbio: Simbio framework object
     * @param   string  $str_args: method main argument
     * @return  void
     */
    public function delete(&$simbio, $str_args) {
        if ($str_args == 'term') {
            if (isset($_POST['record'])) {
                // convert scalar var to array var
                if (!is_array($_POST['record'])) {
                    $_POST['record'][0] = $_POST['record'];
                }
                foreach ($_POST['record'] as $_rec_ID) {
                    $_rec_ID = (integer)$_rec_ID;
                    $simbio->dbDelete('term_id='.$_rec_ID, 'taxonomy_term');
                }
            }
            $simbio->loadView('<script type="text/javascript">jQuery.ajaxPrevious();</script>', 'CLOSURE');
        } else {
            if (isset($_POST['record'])) {
                // convert scalar var to array var
                if (!is_array($_POST['record'])) {
                    $_POST['record'][0] = $_POST['record'];
                }
                foreach ($_POST['record'] as $_rec_ID) {
                    $_rec_ID = (integer)$_rec_ID;
                    $simbio->dbDelete('taxonomy_id='.$_rec_ID, 'taxonomy');
                }
            }
            // get current CLOSURE content
            $_closure = $simbio->getViews('CLOSURE');
            $_closure .= '<script type="text/javascript">top.location.href = \'index.php?p=admin/taxonomy\';</script>';
            // add again to closure
            $simbio->loadView($_closure, 'CLOSURE');
        }
    }


    /**
     * Method to save/update module data
     *
     * @param   object  $simbio: Simbio framework object
     * @param   string  $str_args: method main argument
     * @return  array   an array of status flag and messages
     */
    public function save(&$simbio, $str_args) {
        if ($str_args == 'term') {
            $_data['term'] = $simbio->filterizeSQLString($_POST['term'], true);
            $_data['term_notes'] = $simbio->filterizeSQLString($_POST['term_notes'], true);
            $_data['input_date'] = date('Y-m-d h:i:s');
            $_data['last_update'] = date('Y-m-d h:i:s');
            $_on_update = isset($_POST['update']);
            if ($_on_update) {
                unset($_data['input_date']);
                $_id = (integer)$_POST['updateID'];
                $_update = $simbio->dbUpdate($_data, 'taxonomy_term', 'term_id='.$_id);
            } else if (isset($_POST['add'])) {
                $_data['taxonomy_id'] = (integer)$_POST['taxonomy_id'];
                $_update = $simbio->dbInsert($_data, 'taxonomy_term');
            }
            if (!$_update) {
                $simbio->addError('RECORD_UPDATE_ERROR', 'Failed to update record for Taxonomy Term data. Please contact your system administrator!');
                return;
            }
            if ($_on_update) {
                $simbio->loadView('<script type="text/javascript">jQuery.ajaxPrevious();</script>', 'CLOSURE');
            } else {
                $simbio->loadView('<script type="text/javascript">jQuery(\'#admin-main-content\').load(\'index.php?p=taxonomy/manage/'.$_data['taxonomy_id'].'\');</script>', 'CLOSURE');
            }
        } else {
            $_data['taxonomy_name'] = $simbio->filterizeSQLString($_POST['taxonomy_name'], true);
            $_data['taxonomy_desc'] = $simbio->filterizeSQLString($_POST['taxonomy_desc'], true);
            $_data['input_date'] = date('Y-m-d h:i:s');
            $_data['last_update'] = date('Y-m-d h:i:s');
            if (isset($_POST['update'])) {
                unset($_data['input_date']);
                $_id = (integer)$_POST['updateID'];
                $_update = $simbio->dbUpdate($_data, 'taxonomy', 'taxonomy_id='.$_id);
            } else if (isset($_POST['add'])) {
                $_update = $simbio->dbInsert($_data, 'taxonomy');
            }
            if (!$_update) {
                $simbio->addError('RECORD_UPDATE_ERROR', 'Failed to update record for Taxonomy data. Please contact your system administrator!');
                return;
            }
            $simbio->loadView('<script type="text/javascript">top.location.href = \'index.php?p=admin/taxonomy\';</script>', 'CLOSURE');
        }
    }
}
?>
