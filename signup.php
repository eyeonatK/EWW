<!DOCTYPE html>
<html>
<head>
    <title>회원가입 페이지</title>
 <!-- 여기서부터 -->
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
        <a href="login.php">로그인</a>
        <?php } ?>
    </div>
    <!-- 요기까지 top -->

    <?php
    include_once('dbconn.php');

    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        // 회원가입 폼에서 입력된 값 가져오기
        $email = $_POST['email'];
        $userid = $_POST['userid'];
        $username = $_POST['username'];
        $password = $_POST['password'];
        $signdate = date("Y-m-d");

        // 아이디 중복 검사
        $sql = "SELECT * FROM member WHERE userid = '$userid'";
        $result = $conn->query($sql);

        if ($result->num_rows > 0) {
            echo "중복된 아이디입니다. 다른 아이디를 입력해주세요.";
        } else {
            // 회원 정보 데이터베이스에 삽입
            $sql = "INSERT INTO member (userid, username, password, signdate, email, chapter)
                    VALUES ('$userid', '$username', '$password', '$signdate', '$email', 1)";

            if ($conn->query($sql) === TRUE) {
                echo '<script>alert("회원가입이 완료되었습니다."); location.href="login.php";</script>';
            } else {
                echo "회원가입 중 오류가 발생하였습니다: " . $conn->error;
            }
        }

        $conn->close();
    }
    ?>

    <h2>회원가입</h2>
    <form method="post" action="<?php echo $_SERVER["PHP_SELF"]; ?>">
        <label for="username">아이디:</label>
        <input type="text" id="userid" name="userid" required><br><br>

        <label for="email">이메일:</label>
        <input type="email" id="email" name="email" required><br><br>

        <label for="username">이름:</label>
        <input type="text" id="username" name="username" required><br><br>

        <label for="password">비밀번호:</label>
        <input type="password" id="password" name="password" required><br><br>

        <input type="submit" value="회원가입">
    </form>
</body>
</html>
