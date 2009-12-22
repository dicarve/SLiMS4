<?php
/**
 * FormRadio
 * Radio button Form Element Class
 *
 * Copyright (C) 2009 Arie Nugraha (dicarve@yahoo.com)
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

/* Radio button groups object */
class FormRadio extends FormElement
{
    public function out()
    {
        // check for $this->options param
        if (!is_array($this->options)) {
            return 'The third argument must be an array';
        }

        $_buffer = '';

        // number of element in each column
        if (count($this->options) > 10) {
            $_elmnt_each_column = 4;
        } else {
            $_elmnt_each_column = 2;
        }

        // chunk the array into pieces of array
        $_chunked_array = array_chunk($this->options, $_elmnt_each_column, true);

        $_buffer .= '<table>'."\n";
        $_buffer .= '<tr>'."\n";
        foreach ($_chunked_array as $_chunk) {
            $_buffer .= '<td valign="top">';
            foreach ($_chunk as $_radio) {
                if ($_radio[0] == $this->value) {
                    $_buffer .= '<div><input type="radio" name="'.$this->name.'" id="'.$this->name.'"'
                        .' value="'.$_radio[0].'" style="border: 0;" checked />'
                        .' <label for="'.$this->name.'">'.$_radio[1]."</label></div>\n";
                } else {
                    $_buffer .= '<div><input type="radio" name="'.$this->name.'" id="'.$this->name.'"'
                        .' value="'.$_radio[0].'" style="border: 0;" />'
                        .' <label for="'.$this->name.'">'.$_radio[1]."</label></div>\n";
                }
            }
            $_buffer .= '</td>';
        }
        $_buffer .= '</tr>'."\n";
        $_buffer .= '</table>'."\n";

        return $_buffer;
    }
}
?>
