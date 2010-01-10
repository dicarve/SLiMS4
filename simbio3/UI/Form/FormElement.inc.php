<?php
/**
 * FormElement
 * Abstract Form Element Class
 *
 * Copyright (C) 2007,2008  Arie Nugraha (dicarve@yahoo.com)
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

/* abstract form element class to be inherited by form element classes */
abstract class FormElement
{
    public $type = 'text';
    public $name = '';
    public $description = '';
    public $value;
    public $options;
    public $attribute = '';
    public $cssClass = '';
    public $disabled = false;
    public $label = '';
    public $required = false;

    /**
     * Below method must be inherited
     *
     * @return  string
     */
    abstract protected function out();
}
?>
