<?php


namespace m\extended;


use m\UploadedFileModel;

class UploadedFile
{
    /*
    Array
    (
        [scan_persetujuan_maju] => Array
        (
            [name] => Belum dapat pembimbing.jpeg
            [type] => image/jpeg
            [tmp_name] => /tmp/phpNsybTR
            [error] => 0
            [size] => 24685
        )
    )
     */
    // Readonly properties
    private $_inputName;
    private $_name;
    private $_type;
    private $_tmpName;
    private $_error;
    private $_size;
    private $_model;

    // Read only formed-properties
    private $_nameOnly;
    private $_extension;
    private $_storedName;
    private $_storedCompleteName;

    // For validation
    private $_allowedMaximumSize;
    private $_allowedTypes;
    private $_description;
    private $_lastError;

    public function __construct($inputName, UploadedFileModel $model = null)
    {
        $this->_allowedTypes = array();
        $this->_allowedMaximumSize = -1;
        $this->_description = null;

        $this->_inputName = $inputName;

        if(isset($_FILES[($this->_inputName)]))
            $file = $_FILES[($this->_inputName)];
        else
            terminate('There are no $_FILES ' . " input with name: $inputName.");

        $this->_name    = $file['name'];
        $this->_type    = $file['type'];
        $this->_tmpName = $file['tmp_name'];
        $this->_error   = $this->_confirmError($file);
        $this->_size    = $file['size'];

        $this->_preProcess();

        if($model == null)
            $this->_model = new UploadedFileModel();
        else
            $this->_model = $model;
    }

    public function isEmpty()
    {
        return $this->_error === 4;
    }

    private function _confirmError($rawFileData)
    {
        // TODO: Chek this.
        return $rawFileData['error'];
    }

    /**
     * @param mixed $allowedMaximumSize
     * @return UploadedFile
     */
    public function setAllowedMaximumSizeBytes($allowedMaximumSize)
    {
        $this->_allowedMaximumSize = $allowedMaximumSize;

        return $this;
    }

    public function setAllowedMaximumSizeKiloBytes($allowedMaximumSize)
    {
        $this->_allowedMaximumSize = ($allowedMaximumSize * 1024);

        return $this;
    }

    public function setAllowedMaximumSizeMegaBytes($allowedMaximumSize)
    {
        $this->_allowedMaximumSize = ($allowedMaximumSize * 1024 * 1024);

        return $this;
    }

    public static function detectFileInputNames()
    {
        $inputNames = array();

        foreach ($_FILES as $key => $file)
        {
            $inputNames[] = $key;
        }

        return $inputNames;
    }

    public static function collectErrorMessages(array $uploadedFiles)
    {
        $errorMessages = '';

        foreach ($uploadedFiles as $uploadedFile)
        {
            if(! $uploadedFile->isGood())
            {
                $errorMessages .= $uploadedFile->isGood(true);
                $errorMessages .= ". ";
            }
        }

        return $errorMessages;
    }

    public static function collectEmptyFiles(array $uploadedFiles, $assoc = true)
    {
        $emptyFiles = array();

        foreach ($uploadedFiles as $file)
        {
            if($file->isEmpty())
            {
                if($assoc)
                    $emptyFiles[$file->getInputName] = $file;
                else
                    $emptyFiles[] = $file;
            }
        }

        return $emptyFiles;
    }

    public static function storeUploadedFiles(array $uploadedFiles)
    {
        // Try store all files first
        foreach ($uploadedFiles as $file)
        {
            $file->store();

            if($file->hasError())
                return false;
        }

        // All files have been stored
        return true;
    }

    public static function saveUploadedFiles(array $uploadedFiles)
    {
        $storedNames = array();

        foreach ($uploadedFiles as $file)
        {
            $file->saveToDatabase();

            $storedNames[$file->getInputName()] = $file->getStoredName();
        }

        return $storedNames;
    }

    /**
     * @param array $allowedTypes
     * @return UploadedFile
     */
    public function setAllowedTypes(array $allowedTypes)
    {
        $this->_allowedTypes = $allowedTypes;

        return $this;
    }

    /**
     * @return null
     */
    public function getDescription()
    {
        return $this->_description;
    }

    /**
     * @param null $description
     * @return UploadedFile
     */
    public function setDescription($description)
    {
        $this->_description = $description;
        return $this;
    }

    private function _preProcess()
    {
        $explode = explode('.', $this->_name);

        $this->_extension = $explode[(count($explode) - 1)];

        unset($explode[(count($explode) - 1)]);

        $this->_nameOnly = implode('.', $explode);
    }

