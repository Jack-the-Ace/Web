<?php
header("Content-Type:text/html; charset=utf-8");

// 1. DB 연결
$db = mysqli_connect('localhost', 'jack', 'a1s2d3f4!', 'jack');
mysqli_query($db, "set names utf8");


// =========================================================================
// 🔥 [핵심 수정] POST 방식으로 데이터가 전송되었을 때만 (좋아요 버튼을 눌렀을 때만) 실행
// =========================================================================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['marry'])) {
    
    $marry = $_POST['marry'];
    $msg = $_POST['msg'];
    
    // 파일 업로드 처리 변수 초기화
    $dst_name = "";

    // 파일(img)이 실제로 정상적으로 첨부되었는지 확인
    if (isset($_FILES['img']) && $_FILES['img']['error'] === UPLOAD_ERR_OK) {
        $file = $_FILES['img'];
        $file_name = $file['name'];
        $temp_name = $file['tmp_name'];

        // saved 폴더가 없으면 자동으로 생성
        if (!is_dir("./saved")) {
            mkdir("./saved", 0777, true);
        }

        $dst_name = "./saved/" . date('YmdHis') . "_" . $file_name;
        
        if (move_uploaded_file($temp_name, $dst_name)) {
            echo "File Upload Success !!<br>";
        } else {
            echo "File Upload Failure....<br>";
            $dst_name = "";
        }
    }

    $msg = nl2br(htmlspecialchars($msg)); // 줄바꿈 유지 및 보안 처리
    $now = date('Y-m-d H:i:s'); // 날짜 표기 오타 수정 완료

    // UAS 테이블에 데이터 삽입
    $insert_sql = "INSERT INTO UAS(marry, msg, file, date) VALUES('$marry','$msg','$dst_name','$now')";
    $result = mysqli_query($db, $insert_sql);
    
    if ($result) {
        echo "<script>alert('좋아요와 진심 어린 메시지가 전달되었습니다! ♡');</script>";
    } else {
        echo "마음 전달에 실패했습니다.. 다시 시도해주세요.. <br> ";
    }
}
// =========================================================================


// 2. DB에서 저장된 모든 로그 기록 읽어와서 출력하기 (POST/GET 공통)
$sql = "SELECT * FROM UAS ORDER BY no DESC"; // 최신 로그가 위에 오도록 정렬
$result_table = mysqli_query($db, $sql);

if ($result_table) {
    $row_num = mysqli_num_rows($result_table);
    echo "<h2>좋아요 발송 기록 (총 ".$row_num."건)</h2><hr>";
    
    for ($i=0; $i<$row_num; $i++) {
        $row = mysqli_fetch_array($result_table, MYSQLI_ASSOC);

        $no = $row['no'];
        $marry = $row['marry'];
        $msg = $row['msg'];
        $file = $row['file'];
        $date = $row['date'];

        echo "<h5>로그 번호: $no</h5>";
        echo "<h3>💌 상대방에게</h3>"; 
        echo "<h4>결혼 가치관: $marry</h4>";
        echo "<p>보내신 메시지:<br>$msg</p>";
        echo "<h6>보낸 시간: $date</h6>";

        // 파일이 존재하고 값이 비어있지 않을 때만 이미지 출력
        if ($file && file_exists($file)) {
            echo "<img src='$file' alt='첨부파일' width='150'>";
        }
        echo "<hr>";
    }
} else {
    echo "게시글 리스트를 불러오지 못했습니다...ㅠㅠ<br>";
}

mysqli_close($db);
?>