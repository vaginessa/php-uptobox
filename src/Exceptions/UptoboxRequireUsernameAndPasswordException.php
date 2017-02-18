<?php

//
// Oussama Elgoumri
// contact@sec4ar.com
//
// Sat Feb 18 10:02:18 WET 2017
//

namespace OussamaElgoumri\Exceptions;

class UptoboxRequireUsernameAndPasswordException extends \Exception
{
    /**
     * Initialize Exception.
     */
    public function __construct()
    {
        parent::__construct('Please set a valid username & password!');
    }
}
