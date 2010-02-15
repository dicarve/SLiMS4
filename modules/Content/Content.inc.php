<?php
/**
 * Content module class
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

class Content extends SimbioModel {
    /**
     * Class contructor
     *
     * @param   object  $simbio: Simbio framework object
     * @return  void
     */
    public function __construct(&$simbio) {
        $this->dbTable = 'content';
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
        return array('module-name' => 'Content',
            'module-desc' => 'Add content management system like capabilities to application',
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
        // create form
        $_form = new FormOutput('form', 'index.php?p=content/manage/save', 'post');
        $_form->submitName = 'add';
        $_form->submitIframe = true;
        $_form->submitValue = __('Save');
        $_form->formInfo = __('Fill all required content\'s field');
        // auto generate form
        $this->autoGenerateForm($simbio, $_form);
        // change published field element to radio button
        $_opt_publish[] = array('1', __('Published')); $_opt_publish[] = array('0', __('Not Published'));
        $_form->add(array('id' => 'published', 'label' => __('Published'), 'type' => 'radio', 'options'=> $_opt_publish, 'value' => '1'));
        $_form->add(array('id' => 'content_type', 'label' => __('Content Type'), 'type' => 'text', 'class' => 'searchable', 'attr' => array('search' => 'taxonomy/getterms/content_type')));
        $simbio->loadView($_form, 'CONTENT_FORM');
    }


    /**
     * Datagrid callback method for published field
     *
     * @param   object      $obj_db: database connection object
     * @param   array       $arr_record_data: an array of current record data
     * @param   object      $obj_datagrid: datagrid object
     * @return  void
     */
    public static function cbPublished($obj_db, $arr_record_data, &$obj_datagrid) {
        return ($arr_record_data['Published'] == '1')?'<div class="published">Published</div>':'<div class="unpublished">Not Published</div>';
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
        if (User::isUserLogin()) {
            // include datagrid library
            $simbio->loadLibrary('Datagrid', SIMBIO_BASE.'Databases'.DSEP.'Datagrid.inc.php');
            // create datagrid instance
            $_datagrid = new Datagrid($this->dbc);
            // set column to view in datagrid
            $_datagrid->setSQLColumn(array('ID' => 'content_id', 'Title' => 'content_title',
                'Path' => 'content_path', 'Published' => 'published'));
            // set primary key for detail view
            $_datagrid->setPrimaryKeys(array('ID'));
            // set record actions
            $_action['Del.'] = '<input type="checkbox" name="record[]" value="{rowIDs}" />';
            $_action['Edit'] = '<a class="datagrid-links" href="index.php?p=content/manage/update/{rowIDs}">&nbsp;</a>';
            $_datagrid->setRowActions($_action);
            // set multiple record action options
            $_action_options[] = array('0', 'Select action');
            $_action_options[] = array('content/manage/remove', 'Remove selected content');
            $_action_options[] = array('content/manage/publish', 'Publish selected content');
            $_action_options[] = array('content/manage/unpublish', 'Un-Publish selected content');
            $_datagrid->setActionOptions($_action_options);
            // set result ordering
            $_datagrid->setSQLOrder('input_date DESC');
            // search criteria
            if (isset($_GET['keywords'])) {
                $_search = $simbio->filterizeSQLString($_GET['keywords'], true);
                $_criteria = "MATCH(content_title, content_body) AGAINST ('$_search' IN BOOLEAN MODE)";
                $_datagrid->setSQLCriteria($_criteria);
            }
            // field callback
            $_datagrid->modifyColumnContent('Published', 'callback{Content::cbPublished}');
            // built the datagrid
            $_datagrid->create($this->global['db_prefix'].'content');

            // set header
            $simbio->headerBlockTitle = 'Content';
            $simbio->headerBlockMenu = array(
                    array('class' => 'add', 'link' => 'content/manage/add', 'title' => __('Add Content'), 'desc' => __('Add new Content')),
                    array('class' => 'list', 'link' => 'content/manage', 'title' => __('Content List'), 'desc' => __('View list of existing Content'))
                );
            // build search form
            $_quick_search = new FormOutput('search', 'index.php', 'get');
            $_quick_search->submitName = 'search';
            $_quick_search->submitValue = __('Search');
            // define form elements
            $_form_items[] = array('id' => 'keywords', 'label' => __('Search '), 'type' => 'text', 'maxSize' => '200');
            $_form_items[] = array('id' => 'p', 'type' => 'hidden', 'value' => 'content/manage');
            foreach ($_form_items as $_item) {
                $_quick_search->add($_item);
            }
            $simbio->headerBlockContent = $_quick_search;

            // add to main content
            $simbio->loadView($_datagrid, 'CONTENT_LIST');
        }
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
        if (($str_current_module == 'content' || $str_current_module == 'admin')) {
            // add CSS for rich text editor
            $simbio->addCSS(MODULES_WEB_BASE.'Content/jquery.rte.css');
            // add rich text editor library
            $simbio->addJS(MODULES_WEB_BASE.'Content/jquery.rte.js');
            $simbio->addJS(MODULES_WEB_BASE.'Content/jquery.rte.tb.js');
            $simbio->addJS(MODULES_WEB_BASE.'Content/content.js');
            if ($str_current_module == 'content' && $str_current_method == 'manage' && preg_match('@^(add|update)@i', $str_args)) {
                // get current CLOSURE content
                $_closure = $simbio->getViews('CLOSURE');
                $_closure .= '<script type="text/javascript">jQuery(document).ready(function() { initRTE(); })</script>';
                // add again to closure
                $simbio->loadView($_closure, 'CLOSURE');
            }
        }
    }


    /**
     * Method returning an array of application main menu and navigation menu
     *
     * @param   object  $simbio: Simbio framework object
     * @param   string  $str_args: method main argument
     * @param   string  $str_current_module: current module called by framework
     * @param   string  $str_current_method: current method of current module called by framework
     * @return  array
     */
    public function menu(&$simbio, $str_menu_type = 'navigation', $str_current_module = '', $str_current_method = '') {
        $_menu = array();
        if ($str_menu_type != 'main' && $str_current_module == 'admin' && $str_current_method == 'system') {
            $_menu['System'][] = array('link' => 'content/manage', 'name' => __('Content'), 'description' => __('Content management'));
        }
        return $_menu;
    }


    /**
     * Method to change publish state of content
     *
     * @param   object  $simbio: Simbio framework object
     * @param   string  $str_args: method main argument
     * @return  void
     */
    public function publish(&$simbio, $str_args) {
        if (isset($_POST['record'])) {
            // convert scalar var to array var
            if (!is_array($_POST['record'])) {
                $_POST['record'][0] = $_POST['record'];
            }
            foreach ($_POST['record'] as $_rec_ID) {
                $_rec_ID = (integer)$_rec_ID;
                $_data['published'] = '1';
                $simbio->dbUpdate($_data, 'content', 'content_id='.$_rec_ID);
            }
        }
        $this->index(&$simbio, $str_args);
    }


    /**
     * Method to change unpublish state of content
     *
     * @param   object      $simbio: Simbio framework object
     * @param   string      $str_args: method main argument
     * @return  void
     */
    public function unPublish(&$simbio, $str_args) {
        if (isset($_POST['record'])) {
            // convert scalar var to array var
            if (!is_array($_POST['record'])) {
                $_POST['record'][0] = $_POST['record'];
            }
            foreach ($_POST['record'] as $_rec_ID) {
                $_rec_ID = (integer)$_rec_ID;
                $_data['published'] = '0';
                $simbio->dbUpdate($_data, 'content', 'content_id='.$_rec_ID);
            }
        }
        $this->index(&$simbio, $str_args);
    }


    /**
     * Method to update module data
     *
     * @param   object      $simbio: Simbio framework object
     * @param   string      $str_args: method main argument
     * @return  void
     */
    public function update(&$simbio, $str_args) {
        $_content_id = (integer)$str_args;
        // get record data
        $_rec = $this->getRecords($simbio, array('content_id' => $_content_id));
        if (!$_rec) {
            $simbio->addError('RECORD_NOT_FOUND', __("Content data not found!"));
            return;
        }
        // create form
        $_form = new FormOutput('form', 'index.php?p=content/manage/save', 'post');
        $_form->submitName = 'update';
        $_form->submitIframe = true;
        $_form->submitValue = __('Update');
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
        // change published field element to radio button
        $_opt_publish[] = array('1', __('Published')); $_opt_publish[] = array('0', __('Not Published'));
        $_form->add(array('id' => 'published', 'label' => __('Published'), 'type' => 'radio', 'options'=> $_opt_publish, 'value' => $_rec[0]['published']));
        // add update ID
        $_form->add(array('id' => 'updateID', 'type' => 'hidden', 'value' => $_content_id));
        $simbio->addInfo('UPDATE_RECORD_INFO', __('You are going to update Content data'));
        $simbio->loadView($_form, 'CONTENT_FORM');
    }


    /**
     * Method to remove module data
     *
     * @param   object      $simbio: Simbio framework object
     * @param   string      $str_args: method main argument
     * @return  void
     */
    public function remove(&$simbio, $str_args) {
        if (isset($_POST['record'])) {
            // convert scalar var to array var
            if (!is_array($_POST['record'])) {
                $_POST['record'][0] = $_POST['record'];
            }
            foreach ($_POST['record'] as $_rec_ID) {
                $_rec_ID = (integer)$_rec_ID;
                $simbio->dbDelete('content_id='.$_rec_ID, 'content');
            }
        }
        $this->index(&$simbio, $str_args);
    }


    /**
     * Rerouting module method
     *
     * @param   object      $simbio: Simbio framework object
     * @param   string      $str_called_method: a method called by framework
     * @param   string      $str_args: method main argument
     * @return  void
     */
    public function reRoute(&$simbio, $str_called_method, $str_args) {
        $str_called_method = $simbio->filterizeSQLString($str_called_method);
        if ($str_called_method == 'manage') {
            if (!$str_args) {
                $str_args = 'index';
            }
            if (preg_match('@\/@i', $str_args)) {
                $_method_args = explode('/', $str_args);
                $_method = isset($_method_args[0])?$_method_args[0]:'index';
                $_method_args = isset($_method_args[1])?$_method_args[1]:'none';
                $this->$_method($simbio, $_method_args);
            } else {
                $this->$str_args($simbio, 'none');
            }
        } else {
            $_content_file = LIBS_BASE.'contents'.DSEP.$str_called_method.'.inc.php';
            if (file_exists($_content_file)) {
                ob_start();
                require $_content_file;
                $_content_str = '<div class="content-title">'.$title.'</div>'."\n";
                $_content_str .= '<div class="content-content">'.ob_get_clean().'</div>';
                $simbio->setViewConfig('Page Title', $title);
                $simbio->loadView($_content_str, 'Content');
            } else {
                // get content from database
                $_content = $this->getRecords($simbio, array('content_path' => $str_called_method), array('content_title', 'content_body'));
                if ($_content) {
                    $_content_str = '<div class="content-title">'.$_content[0]['content_title'].'</div>'."\n";
                    $_content_str .= '<div class="content-content">'.$_content[0]['content_body'].'</div>';
                    $simbio->setViewConfig('Page Title', $_content[0]['content_title']);
                    $simbio->loadView($_content_str, 'Content');
                }
            }
        }
    }


    /**
     * Method to save/update module data
     *
     * @param   object      $simbio: Simbio framework object
     * @param   string      $str_args: method main argument
     * @return  array       an array of status flag and messages
     */
    public function save(&$simbio, $str_args) {
        $_data['content_title'] = $simbio->filterizeSQLString($_POST['content_title'], true);
        // don't filterize HTML on body
        $_data['content_body'] = $simbio->filterizeSQLString($_POST['content_body'], false);
        $_data['content_path'] = $simbio->filterizeSQLString($_POST['content_path'], true);
        $_data['content_type'] = $simbio->filterizeSQLString($_POST['content_type'], true);
        $_data['published'] = $simbio->filterizeSQLString($_POST['published'], true);
        $_data['input_date'] = date('Y-m-d h:i:s');
        $_data['last_update'] = date('Y-m-d h:i:s');
        // do update
        if (isset($_POST['update'])) {
            unset($_data['input_date']);
            $_id = (integer)$_POST['updateID'];
            $_update = $simbio->dbUpdate($_data, 'content', 'content_id='.$_id);
        } else if (isset($_POST['add'])) {
            $_update = $simbio->dbInsert($_data, 'content');
        }
        if (!$_update) {
            $simbio->addError('RECORD_UPDATE_ERROR', 'Failed to update record for Content data. Please contact your system administrator!');
            $simbio->setViewConfig('load_type', 'notemplate');
            // send information to parent window since we submitted via iframe
            $_js_string = '<script type="text/javascript">';
            $_js_string .= 'top.alert(\'Failed to update record for Content data. Please contact your system administrator!\');';
            $_js_string .= '</script>';
            $simbio->loadView($_js_string, 'CLOSURE');
        } else {
            $simbio->setViewConfig('load_type', 'notemplate');
            // send information to parent window since we submitted via iframe
            $_js_string = '<script type="text/javascript">';
            $_js_string .= 'top.jQuery(\'#admin-main-content\').simbioAJAX(\'index.php?p=content/manage/index\');';
            $_js_string .= '</script>';
            $simbio->loadView($_js_string, 'CLOSURE');
        }
    }
}
?>
