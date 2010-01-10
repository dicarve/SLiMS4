<?php
/**
 * simbio_fe_select
 * Dropdown Select Form Element Class
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

/* Drop Down Select List object */
class simbio_fe_select extends FormElement
{
    public function out()
    {
        // check for $array_option param
        if (!is_array($this->options)) {
            return '<select class="'.$this.'" name="'.$this->name.'" '.$this->attribute.'></select>';
        }
        // check if disabled
        if ($this->disabled) {
            $_disabled = ' disabled="disabled"';
        } else { $_disabled = ''; }

        // check if required
        $_required = '';
        if ($this->required) {
            $_required = 'required';
        }

        $_buffer = '<select class="'.( $this->cssClass?$this->cssClass.' '.$_required:$_required ).'" name="'.$this->name.'" id="'.$this->name.'" '.$this->attribute.''.$_disabled.'>'."\n";
        foreach ($this->options as $option) {
            if (is_string($option)) {
                // if the selected element is an array then
                // the selected option is also multiple to
                if (is_array($this->value)) {
                    $_buffer .= '<option value="'.$option.'" '.(in_array($option, $this->value)?'selected':'').'>';
                    $_buffer .= $option.'</option>'."\n";
                } else {
                    $_buffer .= '<option value="'.$option.'" '.(($option == $this->value)?'selected':'').'>';
                    $_buffer .= $option.'</option>'."\n";
                }
            } else {
                if (is_array($this->value)) {
                    $_buffer .= '<option value="'.$option[0].'" '.(in_array($option[0], $this->value)?'selected':'').'>';
                    $_buffer .= $option[1].'</option>'."\n";
                } else {
                    $_buffer .= '<option value="'.$option[0].'" '.(($option[0] == $this->value)?'selected':'').'>';
                    $_buffer .= $option[1].'</option>'."\n";
                }
            }
        }
        $_buffer .= '</select>'."\n";

        return $_buffer;
    }
}
?>
