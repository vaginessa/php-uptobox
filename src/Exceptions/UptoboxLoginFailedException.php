<?php

//
// Oussama Elgoumri
// contact@sec4ar.com
//
// Sat Feb 18 10:02:18 WET 2017
//

namespace OussamaElgoumri\Exceptions;

class UptoboxLoginFailedException extends \Exception
{
    /**
     * Initialize exception.
     *
     * @param string    $user
     * @param string    $pwd
     */
    public function __construct($user, $pwd)
    {
        parent::__construct("username: {$user}, password: {$pwd}");
    }
}
