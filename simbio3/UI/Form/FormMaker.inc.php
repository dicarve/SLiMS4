<?php
/**
 * FormMaker
 * Class for creating form with element based on simbio form elements
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

require 'FormElement.inc.php';
require 'FormText.inc.php';
require 'FormSelect.inc.php';
require 'FormAjaxSelect.inc.php';
require 'FormRadio.inc.php';
require 'FormCheckbox.inc.php';
require 'FormContent.inc.php';

abstract class FormMaker
{
    public $submitTarget = '_self';
    public $formName = 'form';
    public $formID = 'form';
    protected $elements = array();
    protected $hiddenElements = array();
    protected $method = '';
    protected $action = '';
    protected $disable = '';
    protected $enableUpload = true;

    /**
     * Class Constructor
     *
     * @param   string  $str_form_name
     * @param   string  $str_form_action
     * @param   string  $str_form_method
     * @param   boolean $bool_enable_upload
     */
    public function __construct($str_form_name = 'mainForm', $str_form_action = '', $str_form_method = 'post', $bool_enable_upload = true) {
        $this->formName = $str_form_name;
        $this->formID = $str_form_name;
        $this->action = $str_form_action;
        $this->method = $str_form_method;
        $this->enableUpload = $bool_enable_upload;
    }


    /**
     * Method to add form elements based on field definition array
     *
     * @param   array   $arr_element_def
     * @return  void
     */
    public function add($arr_element_def) {
        $_element = self::createFormElement($arr_element_def);
        if ($arr_element_def['type'] == 'hidden') {
            $this->hiddenElements[$_element->name] = $_element;
        } else {
            $this->elements[$_element->name] = $_element;
        }
    }


    /**
     * Static factory method to create form element object
     *
     * @param   array       $arr_element_def : form element array
     * @return  object
     */
    public static function createFormElement($arr_element_def) {
        // form element name
        $_name = $arr_element_def['id'];
        // element type
        $_type = $arr_element_def['type'];
        // field length
        $_length = 50;
        if (isset($arr_element_def['maxSize'])) {
            $_length = $arr_element_def['maxSize'];
        }
        // field required
        $_required = false;
        if (isset($arr_element_def['required'])) {
            $_required = true;
        }
        // field description
        $_description = '';
        if (isset($arr_element_def['description']) AND $arr_element_def['description']) {
            $_description = $arr_element_def['description'];
        }
        // field default value
        $_value = '';
        if (isset($arr_element_def['value'])) {
            $_value = $arr_element_def['value'];
        }
        // field additional attribute
        $_attr = '';
        if (isset($arr_element_def['attr'])) {
            $_attr = Utility::createHTMLAttribute($arr_element_def['attr']);
        }

        // creating element instance
        if (in_array($_type, array('text', 'file', 'textarea', 'submit', 'button', 'reset', 'password', 'image'))) {
            // create instance
            $_form_element = new FormText();
            $_form_element->type = $_type;
            $_attr .= ' maxlength="'.$_length.'" ';
        } else if (in_array($_type, array('select', 'list', 'dropdown'))) {
            $_form_element = new FormSelect();
            $_form_element->options = $arr_element_def['options'];
        } else if (in_array($_type, array('choice', 'radio', 'boolean'))) {
            $_form_element = new FormRadio();
            $_form_element->options = $arr_element_def['options'];
        } else if (in_array($_type, array('checklist', 'checkbox'))) {
            $_form_element = new Formcheckbox();
            $_form_element->options = $arr_element_def['options'];
        } else if ($_type == 'date') {
            $_form_element = new FormText();
            $_form_element->type = 'date';
        } else if ($_type == 'hidden') {
            // create instance
            $_form_element = new FormText();
            $_form_element->name = $_name;
            $_form_element->type = 'hidden';
            $_form_element->value = $_value;
            return $_form_element;
        } else if ($_type == 'content') {
            $_form_element = new FormContent();
            $_form_element->content = isset($arr_element_def['content'])?trim($arr_element_def['content']):'';
            return $_form_element;
        }

        // require field marker
        if ($_required) {
            $arr_element_def['label'] .= '*';
            $_form_element->required = true;
        } else if ($_type == 'hidden') { $arr_element_def['label'] = ''; }

        // set attribute
        $_form_element->attribute = $_attr;
        // set default value
        $_form_element->name = $_name;
        $_form_element->description = $_description;
        $_form_element->value = $_value;
        $_form_element->label = $arr_element_def['label'];

        return $_form_element;
    }


    /**
     * Method to start form
     *
     * @return  string
     */
    public function startForm() {
        return '<form name="'.$this->formName.'" id="'.$this->formName.'" '
            .'method="'.$this->method.'" '
            .'action="'.$this->action.'" target="'.$this->submitTarget.'"'.($this->enableUpload?' enctype="multipart/form-data"':'').'>';
    }


    /**
     * Method to end form
     *
     * @return  string
     */
    public function endForm() {
        return '</form>';
    }


    /**
     * Method to get an array of form elements
     *
     * @return array
     */
    public function getElements() {
        return $this->elements;
    }


    /**
     * Method to get an array of hidden form elements
     *
     * @return array
     */
    public function getHiddenElements() {
        return $this->hiddenElements;
    }


    /**
     * Method to remove one or more form element
     *
     * @param   mixed   $mix_form_id: a string or an array of element to remove
     * @return  array   return an array of removed element
     */
    public function removeElement($mix_form_id) {
        $_removed = array();
        if (is_array($mix_form_id)) {
            foreach ($mix_form_id as $_id) {
                $_removed[] = $this->elements[$_id];
                unset($this->elements[$_id]);
            }
        } else if (is_string($mix_form_id)) {
            $_removed[] = $this->elements[$mix_form_id];
            unset($this->elements[$mix_form_id]);
        }

        return $_removed;
    }


    /**
     * Method to output forms
     * Please extend this method
     *
     * @return  void
     */
    abstract public function build();
}
?>
