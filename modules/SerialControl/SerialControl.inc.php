<?php
/**
 * SerialControl module class
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

class SerialControl extends SimbioModel {
    /**
     * Method that must be defined by all child module
     * used by framework to get module information
     *
     * @param   object  $simbio: Simbio framework object
     * @return  array   an array of module information containing
     */
    public static function moduleInfo(&$simbio) {
        return array('module-name' => 'SerialControl',
            'module-desc' => 'Enable tracking of serial publication subscriptions such as journal, magazine, newspaper, etc.',
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

    }


    /**
     * Method to add module data
     *
     * @param   object  $simbio: Simbio framework object
     * @param   string  $str_args: method main argument
     * @return  void
     */
    public function add(&$simbio, $str_args) {

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
            $_menu[] = array('link' => 'admin/serialcontrol', 'name' => 'SerialControl', 'description' => __('Tracking of serial publication subscriptions such as journal, magazine, newspaper, etc.'));
        } else {
            $_menu['Subscrption'][] = array('link' => 'serialcontrol/add', 'name' => __('Add Subscription'), 'description' => __('Add new subscription'));
            $_menu['Subscrption'][] = array('link' => 'serialcontrol', 'name' => __('Subscription List'), 'description' => __('List exisiting subscription'));
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

    }


    /**
     * Method to remove module data
     *
     * @param   object  $simbio: Simbio framework object
     * @param   string  $str_args: method main argument
     * @return  void
     */
    public function delete(&$simbio, $str_args) {

    }


    /**
     * Method to save/update module data
     *
     * @param   object  $simbio: Simbio framework object
     * @param   string  $str_args: method main argument
     * @return  array   an array of status flag and messages
     */
    public function save(&$simbio, $str_args) {

    }
}
?>
