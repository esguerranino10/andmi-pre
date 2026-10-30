<?php
$pageTitle  = 'Dashboard';
$activePage = 'dashboard';
require __DIR__ . '/includes/header.php';
?>

      <div class="page-head">
        <div>
          <h2>Dashboard Overview</h2>
          <p id="todayDate">&nbsp;</p>
        </div>
        <a class="btn btn-primary fw-semibold" href="orders.php#new">New Order</a>
      </div>

      <div id="alertBox"></div>

      <!-- summary cards -->
      <div class="row g-3 mb-3" id="statCards"></div>

      <div class="row g-3">
        <div class="col-lg-8">

          <div class="card mb-3">
            <div class="card-header d-flex justify-content-between align-items-center">
              <span>Recent Orders</span>
              <a class="small text-decoration-none" href="orders.php">View all →</a>
            </div>
            <div class="table-responsive">
              <table class="table">
                <thead><tr><th>Order</th><th>Customer</th><th>Date</th><th>Status</th><th class="text-end">Amount</th></tr></thead>
                <tbody id="recentRows"></tbody>
              </table>
            </div>
          </div>

          <div class="card">
            <div class="card-header">Top Services</div>
            <div class="card-body" id="topServices"></div>
          </div>
        </div>

        <div class="col-lg-4">

          <div class="card mb-3">
            <div class="card-header">Quick Actions</div>
            <div class="card-body d-grid gap-2">
              <a class="quick-btn text-decoration-none" href="orders.php#new">Register New Order</a>
              <a class="quick-btn text-decoration-none" href="customers.php#new">Add Customer</a>
              <a class="quick-btn text-decoration-none" href="queue.php">Monitor Machine Queue</a>
              <a class="quick-btn text-decoration-none" href="inventory.php">Check Inventory</a>
            </div>
          </div>

          <div class="card mb-3">
            <div class="card-header d-flex justify-content-between align-items-center">
              <span>Unclaimed Laundry</span>
              <span class="badge text-bg-warning" id="unclaimedCount">0</span>
            </div>
            <div class="card-body p-0" id="unclaimedList"></div>
          </div>

          <div class="card">
            <div class="card-header">Machine Status</div>
            <div class="card-body" id="machineStatus"></div>
          </div>

        </div>
      </div>

<?php require __DIR__ . '/includes/footer.php'; ?>
<script>
$(document).ready(function () {

  // everything on this page comes from action.php?action=dashboard
  $.ajax({
    url: 'action.php?action=dashboard',
    type: 'GET',
    dataType: 'json',
    success: function (res) {
      if (res.status !== 'success') return;
      const d = res.data;

      $('#todayDate').text(d.date);

      $('#statCards').html(d.stats.map(function (s) {
        return `
          <div class="col-6 col-lg-3">
            <div class="card h-100"><div class="card-body stat"><div class="min-w-0">
              <div class="lbl">${esc(s.label)}</div>
              <div class="val">${esc(s.value)}</div>
              <div class="trend text-${esc(s.color)}" style="white-space:nowrap;overflow:hidden;text-overflow:ellipsis">${esc(s.sub)}</div>
            </div></div></div>
          </div>`;
      }).join(''));

      $('#recentRows').html(d.recent.map(function (o) {
        return `
          <tr>
            <td><span class="fw-semibold font-monospace" style="font-size:.8rem">${esc(o.code)}</span></td>
            <td>${esc(o.customer)}</td>
            <td class="text-body-secondary">${esc(o.created_fmt)}</td>
            <td>${pill(o.status, o.status_label)}${o.payment !== 'paid' ? ' <span class="ms-1">' + pill(o.payment, o.payment_label) + '</span>' : ''}</td>
            <td class="text-end fw-semibold">${esc(o.total_fmt)}</td>
          </tr>`;
      }).join('') || '<tr><td colspan="5" class="empty">No orders yet</td></tr>');

      $('#topServices').html(d.top.map(function (s) {
        return `
          <div class="mb-3">
            <div class="d-flex justify-content-between mb-1">
              <span class="fw-semibold small">${esc(s.name)}</span>
              <span class="text-body-secondary small">${s.orders} order(s) · ${esc(s.revenue_fmt)}</span>
            </div>
            <div class="bar-track"><div class="bar-fill" style="width:${s.pct}%"></div></div>
          </div>`;
      }).join('') || '<div class="empty">No services sold yet</div>');

      $('#unclaimedCount').text(d.unclaimed.length);
      $('#unclaimedList').html(d.unclaimed.map(function (u) {
        return `
          <div class="d-flex justify-content-between align-items-center px-3 py-2 border-bottom">
            <div>
              <div class="fw-semibold small">${esc(u.name)}</div>
              <div class="text-body-secondary" style="font-size:.72rem"><span class="font-monospace">${esc(u.code)}</span> · ${u.days} day(s)</div>
            </div>
            <div class="text-end">
              <div class="fw-bold small">${esc(u.total_fmt)}</div>
              <a class="btn btn-sm btn-link p-0" style="font-size:.7rem" href="orders.php?q=${encodeURIComponent(u.code)}">Open</a>
            </div>
          </div>`;
      }).join('') || '<div class="empty">All caught up</div>');

      $('#machineStatus').html(d.machines.map(function (m) {
        return `
          <div class="d-flex justify-content-between align-items-center mb-2">
            <span class="small">${pill(m.key, m.label)}</span>
            <span class="fw-bold">${m.count}</span>
          </div>`;
      }).join(''));
    },
    error: function () { showAlert('danger', 'Could not load the dashboard.'); }
  });

});
</script>
</body>
</html>
