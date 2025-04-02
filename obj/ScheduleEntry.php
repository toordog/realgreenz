<?php
// Represents a single scheduled viewing entry
class ScheduleEntry {
    // Define public properties that describe a viewing
    public string $listing_id;
    public string $agent_license;
    public string $buyer_last_name;
    public string $date;
    public string $time;
    public string $session_id;
    public string $created_at;

    // Constructor accepts an associative array of schedule data
    public function __construct(array $data) {
        // Map incoming fields directly to object properties
        $this->listing_id = $data['listing_id'];
        $this->agent_license = $data['agent_license'];
        $this->buyer_last_name = $data['buyer_last_name'];
        $this->date = $data['date'];
        $this->time = $data['time'];

        // Automatically capture the current session ID and timestamp
        $this->session_id = session_id();
        $this->created_at = date('Y-m-d H:i:s');
    }

    // Converts the object to an associative array for JSON or storage
    public function toArray(): array {
        return get_object_vars($this);
    }
}
?>

