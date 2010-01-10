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
     * Rerouting module method
     *
     * @param   object      $simbio: Simbio framework object
     * @param   string      $str_called_method: a method called by framework
     * @param   string      $str_args: method main argument
     * @return  void
     */
    public function reRoute(&$simbio, $str_called_method, $str_args) {
        $str_called_method = $simbio->filterizeSQLString($str_called_method);
        $simbio = $simbio;
        $_content_file = LIBS_BASE.'contents'.DSEP.$str_called_method.'.inc.php';
        $simbio->loadView($_content_file, 'Content');
        if (file_exists($_content_file)) {
            ob_start();
            require $_content_file;
            $_content_str = '<div class="content-title">'.$title.'</div>'."\n";
            $_content_str .= '<div class="content-content">'.ob_get_clean().'</div>';
            $simbio->setViewConfig('Page Title', __($title));
            $simbio->loadView($_content_str, 'Content');
        } else {
            // get library information from database
            $_content = $this->getRecords($simbio, array('content_path' => $str_called_method), array('content_title', 'content_desc'));
            if ($_content) {
                $_content_str = '<div class="content-title">'.$_content[0][0].'</div>'."\n";
                $_content_str .= '<div class="content-content">'.$_content[0][1].'</div>';
                $simbio->setViewConfig('Page Title', __($_content[0][0]));
                $simbio->loadView($_content_str, 'Content');
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

    }
}
?>
