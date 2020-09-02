<?php


namespace m\extended;


use m\UploadedFileModel;

class FormValidation
{
    private $_method;
    private $_submitName;
    private $_inputNames;
    private $_rawData;
    private $_data;
    private $_invalidMessage;

    private $_requiredInputs;

    // To handle uploads
    private $_hasFile;
    private $_fileInputNames;
    private $_uploadedFileModel;
    private $_uploadedFiles;
    private $_filesData;
    /**
     * @var string
     */
    private $_uploadedFilesErrorMessages;

    public function __construct(array $inputNames, $hasFile = false)
    {
        $this->_method = 'post';
        $this->_submitName = 'submit';
        $this->_inputNames = $inputNames;

        $this->_requiredInputs = array();

        $this->_hasFile = $hasFile;
        $this->_fileInputNames = array();
        $this->_uploadedFileModel = null;
        $this->_uploadedFiles = null;
        $this->_filesData = null;
        $this->_uploadedFilesErrorMessages = null;

        $this->_retrieveRawData();
        $this->_retrieveData();
    }

    /**
     * @return bool
     */
    public function hasFile()
    {
        return $this->_hasFile;
    }

    public function setUploadedFileModel(UploadedFileModel $model)
    {
        $this->_uploadedFileModel = $model;

        $this->_retrieveUploadedFiles();
    }

    public function getFileInputNames()
    {
        return $this->_fileInputNames;
    }

    private function _retrieveUploadedFiles()
    {
        if(!$this->submitted())
            return;

        $this->_uploadedFiles = UploadedFile::fromInputNames($this->_fileInputNames, $this->_uploadedFileModel);
    }

    public function getUploadedFile($fileInputName)
    {
        return $this->_uploadedFiles[$fileInputName];
    }

    public function setUploadedFile($fileInputName, UploadedFile $uf)
    {
        $this->_uploadedFiles[$fileInputName] = $uf;
    }

    private function _determineProcessedFiles()
    {
        $processedFiles = array();

        foreach ($this->_uploadedFiles as $file)
        {
            $searchInRequired = array_search($file->getInputName(), $this->_requiredInputs);

            if($searchInRequired != null)
                $processedFiles[] = $file;
            else
            {
                // Kalau tidak kosong tetep diproses
                if(!$file->isEmpty())
                    $processedFiles[] = $file;
            }
        }

        //pre_print($processedFiles);

        return $processedFiles;
    }

    public function processUploadedFiles()
    {
        $processedFiles = $this->_determineProcessedFiles();

        // pre_print($processedFiles, true);

        $errorMessages = UploadedFile::collectErrorMessages($processedFiles);

        if(!empty($errorMessages))
        {
            $this->_uploadedFilesErrorMessages = $errorMessages;

            return false;
        }

        $stored = UploadedFile::storeUploadedFiles($processedFiles);

        if(!$stored)
        {
            $this->_uploadedFilesErrorMessages = 'One or more files cannot be stored';

            return false;
        }

        $uploadedFilesData = UploadedFile::saveUploadedFiles($processedFiles);

        // pre_print($uploadedFilesData, true);
        $this->_filesData = $uploadedFilesData;
    }

    /**
     * @return string
     */
    public function getUploadedFilesErrorMessages()
    {
        return $this->_uploadedFilesErrorMessages;
    }

    public function uploadedFilesError()
    {
        return $this->_uploadedFilesErrorMessages !== null;
    }

    public function getFilesData()
    {
        return $this->_filesData;
    }

    private function _retrieveData()
    {
        $this->_data = array();

        foreach ($this->_inputNames as $name)
        {
            if(isset($this->_rawData[$name]))
                $this->_data[$name] = $this->_rawData[$name];
            else
            {
                $this->_data[$name] = null; // Not detected, the field may be disabled
            }
        }

        if($this->_hasFile)
        {
            $this->_fileInputNames = UploadedFile::detectFileInputNames();

            if(empty($this->_fileInputNames) || $this->_fileInputNames == null)
                if($this->submitted())
                    echo("<br/><strong>[M::NOTICE]</strong> Form validation is set to have file, but there is no submitted file detected. Please check your HTML form enctype.<br/>");
        }

        // pre_print($this->_fileInputNames, true);
    }

    private function _retrieveRawData()
    {
        $response = $_POST;

        if($this->_method != 'post')
            $response = $_GET;

        if($this->submitted())
            $this->_rawData = $response;

        //pre_print($this->_rawData, true);
    }

    public function submitted()
    {
        $response = $_POST;

        if($this->_method != 'post')
            $response = $_GET;

        return (isset($response[($this->_submitName)]));
    }

    public function getData()
    {
        return $this->_data;
    }

    public function addRequiredInput($inputName)
    {
        $this->_requiredInputs[] = $inputName;
    }

    public function addRequiredInputs(array $inputNames)
    {
        $this->_requiredInputs = array_merge($this->_requiredInputs, $inputNames);
    }

    private function _requiredInputsAreValid()
    {
        foreach ($this->_requiredInputs as $requiredInput)
        {
            if(array_search($requiredInput, $this->_inputNames) === false)
                return false;
        }

        return true;
    }

    public function isValid()
    {
        if(!$this->_requiredInputsAreValid())
        {
            // TODO: pack this into a reliable exception model
            echo('<pre>');
            echo('One or more required inputs supplied are not valid.');
            echo('<br/>');
            echo('The required input(s) are: ');
            echo('<br/>');
            print_r($this->_requiredInputs);
            echo('<br/>');
            echo('Available input(s) are:');
            echo('<br/>');
            print_r($this->_inputNames);
            echo('<br/>');
            echo('</pre>');
            die(1);
        }

        if(!$this->_checkRequired())
            return $this->_handleInvalid('required');

        return true;
    }

    public function isFileInputName($inputName)
    {
        foreach ($this->_fileInputNames as $fInputName)
        {
            if($inputName == $fInputName)
                return true;
        }

        return false;
    }

    private function _checkRequired()
    {
        //pre_print($_POST);

        foreach ($this->_requiredInputs as $required)
        {
            if(!$this->isFileInputName($required))
            {
                if (empty($this->_data[$required])
                    || $this->_data[$required] == null
                    || $this->_data[$required] == '') {
                    //pre_print("$required --> NOT OK!");
                    return false;
                }
                else // If the input name is in the form of array
                {
                    if(is_array($this->_data[$required]))
                    {
                        foreach ($this->_data[$required] as $element)
                        {
                            if (empty($element) || $element == null || $element == '')
                            {
                                return false;
                            }
                        }
                    }
                }
            }
            else
            {
                // TODO: what if the file is also in the form of array named input too?
                if ($this->getUploadedFile($required)->isEmpty()) {
                    //pre_print("$required --> NOT OK!");
                    return false;
                }
            }

            //pre_print("$required --> OK");
        }

        return true;
    }

    private function _handleInvalid($type)
    {
        $message = 'The form is invalid';

        if($type == 'required')
            $message = 'The following field(s) are required: ' . implode(', ', $this->_requiredInputs);

        $this->_invalidMessage = $message;

        return false;
    }

    public function getInvalidMessage()
    {
        return $this->_invalidMessage;
    }

    public function getEntireData()
    {
        $data = $this->getData();
        $filesData = $this->getFilesData();

        $merge = array_merge($data, $filesData);

        $entireData = array();

        foreach ($this->_inputNames as $name)
        {
            $entireData[$name] = isset($merge[$name]) ? $merge[$name] : null;
        }

        return $entireData; // Final data including unprocessed files.
    }
}