<?php

//
// Oussama Elgoumri
// contact@sec4ar.com
//
// Sat Feb 18 10:02:18 WET 2017
//

namespace OussamaElgoumri\Exceptions;

class UptoboxDirectLinkException extends \Exception
{
    /**
     * Initialize Constructor.
     *
     * @param string    $link
     */
    public function __construct($link)
    {
        parent::__construct("Could not retrieve uptobox direct link: {$link}");
    }
}
