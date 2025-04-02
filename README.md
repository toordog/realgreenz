### How the Scheduler Works

The scheduler is a lightweight PHP-based API that handles basic appointment booking and cancellation. It uses simple JSON file storage by default and runs entirely in memory using PHP's built-in web server—no external dependencies.

Each schedule is associated with a unique token stored in the browser via session or localStorage. This token can be used to cancel or modify the appointment later.

### Using a Real Database

If you want to swap out the JSON file storage for a proper database (like MySQL, PostgreSQL, or SQLite), you’ll only need to update a few functions where file I/O happens:

- Replace `file_get_contents()` / `file_put_contents()` with database queries
- Reflect the ScheduleEntry class in the db schema
- Use PDO for flexible cross-database support
- Store each schedule with a unique ID and token

#### Example (PDO Skeleton)

```php
$pdo = new PDO('mysql:host=localhost;dbname=scheduler', 'user', 'pass');
$stmt = $pdo->prepare('INSERT INTO schedules (name, time, token) VALUES (?, ?, ?)');
$stmt->execute([$name, $time, $token]);
```

## Quick Start
Requires **PHP 8+**.

1. Clone this repo:
   ```bash
   git clone https://github.com/toordog/rescheduler.git
   cd rescheduler
    ```
   
2. php -S localhost:8000
3. Head over to http://localhost:8000

