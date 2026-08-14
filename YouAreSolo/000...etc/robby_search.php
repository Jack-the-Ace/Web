<?php
declare(strict_types=1);

/*
 * API 키 설정
 * 1. NAVER_CLIENT_ID / NAVER_CLIENT_SECRET : 네이버 검색 API 애플리케이션 값
 * 2. KAKAO_REST_API_KEY                   : 카카오 Developers의 REST API 키
 * 3. KAKAO_JAVASCRIPT_KEY                 : 카카오 Developers의 JavaScript 키 (지도 표시용)
 *
 * dothome 등의 공유 호스팅에서는 아래 빈 문자열에 값을 넣어도 됩니다.
 * 가능하면 서버 환경변수에 설정하는 편이 더 안전합니다.
 */
const NAVER_CLIENT_ID = '';
const NAVER_CLIENT_SECRET = '';
const KAKAO_REST_API_KEY = 'd50fc61dbf1c193a3081e1faeb17c2b4';
const KAKAO_JAVASCRIPT_KEY = '2cf74be1aa764149b96061c654ad20ba';

header('Content-Type: application/json; charset=utf-8');

function setting(string $name): string {
    $constant = defined($name) ? constant($name) : '';
    $environment = getenv($name);
    return trim((string) ($environment !== false && $environment !== '' ? $environment : $constant));
}
function reply(array $payload, int $status = 200): never {
    http_response_code($status);
    echo json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}
function request(string $url, array $headers): array {
    if (!function_exists('curl_init')) {
        throw new RuntimeException('서버에 cURL PHP 확장이 필요합니다.');
    }
    $curl = curl_init($url);
    curl_setopt_array($curl, [CURLOPT_RETURNTRANSFER => true, CURLOPT_HTTPHEADER => $headers, CURLOPT_CONNECTTIMEOUT => 5, CURLOPT_TIMEOUT => 12]);
    $body = curl_exec($curl);
    $status = (int) curl_getinfo($curl, CURLINFO_HTTP_CODE);
    $error = curl_error($curl);
    curl_close($curl);
    if ($body === false || $status < 200 || $status >= 300) {
        throw new RuntimeException('외부 검색 API 요청에 실패했습니다' . ($error ? ': ' . $error : ' (HTTP ' . $status . ')'));
    }
    $decoded = json_decode($body, true);
    if (!is_array($decoded)) throw new RuntimeException('검색 API 응답을 해석하지 못했습니다.');
    return $decoded;
}
function cleanTitle(string $title): string {
    return trim(html_entity_decode(strip_tags($title), ENT_QUOTES | ENT_HTML5, 'UTF-8'));
}

if (($_GET['action'] ?? '') === 'config') {
    reply(['ok' => true, 'kakaoJavascriptKey' => setting('KAKAO_JAVASCRIPT_KEY')]);
}
if ($_SERVER['REQUEST_METHOD'] !== 'POST') reply(['ok' => false, 'message' => 'POST 요청만 허용됩니다.'], 405);

$area = trim((string) ($_POST['area'] ?? ''));
$menu = trim((string) ($_POST['menu'] ?? ''));

function textLength(string $text): int{
    return function_exists('mb_strlen') ? mb_strlen($text, 'UTF-8') : strlen($text);
}
if ($area === '' || textLength($area) > 80 || textLength($menu) > 60) reply(['ok' => false, 'message' => '검색어를 다시 확인해 주세요.'], 422);

$naverId = setting('NAVER_CLIENT_ID');
$naverSecret = setting('NAVER_CLIENT_SECRET');
$kakaoKey = setting('KAKAO_REST_API_KEY');
if ($naverId === '' || $naverSecret === '' || $kakaoKey === '') reply(['ok' => false, 'message' => 'API 키가 설정되지 않았습니다. robby_search.php 상단의 설정을 완료해 주세요.'], 500);

try {
    $keywords = trim($area . ' ' . $menu . ' 맛집');
    // 지역검색 API는 최대 5건을 반환합니다. sort=random을 보내지 않아 API 기본 결과 순서를 사용합니다.
    $naver = request('https://openapi.naver.com/v1/search/local.json?display=5&query=' . rawurlencode($keywords), [
        'X-Naver-Client-Id: ' . $naverId,
        'X-Naver-Client-Secret: ' . $naverSecret,
    ]);
    $places = [];
    foreach (array_slice($naver['items'] ?? [], 0, 5) as $item) {
        $title = cleanTitle((string) ($item['title'] ?? ''));
        if ($title === '') continue;
        $address = trim((string) (($item['roadAddress'] ?? '') ?: ($item['address'] ?? '')));
        $kakaoQuery = trim($title . ' ' . $address);
        $kakao = request('https://dapi.kakao.com/v2/local/search/keyword.json?size=1&query=' . rawurlencode($kakaoQuery), ['Authorization: KakaoAK ' . $kakaoKey]);
        $match = $kakao['documents'][0] ?? [];
        $places[] = [
            'title' => $title,
            'category' => (string) ($item['category'] ?? ''),
            'address' => $address,
            'telephone' => (string) ($item['telephone'] ?? ''),
            'latitude' => (string) ($match['y'] ?? ''),
            'longitude' => (string) ($match['x'] ?? ''),
            'kakaoUrl' => (string) ($match['place_url'] ?? ''),
        ];
    }
    $label = trim($area . ($menu !== '' ? ' ' . $menu : ''));
    reply(['ok' => true, 'label' => $label, 'places' => $places]);
} catch (Throwable $error) {
    error_log('Robby search error: ' . $error->getMessage());
    reply(['ok' => false, 'message' => '맛집 정보를 가져오지 못했습니다. API 키와 서버 설정을 확인해 주세요.'], 502);
}