    public function isGood($returnNoGoodAsErrorMessages = false)
    {
        if($this->_error != 0)
        {
            if($returnNoGoodAsErrorMessages)
                return "Error processing uploaded file [{$this->getInputName()}], it has error code: {$this->_error}";

            return false;
        }

        if(!$this->_checkType())
        {
            if($returnNoGoodAsErrorMessages)
            {
                $strAllowedTypes = implode(', ', $this->_allowedTypes);

                return "Error processing uploaded file [{$this->getInputName()}]. It has type: '{$this->_type}', that does not match any allowed types: $strAllowedTypes";
            }

            return false;
        }

        if(!$this->_checkAllowedMaximumSize())
        {
            if($returnNoGoodAsErrorMessages)
                return "Error processing uploaded file [{$this->getInputName()}], it has size: '{$this->_size}', that surpasses the maximum allowed size: {$this->_allowedMaximumSize}";

            return false;
        }

        return true;
    }

    private function _checkType()
    {
        if(count($this->_allowedTypes) < 1 || $this->_allowedTypes == null)
            return true;

        foreach ($this->_allowedTypes as $allowedType)
        {
            if($this->_type == $allowedType)
                return true;
        }

        return false;
    }

    private function _checkAllowedMaximumSize()
    {
        if($this->_allowedMaximumSize < 0)
            return true;

        return $this->_size <= $this->_allowedMaximumSize;
    }

    public function store()
    {
        // To prevent different file with the same unique name (timestamp), we add input file name to back of timestamp
        $newFileName = $this->_model->getUniqueName($this->_inputName);

        $newFileName .= ".{$this->_extension}";

        $this->_moveUploadedFile($newFileName);
    }

    public function storeAs($fileName)
    {
        $this->_moveUploadedFile($fileName);
    }

    public function saveToDatabase()
    {
        $this->_model->save($this);
    }

    private function setLastError($error)
    {
        $this->_lastError = $error;
    }

    private function _moveUploadedFile($newFileName)
    {
        $this->_storedName = $newFileName;

        $path = $this->_model->getUploadDirectory();

        $this->_storedCompleteName = $path . '/' . $this->_storedName;

        // Catch warning for access denied and any others alike
        // https://stackoverflow.com/questions/1241728/can-i-try-catch-a-warning
        set_error_handler(function() {$this->setLastError('Unable to store file, possibly because access is denied');});
        move_uploaded_file($this->_tmpName, $this->_storedCompleteName);
        restore_error_handler();
    }

    public function hasError()
    {
        return $this->_lastError != null & $this->_lastError != '';
    }

    public function getLastError()
    {
        return $this->_lastError;
    }

    /**
     * @return mixed
     */
    public function getStoredName()
    {
        return $this->_storedName;
    }

    /**
     * @return mixed
     */
    public function getStoredCompleteName()
    {
        return $this->_storedCompleteName;
    }

    /**
     * @return mixed
     */
    public function getInputName()
    {
        return $this->_inputName;
    }

    /**
     * @return mixed
     */
    public function getName()
    {
        return $this->_name;
    }

    /**
     * @return mixed
     */
    public function getType()
    {
        return $this->_type;
    }

    /**
     * @return mixed
     */
    public function getTmpName()
    {
        return $this->_tmpName;
    }

    /**
     * @return mixed
     */
    public function getError()
    {
        return $this->_error;
    }

    /**
     * @return mixed
     */
    public function getSize()
    {
        return $this->_size;
    }

    /**
     * @return UploadedFileModel
     */
    public function getModel()
    {
        return $this->_model;
    }

    /**
     * @return mixed
     */
    public function getNameOnly()
    {
        return $this->_nameOnly;
    }

    /**
     * @return mixed
     */
    public function getExtension()
    {
        return $this->_extension;
    }

    public static function fromInputNames(array $inputNames, UploadedFileModel $model = null, $assoc = true)
    {
        $ufs = array();

        foreach ($inputNames as $name)
        {
            $uf = new UploadedFile($name, $model);

            if($assoc)
                $ufs[$name] = $uf;
            else
                $ufs[] = $uf;
        }

        return $ufs;
    }

    public static function fileWithInputNameExists($fileInputName)
    {
        return isset($_FILES[$fileInputName]);
    }

    /**
     * Quickly handle uploaded file using default settings by storing it without saving its metadata to database
     * @param string $fileInputName the name from $_FILES
     * @param int $allowedSizeKb maximum file size, negative number means unlimited
     * @return string|null the complete path of stored file, or null if anything incorrect happened
     */
    public static function quickHandle($fileInputName, $allowedSizeKb = -1)
    {
        if(!self::fileWithInputNameExists($fileInputName))
            return null;

        $uf = new UploadedFile($fileInputName);

        if($allowedSizeKb >= 0)
            $uf->setAllowedMaximumSizeKiloBytes($allowedSizeKb);

        if($uf->isEmpty())
            return null;

        if(!$uf->isGood())
            return null;

        $uf->store();

        return $uf->getStoredCompleteName();
    }
}