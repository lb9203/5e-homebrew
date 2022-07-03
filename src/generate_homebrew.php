<?php

require_once '../vendor/autoload.php';
use CzProject\GitPhp\Git;

const HOMEBREW_DIR  = '../homebrew_schema';
const BASE_FILE     = 'base.json';
const HOMEBREW_FILE = 'homebrew.json';
const PROCESS_TYPES  = [
    'monster',
    'deity',
    'spell',
    'item'
];

$json = json_decode(file_get_contents(sprintf('%s/%s', HOMEBREW_DIR, BASE_FILE)), true);

foreach (PROCESS_TYPES as $type) {
    $typeDir = sprintf('%s/%s', HOMEBREW_DIR, $type);

    echo sprintf("Processing: %s\n", $typeDir);

    if (!is_dir($typeDir)) {
        continue;
    }

    $typeFiles = scandir($typeDir);

    if (!$typeFiles) {
        echo sprintf("\tError: couldn\'t scan type dir %s.\n", $typeDir);
        continue;
    }

    $typeFiles = array_filter($typeFiles, fn(string $filename): bool => $filename !== '.' && $filename !== '..');

    foreach ($typeFiles as $typeFile) {
        $typeFileWithPath = sprintf('%s/%s', $typeDir, $typeFile);

        if (pathinfo($typeFileWithPath)['extension'] !== 'json') {
            echo sprintf("\tError: %s is not a json file.\n", $typeFile);
            continue;
        }

        $typeFileJson = json_decode(file_get_contents($typeFileWithPath), true);

        if ($typeFileJson === null) {
            echo sprintf("\tError: %s does not contain valid json.\n", $typeFile);
            continue;
        }

        $json[$type][] = $typeFileJson;

        echo sprintf("\t%s done\n", $typeFile);
    }
}

echo "Updating dateLastModified\n";

$json['_meta']['dateLastModified'] = $time = time();

file_put_contents(sprintf('%s/%s', HOMEBREW_DIR, HOMEBREW_FILE), json_encode($json, JSON_PRETTY_PRINT));

echo "Updating git repository\n";

$git = new Git();
$repo = $git->open('..');
$repo->addAllChanges();
$repo->commit(sprintf('Automated updated on %s.', $time));
$repo->push(null, ['--repo' => require_once 'git_remote.php']);
