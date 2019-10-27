<?php

namespace m;

class Controller
{
    protected $application;
    protected $view;

    public function __construct(Application $application)
    {
        $this->application = $application;

        $this->view = new View($this->application);
    }

    protected function redirect($route)
    {
        $location = $this->view->homeAddress($route);

        header("Location: " . $location);
    }
}