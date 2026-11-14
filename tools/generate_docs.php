<?php

declare(strict_types=1);

require_once __DIR__ . '/../src/TaskStorage.php';
require_once __DIR__ . '/../src/SqliteTaskRepository.php';
require_once __DIR__ . '/../src/TaskValidator.php';
require_once __DIR__ . '/../src/TaskApi.php';

/** Paverčia pirmą PHPDoc aprašo eilutę paprastu tekstu. */
function summary(ReflectionClass|ReflectionMethod $item): string
{
    $comment = $item->getDocComment() ?: '';
    $comment = preg_replace('~^/\*\*\s*|\s*\*/$~u', '', $comment);
    $lines = preg_split('/\R/u', $comment);
    return trim(preg_replace('/^\s*\*\s?/u', '', $lines[0] ?? ''));
}

$output = "# Sugeneruota PHPDoc santrauka\n\nGeneruojama komanda `php tools/generate_docs.php`.\n";
foreach (['TaskStorage', 'SqliteTaskRepository', 'TaskValidator', 'TaskApi'] as $name) {
    $reflection = new ReflectionClass($name);
    $output .= "\n## $name\n\n" . summary($reflection) . "\n";
    foreach ($reflection->getMethods(ReflectionMethod::IS_PUBLIC) as $method) {
        if ($method->getDeclaringClass()->getName() !== $name) {
            continue;
        }
        $parameters = array_map(
            static fn (ReflectionParameter $parameter): string => (string) $parameter->getType() . ' $' . $parameter->getName(),
            $method->getParameters(),
        );
        $returnType = $method->getReturnType() === null ? '' : ': ' . $method->getReturnType();
        $output .= "\n- `" . $method->getName() . '(' . implode(', ', $parameters) . ')' . $returnType . '` — ' . summary($method) . "\n";
    }
}

file_put_contents(__DIR__ . '/../docs/PHPDOC.md', $output);
echo "docs/PHPDOC.md atnaujintas.\n";
