<?php
session_start();
if (!isset($_SESSION['userid'])) {
    echo "<script>alert('로그인이 필요합니다.'); location.href='./start2.html';</script>";
    exit;
}
$login_id = $_SESSION['userid'];

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
$db = mysqli_connect('localhost', 'jack', 'a1s2d3f4!', 'jack');
mysqli_query($db, "set names utf8");

$sql = "SELECT userid, name, gender, height, job, profile_img FROM UAS_sign WHERE userid = ?";
$stmt = mysqli_prepare($db, $sql);
mysqli_stmt_bind_param($stmt, "s", $login_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$user = mysqli_fetch_assoc($result);

$userid    = $user['userid'] ?? '';
$nickname  = $user['name'] ?? '솔로';
$height    = $user['height'] ?? '미등록';
$job       = $user['job'] ?? '미등록';

$gender_text = "미선택";
if (($user['gender'] ?? '') === 'M') {$gender_text = "남성";}
elseif (($user['gender'] ?? '') === 'W') {$gender_text = "여성";}

$profile_src = "./images/default_profile.png"; 
if (!empty($user['profile_img'])) {$profile_src = "./uploads/" . $user['profile_img'];}
?>

<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>내 정보 - 너는솔로</title>
    <link rel="stylesheet" href="./css/mypage.css">
    <style>
        #logo {flex: 2;  display: flex;  align-items: center;  justify-content: center;}
        #logo img {max-height: 140px;  width: auto;  object-fit: contain;}
        body {
            background-color: #f4f9fc;  min-height: 100vh; overflow-x: hidden;
            font-family: 'Pretendard', -apple-system, BlinkMacSystemFont, system-ui, Roboto, sans-serif;
            color: #2c3e50; margin: 0; padding: 20px 0; display: flex; justify-content: center; align-items: flex-start;             
        }
        #wrap {
            max-width: 450px; width: 100%; padding: 24px 16px; box-sizing: border-box;
            display: flex; flex-direction: column; align-items: center; gap: 15px; background: #d9edf7;
            border-radius: 24px; box-shadow: 0 8px 24px rgba(163, 197, 222, 0.2);
        }
        h2 {
            text-align: center; font-size: 2rem; font-weight: 700; width: 100%; letter-spacing: -0.5px;
            color: #3a8bcd; margin: 0; padding: 2px 0;  line-height: 1; display: block;
        }
        hr {
            width: 100%; border: 0; height: 1px; margin: 2px 0;
            background: linear-gradient(to right, transparent, #bedaf3, transparent);
        }
        .profile_card {
            width: 100%; background: #f4f9fc; border-radius: 20px; padding: 24px; box-sizing: border-box;
            box-shadow: 0 8px 24px rgba(163, 197, 222, 0.2);
            display: flex; flex-direction: column; align-items: center; gap: 20px;
        }
        .profile_image { margin: 0; display: flex; justify-content: center; }
        .profile_image img {
            width: 150px; height: 150px; border-radius: 50%; object-fit: cover;
            border: 4px solid #bedaf3; box-shadow: 0 4px 10px rgba(0,0,0,0.05);
        }
        .profile_info {
            margin: 0; display: flex; flex-direction: column; align-items: center; gap: 8px; width: 100%;
        }
        .profile_info .nickname { font-size: 1.4rem; font-weight: 700; color: #2c3e50; }
        .profile_info .sub_info { font-size: 1rem; color: #7f8c8d; font-weight: 500; }
        .info_list_card {
            width: 100%; background: #f4f9fc; border-radius: 20px; padding: 10px 20px; box-sizing: border-box;
            box-shadow: 0 8px 24px rgba(163, 197, 222, 0.2);
            display: flex; flex-direction: column;
        }
        .info_item {
            display: flex; justify-content: space-between; align-items: center;
            padding: 14px 0; border-bottom: 1px dashed #e1eef6;
        }
        .info_item:last-child { border-bottom: none; }
        .info_label { font-size: 0.9rem; font-weight: 600; color: #5b7e96; }
        .info_value { font-size: 1rem; font-weight: 500; color: #2c3e50; }
        .password_mask { color: #95a5a6; letter-spacing: 2px; font-size: 0.85rem; }
        .log_title { width: 100%; text-align: left; font-size: 1.1rem; margin: 15px 0 5px 0; font-weight: 600; color: #5b7e96; }
        
        .next_section {width: 100%; display: flex; justify-content: center; align-items: center; margin: 15px 0 10px 0;}
        .arrow_btn {
            display: flex; align-items: center; justify-content: center; gap: 8px; width: 85%; height: 54px;
            background: linear-gradient(135deg, #9dd1f7 0%, #72b3e6 100%); border-radius: 27px; 
            position: relative; cursor: pointer; box-shadow: 0 6px 15px rgba(114, 175, 222, 0.4);
            transition: transform 0.2s ease; text-shadow: 0 1px 2px rgba(72, 140, 190, 0.3);
            color: #eaf6ff; font-size: 1.05rem; font-weight: 700; animation: floating 2s ease-in-out infinite;
        }
        .arrow_btn::before {transform: rotate(45deg); margin-top: -1px; 
            content: ''; display: inline-block; width: 10px; height: 10px; border-top: 3px solid #eaf6ff; border-right: 3px solid #eaf6ff;            
        }
        .arrow_btn:active { animation: none; transform: scale(0.95); }

        @keyframes floating {
            0% { transform: translateY(0); box-shadow: 0 6px 15px rgba(114, 175, 222, 0.4); }
            50% { transform: translateY(-8px); box-shadow: 0 14px 20px rgba(114, 175, 222, 0.25); }
            100% { transform: translateY(0); box-shadow: 0 6px 15px rgba(114, 175, 222, 0.4); }
        }
    </style>
</head>
<body>
    <div id="wrap">
        <div id="logo"><img src="./boys/logo.png" alt="logo"></div>
        <hr>
        <h2>내 정보</h2>
        <hr>        
        <div class="profile_card">
            <div class="profile_image">
                <img src="<?php echo $profile_src; ?>" alt="프로필 이미지">
            </div>            
            <div class="profile_info">
                <span class="nickname"><?php echo htmlspecialchars($nickname); ?></span>
                <span class="sub_info"><?php echo htmlspecialchars($height); ?>cm · <?php echo htmlspecialchars($job); ?></span> 
            </div>
        </div>        
        <div class="info_list_card">
            <div class="info_item">
                <span class="info_label">아이디</span>
                <span class="info_value"><?php echo htmlspecialchars($userid); ?></span>
            </div>
            <div class="info_item">
                <span class="info_label">비밀번호</span>
                <span class="info_value password_mask">●●●●●●●●</span>
            </div>
            <div class="info_item">
                <span class="info_label">닉네임</span>
                <span class="info_value"><?php echo htmlspecialchars($nickname); ?></span>
            </div>
            <div class="info_item">
                <span class="info_label">성별</span>
                <span class="info_value"><?php echo $gender_text; ?></span>
            </div>
            <div class="info_item">
                <span class="info_label">키 (신장)</span>
                <span class="info_value"><?php echo htmlspecialchars($height); ?> cm</span>
            </div>
            <div class="info_item">
                <span class="info_label">직업</span>
                <span class="info_value"><?php echo htmlspecialchars($job); ?></span>
            </div>
        </div>        
        <hr>
        <div class="log_title">나의 활동 로그</div>
        <hr>         
        <div class="next_section">
            <div class="arrow_btn" onclick="location.href='https://jack.dothome.co.kr/UAS/like.php'" title="로그 페이지로 이동">로그 페이지로 이동</div>
        </div>    
    </div>
</body>
</html>
<?php
mysqli_stmt_close($stmt);  mysqli_close($db);
?>