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

        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $_SESSION['user_id'] = $user['user_id'];
        $_SESSION['name'] = $user['full_name'];
        $_SESSION['role'] = $user['role'];

        // GET STUDENT ID IF STUDENT
        if ($user['role'] === 'student') {

            $student = $this->user->getStudentByUserId($user['user_id']);

            if ($student) {
                $_SESSION['student_id'] = $student['student_id'];
            }
        }

        return true;
    }

    return false;
}
}