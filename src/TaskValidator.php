<?php

declare(strict_types=1);

/** Tikrina kliento pateiktą užduoties JSON. */
final class TaskValidator
{
    /** Grąžina visas rastas įvesties klaidas.
     *  @param array<string,mixed> $input
     *  @return list<string>
     */
    public function errors(array $input): array
    {
        $errors = [];
        if (!isset($input['title']) || !is_string($input['title']) || trim($input['title']) === '') {
            $errors[] = 'title turi būti netuščias tekstas';
        } elseif (mb_strlen(trim($input['title'])) > 120) {
            $errors[] = 'title negali viršyti 120 simbolių';
        }
        if (array_key_exists('done', $input) && !is_bool($input['done'])) {
            $errors[] = 'done turi būti true arba false';
        }
        foreach (array_keys($input) as $key) {
            if (!in_array($key, ['title', 'done'], true)) {
                $errors[] = "Nežinomas laukas: $key";
            }
        }
        return $errors;
    }
}
