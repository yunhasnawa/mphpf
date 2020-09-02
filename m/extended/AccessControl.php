<?php

namespace m\extended;

use m\Controller;

class AccessControl
{
    private $_subject;

    private $_policies;

    public function __construct(Controller $subject)
    {
        $this->_subject = $subject;

        $this->_policies = [];
    }

    /**
     * @param Policy $policy
     */
    public function addPolicy(Policy $policy)
    {
        $this->_policies[] = $policy;
    }

    public function findPolicy($className)
    {
        foreach($this->_policies as $policy)
        {
            $current = get_class($policy);

            if($current === $className)
                return $policy;
        }

        return null;
    }

    public function getPolicies()
    {
        return $this->_policies;
    }


    public function inspect()
    {
        $result = true;

        $failedPolicy = null;

        foreach ($this->_policies as $policy)
        {
            if($policy->inspect($this->_subject) === false)
            {
                $result = false;

                $failedPolicy = $policy;

                break;
            }
        }

        if($result === false)
            $this->_subject->redirect($failedPolicy->getRedirectRoute());
    }
}