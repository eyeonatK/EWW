<!DOCTYPE html>
<html>
<head>
    <title>회원 탈퇴 페이지</title>
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
        ?>
        <a href="#"><?= $uname ?> 님 환영합니다.</a>
        <a href="index.php">홈으로</a>
        <a href="stats.php">통계보기</a>
        <a href="signmodify.php">회원정보수정</a>        
        <div class="logout">
            <form method="post" action="<?php echo $_SERVER["PHP_SELF"]; ?>">
                <button type="submit" name="logout">로그아웃</button>
            </form>
        </div>
        <?php } else { 
            echo '<script>alert("먼저 로그인 해주세요."); location.href="login.php";</script>';
        } ?>
    </div>
    <!-- 요기까지 top -->
    <?php
    include_once('dbconn.php');
    $userid = $_SESSION['userid'];

    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        // 로그인 폼에서 입력된 값 가져오기
        $password = $_POST['password'];

        // 아이디와 비밀번호 검사
        $serch_query = "SELECT * FROM member WHERE userid = '$userid' AND password = '$password'";
        $result = $conn->query($serch_query);

        if ($result->num_rows == 1) {
            $row = $result->fetch_assoc();
            $username = $row['username'];
            // 회원 탈퇴
            $delete_query = "DELETE FROM member WHERE userid = '$userid'";
            $conn->query($delete_query);
            session_destroy();
            echo '<script>alert("' . $username . ' 님의 회원 탈퇴 완료"); location.href="index.php";</script>'; // index.php 페이지로 이동
            exit();
        } else {
            echo "회원 정보를 다시 입력해주세요";
        }
        $conn->close();
    }
    ?>

    <h2>회원 탈퇴</h2>
    <form method="post" action="<?php echo $_SERVER["PHP_SELF"]; ?>">
        <label for="userid">아이디:</label>
        <input type="text" id="userid" name="userid" value="<?php echo $userid; ?>" readonly><br><br>
        <!-- 아이디 입력란은 로그인에 사용한 아이디로 고정 -->
        <label for="password">비밀번호:</label>
        <input type="password" id="password" name="password" required><br><br>

        <input type="submit" value="로그인">
    </form>
</body>
</html>
