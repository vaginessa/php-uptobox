<?php

//
// Oussama Elgoumri
// contact@sec4ar.com
//
// Sat Feb 18 11:49:03 WET 2017
//

use OussamaElgoumri\Uptobox;
use OussamaElgoumri\Exceptions\UptoboxLinkIsNotValidException;

if (!function_exists('Uptobox__on')) {
    /**
     * Start uptobox worker.
     *
     * @param  string    $link
     * @return array
     */
    function Uptobox__on($link)
    {
        return (new Uptobox($link))
            ->getInfo();
    }
}

if (!function_exists('Uptobox__validate')) {
    /**
     * Validate the given uptobox link.
     *
     * @param  string    $link
     * @return bool
     * @throws UptoboxLinkIsNotValidException
     */
    function Uptobox__validate($link)
    {
        $rc = new ReflectionClass(Uptobox::class);
        $uptobox = $rc->newInstanceWithoutConstructor();

        try {
            $uptobox->validate($link);
            return true;
        } catch (UptoboxLinkIsNotValidException $e) {
            return false;
        }
    }
}

if (!function_exists('getCookieFile')) {
    /**
     * Get full path to cookies file.
     *
     * @return string
     */
    function getCookieFile()
    {
        $username = getenv('UPTOBOX_USERNAME');

        $file = base_path(".cookies/{$username}.cookie");

        if (!file_exists(dirname($file))) {
            mkdir(dirname($file));
        }

        return $file;
    }
}
