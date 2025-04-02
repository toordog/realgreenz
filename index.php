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

  <script>
    // Holds all listing data fetched from the server
    let listings = [];

    // Keeps track of viewings scheduled in this session
    let scheduledViewings = {};

    // Stores the ID of the currently selected listing
    let currentListingId = null;

    // Fetch listing data and scheduled viewings concurrently
    Promise.all([
      fetch('lib/api.php').then(res => res.json()),
      fetch('lib/user_schedule.php').then(res => res.json())
    ])
    .then(([listingData, scheduleData]) => {
      listings = listingData;

      // Map scheduled entries by listing ID for quick access
      scheduleData.forEach(entry => {
        scheduledViewings[entry.listing_id] = {
          date: entry.date,
          time: entry.time
        };
      });

      // Render the listing bar UI after data is loaded
      renderListingBar();
    });

    // Renders the row of listing buttons at the top of the page
    function renderListingBar() {
      const bar = document.getElementById('listingBar');
      bar.innerHTML = ''; // Clear previous buttons

      listings.forEach(listing => {
        const btn = document.createElement('button');
        btn.className = 'btn me-2 listing-btn btn-light';

        // Highlight the currently selected listing
        if (listing.id === currentListingId) {
          btn.classList.add('active');
        }

        btn.title = listing.title;

        // Size and style settings
        btn.style.width = '135px';
        btn.style.minWidth = '135px';
        btn.style.height = '60px';
        btn.style.flex = '0 0 auto';

        // Show a calendar icon if viewing is scheduled
        const isScheduled = scheduledViewings[listing.id];

        btn.innerHTML = `
          ${isScheduled ? `
            <i class="bi bi-calendar-check-fill text-warning position-absolute" style="top: 6px; right: 8px; font-size: 1rem;"></i>
          ` : ''}
          <div class="d-flex align-items-center mt-3">
            <i class="bi bi-house-fill me-2 text-success" style="font-size: 1rem;"></i>
            <div class="text-truncate">${listing.title}</div>
          </div>
        `;

        // When clicked, show details and re-render bar
        btn.onclick = () => {
          showListing(listing.id);
          renderListingBar();
        };

        bar.appendChild(btn);
      });
    }

    // Displays the full details for the selected listing
    function showListing(id) {
      // Hide the about box when a listing is selected
      const aboutBox = document.getElementById('aboutApp');
      if (aboutBox) aboutBox.style.display = 'none';

      currentListingId = id;
      const listing = listings.find(l => l.id == id);
      if (!listing) return;

      const container = document.getElementById('listingDetails');
      container.style.display = 'block';

      const scheduled = scheduledViewings[listing.id];
      let scheduleBtnText = 'Schedule Viewing';
      let btnDisabled = false;
      let cancelBtnHtml = '';

      // If already scheduled, update UI accordingly
      if (scheduled) {
        scheduleBtnText = `Scheduled: ${scheduled.date} @ ${scheduled.time}`;
        btnDisabled = true;
        cancelBtnHtml = `
          <button class="btn btn-outline-danger btn-sm ms-2" onclick="cancelSchedule(${listing.id})">Cancel</button>
        `;
      }

      // Render listing details and action buttons
      container.innerHTML = `
        <h3 class="mb-3 text-success">${listing.title}</h3>
        <div class="d-flex align-items-center mb-3">
          <div class="border bg-white p-5 text-success me-3 d-flex align-items-center justify-content-center" style="width:100px;height:100px;">
            <i class="bi bi-house-fill fs-1"></i>
          </div>
          <div>
            <p>${listing.description}</p>
            <p class="text-muted"><strong>Price:</strong> $${listing.price}</p>
          </div>
        </div>
        <button class="btn btn-success" onclick="openModal(${listing.id})" ${btnDisabled ? 'disabled' : ''}>
          ${scheduleBtnText}
        </button>
        ${cancelBtnHtml}
      `;
    }

    // Opens the modal form for scheduling a viewing
    function openModal(id) {
      document.getElementById('selectedListingId').value = id;

      // Pre-fill the date/time fields if already scheduled
      const scheduled = scheduledViewings[id] || {};
      document.getElementById('agentLicense').value = '';
      document.getElementById('buyerLastName').value = '';
      document.getElementById('viewingDate').value = scheduled.date || '';
      document.getElementById('viewingTime').value = scheduled.time || '';

      // Show the Bootstrap modal
      new bootstrap.Modal(document.getElementById('scheduleModal')).show();
    }

    // Handle submission of the scheduling form
    document.getElementById('scheduleForm').addEventListener('submit', e => {
      e.preventDefault();

      const button = e.submitter; // Get the actual submit button
      button.disabled = true;     // Disable while request is active
      button.innerText = 'Scheduling...';
      
      // Collect form values
      const id = parseInt(document.getElementById('selectedListingId').value, 10);
      const license = document.getElementById('agentLicense').value;
      const lastName = document.getElementById('buyerLastName').value;
      const date = document.getElementById('viewingDate').value;
      const time = document.getElementById('viewingTime').value;

      // Send data to the server to schedule the viewing
      fetch('lib/schedule.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
          listing_id: id,
          agent_license: license,
          buyer_last_name: lastName,
          date: date,
          time: time
        })
      })
      .then(res => res.json())
      .then(data => {
        // Update local state with the new schedule
        if (data.error) {
          alert(data.error); // Optionally display error
        } else {
          scheduledViewings[id] = {
            date: data.scheduled.date,
            time: data.scheduled.time
          };

          // Hide modal and refresh UI
          bootstrap.Modal.getInstance(document.getElementById('scheduleModal')).hide();
          showListing(id);
          renderListingBar();
        }
      })
      .finally(() => {
        // Re-enable the button regardless of success/failure
        button.disabled = false;
        button.innerText = 'Schedule';
      });
    });

    // Cancels a previously scheduled viewing
    function cancelSchedule(id) {
      fetch('lib/cancel.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: `listing_id=${id}`
      })
      .then(res => res.json())
      .then(data => {
        // Remove entry from local tracking
        delete scheduledViewings[id];

        // Refresh listing display and buttons
        showListing(id);
        renderListingBar();
      });
    }
  </script>
</body>
</html>

