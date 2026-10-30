<?php
$pageTitle  = 'Queue';
$activePage = 'queue';
require __DIR__ . '/includes/header.php';
?>

      <div class="page-head">
        <div>
          <h2>Queue</h2>
          <p>Orders that are waiting or being processed, earliest pickup first.</p>
        </div>
        <a class="btn btn-primary fw-semibold" href="orders.php">View Orders</a>
      </div>

      <div id="alertBox"></div>

      <div class="row g-3">
        <div class="col-lg-8">
          <div class="card">
            <div class="card-header" id="queueTitle">In Progress</div>
            <div class="table-responsive">
              <table class="table">
                <thead><tr><th>#</th><th>Order</th><th>Customer</th><th>Services</th><th>Status</th><th>Pickup</th></tr></thead>
                <tbody id="queueRows"></tbody>
              </table>
            </div>
          </div>
        </div>

        <div class="col-lg-4">
          <div class="card">
            <div class="card-header">Machines</div>
            <div class="card-body" id="machineList"></div>
          </div>
        </div>
      </div>

<?php require __DIR__ . '/includes/footer.php'; ?>
<script>
$(document).ready(function () {

  $.ajax({
    url: 'action.php?action=queue',
    type: 'GET',
    dataType: 'json',
    success: function (res) {
      if (res.status !== 'success') return;
      const d = res.data;

      $('#queueTitle').text('In Progress (' + d.queue.length + ')');

      $('#queueRows').html(d.queue.map(function (o, i) {
        return `
          <tr>
            <td class="fw-bold">${i + 1}</td>
            <td><span class="fw-semibold font-monospace" style="font-size:.8rem">${esc(o.code)}</span></td>
            <td>${esc(o.customer)}</td>
            <td class="small">${esc(o.services)}</td>
            <td>${pill(o.status, o.status_label)}</td>
            <td class="small text-body-secondary">${esc(o.pickup_fmt)}</td>
          </tr>`;
      }).join('') || '<tr><td colspan="6" class="empty">The queue is empty</td></tr>');

      $('#machineList').html(d.machines.map(function (m) {
        return `
          <div class="d-flex justify-content-between align-items-center mb-2">
            <span class="fw-semibold small">${esc(m.name)}</span>
            ${pill(m.status, m.status_label)}
          </div>`;
      }).join(''));
    },
    error: function () { showAlert('danger', 'Could not load the queue.'); }
  });

});
</script>
</body>
</html>
