<?php

namespace m;

use m\extended\UploadedFile;

class UploadedFileModel extends \m\Model
{
    const DEFAULT_TABLE_NAME = 'uploaded_file';
    const DEFAULT_UPLOAD_DIRECTORY_NAME = 'upload';

    private $_uploadDirectory;
    private $_relativeUploadDirectory;

    public function __construct($tableName = self::DEFAULT_TABLE_NAME)
    {
        parent::__construct($tableName);

        $this->_uploadDirectory         = $this->_determineUploadDirectory();
        $this->_relativeUploadDirectory = $this->_determineUploadDirectory(false);
    }

    private function _determineUploadDirectory($absolute = true)
    {
        $settings = Settings::getInstance();

        $appFolder = $absolute ? $settings->getAbsoluteAppFolder() : $settings->getAppFolder();

        $uploadFolder = $appFolder . '/' . $this->getDefaultUploadDirectoryName();

        return $uploadFolder;
    }

    public function createFileLink($storedName, $encloseWithAHref = false, $aHrefCaption = null)
    {
        if($storedName == null || $storedName == '')
            return null;

        $uploadDir = $this->getRelativeUploadDirectory();

        $fileLink = "$uploadDir/$storedName";

        if(!$encloseWithAHref)
            return $fileLink;
        else
        {
            if($aHrefCaption == null)
                $aHrefCaption = $this->findOriginalName($storedName);

            return '<a href="' . $fileLink . '">' . $aHrefCaption . '</a>';
        }
    }

    protected function getDefaultUploadDirectoryName()
    {
        return self::DEFAULT_UPLOAD_DIRECTORY_NAME;
    }

    /**
     * @return string
     */
    public function getUploadDirectory()
    {
        return $this->_uploadDirectory;
    }

    public function getRelativeUploadDirectory()
    {
        return $this->_relativeUploadDirectory;
    }

    public function save(UploadedFile $uploadedFile)
    {
        $now = date("Y-m-d H:i:s");
        
        $this->createTableIfNotExists($this->tableName);

        $sql = "INSERT INTO {$this->tableName} VALUES ('{$uploadedFile->getStoredName()}', '{$uploadedFile->getName()}', '{$uploadedFile->getType()}', '{$uploadedFile->getSize()}', '{$uploadedFile->getInputName()}', '$now', '{$uploadedFile->getDescription()}');";

        $this->executeWriteSQL($sql);
    }

    protected function createTableIfNotExists($tableName)
    {
        $sql = <<< PHP_HEREDOC
            CREATE TABLE IF NOT EXISTS $tableName
            (
                stored_name VARCHAR(255) UNIQUE NOT NULL PRIMARY KEY,
                original_name TEXT DEFAULT NULL,
                `type` VARCHAR(255),
                size BIGINT,
                input_name VARCHAR(255),
                stored_time DATETIME DEFAULT CURRENT_TIMESTAMP,
                description TEXT DEFAULT NULL
            );
PHP_HEREDOC;

        $this->executeWriteSQL($sql);
    }

    public function findOriginalName($storedName)
    {
        return $this->findOneRowOneColumnValue('original_name', ['stored_name' => $storedName]);
    }

    public function getUniqueName($fileInputName)
    {
        $timestamp = time();

        $md5 = md5($timestamp . $fileInputName);

        return $md5;
    }
}