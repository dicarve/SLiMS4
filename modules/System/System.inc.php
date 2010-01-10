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
    protected $dbc = null;

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
            $_infos[] = array('PHP version', PHP_VERSION);
            $_infos[] = array('MySQL server version', ($this->dbc->server_version/10000));
            $_infos[] = array('Operating System', PHP_OS);
            $_infos[] = array('Web Server', $_SERVER['SERVER_SOFTWARE']);
            $_infos[] = array('Web Browser', $_SERVER['HTTP_USER_AGENT']);
            // add system information
            $_r = 1;
            foreach ($_infos as $_info) {
                $_sysinfo->appendTableRow($_info);
                $_sysinfo->setCellAttr($_r, 0, 'class=\'sysinfo-label\'');
                $_r++;
            }
            // load the view
            $simbio->loadView('<div class="content-title">'.__('System Information').'</div>', 'SYSINFO HEAD');
            $simbio->loadView($_sysinfo, 'SYSINFO');
        }
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
            $_menu[] = array('link' => 'admin/system', 'name' => 'System', 'description' => 'Manages application wide configurations, backup, logs, user etc.');
        } else {
            // $_menu['System'][] = array('link' => 'system/phpinfo', 'name' => 'PHP Information', 'description' => 'PHP information');
            $_menu['System'][] = array('link' => 'system', 'name' => 'System Information', 'description' => 'System information');
            $_menu['System'][] = array('link' => 'system/config', 'name' => 'Configuration', 'description' => 'Application global configuration');
            $_menu['System'][] = array('link' => 'system/modules', 'name' => 'Modules', 'description' => 'Application modules and plugins management');
            $_menu['System'][] = array('link' => 'content', 'name' => 'Content', 'description' => 'Content management');
            $_menu['System'][] = array('link' => 'user/manage', 'name' => 'Users', 'description' => 'Application user managements');
            $_menu['System'][] = array('link' => 'user/manage/group', 'name' => 'User Groups', 'description' => 'Application user group managements');
            $_menu['System'][] = array('link' => 'system/logs', 'name' => 'System Logs', 'description' => 'View system logs');
            $_menu['System'][] = array('link' => 'system/backup', 'name' => 'Backup', 'description' => 'Manage and create database backup');
        }
        return $_menu;
    }

    /*
    public function phpinfo(&$simbio, $str_args) {
        ob_start();
        phpinfo();
        $_info = ob_get_clean();
        $simbio->loadView($_info, 'PHPINFO');
    }
    */
}
?>
