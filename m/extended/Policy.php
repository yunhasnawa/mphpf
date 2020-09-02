<?php

namespace m\extended;

use m\Controller;

interface Policy
{
    public function inspect(Controller $subject);

    public function getRedirectRoute();
}