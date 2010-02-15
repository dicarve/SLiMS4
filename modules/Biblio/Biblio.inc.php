<?php
/**
 * Biblio module class
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

class Biblio extends SimbioModel {
    private $schema = 'aacr2';


    /**
     * Method that must be defined by all child module
     * used by framework to get module information
     *
     * @param   object      $simbio: Simbio framework object
     * @return  array       an array of module information containing
     */
    public static function moduleInfo(&$simbio) {
        return array('module-name' => 'Biblio',
            'module-desc' => 'Biblio module enable application users to manage bibliographical records/metadata',
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
     * Configuration for Biblio module
     *
     * @param   object      $simbio: Simbio framework object
     * @param   string      $str_args: method main argument
     * @return  void
     */
    public function config(&$simbio, $str_args) {

    }


    /**
     * Get detail of bibliographic data
     *
     * @param   object      $simbio: Simbio framework object
     * @param   string      $str_args: an ID of Bibliographic data to fetch
     * @return  void
     */
    public function detail(&$simbio, $str_args) {

    }


    /**
     * Get advanced search items
     *
     * @return  array   an array of advanced search items
     */
    private function getAdvanceSearch() {

    }


    /**
     * Method to get block content
     *
     * @param   object      $simbio: Simbio framework instance/object
     * @param   string      $str_block_type: string of block type to load
     * @return  string
     */
    public static function getBlock($simbio, $str_block_type) {
        $_block;
        switch ($str_block_type) {
            case 'simple search' :
                $_block = '<form name="simple-search" action="'.APP_WEB_BASE.'index.php/biblio/search" method="get">
                    <input type="text" name="keywords" id="simple-keywords" />
                    <input type="submit" name="search" value="'.__('Search').'" />
                    </form>';
                return Utility::createBlock($_block, 'Simple search', 'simple-search');
                break;
            case 'advanced search' :
                $_block = '<form name="adv-search-form" id="adv-search-form" action="index.php" method="get">
                    <span class="form-label">'.__('Title').':</span>
                    <input type="text" name="title" class="title" />
                    <span class="form-label">'.__('Author(s)').':</span>
                    <input type="text" name="author" class="author" />
                    <span class="form-label">'.__('Subject(s)').':</span>
                    <input type="text" name="subject" class="subject" />
                    <span class="form-label">'.__('ISBN/ISSN').':</span>
                    <input type="text" name="isbn" class="isbn" />
                    <span class="form-label">'.__('GMD').':</span>
                    <select name="gmd" />

                    </select>
                    <span class="form-label">'.__('Collection Type').':</span>
                    <select name="colltype" class="ajaxInputField" />

                    </select>
                    <span class="form-label">'.__('Location').':</span>
                    <select name="location" class="ajaxInputField" />

                    </select>
                    <input type="submit" name="search" value="'.__('Search').'" />
                    </form>';
                return Utility::createBlock($_block, 'Advanced search', 'adv-search');
                break;
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
        if (isset($_GET['p']) && stripos($_GET['p'], 'admin', 0) !== false) {
            // set page title
            $simbio->setViewConfig('Page Title', __('Bibliography'));
        } else {
            // set page title
            $simbio->setViewConfig('Page Title', __('Online Public Access Catalog'));
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
            $_menu[] = array('link' => 'admin/biblio', 'name' => __('Bibliography'), 'description' => __('Bibliography module used to manage library collections metadata'));
        } else {
            if ($str_current_module == 'admin' && $str_current_method == 'biblio') {
                $_menu['Catalog'][] = array('link' => 'biblio/add', 'name' => 'Add New Catalog', 'description' => 'Add new catalog record');
                $_menu['Catalog'][] = array('link' => 'biblio/list', 'name' => 'Catalog List', 'description' => 'List records of existing catalog');
                $_menu['Catalog'][] = array('link' => 'biblio/z3950', 'name' => 'Z3950 Service', 'description' => 'Z39.50 protocol metadata retrieval');
                $_menu['Schema'][] = array('link' => 'biblio/schema', 'name' => 'Bibliographic Schema', 'description' => 'Manage bibliographic data schema');
                $_menu['Schema'][] = array('link' => 'biblio/schema/add', 'name' => 'Add Schema', 'description' => 'Add new bibliographic data schema');
                $_menu['Tools'][] = array('link' => 'biblio/config', 'name' => 'Configuration', 'description' => 'Module configuration');
                $_menu['Tools'][] = array('link' => 'biblio/export', 'name' => 'Export Catalog', 'description' => 'Export catalog records');
                $_menu['Tools'][] = array('link' => 'biblio/import', 'name' => 'Import Catalog', 'description' => 'Import catalog records');
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
