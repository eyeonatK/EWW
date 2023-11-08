<!DOCTYPE html>
<html>
<head>
    <title>로그인 페이지</title>

    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="gamestyles.css">
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
    ?>
    <div class="topnav">
        <?php
        if ($login) {
        echo '<script>alert("먼저 로그아웃 해주세요."); location.href="index.php";</script>';
        } else { ?>
        <a href="index.php">홈으로</a>
        <a href="signup.php">회원가입</a>
        <?php } ?>
    </div>
    <?php
    include_once('dbconn.php');

    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        // 로그인 폼에서 입력된 값 가져오기
        $userid = $_POST['userid'];
        $password = $_POST['password'];

        // 아이디와 비밀번호 검사
        $sql = "SELECT * FROM member WHERE userid = '$userid' AND password = '$password'";
        $result = $conn->query($sql);

        if ($result->num_rows == 1) {
            $row = $result->fetch_assoc();
            $username = $row['username'];
            $_SESSION['userid'] = $userid; // 세션 생성
            $_SESSION['username'] = $username;
            echo '<script>alert("' . $username . '님 환영합니다."); location.href="index.php";</script>'; // index.php 페이지로 이동
            exit();
        } else {
            echo "아이디 또는 비밀번호가 잘못되었습니다.";
        }

        $conn->close();
    }
    ?>

    <h2>로그인</h2>
    <form method="post" action="<?php echo $_SERVER["PHP_SELF"]; ?>">
        <label for="userid">아이디:</label>
        <input type="text" id="userid" name="userid" required><br><br>

        <label for="password">비밀번호:</label>
        <input type="password" id="password" name="password" required><br><br>

        <input type="submit" value="로그인">
    </form>
</body>
</html>
