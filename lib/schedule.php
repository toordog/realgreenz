<?php
// Load the object-oriented class definitions for storage and schedule entries
require '../obj/ScheduleStorage.php';
require '../obj/ScheduleEntry.php';

// Set the response content type to JSON
header('Content-Type: application/json');

// Read and decode the JSON request body
$data = json_decode(file_get_contents('php://input'), true);

// Define required fields for scheduling a viewing
$required = ['listing_id', 'agent_license', 'buyer_last_name', 'date', 'time'];

// Loop through each required field and return a 400 error if any are missing
foreach ($required as $field) {
    if (empty($data[$field])) {
        http_response_code(400);
        echo json_encode(["error" => "Missing $field"]);
        exit;
    }
}

// Initialize the storage handler for the current session
$storage = new ScheduleStorage();

// Load existing schedules (or an empty array if none exist)
$schedules = $storage->load();

// Create a new schedule entry from the input data
$entry = new ScheduleEntry($data);

// Check if an entry with the same listing_id already exists
$alreadyScheduled = array_filter($schedules, function($item) use ($entry) {
    return $item['listing_id'] === $entry->listing_id;
});

if (!empty($alreadyScheduled)) {
    http_response_code(409); // Conflict
    echo json_encode(["error" => "This listing has already been scheduled."]);
    exit;
}

// Add the new entry to the schedule list
$schedules[] = $entry->toArray();

// Save the updated schedule list back to disk
$storage->save($schedules);

// Log the newly scheduled viewing to the server error log
//error_log("[Scheduled Viewing] " . json_encode($entry->toArray()));

// Return a JSON success response with the newly created entry
echo json_encode([
    "message" => "Viewing scheduled!",
    "scheduled" => $entry->toArray()
]);
?>

