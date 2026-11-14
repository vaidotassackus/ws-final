<?php

declare(strict_types=1);

/** HTTP maršrutai ir atsakymai; duomenų saugojimą perduoda TaskStorage. */
final class TaskApi
{
    /** API gauna saugyklą per sąsają ir atskirą validatorių. */
    public function __construct(private TaskStorage $storage, private TaskValidator $validator)
    {
    }

    /** Apdoroja vieną HTTP užklausą be kliento sesijos.
     *  @return array{status:int,headers:array<string,string>,body:array<string,mixed>|null}
     */
    public function handle(string $method, string $path, string $rawBody = '', ?string $ifNoneMatch = null): array
    {
        if ($path === '/') {
            return $method === 'GET'
                ? $this->cacheable(['name' => 'Užduočių API', '_links' => ['tasks' => ['href' => '/tasks']]], $ifNoneMatch)
                : $this->error(405, 'Metodas neleidžiamas', ['Allow' => 'GET']);
        }

        if ($path === '/tasks') {
            return match ($method) {
                'GET' => $this->cacheable([
                    'items' => array_map($this->withLinks(...), $this->storage->all()),
                    '_links' => ['self' => ['href' => '/tasks'], 'create' => ['href' => '/tasks', 'method' => 'POST']],
                ], $ifNoneMatch),
                'POST' => $this->write($rawBody),
                default => $this->error(405, 'Metodas neleidžiamas', ['Allow' => 'GET, POST']),
            };
        }

        if (!preg_match('~^/tasks/([1-9][0-9]*)$~', $path, $matches)) {
            return $this->error(404, 'Nerasta');
        }
        $id = (int) $matches[1];
        return match ($method) {
            'GET' => ($task = $this->storage->find($id)) === null
                ? $this->error(404, 'Užduotis nerasta')
                : $this->cacheable($this->withLinks($task), $ifNoneMatch),
            'PUT' => $this->write($rawBody, $id),
            'DELETE' => $this->storage->delete($id)
                ? ['status' => 204, 'headers' => [], 'body' => null]
                : $this->error(404, 'Užduotis nerasta'),
            default => $this->error(405, 'Metodas neleidžiamas', ['Allow' => 'GET, PUT, DELETE']),
        };
    }

    /** Sukuria arba pakeičia užduotį pagal JSON turinį.
     *  @return array{status:int,headers:array<string,string>,body:array<string,mixed>|null}
     */
    private function write(string $rawBody, ?int $id = null): array
    {
        try {
            $input = json_decode($rawBody, true, 512, JSON_THROW_ON_ERROR);
        } catch (JsonException) {
            return $this->error(400, 'Netaisyklingas JSON');
        }
        if (!is_array($input) || array_is_list($input)) {
            return $this->error(400, 'Reikalingas JSON objektas');
        }
        $errors = $this->validator->errors($input);
        if ($errors !== []) {
            return ['status' => 422, 'headers' => [], 'body' => ['errors' => $errors]];
        }
        $title = trim($input['title']);
        $done = $input['done'] ?? false;
        $task = $id === null
            ? $this->storage->create($title, $done)
            : $this->storage->update($id, $title, $done);
        if ($task === null) {
            return $this->error(404, 'Užduotis nerasta');
        }
        return [
            'status' => $id === null ? 201 : 200,
            'headers' => $id === null ? ['Location' => '/tasks/' . $task['id']] : [],
            'body' => $this->withLinks($task),
        ];
    }

    /** Prideda nuorodas į galimus veiksmus.
     *  @param array{id:int,title:string,done:bool} $task
     *  @return array<string,mixed>
     */
    private function withLinks(array $task): array
    {
        $url = '/tasks/' . $task['id'];
        return $task + ['_links' => [
            'self' => ['href' => $url],
            'collection' => ['href' => '/tasks'],
            'update' => ['href' => $url, 'method' => 'PUT'],
            'delete' => ['href' => $url, 'method' => 'DELETE'],
        ]];
    }

    /** Prideda ETag ir prireikus grąžina 304.
     *  @param array<string,mixed> $body
     *  @return array{status:int,headers:array<string,string>,body:array<string,mixed>|null}
     */
    private function cacheable(array $body, ?string $ifNoneMatch): array
    {
        $etag = '"' . hash('sha256', json_encode($body, JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR)) . '"';
        $headers = ['ETag' => $etag, 'Cache-Control' => 'private, max-age=0, must-revalidate'];
        return $ifNoneMatch === $etag
            ? ['status' => 304, 'headers' => $headers, 'body' => null]
            : ['status' => 200, 'headers' => $headers, 'body' => $body];
    }

    /** Sukuria vienodos formos klaidos atsakymą.
     *  @param array<string,string> $headers
     *  @return array{status:int,headers:array<string,string>,body:array<string,mixed>|null}
     */
    private function error(int $status, string $message, array $headers = []): array
    {
        return ['status' => $status, 'headers' => $headers, 'body' => ['error' => $message]];
    }
}
