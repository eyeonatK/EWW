<?php
session_start();
?>
<!DOCTYPE html>
<html>
<head>
    <title>사전</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="gamestyles.css">
    <style>
        /* 텍스트 박스 스타일 */
        input[type="text"] {
            width: 300px;  /* 너비 설정 */
            height: 40px;  /* 높이 설정 */
            font-size: 18px;  /* 글자 크기 설정 */
            padding: 5px 10px;  /* 내부 여백 설정 */
            margin: 10px 0;  /* 외부 여백 설정 */
        }

        /* 버튼 스타일 */
        input[type="submit"] {
            width: 100px;  /* 너비 설정 */
            height: 45px;  /* 높이 설정 */
            font-size: 18px;  /* 글자 크기 설정 */
            background-color: #4CAF50;  /* 배경색 설정 */
            color: white;  /* 글자색 설정 */
            border: none;  /* 테두리 제거 */
            cursor: pointer;  /* 마우스 커서를 손가락 모양으로 변경 */
            margin: 10px 0;  /* 외부 여백 설정 */
        }

        /* 검색 결과를 왼쪽 정렬로 표시 */
        .dictionary {
            text-align: left;
        }
        .meaning {
            font-size: 20px;
            font-weight: bold;
            margin-top: 20px;
            color:#4CAF50;
        }
        .example {
            font-size: 16px;
            font-style: italic;
            margin-left: 10px;
            color: black;
        }
        .pos {
            font-size: 18px;
            color: #008080;
        }
    </style>
</head>
<body>
<?php 
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
    include 'C:\xampp\htdocs\wwww\topnav.php'; //네비게이션바 
?>
<?php
include_once('dbconn.php');

if (isset($_GET['word'])) {
    $word = $_GET['word'];
    $stmt = $conn->prepare("SELECT word, meaning, example, pos, pro FROM dictionary WHERE word LIKE ?");
    $stmt->bind_param("s", $word);
    $stmt->execute();
    $result = $stmt->get_result();
    $results = [];
    while ($row = $result->fetch_assoc()) {
        $results[] = $row;
    }
}
?>
<div class="dictionary">
<form action="" method="get">
    <input type="text" name="word" placeholder="단어를 입력하세요">
    <input type="submit" value="검색">
</form>

<?php
if (isset($results) && count($results) > 0) {
    echo "<h2>" . $results[0]['word'] . " (" . $results[0]['pro'] . ")</h2>";
    foreach ($results as $row) {
        echo "<div class='meaning'>";
        if ($row['pos']) {
            echo "<span class='pos'>" . $row['pos'] . "</span> ";
        }
        echo $row['meaning'];
        if ($row['example']) {
            echo "<div class='example'>" . $row['example'] . "</div>";
        }
        echo "</div>";
    }
} elseif (isset($_POST['word'])) {
    echo "단어를 찾을 수 없습니다.";
}
?>
</div>
</body>
</html>
