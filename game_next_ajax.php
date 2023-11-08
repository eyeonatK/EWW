<?php
session_start();
include_once('dbconn.php');
$response = array();
$totalQuestions = $_SESSION['totalQuestions'];
$chapter = $_SESSION['chapter'];
$uname = $_SESSION['username']; 
$userid= $_SESSION['userid'];

if (isset($_POST['nextlevel'])) {
    if ($_SESSION['current_question'] == $totalQuestions) {
        $response['status'] = 'game_complete';
        
        $userid = $_SESSION['userid'];
        $sql = "SELECT chapter, goal FROM member WHERE userid = '$userid'";
        $result = $conn->query($sql);
        if ($result->num_rows == 1) {
            $row = $result->fetch_assoc();
            $currentChapter = $row['chapter'];
            $currentGoal = $row['goal'];
            // chapter 정보가 NULL인 경우 1으로 초기화
            if ($currentChapter == NULL) {
                $currentChapter = 1;
            }

            if ($currentChapter == $chapter) {
                $updatedChapter = $currentChapter + 1;
                $updateSql = "UPDATE member SET chapter = '$updatedChapter' WHERE userid = '$userid'";
                $conn->query($updateSql);
                $response['message'] = $uname . '님 ' . $chapter . 'chapter를 완료했습니다!';

                // memhis 테이블 업데이트
                $today = date('Y-m-d');
                $memhisSql = "SELECT * FROM memhis WHERE userid = '$userid' AND today = '$today'";
                $memhisResult = $conn->query($memhisSql);
                if ($memhisResult->num_rows > 0) {
                    // 오늘 날짜의 레코드가 이미 있으면 chapno에 1을 더함
                    $memhisRow = $memhisResult->fetch_assoc();
                    $chapno = $memhisRow['chapno'] + 1;
                    $updatememhisSql = "UPDATE memhis SET chapno = '$chapno' WHERE userid = '$userid' AND today = '$today'";
                    $conn->query($updatememhisSql);
                } else {
                    // 오늘 날짜의 레코드가 없으면 새 레코드 생성
                    $insertmemhisSql = "INSERT INTO memhis (userid, today, chapno) VALUES ('$userid', '$today', 1)";
                    $conn->query($insertmemhisSql);
                    $chapno = 1;
                }
                if ($currentGoal = $chapno){
                    $response['message'] .= ' 대단해요! 오늘의 목표치를 완료하셨습니다!';
                }
            } else {
                // 이미 완료한 챕터를 다시 완료한 경우
                $response['message'] = $uname . '님은 이미 ' . $chapter . 'chapter를 완료했습니다.';
            }
            echo json_encode($response);
            exit();
        }
    } else {
    $response['status'] = 'next_question';
    $response['gameprogress'] = $_SESSION['current_question'];
    
    // 문제 생성 로직
    $wordgame = $_SESSION['wordgame'];
    $currentWord = $wordgame[$_SESSION['current_question']];
    $response['current_word'] = $currentWord;

    // 선택지 생성 로직
    $korean = $currentWord['korean'];
    $choices = array($korean);
    $sql = "SELECT korean FROM word WHERE korean <> '$korean' AND korean <> '' ORDER BY RAND() LIMIT 3";
    $result = $conn->query($sql);
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $choices[] = $row['korean'];
        }
    }
    shuffle($choices);
    $response['choices'] = $choices;
}
} else {
    $response['status'] = 'error';
    $response['message'] = 'Invalid request';
}
echo json_encode($response);
?>
