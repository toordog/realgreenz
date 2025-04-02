<?php
header('Content-Type: application/json');

// Get raw POST data
$data = json_decode(file_get_contents('php://input'), true);

if (!isset($data['listing_id'], $data['agent_license'], $data['buyer_last_name'])) {
    http_response_code(400);
    echo json_encode(["error" => "Missing required fields."]);
    exit;
}

// Simulate storing the schedule (could write to a DB or file)
$response = [
    "message" => "Viewing scheduled successfully!",
    "data" => [
        "listing_id" => $data['listing_id'],
        "agent_license" => $data['agent_license'],
        "buyer_last_name" => $data['buyer_last_name'],
        "timestamp" => date('Y-m-d H:i:s')
    ]
];

echo json_encode($response);
?>
