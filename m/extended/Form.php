<?php


namespace m\extended;


class Form
{
    private $_fields;
    private $_inputs;
    private $_method;
    private $_action;
    private $_submit;
    private $_enctype;

    public function __construct(array $fields = array())
    {
        $this->_fields = $fields;

        $this->_method = 'post';

        $this->_action = '';

        // Convert fields to input list
        $this->_inputs = Input::generateFromList($this->_fields, true);

        // Default submit button
        $this->_submit = Input::createSubmit('submit', 'Submit');

        // Default enctype, is without enctype
        $this->_enctype = null;
    }

    /**
     * @return array
     */
    public function getFields()
    {
        return $this->_fields;
    }

    /**
     * @return null
     */
    public function getEnctype()
    {
        return $this->_enctype;
    }

    /**
     * @param null $enctype
     * @return Form
     */
    public function setEnctype($enctype)
    {
        $this->_enctype = $enctype;
        return $this;
    }

    /**
     * @param string $method
     * @return Form
     */
    public function setMethod($method)
    {
        $this->_method = $method;
        return $this;
    }

    /**
     * @param string $action
     * @return Form
     */
    public function setAction($action)
    {
        $this->_action = $action;
        return $this;
    }

    public function getInputIds()
    {
        $ids = [];

        foreach ($this->_inputs as $input)
            $ids[] = $input->getId();

        return $ids;
    }

    public function renderOpen($extras = '')
    {
        $enctype = $this->_enctype == null ? '' : 'enctype="' . $this->_enctype . '"';

        $html = <<< PHP_HEREDOC
<form method="{$this->_method}" action="{$this->_action}" {$enctype} {$extras} >
PHP_HEREDOC;

        return $html;
    }

    public function renderSubmit($extras = '')
    {
        return $this->_submit->renderControl($extras);
    }

    public function getSubmit()
    {
        return $this->_submit;
    }

    public function setSubmit(Input $submit)
    {
        $this->_submit = $submit;
    }

    public function renderClose()
    {
        $html = '</form>';

        return $html;
    }

    public function render()
    {
        $html = $this->renderOpen();

        $html .= $this->renderInputs();

        $html .= $this->renderClose();

        return $html;
    }

    public function renderInputs()
    {
        $html = '';

        foreach ($this->_inputs as $input)
        {
            $html .= $input->renderLabel();
            $html .= $input->renderControl();
        }

        return $html;
    }

    public function getInputs($excludeHiddenInputs = false)
    {
        if(!$excludeHiddenInputs)
            return $this->_inputs;
        else
        {
            $noHidden = array();

            foreach ($this->_inputs as $input)
            {
                if($input->getType() != 'hidden')
                    $noHidden[] = $input;
            }

            return $noHidden;
        }
    }

    public function renderHiddenInputs()
    {
        $html = '';

        foreach ($this->_inputs as $input)
        {
            if($input->getType() == 'hidden')
                $html .= $input->renderControl();
        }

        return $html;
    }

    public function renderInput($name, $extras = '', $withLabel = true)
    {
        $input = $this->getInputWithName($name);

        if($input == null)
            return;

        $html = '';

        if($withLabel)
            $html .= $input->renderLabel();

        $html .= $input->renderControl($extras);

        return $html;
    }

    public function getInputWithName($name)
    {
        foreach ($this->_inputs as $input)
        {
            if($input->getName() == $name)
                return $input;
        }

        return null;
    }

    public function getInput($id)
    {
        if(!isset($this->_inputs[$id]))
            return null;

        return $this->_inputs[$id];
    }

    public function inputIdExists($id)
    {
        return array_search($id, $this->_fields) !== false;
    }

    public function setInput($id, Input $input)
    {
        if(isset($this->_inputs[$id]))
        {
            $this->_inputs[$id] = $input;

            return true;
        }

        return false;
    }

    /**
     * Try to fill array of values to form's input
     * @param array $idValuePairs Array contains data like ['field' => 'value']
     * @param bool $strict If a field already contains value, it will be overridden (True)
     */
    public function applyValues(array $idValuePairs, $strict = false)
    {
        foreach ($idValuePairs as $id => $value)
        {
            if(isset($this->_inputs[$id]))
            {
                if($strict)
                {
                    if($this->_inputs[$id] == null || $this->_inputs[$id] == '')
                        $this->_inputs[$id]->setValue($value);
                }
                else
                    $this->_inputs[$id]->setValue($value);
            }
        }
    }
}