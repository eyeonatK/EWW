<?php
session_start();
include_once('dbconn.php');
date_default_timezone_set("Asia/Seoul");
if (isset($_POST['isCorrect'])) {
    $isCorrect = $_POST['isCorrect'] === 'true' ? 1 : 0;
    $currentQuestion = $_SESSION['current_question'];
    $chapter = $_SESSION['chapter'];
    $wordgame = $_SESSION['wordgame'];
    $word_id = $wordgame[$currentQuestion]['word_id'];
    $userid = $_SESSION['userid'];
    $last_studied = date('Y-m-d H:i:s');

    // DB에서 해당 단어의 통계 데이터 가져오기
    $stmt = $conn->prepare("SELECT * FROM gamestats WHERE userid = ? AND word_id = ? AND chapter = ?");
    $stmt->bind_param("sii", $userid, $word_id, $chapter);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows == 0) {
        // 통계 데이터가 없는 경우 새로 생성
        $stmt = $conn->prepare("INSERT INTO gamestats (userid, word_id, correct, incorrect, last_studied, chapter) VALUES (?, ?, ?, ?, ?, ?)");
        $incorrect = $isCorrect ? 0 : 1;
        $stmt->bind_param("siiisi", $userid, $word_id, $isCorrect, $incorrect, $last_studied, $chapter);
        if (!$stmt->execute()) {
            echo 'error: ' . $stmt->error;
            exit();
        }
    } else {
        // 통계 데이터 업데이트
        $updateField = $isCorrect ? 'correct' : 'incorrect';
        $stmt = $conn->prepare("UPDATE gamestats SET $updateField = $updateField + 1, last_studied = ? WHERE userid = ? AND word_id = ? AND chapter = ?");
        $stmt->bind_param("ssii", $last_studied, $userid, $word_id, $chapter);
        if (!$stmt->execute()) {
            echo 'error: ' . $stmt->error;
            exit();
        }
    }
    if (!$isCorrect){
        $_SESSION['wordgame'][] = $_SESSION['wordgame'][$currentQuestion];
        $_SESSION['totalQuestions']+= 1;
    }
    echo 'success';
    $_SESSION['current_question']+= 1;
} else {
    echo 'error';
}
?>
