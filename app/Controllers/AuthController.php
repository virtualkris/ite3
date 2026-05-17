<?php
namespace App\Controllers;

use App\Models\User;
use App\Helpers\Validator;

class AuthController extends Controller {
    public function showLoginForm() {
        return $this->render('login');
    }

    public function login() {
        $username = $_POST['username'] ?? '';
        $password = $_POST['password'] ?? '';

        Validator::clearErrors();
        Validator::required('username', $username);
        Validator::required('password', $password);

        if (Validator::getErrors()) {
            return $this->render('login', [
                'errors' => Validator::getErrors()
            ]);
        }

        $userModel = new User();
        $user = $userModel->findByUsername($username);

        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            header('Location: /ite3/home');
            exit;
        }

        return $this->render('login', [
            'errors' => ['auth' => 'Invalid username or password!']
        ]);
        
    }

    public function logout() {
        session_destroy();
        header('Location: /ite3/login');
        exit;
    }

}
