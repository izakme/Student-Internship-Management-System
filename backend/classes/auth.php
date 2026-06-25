<?php

class Auth
{
    private $user;

    public function __construct($user)
    {
        $this->user = $user;
    }

    public function login($email,$password)
    {

        $user = $this->user->findByEmail($email);

        if(
            $user &&
            password_verify(
                $password,
                $user['password']
            )
        ){

            $_SESSION['user_id']
                = $user['user_id'];

            $_SESSION['name']
                = $user['full_name'];

            $_SESSION['role']
                = $user['role'];

            return true;
        }

        return false;
    }
}