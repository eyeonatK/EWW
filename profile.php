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
        .chart-container {
            position: relative;
            width: 70%;
            margin: auto;
        }
        .chart-navigation {
            text-align: center;
            margin-top: 10px;
        }
        .chart-navigation button {
            padding: 5px 10px;
            margin: 0 5px;
            border: none;
            background-color: #4CAF50;
            color: white;
            cursor: pointer;
        }
        .chart-navigation button:disabled {
            background-color: #ccc;
            cursor: not-allowed;
        }
        .procon {
            background-color: #fff;
            padding: 40px; /* 패딩을 늘림 */
            border-radius: 8px;
            box-shadow: 0px 0px 10px rgba(0, 0, 0, 0.1);
            text-align: center;
            margin-top: 60px; /* topnav와의 간격 조정 */
        }
        .protext {
            text-align: left;
        }
        .goalbutton button{
            padding: 5px 10px;
            margin: 0 5px;

            background-color: black;
            color: white;
            cursor: pointer;
        }
        .hbut a {
            flex-grow: 1;
            float: left;
            display: block;
            color: black;
            text-align: center;
            padding: 14px 16px;
            text-decoration: none;
            font-size: 17px;
        }

        .hbut a:hover {
            background-color: #00ff33;
            color: black;
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
    <div class="procon">
    <div class="protext">
    <?php
    // 목표치 설정 폼 제출 처리
    if (isset($_POST['set_goal'])) {
        $goal = intval($_POST['goal']);
        if ($goal > 0) {
            $updateGoalSql = "UPDATE member SET goal = '$goal' WHERE userid = '$userid'";
            if ($conn->query($updateGoalSql) === TRUE) {
                echo "<p>목표치가 성공적으로 설정되었습니다! 현재 목표치 ". $goal." </p>";
            } else {
                echo "<p>목표치 설정 중 오류가 발생했습니다: " . $conn->error . "</p>";
            }
        } else {
            echo "<p>목표치는 1 이상이어야 합니다.</p>";
        }
    }
    ?>
    <div class="goalbutton">
        <form method="post" action="">
            <label>하루에 클리어할 챕터 수: </label>
            <input type="number" name="goal" required>
            
                <button type="submit" name="set_goal">목표치 설정</button>
            
        </form>
    </div>
    <?php
        // member 테이블에서 signdate 가져오기
        $signDateSql = "SELECT signdate FROM member WHERE userid = '$userid'";
        $signDateResult = $conn->query($signDateSql);
        if ($signDateResult->num_rows > 0) {
            $row = $signDateResult->fetch_assoc();
            $signDate = new DateTime($row['signdate']);
            $currentDate = new DateTime();
            
            // 계정을 만든 날짜부터 현재까지의 일수 계산
            $daysSinceSignUp = $signDate->diff($currentDate)->days;
            
            // 결과 출력
            echo "<p>계정을 만든 지 " . $daysSinceSignUp . "일 째 공부 중입니다!</p>";
        } else {
            echo "<p>계정 정보를 가져오는데 문제가 발생했습니다.</p>";
        }
        
    ?>
    <?php
        $memhisSql = "SELECT * FROM memhis WHERE userid = '$userid' ORDER BY today DESC";
        $memhisResult = $conn->query($memhisSql);
        if ($memhisResult->num_rows > 0) {
            $continuousDays = 1; // 연속 학습일
            $goalAchievementDays = 0; // 목표 달성 연속일
            $previousDate = new DateTime(); // 오늘 날짜
            $firstRow = true;
            $goalSql = "SELECT goal FROM member WHERE userid = '$userid'";
            $goalResult = $conn->query($goalSql);
            $goalRow = $goalResult->fetch_assoc();
            $goal = $goalRow['goal'];

            while ($row = $memhisResult->fetch_assoc()) {
                $today = new DateTime($row['today']);
                $chapno = $row['chapno'];

                if ($firstRow) {
                    $firstRow = false;
                } else {
                    $interval = $previousDate->diff($today)->format('%a');
                    if ($interval == 1) {
                        $continuousDays++;
                    } else {
                        break;
                    }
                }
                if ($chapno >= $goal) {
                    $goalAchievementDays++;
                }
                $previousDate = $today;
            }

            echo "현재 " . $continuousDays . "일 연속 학습 중입니다!<br>";
            
            echo "현재 " . $goalAchievementDays . "일 연속 목표 달성 중입니다!<br>";
            
        } else {
            echo "클리어한 학습 데이터가 없습니다";
        }
    ?>
    <div class="hbut">
        <a href="stats.php">통계보기</a>
    </div>
</div>
<div class="chart-container" style="width: 70%; margin: auto;">
        <canvas id="myChart"></canvas>
        <div class="chart-navigation" style="text-align: center; margin-top: 10px;">
            <button onclick="updateChart(-7)">이전 주</button>
            <button onclick="updateChart(7)">다음 주</button>
        </div>
    </div>

</div>
<?php
if (!isset($_SESSION['today'])) {
    $_SESSION['today'] = date('Y-m-d');
}
?>
<script>
        var myChart;
        $(document).ready(function() {
            updateChart(0);
        });
        function updateChart(offset) {
            $.ajax({
                url: 'update_chart_data.php',
                type: 'POST',
                data: { offset: offset },
                dataType: 'json',
                success: function(data) {
                    if (!myChart) {
                        var ctx = document.getElementById('myChart').getContext('2d');
                        myChart = new Chart(ctx, {
                            type: 'bar',
                            data: {
                                labels: ['일', '월', '화', '수', '목', '금', '토'],
                                datasets: [{
                                    label: '학습 챕터수',
                                    data: data,
                                    backgroundColor: 'rgba(255, 99, 132, 0.2)',
                                    borderColor: 'rgba(255, 99, 132, 1)',
                                    borderWidth: 1
                                }]
                            },
                            options: {
                                scales: {
                                    y: {
                                        beginAtZero: true,
                                        ticks: {
                                            stepSize: 1
                                        }
                                    }
                                }
                            }
                        });
                    } else {
                        myChart.data.datasets[0].data = data;
                        myChart.update();
                    }
                },
                error: function(xhr, status, error) {
                    console.error(error);
                }
            });
        }
    </script>
</body>

