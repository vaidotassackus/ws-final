<?php

declare(strict_types=1);

require_once __DIR__ . '/../src/TaskStorage.php';
require_once __DIR__ . '/../src/SqliteTaskRepository.php';
require_once __DIR__ . '/../src/TaskValidator.php';
require_once __DIR__ . '/../src/TaskApi.php';

function check(bool $condition, string $message): void
{
    if (!$condition) {
        throw new RuntimeException($message);
    }
    echo "✓ $message\n";
}

// Vienetiniai testai: kiekviena konkreti klasė tikrinama atskirai.
$validator = new TaskValidator();
check($validator->errors(['title' => ' Pirkti pieno ', 'done' => false]) === [], 'TaskValidator priima tinkamus duomenis');
check(count($validator->errors(['title' => '', 'done' => 'false', 'extra' => 1])) === 3, 'TaskValidator atmeta netinkamus laukus');

$repository = new SqliteTaskRepository(new PDO('sqlite::memory:'));
check($repository->all() === [], 'SqliteTaskRepository sukuria tuščią lentelę');
$created = $repository->create('Pirkti pieno', false);
check($created['id'] === 1 && $repository->find(1) === $created, 'SqliteTaskRepository įrašo ir randa užduotį');
check($repository->update(1, 'Pirkti duonos', true)['done'] === true, 'SqliteTaskRepository keičia užduotį');
check($repository->delete(1) && $repository->find(1) === null, 'SqliteTaskRepository pašalina užduotį');

$api = new TaskApi(new SqliteTaskRepository(new PDO('sqlite::memory:')), $validator);
check($api->handle('GET', '/')['status'] === 200, 'TaskApi pateikia pradžios nuorodą');
check($api->handle('POST', '/tasks', '{')['status'] === 400, 'TaskApi atmeta blogą JSON');
check($api->handle('POST', '/tasks', '{"title":""}')['status'] === 422, 'TaskApi grąžina validavimo klaidą');
check($api->handle('PATCH', '/tasks')['status'] === 405, 'TaskApi atmeta neleistiną metodą');
check($api->handle('GET', '/nezinomas')['status'] === 404, 'TaskApi grąžina 404');

// Priėmimo testai per tikrą HTTP serverį ir atskirą laikiną SQLite failą.
$socket = stream_socket_server('tcp://127.0.0.1:0', $errno, $error);
if ($socket === false) {
    throw new RuntimeException($error);
}
$port = (int) explode(':', stream_socket_get_name($socket, false))[1];
fclose($socket);
$dbPath = tempnam(sys_get_temp_dir(), 'tasks-api-');
$command = [PHP_BINARY, '-S', '127.0.0.1:' . $port, '-t', __DIR__ . '/../public', __DIR__ . '/../public/index.php'];
$process = proc_open($command, [0 => ['pipe', 'r'], 1 => ['file', '/dev/null', 'w'], 2 => ['file', '/dev/null', 'w']], $pipes, __DIR__ . '/..', ['DB_PATH' => $dbPath]);
if (!is_resource($process)) {
    throw new RuntimeException('Nepavyko paleisti HTTP serverio');
}
fclose($pipes[0]);

/** @return array{status:int,headers:array<string,string>,body:array<string,mixed>|null} */
function request(int $port, string $method, string $path, ?string $body = null, array $headers = []): array
{
    $options = ['http' => [
        'method' => $method,
        'ignore_errors' => true,
        'header' => implode("\r\n", array_merge(['Content-Type: application/json'], $headers)),
        'content' => $body ?? '',
        'timeout' => 2,
    ]];
    $stream = @fopen('http://127.0.0.1:' . $port . $path, 'r', false, stream_context_create($options));
    if ($stream === false) {
        throw new RuntimeException('HTTP užklausa nepavyko: ' . $path);
    }
    $responseHeaders = stream_get_meta_data($stream)['wrapper_data'];
    $raw = stream_get_contents($stream);
    fclose($stream);
    $resultHeaders = [];
    foreach (array_slice($responseHeaders, 1) as $line) {
        if (str_contains($line, ':')) {
            [$name, $value] = explode(':', $line, 2);
            $resultHeaders[strtolower($name)] = trim($value);
        }
    }
    return [
        'status' => (int) explode(' ', $responseHeaders[0])[1],
        'headers' => $resultHeaders,
        'body' => $raw === '' ? null : json_decode($raw, true, 512, JSON_THROW_ON_ERROR),
    ];
}

try {
    $ready = false;
    for ($attempt = 0; $attempt < 30; $attempt++) {
        usleep(100000);
        try {
            request($port, 'GET', '/');
            $ready = true;
            break;
        } catch (RuntimeException) {
            // Serveris dar paleidžiamas.
        }
    }
    check($ready, 'HTTP serveris paleistas');
    $list = request($port, 'GET', '/tasks');
    check($list['status'] === 200 && $list['body']['items'] === [], 'GET /tasks grąžina tuščią sąrašą');
    $created = request($port, 'POST', '/tasks', '{"title":"Pirkti pieno"}');
    check($created['status'] === 201 && $created['headers']['location'] === '/tasks/1', 'POST /tasks sukuria užduotį');
    $item = request($port, 'GET', '/tasks/1');
    check($item['body']['title'] === 'Pirkti pieno' && isset($item['body']['_links']['delete']), 'GET /tasks/1 pateikia duomenis ir nuorodas');
    $cached = request($port, 'GET', '/tasks/1', null, ['If-None-Match: ' . $item['headers']['etag']]);
    check($cached['status'] === 304 && $cached['body'] === null, 'ETag leidžia sąlyginį GET');
    $updated = request($port, 'PUT', '/tasks/1', '{"title":"Pirkti duonos","done":true}');
    check($updated['status'] === 200 && $updated['body']['done'] === true, 'PUT /tasks/1 atnaujina užduotį');
    check(request($port, 'GET', '/tasks/1', null, ['If-None-Match: ' . $item['headers']['etag']])['status'] === 200, 'Pakeitus užduotį senas ETag nebegalioja');
    check(request($port, 'OPTIONS', '/tasks')['status'] === 204, 'Swagger Editor CORS patikra veikia');
    check(request($port, 'POST', '/tasks', '{')['status'] === 400, 'Blogas JSON grąžina 400');
    check(request($port, 'POST', '/tasks', '{"title":""}')['status'] === 422, 'Neteisingi duomenys grąžina 422');
    check(request($port, 'DELETE', '/tasks/1')['status'] === 204, 'DELETE /tasks/1 ištrina užduotį');
    check(request($port, 'GET', '/tasks/1')['status'] === 404, 'Ištrinta užduotis grąžina 404');
} finally {
    proc_terminate($process);
    proc_close($process);
    unlink($dbPath);
}

echo "Visi testai praėjo.\n";
