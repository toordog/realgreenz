<?php
if (!isset($_COOKIE['visited'])) {
    setcookie('visited', 'true', time() + (86400 * 30), "/");
}

// 7 days in seconds
$sevenDays = 60 * 60 * 24 * 7;

session_set_cookie_params([
    'lifetime' => $sevenDays,
    'path' => '/',
    'domain' => '',       // use default
    'secure' => false,    // set true if using HTTPS
    'httponly' => true,
    'samesite' => 'Lax'   // or 'Strict' or 'None'
]);

ini_set('session.gc_maxlifetime', $sevenDays);

session_start();

?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Real Estate Scheduler</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" />
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
  <link href="style.css" rel="stylesheet" />
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</head>
<body class="bg-light text-dark">
  <div class="container py-4">
    <h1 class="text-success text-center mb-4">
         <i class="bi bi-house-fill me-2" style="font-size: 2rem;"></i>
        Real Estate Scheduler
    </h1>
    <div class="d-flex overflow-auto mb-3 gap-2 flex-nowrap" id="listingBar"></div>
    <div id="listingDetails" class="card shadow-sm p-4" style="display:none;"></div>
    
    <div class="card shadow-sm p-4 mb-4 text-muted" id="aboutApp">
      <h5 class="text-success mb-3">Demo: RealGreenz Property Scheduling</h5>
      <p>
        This is a lightweight demo app for scheduling real estate viewings.
        It runs entirely in the browser using session-based storage — no logins, no accounts.
      </p>
      <p>
        Click on a listing to view its details and schedule a showing.
        Your session-specific data is saved temporarily, making this perfect for showcasing scheduling flows
        without backend complexity.
      </p>
      <p class="small fst-italic">
        Built with Bootstrap 5, PHP8.2, and a simple file-based backend. Ideal for internal tools,
        prototyping, or client walkthroughs. The back-end can be created as a database using MySQL, PostgreSQL.
      </p>
    </div>
  </div>

  <!-- Modal -->
  <div class="modal fade" id="scheduleModal" tabindex="-1" aria-labelledby="scheduleLabel" aria-hidden="true">
    <div class="modal-dialog">
      <div class="modal-content border-success">
        <div class="modal-header bg-success text-white">
          <h5 class="modal-title" id="scheduleLabel">Schedule Viewing</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <form id="scheduleForm">
            <input type="hidden" id="selectedListingId" />
            <div class="mb-3">
              <label for="agentLicense" class="form-label">Agent License</label>
              <input type="text" class="form-control" id="agentLicense" required>
            </div>
            <div class="mb-3">
              <label for="buyerLastName" class="form-label">Buyer Last Name</label>
              <input type="text" class="form-control" id="buyerLastName" required>
            </div>
            <div class="mb-3">
              <label for="viewingDate" class="form-label">Date</label>
              <input type="date" class="form-control" id="viewingDate" required>
            </div>
            <div class="mb-3">
              <label for="viewingTime" class="form-label">Time</label>
              <input type="time" class="form-control" id="viewingTime" required>
            </div>
            <button type="submit" class="btn btn-success">Schedule</button>
          </form>
        </div>
      </div>
    </div>
  </div>
  <script src="app.js">
    // XXX: Come back to this
  </script>
</body>
</html>

