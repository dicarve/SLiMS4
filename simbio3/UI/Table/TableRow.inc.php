<?php
/**
 * TableRow class
 *
 * Copyright (C) 2009  Arie Nugraha (dicarve@yahoo.com)
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

class TableRow
{
    public $attr;
    public $fields = array();
    public $allCellAttr;

    /**
     * Class Constructor
     *
     * @param   string  $str_attr
     */
    public function __construct($array_field_content, $str_attr = '')
    {
        $this->attr = $str_attr;
        $this->addFields($array_field_content);
    }


    /**
     * Method to create TableField array from array
     *
     * @param   array   $array_field_content
     * @return  array
     */
    protected function addFields($array_field_content)
    {
        foreach ($array_field_content as $idx => $fld_content) {
            $_field_obj = new TableField();
            $_field_obj->value = $fld_content;
            $this->fields[$idx] = $_field_obj;
        }
    }
}

?>
