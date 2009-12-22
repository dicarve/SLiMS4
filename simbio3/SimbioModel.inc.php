<?php
/**
 * SimbioModel abstract class
 * Simbio Framework's module template/abstract class
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

abstract class SimbioModel {
    /**
     * An array of database table fields
     */
    protected $dbFields = array();

    /**
     * Method to add module data
     *
     * @param   object      $obj_framework: Simbio framework object
     * @param   string      $str_args: method main argument
     * @return  void
     */
    public function add(&$obj_framework, $str_args) {

    }


    /**
     * Method to get block content
     *
     * @param   object      $obj_framework: Simbio framework instance/object
     * @param   string      $str_block_type: string of block type to load
     * @return  string
     */
    public static function getBlock($obj_framework, $str_block_type) {

    }


    /**
     * Default module page method
     * All module must have this method
     *
     * @param   object      $obj_framework: Simbio framework object
     * @param   string      $str_args: method main argument
     * @return  void
     */
    public function index(&$obj_framework, $str_args) {

    }


    /**
     * Module initialization method
     * All preparation for module such as loading library should be doing here
     *
     * @param   object      $obj_framework: Simbio framework object
     * @param   string      $str_args: method main argument
     * @return  void
     */
    public function init(&$obj_framework, $str_args) {

    }


    /**
     * Method to update module data
     *
     * @param   object      $obj_framework: Simbio framework object
     * @param   string      $str_args: method main argument
     * @return  void
     */
    public function update(&$obj_framework, $str_args) {

    }


    /**
     * Method to remove module data
     *
     * @param   object      $obj_framework: Simbio framework object
     * @param   string      $str_args: method main argument
     * @return  void
     */
    public function remove(&$obj_framework, $str_args) {

    }


    /**
     * Method to save/update module data
     *
     * @param   object      $obj_framework: Simbio framework object
     * @param   string      $str_args: method main argument
     * @return  array       an array of status flag and messages
     */
    public function save(&$obj_framework, $str_args) {

    }


    /**
     * Method to validate processed module data
     *
     * @param   object      $obj_framework: Simbio framework object
     * @param   string      $str_args: method main argument
     * @return  boolean/array       boolean true if validation success OR an array of status flag and messages if validation failed
     */
    public function validate(&$obj_framework) {
        return true;
    }
}
?>
