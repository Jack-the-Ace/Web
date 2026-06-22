<?php
header('Content-Type: application/json; charset=utf-8');
$db = mysqli_connect('localhost', 'jack', 'a1s2d3f4!', 'jack');
$userid = isset($_POST['userid']) ? trim($_POST['userid']) : '';
$response = array('status' => 'success', 'exists' => false);

if (!empty($userid)) {
    $sql = "SELECT no FROM UAS_sign WHERE userid = ?";
    $stmt = mysqli_prepare($db, $sql);
    mysqli_stmt_bind_param($stmt, "s", $userid);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_store_result($stmt);
    
    if (mysqli_stmt_num_rows($stmt) > 0) {
        $response['exists'] = true;
    }
    mysqli_stmt_close($stmt);
}

mysqli_close($db);
echo json_encode($response);
?>