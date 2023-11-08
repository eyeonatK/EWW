<!DOCTYPE html>
<html>
<head>
    <title>학습 통계</title>
    <!-- 여기서부터 -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="gamestyles.css">
    <style>
        table {
            border-collapse: separate;
            border-spacing: 10px; /* 원하는 간격으로 조절하세요 */
        }

        th, td {
            padding: 10px; /* 원하는 간격으로 조절하세요 */
        }
        th:hover{
            background-color: #00ff33;
            color: black;
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
    ?>

    <div class="topnav">
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
        <?php } else { 
            echo '<script>alert("먼저 로그인 해주세요."); location.href="login.php";</script>';
        } ?>
    </div>
    <!-- 요기까지 top -->
    <div class="content">
    <?php

include_once('dbconn.php');
if (isset($_SESSION['userid'])) {
    $userid = $_SESSION['userid'];

    // gamestats 테이블에서 현재 로그인한 사용자의 통계 데이터를 가져옴
    // 동일한 단어에 대한 데이터를 그룹화하고, 정답과 오답의 합계를 구함
    $sql = "SELECT SUM(gs.correct) AS total_correct, SUM(gs.incorrect) AS total_incorrect, MAX(gs.last_studied) AS last_studied, w.english, w.korean
        FROM gamestats gs
        INNER JOIN word w ON gs.word_id = w.word_id
        WHERE gs.userid = ?
        GROUP BY w.word_id
        ORDER BY last_studied DESC";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $userid);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        echo '<table>
                <thead>
                    <tr>
                        <th>영어</th>
                        <th>한국어</th>
                        <th>정답 수</th>
                        <th>오답 수</th>
                        <th>정답률</th>
                        <th>마지막 공부 시간</th>
                    </tr>
                </thead>
                <tbody>';
        
        while ($row = $result->fetch_assoc()) {
            $english = $row['english'];
            $korean = $row['korean'];
            $totalCorrect = $row['total_correct'];
            $totalIncorrect = $row['total_incorrect'];
            $lastStudied = $row['last_studied'];

            // 정답률 계산
            $correctRatio = ($totalCorrect + $totalIncorrect) > 0 ? round(($totalCorrect / ($totalCorrect + $totalIncorrect)) * 100, 2) : 0;

            echo '<tr>
                    <td>' . $english . '</td>
                    <td>' . $korean . '</td>
                    <td>' . $totalCorrect . '</td>
                    <td>' . $totalIncorrect . '</td>
                    <td>' . $correctRatio . '%</td>
                    <td>' . $lastStudied . '</td>
                </tr>';
        }

        echo '</tbody></table>';
    } else {
        echo '통계 데이터가 없습니다.';
    }
} else {
    echo '로그인되어 있지 않습니다.';
}

?></div>
    </body>
    <script>
    document.addEventListener('DOMContentLoaded', function () {
        const getCellValue = (tr, idx) => tr.children[idx].innerText || tr.children[idx].textContent;

        const comparer = (idx, asc) => (a, b) => ((v1, v2) => {
            // 정답률 열인 경우 "%" 문자를 제거하고 숫자로 변환
            if (idx === 4) {
                v1 = v1.replace('%', '');
                v2 = v2.replace('%', '');
            }
            return v1 !== '' && v2 !== '' && !isNaN(v1) && !isNaN(v2) ? v1 - v2 : v1.toString().localeCompare(v2);
        })(getCellValue(asc ? a : b, idx), getCellValue(asc ? b : a, idx));

        document.querySelectorAll('th').forEach(th => th.addEventListener('click', (() => {
            const table = th.closest('table');
            const tbody = table.querySelector('tbody');
            Array.from(tbody.querySelectorAll('tr'))
                .sort(comparer(Array.from(th.parentNode.children).indexOf(th), this.asc = !this.asc))
                .forEach(tr => tbody.appendChild(tr) );
        })));
    });
</script>


    </html>