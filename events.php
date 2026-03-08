<?php
// Server-Sent Events endpoint that notifies clients when products.json changes
set_time_limit(0);
ignore_user_abort(true);
header('Content-Type: text/event-stream');
header('Cache-Control: no-cache');

$file = __DIR__ . '/products.json';
$lastMTime = 0;
if(file_exists($file)) $lastMTime = filemtime($file);

echo "retry: 2000\n\n"; // client should retry 2s on disconnect
ob_flush(); flush();

while (!connection_aborted()) {
    clearstatcache(false, $file);
    $mtime = file_exists($file) ? filemtime($file) : 0;
    if ($mtime !== $lastMTime) {
        $lastMTime = $mtime;
        $data = json_encode(['ts' => $lastMTime]);
        echo "event: products-update\n";
        echo "data: $data\n\n";
        ob_flush(); flush();
    }
    // sleep a short while to avoid busy loop
    sleep(1);
}
