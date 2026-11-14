<?php

declare(strict_types=1);

/** SQLite užklausos ir lentelės sukūrimas vienoje vietoje. */
final class SqliteTaskRepository implements TaskStorage
{
    /** Atidaro saugyklą ir sukuria lentelę, jei jos dar nėra. */
    public function __construct(private PDO $pdo)
    {
        $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $this->pdo->exec('CREATE TABLE IF NOT EXISTS tasks (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            title TEXT NOT NULL,
            done INTEGER NOT NULL DEFAULT 0 CHECK (done IN (0, 1))
        )');
    }

    /** Grąžina visas užduotis ID tvarka. */
    public function all(): array
    {
        $rows = $this->pdo->query('SELECT id, title, done FROM tasks ORDER BY id')->fetchAll(PDO::FETCH_ASSOC);
        return array_map([$this, 'normalize'], $rows);
    }

    /** Randa užduotį pagal ID. */
    public function find(int $id): ?array
    {
        $statement = $this->pdo->prepare('SELECT id, title, done FROM tasks WHERE id = :id');
        $statement->execute(['id' => $id]);
        $row = $statement->fetch(PDO::FETCH_ASSOC);
        return $row === false ? null : $this->normalize($row);
    }

    /** Sukuria ir grąžina naują užduotį. */
    public function create(string $title, bool $done): array
    {
        $statement = $this->pdo->prepare('INSERT INTO tasks (title, done) VALUES (:title, :done)');
        $statement->execute(['title' => $title, 'done' => (int) $done]);
        return $this->find((int) $this->pdo->lastInsertId());
    }

    /** Pakeičia užduotį arba grąžina null, jei jos nėra. */
    public function update(int $id, string $title, bool $done): ?array
    {
        $statement = $this->pdo->prepare('UPDATE tasks SET title = :title, done = :done WHERE id = :id');
        $statement->execute(['id' => $id, 'title' => $title, 'done' => (int) $done]);
        return $this->find($id);
    }

    /** Ištrina užduotį; grąžina false, jei jos nėra. */
    public function delete(int $id): bool
    {
        $statement = $this->pdo->prepare('DELETE FROM tasks WHERE id = :id');
        $statement->execute(['id' => $id]);
        return $statement->rowCount() > 0;
    }

    /** @param array{id:int|string,title:string,done:int|string} $row
     *  @return array{id:int,title:string,done:bool}
     */
    private function normalize(array $row): array
    {
        return ['id' => (int) $row['id'], 'title' => $row['title'], 'done' => (bool) $row['done']];
    }
}
