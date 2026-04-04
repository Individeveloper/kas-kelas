<?php 
$db = new mysqli('localhost', 'root', '', 'kasKelas');
if ($db->connect_errno) {
    die("Failed to connect to MySQL: " . $db->connect_error);
} 
?>