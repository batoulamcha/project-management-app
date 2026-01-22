<?php

$configFile = __DIR__ . '/../config/config.php';
if (file_exists($configFile)) {
    $configContent = file_get_contents($configFile);
    if (preg_match("/define\s*\(\s*['\"]API_BASE_URL['\"]\s*,\s*['\"]([^'\"]+)['\"]\s*\)/", $configContent, $matches)) {
        $apiBaseUrl = $matches[1];
    } else {
        $apiBaseUrl = 'http://localhost:8000/api';
    }
} else {
    $apiBaseUrl = 'http://localhost:8000/api';
}

echo "const API_BASE_URL = '" . $apiBaseUrl . "';\n";
