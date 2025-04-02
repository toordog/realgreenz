<?php
// Start the session to access the current user's session ID
session_start();

// Indicate the response will be JSON
header('Content-Type: application/json');

// Build the path to the per-session schedules.json file
$file = __DIR__ . '/../storage/' . session_id() . '/schedules.json';

// If the file exists, decode its JSON content into an array; otherwise, use an empty array
$schedules = file_exists($file) ? json_decode(file_get_contents($file), true) : [];

// Filter the schedules to only include entries that match the current session ID
$userSchedules = array_filter($schedules, function($entry) {
    return isset($entry['session_id']) && $entry['session_id'] === session_id();
});

// Re-index the filtered array and return it as a JSON response
echo json_encode(array_values($userSchedules));
?>

