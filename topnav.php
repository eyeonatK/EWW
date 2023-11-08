<nav class="topnav">
    <?php
    if ($login) {
    ?>
    <a href="#"><?= $uname ?> 님 환영합니다.</a>
    <a href="index.php">홈으로</a>
    
    <a href="signmodify.php">회원정보수정</a>
    <a href="signout.php">회원탈퇴</a>
    <div class="logout">
        <form method="post" action="<?php echo $_SERVER["PHP_SELF"]; ?>">
            <button type="submit" name="logout">로그아웃</button>
        </form>
    </div>
    <?php } else { ?>
        <a href="login.php">로그인</a>
        <a href="signup.php">회원가입</a>
        <?php
        $current_page = basename($_SERVER['PHP_SELF']);
        if ($current_page != "index.php") {
            echo '<script>alert("먼저 로그인 해주세요."); location.href="login.php";</script>';
        }
    } ?>
</nav>
