<?php
// Start the session to access session-specific data
session_start();

// Indicate that the response will be JSON
header('Content-Type: application/json');

// Retrieve the listing_id from the POST request; default to null if missing
$listingId = $_POST['listing_id'] ?? null;

// If listing_id is missing, return a 400 error and stop execution
if (!$listingId) {
    http_response_code(400);
    echo json_encode(["error" => "Missing listing_id"]);
    exit;
}

// Define the path to the session-specific schedules file
$file = __DIR__ . '/storage/' . session_id() . '/schedules.json';

// Load the schedules from file if it exists; otherwise, use an empty array
$schedules = file_exists($file) ? json_decode(file_get_contents($file), true) : [];

// Filter out the schedule entry that matches both the current session and listing ID
$schedules = array_filter($schedules, function($entry) use ($listingId) {
    return !(isset($entry['session_id']) && $entry['session_id'] === session_id() && $entry['listing_id'] == $listingId);
});

// Save the updated list back to the file, re-indexing the array
file_put_contents($file, json_encode(array_values($schedules), JSON_PRETTY_PRINT));

// Log the cancellation event to the server logs for traceability
error_log("[Cancelled Viewing] listing_id=$listingId by session=" . session_id());

// Return a confirmation message as JSON
echo json_encode(["message" => "Cancelled", "listing_id" => $listingId]);
?>

