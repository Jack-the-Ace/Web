<?php
// 한글 깨짐 방지 및 에러 모드 설정
header('Content-Type: text/html; charset=utf-8');
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

// 1. DB 연결 (본인의 닷홈 DB 정보로 채우기)
$db=mysqli_connect('localhost','jack','a1s2d3f4!','jack');
mysqli_query($db, "set names utf8");
// $conn --> $db 로 하기!

// 2. POST 데이터 가져오기
$userid = $_POST['userid'];
$password = $_POST['password'];
$name = $_POST['name'];
$gender = $_POST['gender'];

// 3. 비밀번호 안전하게 해싱(암호화)하기
// 사용자 비번이 1234여도 약 60글자의 랜덤 텍스트로 안전하게 바뀝니다.
$hashed_password = password_hash($password, PASSWORD_DEFAULT);

try {
    // 4. SQL 질의문 작성 (안전한 Prepared Statement 방식 사용)
    $sql = "INSERT INTO member (userid, password, name, gender) VALUES (?, ?, ?, ?)";
    $stmt = mysqli_prepare($conn, $sql);
    
    // 파라미터 바인딩 (s = string 총 4개)
    mysqli_stmt_bind_param($stmt, "ssss", $userid, $hashed_password, $name, $gender);
    
    // 실행
    mysqli_stmt_execute($stmt);
    
    // 5. 성공 시 메시지 띄우고 로그인 화면으로 이동
    echo "<script>
            alert('솔로나라에 정상적으로 가입되었습니다! 로그인해 주세요.');
            location.href = '../log_in.html';
          </script>";

} catch (mysqli_sql_exception $e) {
    // 중복된 아이디 에러 등이 터졌을 때 예외 처리
    if ($e->getCode() == 1062) {
        echo "<script>
                alert('이미 사용 중인 아이디입니다.');
                history.back();
              </script>";
    } else {
        echo "오류 발생: " . $e->getMessage();
    }
}

// 연결 종료
mysqli_stmt_close($stmt);
mysqli_close($conn);
?>