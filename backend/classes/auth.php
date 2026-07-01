<?php

class Auth
{
    private $user;

    public function __construct($user)
    {
        $this->user = $user;
    }

    public function login($email, $password)
    {
        $user = $this->user->findByEmail($email);

        if ($user && password_verify($password, $user['password'])) {

            session_start(); // IMPORTANT SAFETY FIX

            $_SESSION['user_id'] = $user['user_id'];
            $_SESSION['name'] = $user['full_name'];
            $_SESSION['role'] = $user['role'];

            // ✅ CRITICAL FIX FOR STUDENT SYSTEM
            if ($user['role'] === 'student') {
                $_SESSION['student_id'] = $user['user_id']; 
                // OR correct column if you have student_id separately
            }

            return true;
        }

        return false;
    }
}