<?php
header('Content-Type: application/json; charset=utf-8');

$keyword= $_GET['keyword'] ?? '강남역';
$kakao_key= 'd50fc61dbf1c193a3081e1faeb17c2b4';

$url= 'https://dapi.kakao.com/v2/local/search/keyword.json?query=' . urlencode($keyword." 맛집"). "&size=5";

$curl = curl_init();

curl_setopt_array($curl, [
    CURLOPT_URL => $url,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_HTTPHEADER => [
        "Authorization: KakaoAK " . $kakao_key
    ]
]);

$response = curl_exec($curl);
curl_close($curl);

$data= json_decode($response, true);

$places= $data['documents'] ?? [];
echo json_encode([
    'status' => !empty($places) ? 'success' : 'empty',
    'keyword' => $keyword,
    'places' => $places
], JSON_UNESCAPED_UNICODE);

?>