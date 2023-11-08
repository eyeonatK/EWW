<!DOCTYPE html>
<html>
<head>
    <title>EWW</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="gamestyles.css">
    <script src="https://kit.fontawesome.com/d3bd33114f.js" crossorigin="anonymous"></script>
    <style>
        .chapter a {
            display: block;
            padding: 14px 16px;
            font-size: 17px;
            text-decoration: none;
            color: #f1f1f1; 
            background-color: #631A86;
        }

        .chapter a:hover {
            color: black;
            background-color: #FFA500;
        }

        .content {
            display: flex;
            flex-wrap: wrap;
            justify-content: space-between;
        }

        .word-list {
            list-style-type: none;
            padding: 0;
        }
        .word-list li {
            padding: 5px;
        }
        .word-list li:nth-child(odd) {
            background-color: #f0f0f0;
        }
        .english, .korean {
            display: inline-block;
        }
        .hide-english .english, .hide-korean .korean {
            display: none;
        }
        .langbutton{
            text-align: center;
        }
        button {
            background-color: #30fb81;
            color: black;
            border: none;
            padding: 10px 20px;
            text-align: center;
            text-decoration: none;
            display: inline-block;
            font-size: 16px;
            margin: 4px 2px;
            transition-duration: 0.4s;
            cursor: pointer;
            border-radius: 12px;
        }
        button:hover {
            background-color: white;
            color: black;
        }
        .even{
            color: green;
        }
        .card {
            width: 100px;
            height: 150px;
            perspective: 1000px;
            margin: 10px;
            
        }
        .card-inner {
            width: 100%;
            height: 100%;
            transform-style: preserve-3d;
            transition: transform 0.5s;
            border: 1px solid #000;
            border-radius: 10px;
        }
        .card-flipped .card-inner {
            transform: rotateY(180deg); 
        }
        .card-front,
        .card-back {
        width: 100%;
        height: 100%;
        position: absolute;
        backface-visibility: hidden;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 10px;
        }

        .card-front {
        background: url('path_to_your_front_image.jpg') center/cover;
        }

        .card-back {
        background: url('path_to_your_back_image.jpg') center/cover;
        transform: rotateY(180deg);
        }
        .cardcontainer{
            display: flex;
            flex-wrap: wrap;
            margin-top: 14px;
            justify-content: center; /* 요소들을 가운데 정렬 */
            align-items: center; /* 세로 방향으로 가운데 정렬 (필요하다면) */
            background-color: white;
        }
        .play-sound {
            border: none;
            background-color: transparent;
            cursor: pointer;
            font-size: 16px;
            position: absolute;
            top: 14px;
            right: 14px;
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
    include 'topnav.php';?>
    

    <div>
        <?php
        $chapter = $_GET['chapter'];
        $start = ($chapter - 1) * 10 + 1; // 시작 단어 ID
        $end = $start + 10;
        // 문제를 생성하는 로직
        
        // 새로운 게임 시작
        $wordgame = array(); // 게임 정보를 담을 배열        
    
        // 단어를 가져와서 저장하는 로직
        $sql = "SELECT * FROM word WHERE word_id >= $start AND word_id < $end ORDER BY RAND()";
        $result = $conn->query($sql);
    
        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $word_id = $row['word_id'];
                $english = $row['english'];
                $korean = $row['korean'];
    
                // 단어 정보를 배열에 저장
                $wordgame[] = array(
                    'word_id' => $word_id,
                    'english' => $english,
                    'korean' => $korean,
                    );
            }
        }
        ?>
        <div class="chapter">
            <?php
            if ($login) {
                $chaptersql = "SELECT chapter FROM member WHERE userid = '$userid'";
                    $chapresult = $conn->query($chaptersql);
                    
                    if ($chapresult->num_rows == 1) {
                        $row = $chapresult->fetch_assoc(); // $row 변수 추가
                        $currentChapter = $row['chapter'];
                        if ($currentChapter == NULL) {
                            $currentChapter = 0;
                        }
                        // 현재 진행한 챕터를 기반으로 버튼 생성
                        
                        echo '<a href="game.php?chapter=' . $currentChapter . '&gameprogress=0"> [챕터 ' . $currentChapter . '] 테스트 보러가기</a>';
                    }else {
                        echo"단어 학습을 이용하려면 로그인 해주세요!";
                    }
            }
            ?>
        </div>
        <div class="langbutton">
            
            <button onclick="toggleLanguage('both')">둘 다 보기</button>
            <button onclick="toggleLanguage('english')">영어만 보기</button>
            <button onclick="toggleLanguage('korean')">한국어만 보기</button>
            <button onclick="toggleLanguage('hideall')">가리기</button>
            <button onclick="toggleCards()">카드 표시/숨기기</button>
        </div>  
        <div class="content">
            
            
            <?php
                    foreach ($wordgame as $index => $word) {
                        echo '<li class="' . ($index % 2 == 0 ? 'even' : 'odd') . '">';
                        echo '<span class="english">' . $word['english'] . '</span>';
                        echo ' - ';
                        echo '<span class="korean">' . $word['korean'] . '</span>';
                        echo '</li>';
                    }
                ?>
        </div>
        
    </div>

        <div class="cardcontainer">
            <?php
                    foreach ($wordgame as $index => $word) {
                        echo '<div class="card" onclick="flipCard(this)"><div class="card-inner">';
                        echo '<div class="card-front"><span class="english">' . $word['english'] . '</span>
                        <button class="play-sound" onclick="playSound(event, this)">▶️</button><audio class="word-sound">
                        <source src="voice/'.$word['english'].'.mp3" type="audio/mpeg">
                        Your browser does not support the audio element.</audio></div>';
                        echo '<div class="card-back"><span class="korean">' . $word['korean'] . '</span></div></div></div>';

                    }
                ?>
        </div>

    <script>
        function toggleLanguage(language) {
            const contentDiv = document.querySelector('.content');
            if (language === 'both') {
                contentDiv.classList.remove('hide-korean', 'hide-english');
            } else if (language === 'english') {
                contentDiv.classList.add('hide-korean');
                contentDiv.classList.remove('hide-english');
            } else if (language === 'korean') {
                contentDiv.classList.add('hide-english');
                contentDiv.classList.remove('hide-korean');
            } else if (language === 'hideall') {
                contentDiv.classList.add('hide-english');
                contentDiv.classList.add('hide-korean');
            }
        }
        function flipCard(card) {
            const cardInner = card.querySelector('.card-inner');
            cardInner.style.transform = cardInner.style.transform === 'rotateY(180deg)' ? '' : 'rotateY(180deg)';
        }
        function toggleCards() {
            const cardContainer = document.querySelector('.cardcontainer');
            cardContainer.style.display = cardContainer.style.display === 'none' ? '' : 'none';
        }
        function playSound(event, button) {
            event.stopPropagation();
            const audio = button.nextElementSibling;
            audio.play();
        }

    </script>
</body>
</html>
