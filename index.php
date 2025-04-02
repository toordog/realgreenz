<?php
// Set a cookie to track the user's visit
if (!isset($_COOKIE['visited'])) {
    setcookie('visited', 'true', time() + (86400 * 30), "/"); // 30 days
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>RealGreenz</title>
  <link rel="icon" href="data:,">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" />
  <link href="style.css" rel="stylesheet" />
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</head>
<body class="bg-light text-dark">
  <div class="container py-4">
    <h1 class="text-success text-center mb-4">RealGreenz 🌿</h1>

    <!-- Listing Scroll Bar -->
    <div class="d-flex overflow-auto mb-3 gap-2" id="listingBar"></div>

    <!-- Selected Listing -->
    <div id="listingDetails" class="card shadow-sm p-4" style="display:none;"></div>
  </div>

  <!-- Schedule Modal -->
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
            <button type="submit" class="btn btn-success">Schedule</button>
          </form>
        </div>
      </div>
    </div>
  </div>

  <script>
    // Fetch listings from API
    let listings = [];

    fetch('api.php')
      .then(res => res.json())
      .then(data => {
        listings = data;
        const bar = document.getElementById('listingBar');
        data.forEach(listing => {
          const btn = document.createElement('button');
          btn.className = 'btn btn-outline-success';
          btn.textContent = listing.title;
          btn.onclick = () => showListing(listing.id);
          bar.appendChild(btn);
        });
      });

    function showListing(id) {
      const listing = listings.find(l => l.id == id);
      if (!listing) return;
      const container = document.getElementById('listingDetails');
      container.style.display = 'block';
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
        <button class="btn btn-success" onclick="openModal(${id})">Schedule Viewing</button>
      `;
    }

    function openModal(id) {
      document.getElementById('selectedListingId').value = id;
      new bootstrap.Modal(document.getElementById('scheduleModal')).show();
    }

    document.getElementById('scheduleForm').addEventListener('submit', e => {
      e.preventDefault();

      const id = document.getElementById('selectedListingId').value;
      const license = document.getElementById('agentLicense').value;
      const lastName = document.getElementById('buyerLastName').value;

      fetch('schedule.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
          listing_id: id,
          agent_license: license,
          buyer_last_name: lastName
        })
      })
      .then(res => res.json())
      .then(data => {
        alert(data.message || "Scheduled!");
        bootstrap.Modal.getInstance(document.getElementById('scheduleModal')).hide();
      })
      .catch(err => {
        alert("Error scheduling viewing.");
        console.error(err);
      });
    });

  </script>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
</body>
</html>

