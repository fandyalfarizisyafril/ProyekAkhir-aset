<?php

$connection = $_SERVER['DB_CONNECTION'] ?? $_ENV['DB_CONNECTION'] ?? getenv('DB_CONNECTION') ?: null;
$database = $_SERVER['DB_DATABASE'] ?? $_ENV['DB_DATABASE'] ?? getenv('DB_DATABASE') ?: null;

if ($connection !== 'sqlite' || $database !== ':memory:') {
    fwrite(
        STDERR,
        "Tests must run on the in-memory SQLite database. Check phpunit.xml before running the test suite.\n"
    );

    exit(1);
}

require __DIR__ . '/../vendor/autoload.php';
