<?php
session_start();

$response = array();

if (isset($_SESSION['current_question']) && isset($_SESSION['totalQuestions'])) {
    $response['current_question'] = $_SESSION['current_question'];
    $response['totalQuestions'] = $_SESSION['totalQuestions'];
    $response['status'] = 'success';
} else {
    $response['status'] = 'error';
    $response['message'] = '세션 데이터가 없습니다.';
}

echo json_encode($response);
?>
