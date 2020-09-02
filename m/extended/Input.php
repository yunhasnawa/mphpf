<?php

namespace m\extended;

use m\Util;

class Input
{
    private $_type;
    private $_name;
    private $_id;
    private $_value;
    private $_label;
    private $_options;
    private $_readOnly;
    private $_disabled;
    private $_extras;
    private $_additionalAttributes;

    public function __construct($id, $type = 'text')
    {
        $this->_id = $id;
        $this->_name = $this->_id;
        $this->_value = '';
        $this->_type = $type;
        $this->_options = array();
        $this->_readOnly = false;
        $this->_disabled = false;
        $this->_label = Util::strFormatTableColumnName($this->_name);
        $this->_extras = '';

        // Attributes added by user
        $this->_additionalAttributes = array();
    }

    public function addAdditionalAttribute($key, $value)
    {
        $this->_additionalAttributes[$key] = $value;
        return $this;
    }

    /**
     * @return string
     */
    public function getExtras()
    {
        return $this->_extras;
    }

    /**
     * @param string $extras
     * @return Input
     */
    public function setExtras($extras)
    {
        $this->_extras = $extras;
        return $this;
    }

    /**
     * @return bool
     */
    public function isDisabled()
    {
        return $this->_disabled;
    }

    /**
     * @param bool $disabled
     * @return Input
     */
    public function setDisabled($disabled)
    {
        $this->_disabled = $disabled;
        return $this;
    }

    /**
     * @return bool
     */
    public function isReadOnly()
    {
        return $this->_readOnly;
    }

    /**
     * @param bool $readOnly
     * @return Input
     */
    public function setReadOnly($readOnly)
    {
        $this->_readOnly = $readOnly;
        return $this;
    }

    public function addOption(Option $option)
    {
        $this->_options[] = $option;
    }

    /**
     * @return array
     */
    public function getOptions()
    {
        return $this->_options;
    }

    /**
     * @param array $options
     * @return Input
     */
    public function setOptions(array $options)
    {
        $this->_options = $options;

        return $this;
    }

    public function setOptionsFromList(array $valueCaptionPairs)
    {
        $this->_options = Option::generateFromList($valueCaptionPairs);

        return $this;
    }

    /**
     * @return string
     */
    public function getType()
    {
        return $this->_type;
    }

    /**
     * @param string $type
     * @return Input
     */
    public function setType($type)
    {
        $this->_type = $type;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getName()
    {
        return $this->_name;
    }

    /**
     * @param mixed $name
     * @return Input
     */
    public function setName($name)
    {
        $this->_name = $name;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->_id;
    }

    /**
     * @param mixed $id
     * @return Input
     */
    public function setId($id)
    {
        $this->_id = $id;
        return $this;
    }

    /**
     * @return string
     */
    public function getValue()
    {
        return $this->_value;
    }

    /**
     * @param string $value
     * @return Input
     */
    public function setValue($value)
    {
        $this->_value = $value;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getLabel()
    {
        return $this->_label;
    }

    /**
     * @param mixed $label
     * @return Input
     */
    public function setLabel($label)
    {
        $this->_label = $label;
        return $this;
    }

    private function _allAdditionalAttributesAsString()
    {
        $attrib = ' ';

        foreach ($this->_additionalAttributes as $key => $value)
        {
            $pair = $key . '=' . '"' . $value . '" ';

            $attrib .= $pair;
        }

        return $attrib;
    }

    private function _renderOptions()
    {
        $html = '';

        foreach ($this->_options as $option)
        {
            $html .= $option->renderCheckSelected($this->_value);
        }

        return $html;
    }

    private function _renderSelect($extras = '')
    {
        $extras = "{$this->_extras} $extras";

        $attrib = $this->_allAdditionalAttributesAsString();

        $html = <<< PHP_HEREDOC
        <select name="{$this->_name}" id="{$this->_id}" {$attrib} {$extras} {$this->_readOnly()} {$this->_disabled()}>{$this->_renderOptions()}</select>
PHP_HEREDOC;

        return $html;
    }

    private function _renderTextArea($extras = '')
    {
        $extras = "{$this->_extras} $extras";

        $attrib = $this->_allAdditionalAttributesAsString();

        $html = <<< PHP_HEREDOC
        <textarea name="{$this->_name}" id="{$this->_id}" {$attrib} {$extras} {$this->_readOnly()} {$this->_disabled()}>{$this->_value}</textarea>
PHP_HEREDOC;

        return $html;
    }

    private function _renderFile($extras = '')
    {
        $extras = "{$this->_extras} $extras";

        $attrib = $this->_allAdditionalAttributesAsString();

        $existingFile = $this->_value == null ? '' : "<div>Existing: {$this->_value}</div>";

        $html = <<< PHP_HEREDOC
        <input type="file" name="{$this->_name}" id="{$this->_id}" {$attrib} {$extras} {$this->_readOnly()} {$this->_disabled()} />
        {$existingFile}
PHP_HEREDOC;

        return $html;
    }

    public function renderControl($extras = '')
    {
        if($this->_type == 'select')
            return $this->_renderSelect($extras);

        if($this->_type == 'textarea')
            return $this->_renderTextArea($extras);

        if($this->_type == 'file')
            return $this->_renderFile($extras);

        $extras = "{$this->_extras} $extras";

        $attrib = $this->_allAdditionalAttributesAsString();

        $html = <<< PHP_HEREDOC
        <input type="{$this->_type}" value="{$this->_value}" name="{$this->_name}" id="{$this->_id}" {$attrib} {$extras} {$this->_readOnly()} {$this->_disabled()}/>
PHP_HEREDOC;

        return $html;
    }

    private function _readOnly()
    {
        if($this->_readOnly) return 'readonly';

        return '';
    }

    private function _disabled()
    {
        if($this->_disabled) return 'disabled';

        return '';
    }

    public function renderLabel($extras = '')
    {
        $extras = "{$this->_extras} $extras";

        $html = <<< PHP_HEREDOC
<label for="{$this->_id}" {$extras} >{$this->_label}</label>
PHP_HEREDOC;

        return $html;
    }

    public static function generateFromList(array $fieldList, $assoc = true)
    {
        $generated = array();

        if(!$assoc)
            foreach ($fieldList as $field)
                $generated[] = new Input($field);
        else
            foreach ($fieldList as $field)
                $generated[$field] = new Input($field);

        return $generated;
    }

    public static function createSubmit($id, $value = 'Submit')
    {
        $submit = new Input($id, 'submit');
        $submit->setValue($value);

        return $submit;
    }
}