<?php
// Responsible for reading and writing schedule data tied to a specific session
class ScheduleStorage {
    // Path to the current session's JSON file
    private string $file;

    // Constructor initializes the storage directory and file path
    public function __construct() {
        // Start the session to access the session ID
        session_start();

        // Build a session-specific directory under /storage/
        $dir = __DIR__ . '/../storage/' . session_id();

        // Create the directory if it doesn't exist (including parent dirs)
        if (!is_dir($dir)) {
            mkdir($dir, 0777, true);
        }

        // Define the full path to the session's schedule file
        $this->file = "$dir/schedules.json";
    }

    // Loads and returns all scheduled viewings as an array
    public function load(): array {
        return file_exists($this->file)
            ? json_decode(file_get_contents($this->file), true)
            : []; // Return empty array if file doesn't exist
    }

    // Saves the given array of schedule entries to the file in JSON format
    public function save(array $data): void {
        file_put_contents(
            $this->file,
            json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES),
            LOCK_EX // Prevents simultaneous writes from corrupting the file
        );
    }
}
?>

