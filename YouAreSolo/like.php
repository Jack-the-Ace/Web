<?php
header("Content-Type:text/html; charset=utf-8");
$db = mysqli_connect('localhost', 'jack', 'a1s2d3f4!', 'jack');
mysqli_query($db, "set names utf8");
?>
<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>로그 - 너는솔로</title>
    <style>
        body {
            background-color: #f4f9fc; font-family: 'Pretendard', -apple-system, BlinkMacSystemFont, system-ui, Roboto, sans-serif;
            color: #2c3e50; margin: 0; padding: 20px 0; display: flex; justify-content: center; align-items: flex-start; 
            min-height: 100vh; overflow-x: hidden; box-sizing: border-box;
        }
        #wrap {
            max-width: 450px; width: 100%; padding: 24px 20px; box-sizing: border-box; background: #d9edf7; 
            border: 1px solid #e1eef6; border-radius: 24px;  box-shadow: 0 8px 24px rgba(163, 197, 222, 0.2);
            display: flex;  flex-direction: column;  gap: 15px;
        }
        hr {
            width: 100%; border: 0; height: 1px; margin: 10px 0;
            background: linear-gradient(to right, transparent, #bedaf3, transparent);
        }
        .log_card {
            background: #f8fbfd; border: 1px solid #edf4f8; border-radius: 16px; padding: 16px; margin-bottom: 5px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.02);}
        .log_card h6 { margin: 0 0 5px 0; color: #7f8c8d; font-size: 0.8rem; }
        .log_card h4 { margin: 5px 0; color: #e74c3c; font-size: 1.15rem; font-weight: 700; }
        .log_card h5 { margin: 8px 0; color: #34495e; font-size: 0.95rem; font-weight: 600; }
        .log_card p { margin: 10px 0; color: #2c3e50; font-size: 0.95rem; line-height: 1.5; background: #fff; padding: 10px; border-radius: 8px; border: 1px solid #eef2f5; }
        .log_card .log_date { margin: 5px 0 0 0; color: #95a5a6; font-size: 0.8rem; text-align: right; }
        .log_card img { border-radius: 12px; margin-top: 10px; max-width: 100%; height: auto; object-fit: cover; }

        h2 { text-align: center; font-size: 1.4rem; font-weight: 700; color: #3a8bcd; margin: 10px 0; }
        .success_box { display: flex; flex-direction: column; align-items: center; justify-content: center; padding: 15px; background-color: #d9edf7; border-radius: 16px; width: 100%; box-sizing: border-box; }
    </style>
</head>
<body>
    <div id="wrap">
<?php
echo "<div class='success_box'>";
echo "    <a href='./main2.html' style='text-decoration: none; display: flex; flex-direction: column; align-items: center; gap: 8px;' title='메인페이지로 이동'>";
echo "        <img src='./boys/logo.png' alt='로고' style='max-height: 120px; width: auto; object-fit: contain;'>";
echo "        <span style='font-size: 1rem; color: #ff2233; font-weight: 700; text-decoration: underline;'>메인화면으로 가기</span>";
echo "    </a>";
echo "</div>";

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['marry'])) {
    $marry = $_POST['marry'];
    $msg = $_POST['msg'];
    $target_name = isset($_POST['target_name']) ? $_POST['target_name'] : '상대방'; 
    $dst_name = "";
    
    if (isset($_FILES['img']) && $_FILES['img']['error'] === UPLOAD_ERR_OK) {
        $file = $_FILES['img'];
        $file_name = $file['name'];
        $temp_name = $file['tmp_name'];

        if (!is_dir("./saved")) {
            mkdir("./saved", 0777, true);
        }
        $dst_name = "./saved/" . date('YmdHis') . "_" . $file_name;
        if (!move_uploaded_file($temp_name, $dst_name)) {
            $dst_name = ""; 
        }
    }    
    $msg = nl2br(htmlspecialchars($msg));
    $now = date('Y-m-d H:i:s');

    $insert_sql = "INSERT INTO UAS(target_name, marry, msg, file, date) VALUES('$target_name', '$marry', '$msg', '$dst_name', '$now')";
    $result = mysqli_query($db, $insert_sql);
    
    if ($result) {       
        echo "<div style='text-align:center; font-weight: bold; color: #2c3e50; font-size: 1.2rem; margin: 10px 0;'>좋아요 보내기 성공! !!</div>";
    } else {
        echo "<div style='color:red; text-align:center;'>마음 전달에 실패했습니다.. 다시 시도해주세요..</div>";
    }
}
$sql = "SELECT * FROM UAS ORDER BY no DESC"; 
$result_table = mysqli_query($db, $sql);

if ($result_table) {
    $row_num = mysqli_num_rows($result_table);
    echo "<hr><h2>좋아요 발송 기록 (총 ".$row_num."건)</h2><hr>";    
    for ($i=0; $i<$row_num; $i++) {
        $row = mysqli_fetch_array($result_table, MYSQLI_ASSOC);

        $no = $row['no'];
        $target_name = !empty($row['target_name']) ? htmlspecialchars($row['target_name']) : '상대방'; 
        $marry = $row['marry'];
        $msg = $row['msg'];
        $file = $row['file'];
        $date = $row['date'];

        echo "<div class='log_card'>";
        echo "    <h6>No: $no</h6>";
        echo "    <h4>💌 {$target_name}님에게♡</h4>"; 
        echo "    <h5>결혼 가치관: $marry</h5>";
        echo "    <p>보낸 메시지:<br>$msg</p>";

        if ($file && file_exists($file)) {
            echo "    <div><img src='$file' alt='첨부파일'></div>";
        }
        echo "    <h6 class='log_date'>보낸 시간: $date</h6>";
        echo "</div>";
    }
} else {
    echo "<div style='text-align:center;'>게시글 리스트를 불러오지 못했습니다...ㅠㅠ</div>";
}
mysqli_close($db);
?>
    </div>
</body>
</html>