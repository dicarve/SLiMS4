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
     * Get advanced search items
     *
     * @return  array   an array of advanced search items
     */
    private function getAdvanceSearch() {

    }


    /**
     * Method to get block content
     *
     * @param   object      $obj_framework: Simbio framework instance/object
     * @param   string      $str_block_type: string of block type to load
     * @return  string
     */
    public static function getBlock($obj_framework, $str_block_type) {
        $_block;
        switch ($str_block_type) {
            case 'simple search' :
                $_block = '<form name="simple-search" action="index.php" method="get">
                    <input type="text" name="keywords" />
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
     * @return  array/boolean       an array of status flag and messages or boolean true if validation succed
     */
    public function validate(&$obj_framework) {

    }
}
?>
