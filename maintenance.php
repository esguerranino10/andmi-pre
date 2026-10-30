<?php
$pageTitle  = 'Maintenance';
$activePage = 'maintenance';
require __DIR__ . '/includes/header.php';
?>

      <div class="page-head">
        <div>
          <h2>Maintenance</h2>
          <p>Cleaning, inspections and repairs for every machine.</p>
        </div>
        <button type="button" class="btn btn-primary fw-semibold" id="btnOpenAddModal">Schedule Task</button>
      </div>

      <?php include __DIR__ . '/modals/maintenance_modal.php'; ?>

      <div id="alertBox"></div>

      <div class="card">
        <div class="table-responsive">
          <table class="table">
            <thead><tr><th>Machine</th><th>Task</th><th>Date</th><th>Status</th><th class="text-end">Action</th></tr></thead>
            <tbody id="userData"></tbody>
          </table>
        </div>
      </div>

<?php require __DIR__ . '/includes/footer.php'; ?>
<script>
$(document).ready(function () {

  initCrud({
    entity: 'maintenance',
    label: 'Task',
    tbody: '#userData',
    colspan: 5,
    empty: 'No maintenance tasks yet',

    renderRow: function (m) {
      return `
        <tr>
          <td class="fw-semibold">${esc(m.machine)}</td>
          <td>${esc(m.task)}</td>
          <td class="small text-body-secondary">${esc(m.date_fmt)}</td>
          <td>${pill(m.pill, m.status_label)}</td>
          ${rowButtons(m.id)}
        </tr>`;
    },

    fill: function (m) {
      $('#machine').val(m.machine);
      $('#task').val(m.task);
      $('#date').val(m.date);
      $('#status').val(m.status);
    }
  });

});
</script>
</body>
</html>
