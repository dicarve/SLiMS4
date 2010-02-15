<?php
/**
 * System module class
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

class System extends SimbioModel {
    /**
     * Class contructor
     *
     * @param   object  $simbio: Simbio framework object
     * @return  void
     */
    public function __construct(&$simbio) {
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
        return array('module-name' => 'System',
            'module-desc' => 'Manages application wide configuration such as database backup, user and privileges management, etc.',
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
     * Configuration
     *
     * @param   object      $simbio: Simbio framework object
     * @param   string      $str_args: method main argument
     * @return  void
     */
    public function config(&$simbio, $str_args) {
        if (User::isUserLogin()) {
            // create config form
            $_form = new FormOutput('form', 'index.php?p=system/saveconfig', 'post');
            $_form->submitName = 'add';
            $_form->submitAjax = true;
            $_form->submitValue = __('Save Configuration');
            $_form->includeReset = true;
            $_form->disabled = true;
            $_form->formInfo = '<div class="form-update-buttons"><a href="#" class="form-unlock">'.__('Change Configuration').'</a>'
                .'</div><hr size="1" />';
            // set non configurable settings
            $_non_configurable = array('default_module', 'show_error', 'db_uri', 'db_prefix');
            // get all global config
            foreach ($this->global as $_config_name => $_config_val) {
                if (in_array($_config_name, $_non_configurable)) {
                    continue;
                }
                $_config_val = htmlentities(trim($_config_val));
                $_config_label = ucwords(str_replace(array('_', '-'), ' ', $_config_name));
                $_form->add(array('id' => $_config_name, 'type' => 'text', 'label' => $_config_label, 'value' => $_config_val));
            }

            // set header
            $simbio->headerBlockTitle = 'System Configuration';
            $simbio->headerBlockContent = __('Global application configuration');

            $simbio->loadView($_form, 'SYSCONFIG_FORM');
        }
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
            // get database connection object
            $this->dbc = $simbio->getDBC();
            // add simbio table library
            $simbio->loadLibrary('Table', SIMBIO_BASE.'UI'.DSEP.'Table'.DSEP.'Table.inc.php');
            // create table object
            $_sysinfo = new Table();
            $_sysinfo->tableAttr = Utility::createHTMLAttribute(array('cellspacing' => '0'));
            $_sysinfo->tableName = 'sysinfo';
            $_sysinfo->alternateRow = false;
            // system information data
            $_infos[] = array('SLiMS version', APP_VERSION);
            $_infos[] = array(__('PHP version'), PHP_VERSION);
            $_infos[] = array(__('MySQL server version'), ($this->dbc->server_version/10000));
            $_infos[] = array(__('PHP MySQL extension used'), is_a($this->dbc, 'mysqli')?'MySQLi':'MySQL' );
            $_infos[] = array(__('Operating System'), PHP_OS);
            $_infos[] = array(__('Web Server'), $_SERVER['SERVER_SOFTWARE']);
            $_infos[] = array(__('Web Browser'), $_SERVER['HTTP_USER_AGENT']);
            $_infos[] = array(__('License'), 'SLiMS is licensed under GNU GPL version 3. SIMBIO3 Framework is licensed under GNU GPL version 3');
            // add system information
            $_r = 1;
            foreach ($_infos as $_info) {
                $_sysinfo->appendTableRow($_info);
                $_sysinfo->setCellAttr($_r, 0, 'class=\'sysinfo-label\'');
                $_r++;
            }

            $simbio->headerBlockTitle = 'System Information';
            $simbio->headerBlockContent = '<div class="sysinfo">'.__('Table below show information about application platforms where SLiMS is running').'</div>';

            // load the view
            $simbio->loadView($_sysinfo, 'SYSINFO');
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
        if ($str_current_module == 'admin' || $str_current_module == 'system') {
            $simbio->addCSS(MODULES_WEB_BASE.'System/system.css');
            // add Admin module javascript library
            $simbio->addJS(MODULES_WEB_BASE.'System/system.js');
            // get current CLOSURE content
            $_closure = $simbio->getViews('CLOSURE');
            if (isset($_SERVER['HTTP_X_REQUESTED_WITH'])) {
                if ($_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest') {
                    // append javascript string to re-register event handler
                    $_closure .= '<script type="text/javascript">jQuery(\'#admin-main-content\').unRegisterEvents().registerSystemEvents()</script>';
                }
            }
            // add again to closure
            $simbio->loadView($_closure, 'CLOSURE');
        }
    }


    /**
     * Module to check if module installed
     *
     * @param   object  $simbio: Simbio framework object
     * @param   string  $str_module_name: module name to check
     * @return  boolean
     */
    public static function isModuleInstalled(&$simbio, $str_module_name) {
        $_q = $simbio->dbQuery('SELECT COUNT(module_id) FROM {modules} WHERE module_name LIKE \''.$str_module_name.'\'');
        $_d = $_q->fetch_row();
        $_installed = $_d[0];
        if ($_installed > 0) {
            return true;
        }
        return false;
    }


    /**
     * Method returning an array of application main menu and navigation menu
     *
     * @param   object      $simbio: Simbio framework object
     * @param   string      $str_args: method main argument
     * @param   string  $str_current_module: current module called by framework
     * @param   string  $str_current_method: current method of current module called by framework
     * @return  array
     */
    public function menu(&$simbio, $str_menu_type = 'navigation', $str_current_module = '', $str_current_method = '') {
        $_menu = array();
        if ($str_menu_type == 'main') {
            $_menu[] = array('link' => 'admin/system', 'name' => __('System'), 'description' => __('Manages application wide configurations, backup, logs, user etc.'));
        } else {
            if ($str_current_module == 'admin' && $str_current_method == 'system') {
                $_menu['System'][] = array('link' => 'system', 'name' => __('System Information'), 'description' => __('System information'));
                $_menu['System'][] = array('link' => 'system/config', 'name' => __('Configuration'), 'description' => __('Application global configuration'));
                $_menu['System'][] = array('link' => 'system/modules', 'name' => __('Modules'), 'description' => __('Application modules and plugins management'));
                $_menu['System'][] = array('link' => 'content/manage', 'name' => __('Content'), 'description' => __('Content management'));
                $_menu['System'][] = array('link' => 'system/logs', 'name' => __('System Logs'), 'description' => __('View system logs'));
                $_menu['System'][] = array('link' => 'system/optimize', 'name' => __('Optimize Database'), 'description' => __('Optimize database storage'));
                $_menu['System'][] = array('link' => 'system/backup', 'name' => __('Backup'), 'description' => __('Manage and create database backup'));
            }
        }
        return $_menu;
    }


    /**
     * Method returning an array of application main menu and navigation menu
     *
     * @param   object      $simbio: Simbio framework object
     * @param   string      $str_args: method main argument
     * @return  array
     */
    public function modules(&$simbio, $str_args) {
        if (!$str_args) {
            // show list of all available modules
            // scan modules directory
            $_modules = scandir(MODULES_BASE);
            if ($_modules) {
                // create table object
                $_listing = new Table(array('cellspacing' => 0, 'cellpadding' => 3));
                $_listing->tableName = 'datagrid';
                // set header
                $_listing->setHeader(array('<a href="#" id="install-all">'.__('Install All').'</a>',
                    '<span class="datagrid-head-plain">'.__('Module Name').'</span>',
                    '<span class="datagrid-head-plain">'.__('Description').'</span>',
                    '<span class="datagrid-head-plain">'.__('Dependancies').'</span>'));
                // iterate each module directory
                foreach ($_modules as $_module) {
                    if (is_file(MODULES_BASE.$_module) || in_array($_module, array('.', '..'))) {
                        continue;
                    }
                    // check if module class exists
                    if (!file_exists(MODULES_BASE.$_module.DSEP.$_module.'.inc.php')) {
                        continue;
                    }
                    // include the module
                    require_once MODULES_BASE.$_module.DSEP.$_module.'.inc.php';
                    // get module info
                    $_info = call_user_func(array($_module, 'moduleInfo'), $simbio);
                    if ($_info) {
                        $_checked = '';
                        $_installed = System::isModuleInstalled($simbio, $_info['module-name']);
                        if ($_installed) {
                            $_checked = ' checked';
                        }
                        // prepend chekcbox
                        $_info = array_merge(array('<input type="checkbox" name="modules[]" value="'.$_info['module-name'].'"'.$_checked.' />'), $_info);
                        $_listing->appendTableRow($_info);
                    }
                }

                $_head = '<form name="module-install" id="module-install" action="index.php?p=system/module/install">'
                    .'<div class="datagrid-action"><input type="submit" name="install" value="'.__('Install selected modules').'" /></div>';
                $simbio->loadView($_head, 'MODULE_LISTING_HEAD');
                $simbio->loadView($_listing, 'MODULE_LISTING');
                $simbio->loadView('</form>', 'MODULE_LISTING_FOOT');
            } else {
                $simbio->addInfo('NO_MODULES', 'There is no additional modules detected.');
            }
        } else {

        }
        // set header
        $simbio->headerBlockTitle = 'Modules';
        $simbio->headerBlockContent = __('Application modules/plugins configuration.');
    }


    /**
     * Method to save configuration settings
     *
     * @param   object      $simbio: Simbio framework object
     * @param   string      $str_args: method main argument
     * @return  array
     */
    public function saveConfig(&$simbio, $str_args) {

    }
}
?>
