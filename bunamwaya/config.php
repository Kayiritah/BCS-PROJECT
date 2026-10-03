<?php
session_start();

// Database Configuration - XAMPP Default
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'bunamwaya_db');


$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);


if($conn->connect_error){
    // If DB not exist, try create it
    $temp = new mysqli(DB_HOST, DB_USER, DB_PASS);
    if($temp->connect_error){
        die("Connection failed: ". $temp->connect_error);
    }
    $temp->query("CREATE DATABASE IF NOT EXISTS ".DB_NAME);
    $temp->close();
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    if($conn->connect_error){
        die("DB Connection failed after creating: ".$conn->connect_error);
    }
}

$conn->set_charset("utf8mb4");


date_default_timezone_set('Africa/Kampala');

// Helper function to prevent SQL injection
function clean($conn, $data){
    return $conn->real_escape_string(trim($data));
}

function isLoggedIn(){
    return isset($_SESSION['user_id']);
}
?>