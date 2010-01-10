<?php
/**
 * FormText
 * Text Field Form Element Class
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

/* Text field object */
class FormText extends FormElement
{
    public function out()
    {
        $_buffer = '';
        if (!in_array($this->type, array('textarea', 'text', 'password', 'button', 'file', 'hidden', 'submit', 'button', 'reset', 'date'))) {
            return 'Unrecognized element type!';
        }
        // check if disabled
        if ($this->disabled) {
            $_disabled = ' disabled="disabled"';
        } else { $_disabled = ''; }
        // maxlength attribute
        if (!stripos($this->attribute, 'maxlength')) {
            if ($this->type == 'text') {
                $this->attribute .= 'maxlength="256"';
            } else if ($this->type == 'textarea') {
                $this->attribute .= 'maxlength="'.(30*1024).'"';
            }
        }
        // check if required
        $_required = '';
        if ($this->required) {
            $_required = 'required';
        }

        // checking element type
        if ($this->type == 'textarea') {
            $_buffer .= '<textarea class="'.( $this->cssClass?$this->cssClass.' '.$_required:$_required ).'" name="'.$this->name.'" id="'.$this->name.'" '.$this->attribute.''.$_disabled.'>';
            $_buffer .= $this->value;
            $_buffer .= '</textarea>'."\n";
        } else if (stripos($this->type, 'date', 0) !== false) {
            $_buffer .= '<input class="dateInput'.( $this->cssClass?' '.$this->cssClass.' '.$_required:' '.$_required ).'" type="'.$this->type.'" name="'.$this->name.'" id="'.$this->name.'" ';
            $_buffer .= 'value="'.$this->value.'" '.$this->attribute.''.$_disabled.' />'."\n";
        } else {
            $_buffer .= '<input class="'.( $this->cssClass?$this->cssClass.' '.$_required:$_required ).'" type="'.$this->type.'" name="'.$this->name.'" id="'.$this->name.'" ';
            $_buffer .= 'value="'.$this->value.'" '.$this->attribute.''.$_disabled.' />'."\n";
        }

       return $_buffer;
    }
}
?>
