<?php
require_once __DIR__ . '/config.php';

function getDbConnection() {
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    if ($conn->connect_error) {
        throw new Exception('Database connection failed. Please try again later.');
    }
    $conn->set_charset('utf8mb4');
    return $conn;
}
