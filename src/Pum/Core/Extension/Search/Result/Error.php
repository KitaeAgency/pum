<?php

namespace Pum\Core\Extension\Search\Result;

abstract class Error
{
    private $failed = false;
    private $error;

    public function setFailed($failed)
    {
        $this->failed = $failed;

        return $this;
    }

    public function isFailed()
    {
        return $this->failed;
    }

    public function setError($error)
    {
        $this->error = $error;

        return $this;
    }

    public function getError()
    {
        return $this->error;
    }
}
