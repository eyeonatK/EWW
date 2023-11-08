<div class="creatQ">
        <?php
        // 받은 챕터 정보로 10개의 단어를 배열에 저장
        $_SESSION['chapter'] = isset($_GET['chapter']) ? $_GET['chapter'] : (isset($_SESSION['chapter']) ? $_SESSION['chapter'] : 1);

        $chapter=$_SESSION['chapter'];
        $gameprogress = isset($_GET['gameprogress']) ? $_GET['gameprogress'] : -1;
        $start = ($chapter - 1) * 10 + 1; // 시작 단어 ID
        $end = $start + 10;
        // 문제를 생성하는 로직
        if ($gameprogress == 0 ) {
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
            if ($chapter != 1) {
                $sql = "SELECT w.*, 
                        SUM(g.correct) AS total_correct, 
                        SUM(g.incorrect) AS total_incorrect,
                        (SUM(g.incorrect) / (SUM(g.correct) + SUM(g.incorrect))) AS error_rate
                        FROM word w
                        JOIN gamestats g ON w.word_id = g.word_id
                        WHERE g.userid = '$userid'
                        GROUP BY w.word_id
                        ORDER BY error_rate DESC, total_incorrect DESC
                        LIMIT 10;"; // word_id로 묶어서 오답률이 높은 단어 5개를 선택
                $result = $conn->query($sql);
            
                if ($result->num_rows > 0) {
                    while ($row = $result->fetch_assoc()) {
                        $word_id = $row['word_id'];
                        $english = $row['english'];
                        $korean = $row['korean'];
            
                        // 단어 정보를 배열에 추가
                        $wordgame[] = array(
                            'word_id' => $word_id,
                            'english' => $english,
                            'korean' => $korean,
                            
                        );
                    }
                }
            }

            // 게임 진행 상태를 세션에 저장
            $_SESSION['wordgame'] = $wordgame;
            $_SESSION['totalQuestions'] = count($wordgame);
            $_SESSION['current_question'] = 0;
        } else {
            // 기존 게임 재개
            $wordgame = $_SESSION['wordgame'];
        }
        ?>
    </div>