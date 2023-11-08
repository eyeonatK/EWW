<!DOCTYPE html>
<html>
<head>
    <title>EWW</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="gamestyles.css">
    <script src="https://kit.fontawesome.com/d3bd33114f.js" crossorigin="anonymous"></script>
    <style>
        .box-container {
            display: flex;
            justify-content: space-around;
            align-items: center;
            height: 400px;
        }
        .box {
            width: calc(33.33% - 30px);
            padding: 20px;
            margin: 10px;
            border: 1px solid #ddd;
            display: flex;
            flex-direction: column;
            align-items: center;
            text-align: center;
            box-sizing: border-box;
            background-color: rgba(100, 100, 100, 0.8); /* 조금 투명한 배경색 */
            border-radius: 15px; /* 박스의 모서리를 둥글게 */
            
        }
        .box img {
            width: 120px;
            height: 120px;
            border-radius: 50%; /* 이미지를 동그랗게 */
            margin-bottom: 20px;
        }
        .box button {
            padding: 10px 20px;
            font-size: 20px;
            cursor: pointer;
            border: none;
            background-color: #E9463F;
            color: black;
            border-radius: 5px; /* 버튼의 모서리를 약간 둥글게 */
        }
        .box button:hover {
            background-color: white;
        }
    </style>
</head>
<body>
    <?php
    session_start();
    $login = false;
    include_once('dbconn.php');
    if (isset($_SESSION['userid'])) {
        $userid= $_SESSION['userid'];
        $uname = $_SESSION['username'];
        $login = true;
    }
    if (isset($_POST['logout'])) {
        session_destroy();
        header('Location: index.php');
        exit();
    }
    include 'topnav.php';?>

    <div class="box-container">
        <div class="box">
            <img src="img/zoom.jpg" alt="Dictionary Image">
            <button onclick="location.href='serchdic.php'">단어사전 <i class="fa-solid fa-spell-check"></i></button>
        </div>
        <div class="box">
            <img src="img/close-up-cubes-with-letters.jpg" alt="Study Image">
            <button onclick="location.href='study.php'">단어학습 <i class="fa-solid fa-school"></i></button>
        </div>
        <div class="box">
            <img src="img/siora-photography-ZslFOaqzERU-unsplash.jpg" alt="Profile Image">
            <button onclick="location.href='profile.php'">대시보드 <i class="fa-solid fa-user"></i></button>            
        </div>
    </div>
</body>
</html>
