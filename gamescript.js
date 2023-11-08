
        window.onload = function() {
            // 페이지 로딩 시 다음 문제 버튼을 비활성화
            document.getElementById("nextBtn").disabled = true;
            setAudioSource();
        }
        function checkAnswer(button) {
            var selectedAnswer = button.value;
            console.log("선택된 답:", selectedAnswer);
            console.log("정답:", correctAnswer);
            var choices = document.querySelectorAll('.choices button');
            // 정답인지 오답인지 확인
            var isCorrect = (selectedAnswer === correctAnswer);
            
            // 정답/오답 표시
            if (isCorrect) {
                button.classList.add('correct');
                // 서버로 정답 데이터 전송
                updateStatistics(true);
            } else {
                button.classList.add('incorrect');
                // 옳은 답을 표시
                for (var i = 0; i < choices.length; i++) {
                    if (choices[i].value === correctAnswer) {
                        choices[i].classList.add('correct');
                        break;
                    }
                }
                // 서버로 오답 데이터 전송
                updateStatistics(false);
            }
            // 모든 버튼 비활성화
            for (var i = 0; i < choices.length; i++) {
                choices[i].disabled = true;
            }
            updateProgressFromServer();
            // 정답을 선택한 후, 다음 문제 버튼 활성화
            document.getElementById("nextBtn").disabled = false;
        }   

        function updateStatistics(isCorrect) {
            var xhr = new XMLHttpRequest();
            xhr.open('POST', 'updategamestats.php', true);
            xhr.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');
            xhr.onreadystatechange = function() {
                if (xhr.readyState === 4 && xhr.status === 200) {
                    if (xhr.responseText === 'success') {
                        // 통계 업데이트 성공한 경우에 대한 처리
                        console.log('통계 업데이트 성공');
                        updateProgressFromServer();
                    } else {
                        // 통계 업데이트 실패한 경우에 대한 처리
                        console.log('통계 업데이트 실패');
                    }
                }
            };
            xhr.send('isCorrect=' + (isCorrect ? 'true' : 'false'));
        }

        function setAudioSource() {
            var audio = document.getElementById('audio');
            audio.src = 'http://localhost/wwww/voice/' + voiceeng + '.mp3';
        }
        

        
        // 진행바 업데이트 함수 호출
        updateProgressBar(currentQuestion, totalQuestions);

        function updateProgressBar(current, total) {
            // 진행바 업데이트
            var progress = document.getElementById("progress");
            var percentage = (current / total) * 100;
            progress.style.width = percentage + "%";
            progress.innerHTML = Math.round(percentage) + "%";
        }
        function updateProgressFromServer() {
            fetch('get_progress.php')
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    var currentQuestion = data.current_question;
                    var totalQuestions = data.totalQuestions;
                    updateProgressBar(currentQuestion, totalQuestions);
                } else {
                    console.error(data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
            });
        }

        //문제로
        document.getElementById("nextBtn").addEventListener("click", function(event) {
            event.preventDefault();
            fetch('game_next_ajax.php', {
                method: 'POST',
                body: new URLSearchParams({
                    'nextlevel': true
                }),
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded'
                }
            })
            .then(response => {
                // 응답의 상태 코드와 본문을 콘솔에 출력
                console.log("응답 상태:", response.status);
                return response.text().then(text => {
                    console.log("응답 본문:", text);
                    return JSON.parse(text);  // 본문을 JSON으로 파싱
                });
            })
            .then(data => {
                if (data.status === 'next_question') {
                    // 문제 업데이트 로직
                    var currentWord = data.current_word;
                    document.querySelector('.question').textContent = currentWord.english;
                    voiceeng = currentWord.english;
                    // 선택지 업데이트 로직
                    var choicesDiv = document.querySelector('.choices');
                    choicesDiv.innerHTML = ''; // 기존 선택지 삭제
                    data.choices.forEach(function(choice) {
                        var btn = document.createElement('button');
                        btn.type = 'button';
                        btn.onclick = function() { checkAnswer(this); };
                        btn.value = choice;
                        btn.textContent = choice;
                        choicesDiv.appendChild(btn);
                    });
                    // 정답 업데이트
                    correctAnswer = currentWord.korean;
                    // 다음 문제 버튼 비활성화
                    document.getElementById("nextBtn").disabled = true;
                    setAudioSource();
                    // 선택지 버튼 활성화
                    var choicesButtons = document.querySelectorAll('.choices button');
                    for (var i = 0; i < choicesButtons.length; i++) {
                        choicesButtons[i].disabled = false;
                    }
                } else if (data.status === 'game_complete') {
                    // 게임 완료 로직
                    alert(data.message);
                    window.location.href = 'study.php'; // 페이지 리디렉션
                }                
            })
            .catch(error => {
                console.error('Error:', error);
            });
        });
        
        
        
        