<?php
header('Content-Type: text/html; charset=utf-8');
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
$db = mysqli_connect('localhost', 'jack', 'a1s2d3f4!', 'jack');
mysqli_query($db, "set names utf8");

$userid = $_POST['userid'];
$password = $_POST['password'];
try {
    $sql = "SELECT userid, password, name FROM UAS_sign WHERE userid = ?";
    $stmt = mysqli_prepare($db, $sql);
    mysqli_stmt_bind_param($stmt, "s", $userid);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $user = mysqli_fetch_assoc($result);

    if ($user) {   
        if (password_verify($password, $user['password'])) {
            session_start();
            $_SESSION['userid'] = $user['userid'];
            $_SESSION['name'] = $user['name'];
            echo "<script>
                    alert('" . $user['name'] . "님, 솔로나라에 오신 것을 환영합니다! ❤️');
                    location.href = './main2.html'; 
                  </script>";
        } else {         
            echo "<script>
                    alert('비밀번호가 일치하지 않습니다.');
                    history.back();
                  </script>";
        }
    } else {
        echo "<script>
                alert('존재하지 않는 아이디입니다. 회원가입을 먼저 해주세요.');
                location.href = './sign_in.html';
              </script>";
    }
} catch (mysqli_sql_exception $e) {
    echo "오류 발생: " . $e->getMessage();
}

mysqli_stmt_close($stmt);
mysqli_close($db);
?>