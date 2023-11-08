<div class="maincontents">
        <div class="progress-container">
            <div id="progress" class="progress-bar"></div>
        </div>
        <div class="game">
            <?php            
            $currentQuestion = $_SESSION['current_question']; // 현재 문제 번호
            if ($currentQuestion < count($wordgame)) {
                // 문제를 표시하는 부분
                $english = $wordgame[$currentQuestion]['english'];
                $korean = $wordgame[$currentQuestion]['korean'];

                echo '<div class="question">' . $english . '</div>';
                echo '<div class="choices">';
                // 보기 배열을 섞음
                $choices = array($korean); // 정답을 포함한 보기 배열
                $sql = "SELECT korean FROM word WHERE korean <> '$korean' AND korean <> '' ORDER BY RAND() LIMIT 3";
                $result = $conn->query($sql);

                if ($result->num_rows > 0) {
                    while ($row = $result->fetch_assoc()) {
                        $choices[] = $row['korean']; // 다른 한국어 뜻 추가
                    }
                }

                // 보기 배열을 섞음
                shuffle($choices);

                // 보기 출력
                foreach ($choices as $choice) {
                    echo '<button type="button" onclick="checkAnswer(this)" value="' . $choice . '">' . $choice . '</button>';
                }
                echo '</div>';
            } else {
                echo '<div class="game-complete">게임이 완료되었습니다.</div>';
            }
            ?>
            <audio id="audio" controls>
                Your browser does not support the audio element.
            </audio>
            
            <?php if ($currentQuestion < count($wordgame)) { ?>
                <!-- 다음 문제 버튼에 ID 추가 -->
                <form id="nextForm" method="post" desc>
                    <button type="button" name="nextlevel" id="nextBtn" disabled>다음 문제</button>

                </form>

            <?php } ?>
        </div>
    </div>