<?php

function pdo_connect(){
    $DATABASE_HOST = 'localhost';
    $DATABASE_USER = 'quizbot';
    $DATABASE_PASS = 'quizbot';
    $DATABASE_NAME = 'quiz_os';
    try {
    	return new PDO('mysql:host=' . $DATABASE_HOST . ';dbname=' . $DATABASE_NAME, $DATABASE_USER, $DATABASE_PASS);
    } catch (PDOException $exception) {
    	die ('Failed to connect to database!');
    }
}

function bot_token_path(){
    $token = "7735227310:AAGyc8o6vyEeaSNgl_Vz18W1YqFSTKr0FzA";
    $path = "https://api.telegram.org/bot" . $token;

    return $path;
}


function style_script(){
    return '<link rel="stylesheet" href="css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.1.3/css/bootstrap.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.10.20/css/dataTables.bootstrap4.min.css">
    <script src="js/jquery-3.4.1.slim.min.js"></script>
    <script src="js/popper.min.js"></script>
    <script src="js/bootstrap.min.js"></script>
    <script src="https://cdn.datatables.net/1.10.20/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.10.20/js/dataTables.bootstrap4.min.js"></script>';
}
?>
