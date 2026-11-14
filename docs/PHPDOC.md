# Sugeneruota PHPDoc santrauka

Generuojama komanda `php tools/generate_docs.php`.

## TaskStorage

Duomenų saugyklos sutartis, nuo kurios nepriklauso konkretus DBMS.

- `all(): array` — Grąžina visas užduotis.

- `find(int $id): ?array` — Randa vieną užduotį.

- `create(string $title, bool $done): array` — Sukuria užduotį.

- `update(int $id, string $title, bool $done): ?array` — Pakeičia užduotį.

- `delete(int $id): bool` — Ištrina užduotį.

## SqliteTaskRepository

SQLite užklausos ir lentelės sukūrimas vienoje vietoje.

- `__construct(PDO $pdo)` — Atidaro saugyklą ir sukuria lentelę, jei jos dar nėra.

- `all(): array` — Grąžina visas užduotis ID tvarka.

- `find(int $id): ?array` — Randa užduotį pagal ID.

- `create(string $title, bool $done): array` — Sukuria ir grąžina naują užduotį.

- `update(int $id, string $title, bool $done): ?array` — Pakeičia užduotį arba grąžina null, jei jos nėra.

- `delete(int $id): bool` — Ištrina užduotį; grąžina false, jei jos nėra.

## TaskValidator

Tikrina kliento pateiktą užduoties JSON.

- `errors(array $input): array` — Grąžina visas rastas įvesties klaidas.

## TaskApi

HTTP maršrutai ir atsakymai; duomenų saugojimą perduoda TaskStorage.

- `__construct(TaskStorage $storage, TaskValidator $validator)` — API gauna saugyklą per sąsają ir atskirą validatorių.

- `handle(string $method, string $path, string $rawBody, ?string $ifNoneMatch): array` — Apdoroja vieną HTTP užklausą be kliento sesijos.
