<?php

//
// Oussama Elgoumri
// contact@sec4ar.com
//
// Sat Feb 18 11:49:03 WET 2017
//

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
