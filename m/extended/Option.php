<?php


namespace m\extended;


class Option
{
    private $_value;
    private $_caption;

    public function __construct($value, $caption = null)
    {
        $this->_value = $value;

        if($caption == null)
            $this->_caption = $this->_value;
        else
            $this->_caption = $caption;
    }

    public function render($extras = '')
    {
        return '<option value="' . $this->_value . '" ' . $extras . '>' . $this->_caption . '</option>';
    }

    public function renderCheckSelected($compare, $ignoreCase = true)
    {
        if($ignoreCase)
        {
            // TODO: Make sure this is okay!
            // Untuk mengatasi perbedaan case pada string
            $compare = strtolower($compare);
            $value = strtolower($this->_value);
        }
        else
            $value = $this->_value;

        //pre_print("$compare vs {$this->_value}");

        if($compare === '')
            $selected = $compare === /*$this->_value*/ $value ? 'selected' : ''; // Strict compare (also compares data type) membedakan 0 & ''
        else
            $selected = $compare == /*$this->_value*/ $value ? 'selected' : '';

        //pre_print("SELECTED --> [$selected]");

        return '<option value="' . $this->_value . '" ' . $selected . '>' . $this->_caption . '</option>';
    }

    /**
     * @return mixed
     */
    public function getValue()
    {
        return $this->_value;
    }

    /**
     * @param mixed $value
     * @return Option
     */
    public function setValue($value)
    {
        $this->_value = $value;
        return $this;
    }

    /**
     * @return null
     */
    public function getCaption()
    {
        return $this->_caption;
    }

    /**
     * @param null $caption
     * @return Option
     */
    public function setCaption($caption)
    {
        $this->_caption = $caption;
        return $this;
    }

    public static function generateFromList(array $valueCaptionPairs = array())
    {
        $options = [];

        foreach ($valueCaptionPairs as $value => $caption)
        {
            $options[] = new Option($value, $caption);
        }

        return $options;
    }
}