<?php
$url = $_GET['url'] ?? '';
if (!filter_var($url, FILTER_VALIDATE_URL)) {
    http_response_code(400);
    exit('Invalid URL');
}

$headers = get_headers($url, 1);
$contentType = $headers['Content-Type'] ?? 'image/jpeg';
header("Content-Type: $contentType");

echo file_get_contents($url);
