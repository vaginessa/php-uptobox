<?php

// 
// Oussama Elgoumri
// contact@sec4ar.com
//
// Sat Feb 18 09:59:59 WET 2017
// 

namespace OussamaElgoumri;

use OussamaElgoumri\Exceptions\UptoboxLoginFailedException;
use OussamaElgoumri\Exceptions\UptoboxRequireUsernameAndPasswordException;

class Login
{
    const LOGIN_POST_URL = 'https://login.uptobox.com/logarithme';

    /**
     * Login to uptobox account.
     *
     * @throws UptoboxLoginFailed
     */
    public function login()
    {
        $data = Request::post(
            static::LOGIN_POST_URL,
            [  'login'    => getenv('UPTOBOX_USERNAME'),
               'password' => getenv('UPTOBOX_PASSWORD'),
               'op'       => 'login',
            ], [ CURLOPT_REFERER    => 'https://login.uptobox.com/' ],
            false
        );

        if (!$data) {
            throw new UptoboxLoginFailedException($this->username, $this->password);
        }

        $data = json_decode($data);

        if (!isset($data->success) && !$data->success === 'Ok') {
            throw new UptoboxLoginFailedException($this->username, $this->password);
        }
    }
}
