<?php


namespace m\extended;


use m\Util;

class FilterForm extends Form
{
    private $_filterFields;

    public function __construct(array &$tableArray, array $filterColumns = null)
    {
        //pre_print($tableArray);

        $filterFields = FilterField::filterFieldsFromTableArray($tableArray, $filterColumns);

        parent::__construct(FilterField::filterFieldsGetFieldNames($filterFields));

        $this->_filterFields = $filterFields;
    }

    public function configure()
    {
        foreach ($this->_filterFields as $field)
        {
            $id = $field->getFieldName();

            $options = $field->fieldValuesAsKeyValuePairs();

            // Jika buka text yang disarankan, jadikan combo box
            if($field->suggestedInputType() == FilterField::SUGGESTED_INPUT_TYPE_SELECT)
                $this->getInput($id)->setType('select')->setOptionsFromList($options)->setValue('');
        }
    }

    public function preFill(array $filterPairs)
    {
        if($filterPairs == null)
            return;

        foreach ($filterPairs as $inputId => $inputValue)
        {
            if($this->inputIdExists($inputId)) // Jika langsung diberi $_POST, maka button submit tidak ikut.
                $input = $this->getInput($inputId)->setValue($inputValue);
        }
    }
}

class FilterField
{
    const SUGGESTED_INPUT_TYPE_TEXT = 'text';
    const SUGGESTED_INPUT_TYPE_SELECT = 'select';

    private $_fieldName;
    private $_fieldValues;
    private $_operand;

    public function __construct($fieldName, array $fieldValues, $_operand = '=')
    {
        $this->_fieldName = $fieldName;
        $this->_fieldValues = $fieldValues;
    }

    public function suggestedInputType()
    {
        if(count($this->_fieldValues) < 2)
        {
            if($this->_fieldValues[0] === null) // Harus benar-benar null supaya dianggap 'text' type inputnya.
                return self::SUGGESTED_INPUT_TYPE_TEXT;
        }

        return self::SUGGESTED_INPUT_TYPE_SELECT;
    }

    /**
     * @return mixed
     */
    public function getFieldName()
    {
        return $this->_fieldName;
    }

    /**
     * @param mixed $fieldName
     * @return FilterField
     */
    public function setFieldName($fieldName)
    {
        $this->_fieldName = $fieldName;
        return $this;
    }

    /**
     * @return array
     */
    public function getFieldValues()
    {
        return $this->_fieldValues;
    }

    /**
     * @param array $fieldValues
     * @return FilterField
     */
    public function setFieldValues($fieldValues)
    {
        $this->_fieldValues = $fieldValues;
        return $this;
    }

    public function fieldValuesAsKeyValuePairs($defaultOptionValue = '', $defaultOptionCaption = '-- All --')
    {
        $kvPairs = array(
            $defaultOptionValue => $defaultOptionCaption
        );

        foreach ($this->_fieldValues as $value)
            $kvPairs[$value] = $value;

        return $kvPairs;
    }

    public static function filterFieldsFromTableArray(array &$tableArray, array $filterColumns = null)
    {
        if($tableArray == null || count($tableArray) < 1)
            return null;

        $columnNames = Util::arrayTableGetHeaders($tableArray, false);

        $filterFields = array();

        foreach ($columnNames as $name)
        {
            // TODO: figure out what is these lines for? Maybe for excluding field?
            if($filterColumns != null)
                if(array_search($name, $filterColumns) === false)
                    continue;

            $columnValues = array_column($tableArray, $name);
            $columnValues = array_unique($columnValues);

            sort($columnValues);

            $filterField = new FilterField($name, $columnValues);

            //pre_print(count($columnValues));

            $filterFields[] = $filterField;
        }

        //pre_print($filterFields);

        return $filterFields;
    }

    public static function filterFieldsGetFieldNames(array $filterFields)
    {
        $fields = array();

        foreach ($filterFields as $filter)
            $fields[] = $filter->getFieldName();

        return $fields;
    }
}