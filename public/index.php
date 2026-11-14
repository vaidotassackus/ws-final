<?php

declare(strict_types=1);

require_once __DIR__ . '/../src/TaskStorage.php';
require_once __DIR__ . '/../src/SqliteTaskRepository.php';
require_once __DIR__ . '/../src/TaskValidator.php';
require_once __DIR__ . '/../src/TaskApi.php';

header('Access-Control-Allow-Origin: https://editor.swagger.io');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, If-None-Match');
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}

$dbPath = getenv('DB_PATH') ?: __DIR__ . '/../data/tasks.sqlite';
$pdo = new PDO('sqlite:' . $dbPath);
$api = new TaskApi(new SqliteTaskRepository($pdo), new TaskValidator());
$response = $api->handle(
    $_SERVER['REQUEST_METHOD'],
    parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) ?: '/',
    file_get_contents('php://input'),
    $_SERVER['HTTP_IF_NONE_MATCH'] ?? null,
);

http_response_code($response['status']);
foreach ($response['headers'] as $name => $value) {
    header($name . ': ' . $value);
}
if ($response['body'] !== null) {
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($response['body'], JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR);
}
