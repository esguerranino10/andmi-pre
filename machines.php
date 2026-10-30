<?php
$pageTitle  = 'Machines';
$activePage = 'machines';
require __DIR__ . '/includes/header.php';
?>

      <div class="page-head">
        <div>
          <h2>Machines</h2>
          <p id="resultCount">&nbsp;</p>
        </div>
        <button type="button" class="btn btn-primary fw-semibold" id="btnOpenAddModal">Add Machine</button>
      </div>

      <?php include __DIR__ . '/modals/machines_modal.php'; ?>

      <div id="alertBox"></div>

      <div class="row g-3 mb-3" id="machineCards"></div>

      <div class="card">
        <div class="table-responsive">
          <table class="table">
            <thead><tr><th>Machine</th><th>Type</th><th>Status</th><th class="text-end">Action</th></tr></thead>
            <tbody id="userData"></tbody>
          </table>
        </div>
      </div>

<?php require __DIR__ . '/includes/footer.php'; ?>
<script>
$(document).ready(function () {

  const cardColors = { available: 'success', in_use: 'primary', maintenance: 'warning', tub_clean: 'secondary' };
  const cardLabels = <?= json_encode(MACHINE_LABELS) ?>;

  initCrud({
    entity: 'machines',
    label: 'Machine',
    tbody: '#userData',
    colspan: 4,
    empty: 'No machines yet',

    // the summary cards are worked out from the same rows
    onLoaded: function (rows) {
      $('#resultCount').text(rows.length + ' machines in total');
      let cards = '';
      Object.keys(cardLabels).forEach(function (key) {
        const n = rows.filter(function (m) { return m.status === key; }).length;
        cards += `
          <div class="col-6 col-lg-3">
            <div class="card h-100"><div class="card-body stat"><div class="min-w-0">
              <div class="lbl">${esc(cardLabels[key])}</div>
              <div class="val">${n}</div>
              <div class="trend text-${cardColors[key]}">machine(s)</div>
            </div></div></div>
          </div>`;
      });
      $('#machineCards').html(cards);
    },

    renderRow: function (m) {
      return `
        <tr>
          <td class="fw-semibold">${esc(m.name)}</td>
          <td>${esc(m.type)}</td>
          <td>${pill(m.status, m.status_label)}</td>
          ${rowButtons(m.id)}
        </tr>`;
    },

    fill: function (m) {
      $('#name').val(m.name);
      $('#status').val(m.status);
    }
  });

});
</script>
</body>
</html>
