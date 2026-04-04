<?php
/**
 * Auth Controller
 * Handle autentikasi login/logout
 */

namespace App\Controllers;

use App\Models\User;

class AuthController {
    /**
     * Login page
     */
    public static function loginPage() {
        if (User::isLoggedIn()) {
            header('Location: /cashflowKas/index.php');
            exit;
        }
        
        $error = '';
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $username = $_POST['username'] ?? '';
            $password = $_POST['password'] ?? '';
            
            if (User::login($username, $password)) {
                header('Location: /cashflowKas/index.php');
                exit;
            } else {
                $error = 'Username atau password salah!';
            }
        }
        
        require SRC_PATH . '/Views/auth/login.php';
    }

    /**
     * Logout
     */
    public static function logout() {
        User::logout();
        header('Location: /cashflowKas/login.php');
        exit;
    }

    /**
     * Unauthorized page
     */
    public static function unauthorized() {
        require SRC_PATH . '/Views/auth/unauthorized.php';
    }
}
