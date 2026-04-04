<?php
/**
 * Routes Definition
 * Definisikan semua rute aplikasi di sini
 */

use App\Router;

// Auth Routes
Router::get('/login', 'AuthController@loginPage');
Router::post('/login', 'AuthController@loginPage');
Router::get('/logout', 'AuthController@logout');
Router::get('/unauthorized', 'AuthController@unauthorized');

// Dashboard Routes
Router::get('/', 'DashboardController@index');
Router::get('/index.php', 'DashboardController@index');

// Tambahkan routes lainnya di sini
// Router::get('/murid', 'MuridController@index');
// Router::post('/murid', 'MuridController@store');
// dst...
