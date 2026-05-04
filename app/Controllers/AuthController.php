<?php
namespace App\Controllers;

use App\Models\User;

class AuthController extends Controller {
    public function login() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $username = $_POST['username'];
            $password = $_POST['password'];

            $userModel = new User();
            $user = $userModel->findByUsername($username);

            if ($user && password_verify($password, $user['password'])) {
                // Authentication successful
                $_SESSION['user_id'] = $user['id'];
                header('Location: /ite3/home');
                exit;
            } else {
                // Authentication failed
                $this->render('login', [
                    'title' => 'Login',
                    'error' => 'Invalid username or password'
                ]);
                return;
            }
        }

        // Show the login form
        $this->render('login', [
            'title' => 'Login'
        ]);
    }

    public function logout() {
        session_destroy();
        header('Location: /ite3/login');
        exit;
    }

}