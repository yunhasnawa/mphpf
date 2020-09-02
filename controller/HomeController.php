<?php


namespace controller;


use m\Application;
use m\Controller;

use model\StudentModel;

class HomeController extends Controller
{
    private $_mStudent;

    public function __construct(Application $application)
    {
        parent::__construct($application);

        $this->_mStudent = new StudentModel();
    }

    public function index()
    {
        $students = $this->_mStudent->findAll();

        $data = array(
            'all_students' => $students
        );

        $this->view->setData($data);

        $this->view->setContentTemplate('/home/index_template.php');
        $this->view->render();
    }

    public function addStudent()
    {
        if(isset($_POST['submit']))
        {
            // Berarti user sudah mengisi data, tinggal simpan
            $this->saveStudentData();

            // Redirect ke halaman awal
            $this->redirect('/');
        }

        $this->view->setContentTemplate('/home/add_student_template.php');
        $this->view->render();
    }

    private function saveStudentData()
    {
        $name        = $_POST['name'];
        $address     = $_POST['address'];
        $phoneNumber = $_POST['phone_number'];

        $this->_mStudent->addNew($name, $address, $phoneNumber);
    }
}


// http://github.com/yunhasnawa/mphpf