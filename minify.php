<?php
// Simple minification script
header('Content-Type: text/plain');

$files = [
    'css/style.css',
    'js/script.js',
    'js/cookie.js',
    'js/campaign.js',
    'js/google-analytics.js'
];

foreach ($files as $file) {
    echo "// === $file ===\n";
    if (file_exists($file)) {
        $content = file_get_contents($file);
        // Remove comments
        $content = preg_replace('!/\*[^*]*\*+([^/][^*]*\*+)*/!', '', $content);
        // Remove whitespace
        $content = preg_replace('/\s+/', ' ', $content);
        $content = preg_replace('/\s*([{}:;,])\s*/', '$1', $content);
        echo $content . "\n\n";
    } else {
        echo "// File not found\n\n";
    }
}
?>