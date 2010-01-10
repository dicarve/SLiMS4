<?php
/**
 * Circulation module class
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

class Circulation extends SimbioModel {

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
     * Method to get block content
     *
     * @param   object      $simbio: Simbio framework instance/object
     * @param   string      $str_block_type: string of block type to load
     * @return  string
     */
    public static function getBlock($simbio, $str_block_type) {
        $_block;
        return $_block;
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
        // set page title
        $simbio->setViewConfig('Page Title', __('Circulation'));
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
            $_menu[] = array('link' => 'admin/circulation', 'name' => __('Circulation'), 'description' => __('Collection borrowing and returning management'));
        } else {
            $_menu['Circulation'][] = array('link' => 'circulation', 'name' => 'Transaction', 'description' => 'Circulation transaction');
            $_menu['Circulation'][] = array('link' => 'circulation/quickreturn', 'name' => 'Quick Return', 'description' => 'Quick return');
            $_menu['Circulation'][] = array('link' => 'circulation/rules', 'name' => 'Loan Rules', 'description' => 'Manage circulation loan rules');
            $_menu['Circulation'][] = array('link' => 'circulation/reservation', 'name' => 'Reservation', 'description' => 'View reservation records');
            $_menu['Tools'][] = array('link' => 'circulation/config', 'name' => 'Configuration', 'description' => 'Module configuration');
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
