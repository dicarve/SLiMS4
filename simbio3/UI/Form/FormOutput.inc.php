<?php
/**
 * Simbio Form Output processor
 * Arie Nugraha 2009 - dicarve@yahoo.com
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

class FormOutput extends FormMaker
{
    public $disabled = false;
    public $formInfo = '';
    public $submitName = 'Submit';
    public $submitValue = 'Submit';
    public $submitCallback = false;
    public $iframeSubmit = false;
    protected $fieldset = false;

    /**
     * Constructor
     *
     * @param   string  $str_output_type
     * @param   boolean $bool_is_disabled
     * @return  void
     */
    public function __construct($str_form_name = 'simbio-form', $str_form_action = '', $str_form_method = 'post', $bool_enable_upload = true)
    {
        parent::__construct($str_form_name, $str_form_action, $str_form_method, $bool_enable_upload);
    }


    /**
     * Method to add form elements based on field definition array
     *
     * @param   array   $arr_element_def
     * @return  void
     */
    public function add($arr_element_def)
    {
        if ($arr_element_def['type'] == 'fieldset') {
            $this->elements[$arr_element_def['label']] = array('fieldset' => 1, 'label' => $arr_element_def['label']);
        } else if ($arr_element_def['type'] == 'fieldset_end') {
            $this->elements[] = array('fieldset_end' => 1);
        } else if ($arr_element_def['type'] == 'header') {
            $this->elements[$arr_element_def['label']] = array('header' => 1, 'label' => $arr_element_def['label']);
        } else {
            parent::add($arr_element_def);
        }
    }


    /**
     * Method to output form in list model view
     *
     * @param   object  $obj_form_maker
     * @return  string
     */
    public function output()
    {
        $_buffer = '<div class="form-wrapper '.$this->form_name.'">'."\n";
        $_buffer .= parent::startForm();
        if ($this->formInfo) {
            $_buffer .= '<div class="form-info">'.$this->formInfo.'</div>'."\n";
        }
        // section form tabs
        if (count($this->sections) > 0) {
            $_buffer .= '<div class="form-section tabs tabs-border-bottom">';
            $_buffer .= '<ul class="tab-list">';
            foreach ($this->sections as $_tab) {
                $_buffer .= '<li><a href="#" section="'.$_tab.'"><span>'.ucwords(str_replace(array('-', '_'), ' ', $_tab)).'</span></a></li>';
            }
            $_buffer .= '</ul>&nbsp;';
            $_buffer .= '</div>'."\n";
            $_buffer .= '<div class="tabs-spacer">&nbsp;</div>'."\n";
        }
        // output element
        foreach ($this->elements AS $_form_element) {
            // check for fieldset
            if (isset($_form_element['header'])) {
                $_buffer .= '<a name="'.$_form_element['label'].'"></a><h3>'.ucwords($_form_element['label']).'</h3>'."\n";
            } else if (isset($_form_element['fieldset'])) {
                // check for fieldset closure
                if ($this->fieldset) {
                    $_buffer .= '</fieldset>'."\n";
                }
                $this->fieldset = true;
                $_buffer .= '<fieldset>'."\n";
                $_buffer .= '<legend class="form-element-label">'.ucwords($_form_element['label']).'</legend>';
            } else if (isset($_form_element['fieldset_end'])) {
                $this->fieldset = false;
                $_buffer .= '</fieldset>'."\n";
            } else {
                $_buffer .= '<div class="form-element-wrapper'.( !empty($_form_element['section'])?' '.$_form_element['section']:'' ).'">';
                $_buffer .= '<label class="form-element-label" for="'.$_form_element['element']->element_name.'">'.$_form_element['element']->label_text.'</label>'."\n";
                $_buffer .= '<div class="form-element-content">'.$_form_element['element']->out().'</div>'."\n";
                if ($_form_element['element']->element_desc) {
                    $_buffer .= '<div class="form-element-desc">'.$_form_element['element']->element_desc.'</div>'."\n";
                }
                $_buffer .= '</div>';
            }
        }
        // fieldset final closure
        if ($this->fieldset) {
            $_buffer .= '</fieldset>'."\n";
        }
        // hidden element
        foreach ($this->getHiddenElements() AS $_hidden_element) {
            $_buffer .= $_hidden_element->out()."\n";
        }
        // submit button
        if ($this->submitCallback) {
            $_buffer .= '<div class="form-buttons"><input type="button" value="'.$this->submitValue.'" class="form-submit-callback" /></div>';
        } else {
            $_buffer .= '<div class="form-buttons"><input type="submit" name="'.$this->submitName.'" class="form-submit" value="'.$this->submitValue.'" /></div>'."\n";
            if ($this->iframeSubmit) {
                $_buffer .= '<input type="hidden" name="ajaxID" value="8hya77yt5129h" />'."\n";
                $_buffer .= '<iframe name="submitExec" style="visibility: hidden; width: 0; height: 0; border: 0;"></iframe>'."\n";
                // below line is for debugging purpose only
                // $_buffer .= '<iframe name="submitExec" style="visibility: visible; width: 100%; height: 300px;"></iframe>'."\n";
            }
        }
        $_buffer .= parent::endForm();
        // disable form
        if ($this->disabled) {
            $_buffer .= '<script type="text/javascript"></script>';
        }
        $_buffer .= '</div>';

        return $_buffer;
    }
}
?>
