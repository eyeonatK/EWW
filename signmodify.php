<!DOCTYPE html>
<html>
<head>
    <title>회원정보 수정</title>
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
        <a href="signout.php">회원탈퇴</a>
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

    // 로그인 상태 확인
    if (!isset($_SESSION['userid'])) {
        echo "로그인 상태가 아닙니다.";
        exit();
    }

    $userid = $_SESSION['userid'];

    // 중복 아이디 검사
    if (isset($_POST['new_userid'])) {
        $new_userid = $_POST['new_userid'];
        
        if(!$new_userid == $userid){
            // 중복 검사
        $sql = "SELECT * FROM member WHERE userid = '$new_userid'";
        $result = $conn->query($sql);

        if ($result->num_rows > 0) {
            echo "중복된 아이디입니다. 다른 아이디를 입력해주세요.";
            exit();
        }
        }
        

        // 아이디 변경
        $sql = "UPDATE member SET userid = '$new_userid' WHERE userid = '$userid'";
        if ($conn->query($sql) === TRUE) {
            $_SESSION['userid'] = $new_userid;
            echo "아이디가 변경되었습니다.";
        } else {
            echo "아이디 변경 중 오류가 발생하였습니다: " . $conn->error;
            exit();
        }

        $userid = $new_userid; // 변경된 아이디로 업데이트
    }
    // 비밀번호 변경
    if (isset($_POST['new_password'])) {
        $new_password = $_POST['new_password'];

        $sql = "UPDATE member SET password = '$new_password' WHERE userid = '$userid'";
        if ($conn->query($sql) === TRUE) {
            echo "비밀번호가 변경되었습니다.";
        } else {
            echo "비밀번호 변경 중 오류가 발생하였습니다: " . $conn->error;
            exit();
        }
    }

    // 이메일 변경
    if (isset($_POST['new_email'])) {
        $new_email = $_POST['new_email'];

        $sql = "UPDATE member SET email = '$new_email' WHERE userid = '$userid'";
        if ($conn->query($sql) === TRUE) {
            echo "이메일이 변경되었습니다.";
        } else {
            echo "이메일 변경 중 오류가 발생하였습니다: " . $conn->error;
            exit();
        }
    }

    // 회원 정보 가져오기
    $sql = "SELECT * FROM member WHERE userid = '$userid'";
    $result = $conn->query($sql);

    if ($result->num_rows == 1) {
        $row = $result->fetch_assoc();
        $username = $row['username'];
        $email = $row['email'];

        // 회원 정보 수정 폼
        ?>
        <h2>회원정보 수정</h2>
        <form method="post" action="<?php echo $_SERVER["PHP_SELF"]; ?>">
            <label for="username">사용자명:</label>
            <input type="text" id="username" name="username" value="<?php echo $username; ?>" disabled><br><br>

            <label for="new_userid">아이디 변경:</label>
            <input type="text" id="new_userid" name="new_userid" value="<?php echo $userid; ?>" required><br><br>

            <label for="new_password">비밀번호 변경:</label>
            <input type="password" id="new_password" name="new_password" required><br><br>

            <label for="new_email">이메일 변경:</label>
            <input type="email" id="new_email" name="new_email" value="<?php echo $email; ?>" required><br><br>

            <input type="submit" value="수정">
        </form>
    <?php
    } else{
        echo"회원정보가 조회되지않습니다.";
    }
