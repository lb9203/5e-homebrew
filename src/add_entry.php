<?php

const VALID_TYPES = ['monster', 'spell', 'deity'];

$type = $_POST['type'];
$json = $_POST['json'];

if (($decodedJson = json_decode($json, true)) === null) {
    http_response_code(400);
    echo "Invalid JSON";
    exit(400);
}

if (!in_array($type, VALID_TYPES, true)) {
    http_response_code(400);
    echo "Invalid type";
    exit(400);
}

try {
    $name = $decodedJson['name'];

    if (!is_dir($directory =  sprintf('../homebrew_schema/%s', $type))) {
        mkdir($directory);
    }

    $filename = str_replace(' ', '_', strtolower($name));
    $filename = str_replace(',', '', $filename);
    $filename = sprintf('%s/%s.json', $directory, $filename);

    file_put_contents($filename, $json);

    require 'generate_homebrew.php';

    exit(200);
} catch (Throwable $throwable) {
    echo $throwable->getMessage();
    exit(500);
}



