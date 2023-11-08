<!DOCTYPE html>
<html>
    <head>
        <title>EWW</title>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <link rel="stylesheet" href="gamestyles.css">
        <script src="https://kit.fontawesome.com/d3bd33114f.js" crossorigin="anonymous"></script>
        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
        <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
        <style>


            .chapter-progress {
                padding: 14px;
                background-color: #ccc; /* 회색 */
                cursor: not-allowed; /* 선택할 수 없음을 나타내는 커서 */
            }

            .chapter.completed {
                background-color: #FFC803; /* 완료된 챕터의 배경색 */
                cursor: not-allowed; /* 선택할 수 없음을 나타내는 커서 */
            }
            
            .chapter.active a{
                background-color: #631A86; /* 현재 진행 중인 챕터의 배경색 */
                cursor: pointer; /* 선택할 수 있음을 나타내는 커서 */
            }
            .chapter.active a:hover {
                color: black;
                background-color: #FFA500;
            }
            .chapter.completed a{
                background: #695FD2;
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
            include 'topnav.php';
        ?>
        <div>
            <div class="chapter">
                <?php
                if ($login) {
                    $sql = "SELECT chapter FROM member WHERE userid = '$userid'";
                        $result = $conn->query($sql);
                        
                        if ($result->num_rows == 1) {
                            $row = $result->fetch_assoc(); // $row 변수 추가
                            $currentChapter = $row['chapter'];
                            if ($currentChapter == NULL) {
                                $currentChapter = 1;
                            }
                            // 현재 진행한 챕터를 기반으로 버튼 생성
                            if ($currentChapter == 1){
                                echo '<div class="chapter active"><a href="chapterstudy.php?chapter=' . $currentChapter . '"> [챕터 ' . $currentChapter . '] 공부하기</a></div>';
                            }else{?>
                                <div class="chapter-progress"><?php
                                    echo '[챕터 ' . ($currentChapter + 1) . '] NEXT';
                                ?>
                                </div>
                                <div class="chapter active"><?php
                                    echo '<a href="chapterstudy.php?chapter=' . $currentChapter . '"> [챕터 ' . $currentChapter . '] 공부하기</a>';
                                ?>
                                </div>
                                <?php
                                for ($i = $currentChapter - 1; $i > 0; $i--) {
                                    ?>
                                    <div class="chapter completed">
                                        [챕터 <?php echo $i; ?>] CLEAR!
                                        <a href="studyreview.php?chapter=<?php echo $i; ?>" class="review-button">복습하기</a>
                                    </div>
                                    <?php
                                }
                            }
                            
                        }else {
                            echo"단어 학습을 이용하려면 로그인 해주세요!";
                        }
                }
                ?>
            </div>
        </div>
    </body>
</html>
