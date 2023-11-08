<?php


session_start();
include_once('dbconn.php'); // 데이터베이스 연결 설정 파일 포함

if (isset($_POST['offset'])) {
    $offset = intval($_POST['offset']);
    $currentDate = new DateTime($_SESSION['today']);
    $currentDate->modify("$offset days");
    $_SESSION['today'] = $currentDate->format('Y-m-d');

    $userid = $_SESSION['userid']; // 사용자 ID 가져오기

    // 데이터베이스에서 데이터 조회
    $dayOfWeek = $currentDate->format('w'); // 0 (일요일) ~ 6 (토요일)
    $firstDayOfWeek = clone $currentDate;
    $firstDayOfWeek->sub(new DateInterval('P' . $dayOfWeek . 'D'));

    // 일주일 간의 데이터 가져오기
    $weekData = array();
    for ($i = 0; $i < 7; $i++) {
        $date = clone $firstDayOfWeek;
        $date->add(new DateInterval('P' . $i . 'D'));
        $formattedDate = $date->format('Y-m-d');

        $sql = "SELECT chapno FROM memhis WHERE userid = '$userid' AND today = '$formattedDate'";
        $result = $conn->query($sql);
        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            $weekData[$i] = $row['chapno'];
        } else {
            $weekData[$i] = 0;
        }
    }

    // 데이터를 JSON 형식으로 반환
    echo json_encode($weekData);
    exit;
}
?>
