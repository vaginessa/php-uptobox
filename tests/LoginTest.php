<?php

//
// Oussama Elgoumri
// contact@sec4ar.com
//
// Sat Feb 18 09:59:43 WET 2017
//

namespace OussamaElgoumri;

use ReflectionMethod;

class LoginTest extends TestCommon
{
    /**
     * @test
     */
    public function login()
    {
        $login = new Login;
        $login->login();
        $this->assertTrue(true);    // because no exception is thrown :)
        $this->assertFileExists(getCookieFile());
        $this->assertRegExp('/# Netscape HTTP Cookie File/', file_get_contents(getCookieFile()));
    }
}
