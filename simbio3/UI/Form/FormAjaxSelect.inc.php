<?php
/**
 * FormAJAXSelect
 * AJAX dropdown Form Element Class
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

/* AJAX drop down select list object */
class FormAJAXSelect extends FormElement
{
    /**
     * AJAX drop down special properties
     */
    public $handlerURL = 'about:blank';
    public $listClass = 'ajaxDDlist';
    public $defaultText = 'SEARCHING...';
    public $params = '';

    public function out()
    {
        $_buffer = '<input type="text" id="'.$this->name.'" name="'.$this->name.'" class="'.$this->element_css_class.'" onkeyup="showDropDown(\''.$this->handlerURL.'\', \''.$this->name.'\', \''.$this->params.'\')" value="'.$this->value.'" />';
        $_buffer .= '<ul class="'.$this->listClass.'" id="'.$this->name.'List"><li style="padding: 2px; font-weight: bold;">'.$this->defaultText.'</li></ul>';

        return $_buffer;
    }
}
?>
