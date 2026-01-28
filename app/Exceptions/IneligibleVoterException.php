<?php

namespace App\Exceptions;

use Exception;

class IneligibleVoterException extends Exception
{
    protected $message = 'Access Denied: You are not on the official voter list for this organization.';
    protected $code = 403;
}
