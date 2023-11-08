<!DOCTYPE html>
<html>
<head>
    <title>EWW</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="gamestyles.css">
    <script src="https://kit.fontawesome.com/d3bd33114f.js" crossorigin="anonymous"></script>
    <style>
        .content {
            display: flex;
            justify-content: space-between;
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
            background: white;
            
        }
        .card.incorrect .card-inner {
            background: white;
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
            border: 1px solid #000;
        }
        .card-back {
            transform: rotateY(180deg);
        }
        .card.incorrect .card-front,
        .card.incorrect .card-back {
            background: red;
            color: white; /* 또는 원하는 색상 코드 */
        }
        .cardcontainer{
            margin-top: 15px;
            display: flex;
            flex-wrap: wrap;
            justify-content: center; /* 요소들을 가운데 정렬 */
            align-items: center; /* 세로 방향으로 가운데 정렬 (필요하다면) */
            background: white;
        }
        .content{
            flex-wrap: wrap;
            
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
        $wordreview = array();

        // gamestats 테이블에서 $userid와 $chapter로 검색
        $sql = "SELECT gs.word_id, gs.incorrect, w.english, w.korean
        FROM gamestats gs
        INNER JOIN word w ON gs.word_id = w.word_id
        WHERE gs.userid = ? AND gs.chapter = ?";

        $stmt = $conn->prepare($sql);
        $stmt->bind_param("si", $userid, $chapter);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $word_id = $row['word_id'];
                $incorrect = $row['incorrect'];
                $english = $row['english'];
                $korean = $row['korean'];

                $wordreview[] = array(
                    'word_id' => $word_id,
                    'incorrect' =>$incorrect,
                    'english' => $english,
                    'korean' => $korean 
                );
            }
        }
        ?>
    </div>

        <div class="cardcontainer">
        <?php
        foreach ($wordreview as $index => $word) {
            $cardClass = $word['incorrect'] > 0 ? 'card incorrect' : 'card';
            echo '<div class="' . $cardClass . '" onclick="flipCard(this)"><div class="card-inner">';
            echo '<div class="card-front"><span class="english">' . $word['english'] . '</span>
            <button class="play-sound" onclick="playSound(event, this)">▶️</button><audio class="word-sound">
            <source src="voice/'.$word['english'].'.mp3" type="audio/mpeg">
            Your browser does not support the audio element.
            </audio></div>';
            echo '<div class="card-back"><span class="korean">' . $word['korean'] . '</span></div></div></div>';
        }
        ?>
        </div>


    <script>
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
