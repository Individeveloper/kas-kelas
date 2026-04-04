<?php
/**
 * User Model
 * Handle semua operasi terkait user
 */

namespace App\Models;

use App\Helpers\Database;

class User {
    /**
     * Login User
     */
    public static function login($username, $password) {
        $username = Database::escape($username);
        $result = Database::query("SELECT * FROM user WHERE username = '$username'");
        
        if ($row = $result->fetch_assoc()) {
            if ($password === $row['password']) {
                $_SESSION['user_id'] = $row['id_user'];
                $_SESSION['username'] = $row['username'];
                $_SESSION['role'] = $row['role'];
                return true;
            }
        }
        return false;
    }

    /**
     * Check if user is logged in
     */
    public static function isLoggedIn() {
        return isset($_SESSION['user_id']);
    }

    /**
     * Get Current User
     */
    public static function getCurrentUser() {
        if (self::isLoggedIn()) {
            return [
                'id' => $_SESSION['user_id'],
                'username' => $_SESSION['username'],
                'role' => $_SESSION['role']
            ];
        }
        return null;
    }

    /**
     * Get User Role
     */
    public static function getRole() {
        return $_SESSION['role'] ?? null;
    }

    /**
     * Check if Bendahara
     */
    public static function isBendahara() {
        return self::getRole() === 'bendahara';
    }

    /**
     * Check if Wali Kelas
     */
    public static function isWaliKelas() {
        return self::getRole() === 'wali kelas';
    }

    /**
     * Check if Ketua Kelas
     */
    public static function isKetuaKelas() {
        return self::getRole() === 'ketua kelas';
    }

    /**
     * Logout User
     */
    public static function logout() {
        session_destroy();
    }
}
