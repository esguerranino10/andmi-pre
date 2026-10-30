<?php
$pageTitle  = 'Reports';
$activePage = 'reports';
require __DIR__ . '/includes/header.php';
?>

      <div class="page-head">
        <div>
          <h2>Reports</h2>
          <p>Sales summary from all orders.</p>
        </div>
      </div>

      <div id="alertBox"></div>

      <div class="row g-3 mb-3" id="reportCards"></div>

      <div class="row g-3">
        <div class="col-lg-8">
          <div class="card">
            <div class="card-header">Revenue by Service</div>
            <div class="card-body" id="serviceBars"></div>
          </div>
        </div>
        <div class="col-lg-4">
          <div class="card">
            <div class="card-header">Orders by Status</div>
            <div class="card-body" id="statusList"></div>
          </div>
        </div>
      </div>

<?php require __DIR__ . '/includes/footer.php'; ?>
<script>
$(document).ready(function () {

  $.ajax({
    url: 'action.php?action=reports',
    type: 'GET',
    dataType: 'json',
    success: function (res) {
      if (res.status !== 'success') return;
      const d = res.data;

      $('#reportCards').html(d.cards.map(function (c) {
        return `
          <div class="col-6 col-lg-3">
            <div class="card h-100"><div class="card-body stat"><div class="min-w-0">
              <div class="lbl">${esc(c.label)}</div>
              <div class="val">${esc(c.value)}</div>
              <div class="trend text-${esc(c.color)}">${esc(c.sub)}</div>
            </div></div></div>
          </div>`;
      }).join(''));

      $('#serviceBars').html(d.services.map(function (s) {
        return `
          <div class="mb-3">
            <div class="d-flex justify-content-between mb-1">
              <span class="fw-semibold small">${esc(s.name)}</span>
              <span class="text-body-secondary small">${esc(s.revenue_fmt)}</span>
            </div>
            <div class="bar-track"><div class="bar-fill" style="width:${s.pct}%"></div></div>
          </div>`;
      }).join('') || '<div class="empty">No sales yet</div>');

      $('#statusList').html(d.statuses.map(function (s) {
        return `
          <div class="d-flex justify-content-between align-items-center mb-2">
            ${pill(s.key, s.label)}
            <span class="fw-bold">${s.count}</span>
          </div>`;
      }).join(''));
    },
    error: function () { showAlert('danger', 'Could not load the reports.'); }
  });

});
</script>
</body>
</html>
