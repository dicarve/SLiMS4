<?php
/**
 * Simbio Listing
 * Listing creator
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

class Listing
{
    protected $list = array();
    public $listClass = 'listing';
    public $listName = 'listing';
    public $numShow = 20;


    /**
     * Get list records
     *
     * @param   array   $arr_record: record to add
     * @return  void
     */
    public function appendRecord($arr_record) {
        $this->list[] = $arr_record;
    }


    /**
     * Build listing
     *
     * @param   integer $int_num_show: number of list to show
     * @return  string  listing
     */
    public function build() {
        $_listing = '<div class="'.$this->listClass.'"'.($this->listName?' id="'.$this->listName.'"':'').'>'."\n";
        $_r = 1;
        foreach ($this->list as $_rec) {
            if ($_r == $this->numShow) {
                break;
            }
            $_listing .= '<div class="list-row">'."\n";
            foreach ($_rec as $_id => $_field) {
                $_id = strtolower(str_replace(array(' ', '_'), '-', $_id));
                $_listing .= '<div class="list-field field-'.$_id.'">'.$_field.'</div>'."\n";
            }
            $_listing .= '</div>'."\n";
            $_r++;
        }
        $_listing .= '</div>'."\n";
        return $_listing;
    }


    /**
     * Get list records
     *
     * @return  array   an array of list records
     */
    public function getList() {
        return $this->list;
    }


    /**
     * Set list of record to be listed
     *
     * @param   array   $arr_list_data: an array of record to be listed
     * @return  void
     */
    public function setList($arr_list_data) {
        $this->list = $arr_list_data;
    }
}
?>
