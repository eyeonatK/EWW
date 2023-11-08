<?php
session_start();
?>
<!DOCTYPE html>
<html>
<head>
    <title>EWW - 게임</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="gamestyles.css">
    <link rel="stylesheet" href="wordgame.css">
    <style>
        .correct {
            background-color: #00FF00 !important;
            color: black !important;
        }

        .incorrect {
            background-color: #FF0000 !important;
            color: white !important;
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
    include 'C:\xampp\htdocs\wwww\topnav.php'; //네비게이션바 ?>
    <div class="game">
    <?php
    include 'game_creat.php';
    include 'game_logic.php';

    ?>
    </div>
    <script>
        <?php
            $currentQuestion = $_SESSION['current_question'];
            $totalQuestions = count($wordgame);
        ?>
        var totalQuestions = <?php echo $totalQuestions; ?>;
        var currentQuestion = <?php echo json_encode($_SESSION['current_question']); ?>;
        var correctAnswer = <?php echo json_encode($wordgame[$_SESSION['current_question']]['korean']); ?>;
        var voiceeng = "<?php echo $wordgame[$currentQuestion]['english'];?>";
    </script>
    
    <script src="gamescript.js"></script>
    
</body>
</html>