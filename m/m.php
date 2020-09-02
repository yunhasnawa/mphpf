<?php
session_start(); // Prevent session lost after page redirection

include_once 'Session.php';
include_once 'Application.php';
include_once 'Model.php';
include_once 'View.php';
include_once 'Controller.php';
include_once 'Settings.php';
include_once 'Route.php';
include_once 'Util.php';
// include_once 'UserData.php'; This class is still experimental

include_once 'extended/AccessControl.php';
include_once 'extended/Policy.php';
include_once 'extended/AuthPolicy.php';
include_once 'extended/AuthModel.php';
include_once 'extended/Option.php';
include_once 'extended/Input.php';
include_once 'extended/Form.php';
include_once 'extended/FormValidation.php';
include_once 'extended/UploadedFileModel.php';
include_once 'extended/UploadedFile.php';
include_once 'extended/FilterForm.php';

// Global functions

function m_dir()
{
    return dirname(__FILE__);
}

function terminate($message)
{
    die("[M::ERROR] $message");
}

function pre_print($data = array(), $die = false)
{
    $trace = debug_backtrace();
    $firstLevel = $trace[0];

    $caller = "Data printed by: <strong>{$firstLevel['file']}</strong> on line <strong>{$firstLevel['line']}</strong>";

    echo "<br/>$caller<br/>";
    echo '<pre>';
    if($data == null)
        echo 'Data is Null.';
    else
        print_r($data);
    echo '</pre><br/>';

    if($die)
        die;
}