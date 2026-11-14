<?php

declare(strict_types=1);

/** Duomenų saugyklos sutartis, nuo kurios nepriklauso konkretus DBMS. */
interface TaskStorage
{
    /** Grąžina visas užduotis.
     *  @return list<array{id:int,title:string,done:bool}>
     */
    public function all(): array;

    /** Randa vieną užduotį.
     *  @return array{id:int,title:string,done:bool}|null
     */
    public function find(int $id): ?array;

    /** Sukuria užduotį.
     *  @return array{id:int,title:string,done:bool}
     */
    public function create(string $title, bool $done): array;

    /** Pakeičia užduotį.
     *  @return array{id:int,title:string,done:bool}|null
     */
    public function update(int $id, string $title, bool $done): ?array;

    /** Ištrina užduotį. */
    public function delete(int $id): bool;
}
