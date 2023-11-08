<?php
#MySQL 데이터베이스 서버에 접속하기 
$server = 'localhost';  # '127.0.0.1'
$user = 'root';
$passwd = '';
$dbname = 'englishww';
$conn = new mysqli($server, $user, $passwd, $dbname);
if($conn->connect_error) 
    die("데이터베이스 서버 접속에 오류가 발생");
?>