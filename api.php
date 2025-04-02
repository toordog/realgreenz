<?php
// This file acts as a lightweight API endpoint.
// It loads preprocessed data from `data.php` and returns it as JSON.
// No logic is performed here — it's purely a passthrough for client-side use.
require __DIR__ . '/data.php';
header('Content-Type: application/json');
echo json_encode($listings);
?>
