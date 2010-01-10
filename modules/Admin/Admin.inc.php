<?php
/**
 * Admin module class
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

class Admin extends SimbioModel {
    protected $dbc = null;
    private $modules = array();

    /**
     * Class constructor
     *
     * @param   object      $simbio: Simbio framework object
     * @return void
     */
    public function __construct(&$simbio) {
        $this->dbc = $simbio->getDBC();
    }


    /**
     * Method that must be defined by all child module
     * used by framework to get module information
     *
     * @param   object      $simbio: Simbio framework object
     * @return  array       an array of module information containing
     */
    public function moduleInfo(&$simbio) {

    }


    /**
     * Method that must be defined by all child module
     * used by framework to get module privileges type
     *
     * @param   object      $simbio: Simbio framework object
     * @return  array       an array of privileges for this module
     */
    public function modulePrivileges(&$simbio) {

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
     * Configuration for Biblio module
     *
     * @param   object      $simbio: Simbio framework object
     * @param   string      $str_args: method main argument
     * @return  void
     */
    public function config(&$simbio, $str_args) {

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
            header('Location: index.php?p=user/login');
        } else {
            $_global = $simbio->getGlobalConfig();
            // check for directories permission
            if (!is_writable(APP_BASE.'repositories')) {
                $simbio->addError('REPO_DIR_UNWRITABLE', __('Repositories directory ('.APP_BASE.'repositories'.') is not writable.'
                    .'Please change permission for this directory (and all under it)!'));
            }
            // check for directories permission
            if (!is_writable(APP_BASE.'files')) {
                $simbio->addError('FILES_DIR_UNWRITABLE', __('Files directory ('.APP_BASE.'files'.') is not writable.'
                    .'Please change permission for this directory (and all under it)!'));
            }
            // check for mysqldump executable
            if (!is_executable($_global['mysqldump'])) {
                $simbio->addError('MYSQLDUMP_NOT_EXECUTABLE', __('Mysqldump binary ('.$_global['mysqldump'].') not found or not executable.'
                    .'Please supply valid full path to mysqldump binary!'));
            }
            $simbio->setViewConfig('Page Title', __('Management Console'));
            $simbio->addInfo('ADMIN_INFO', __('Welcome, you are currenty logged in as ').$_SESSION['User']['Name']);
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
        $this->modules = $simbio->getModules();
        // add main menu
        foreach ($this->modules as $_module_name => $_module) {
            foreach ($_module->menu($simbio, 'main') as $_menu) {
                $simbio->addMainMenu($_menu);
            }
        }
        // navigation menu
        $str_current_module = strtolower($str_current_module);
        if (isset($this->modules[$str_current_method])) {
            $_curr_module = $this->modules[$str_current_method];
        } else {
            $_curr_module = $this;
        }
        foreach ($_curr_module->menu($simbio, 'navigation') as $_nav_section_name => $_nav_section) {
            foreach ($_nav_section as $_nav_menu) {
                $simbio->addNavigationMenu($_nav_section_name, $_nav_menu);
            }
        }
        // add Admin module javascript library
        $simbio->addJS(MODULES_WEB_BASE.'Admin/admin.js');
        // get current CLOSURE content
        $_closure = $simbio->getViews('CLOSURE');
        if (isset($_SERVER['HTTP_X_REQUESTED_WITH'])) {
            if ($_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest') {
                // append javascript string to re-register event handler
                $_closure .= '<script type="text/javascript">jQuery(\'#admin-main-content\').unRegisterAdminEvents().registerAdminEvents()</script>';
            }
        }
        // add again to closure
        $simbio->loadView($_closure, 'CLOSURE');
    }


    /**
     * Method returning an array of application main menu and navigation menu
     *
     * @param   object  $simbio: Simbio framework object
     * @param   string  $str_menu_type: value can be 'main' or 'navigation'
     * @return  array
     */
    public function menu(&$simbio, $str_menu_type = 'navigation') {
        $_menu = array();
        if ($str_menu_type == 'main') {
            return $_menu;
        } else {
            $_menu['Quick Shortcut'][] = array('link' => 'admin/system', 'name' => __('Preferences'), 'description' => __('Application wide configuration settings/preferences'));
            $_menu['Quick Shortcut'][] = array('link' => 'user/profile', 'name' => __('User Profile'), 'description' => __('View and change your user profiles such as login username and password'));
            $_menu['Quick Shortcut'][] = array('link' => 'system/backup', 'name' => __('Backup'), 'description' => __('View and create database backup'));
        }
        return $_menu;
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
        if ($str_called_method == 'index') {
            $this->index($simbio, $str_args);
        } else if (isset($this->modules[$str_called_method])) {
            // call module method
            if ($str_args) {
                // parse $str_args
                $_args = explode('/', $str_args);
                $_module_method = $_args[0];
                $_module_args = '';
                if (isset($_args[1])) {
                    $_module_args = $_args[1];
                }
                $this->modules[$str_called_method]->$_module_method($simbio, $_module_args);
            } else {
                $str_args = '';
                $this->modules[$str_called_method]->index($simbio, $str_args);
            }
        }
    }
}
?>
