<?php
/**
 * Biblio items/copies module class
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

class Item extends SimbioModel {

    /**
     * Method that must be defined by all child module
     * used by framework to get module information
     *
     * @param   object      $obj_framework: Simbio framework object
     * @return  array       an array of module information containing
     */
    public function moduleInfo(&$obj_framework) {

    }


    /**
     * Method that must be defined by all child module
     * used by framework to get module privileges type
     *
     * @param   object      $obj_framework: Simbio framework object
     * @return  array       an array of privileges for this module
     */
    public function modulePrivileges(&$obj_framework) {

    }


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
     * Configuration for Biblio module
     *
     * @param   object      $obj_framework: Simbio framework object
     * @param   string      $str_args: method main argument
     * @return  void
     */
    public function config(&$obj_framework, $str_args) {

    }


    /**
     * Get detail of bibliographic data
     *
     * @param   object      $obj_framework: Simbio framework object
     * @param   string      $str_args: an ID of Bibliographic data to fetch
     * @return  void
     */
    public function detail(&$obj_framework, $str_args) {

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
}
?>
