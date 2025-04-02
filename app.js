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
