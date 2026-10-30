<?php
$pageTitle  = 'Services';
$activePage = 'services';
require __DIR__ . '/includes/header.php';
?>

      <div class="page-head">
        <div>
          <h2>Services</h2>
          <p>Price list and how each service is performing.</p>
        </div>
        <button type="button" class="btn btn-primary fw-semibold" id="btnOpenAddModal">Add Service</button>
      </div>

      <?php include __DIR__ . '/modals/services_modal.php'; ?>

      <div id="alertBox"></div>

      <div class="card">
        <div class="table-responsive">
          <table class="table">
            <thead>
              <tr>
                <th>Service</th><th>Type</th><th>Unit</th><th class="text-end">Price</th>
                <th class="text-end">Orders</th><th class="text-end">Revenue</th><th class="text-end">Action</th>
              </tr>
            </thead>
            <tbody id="userData"></tbody>
          </table>
        </div>
      </div>

<?php require __DIR__ . '/includes/footer.php'; ?>
<script>
$(document).ready(function () {

  initCrud({
    entity: 'services',
    label: 'Service',
    tbody: '#userData',
    colspan: 7,
    empty: 'No services yet',

    renderRow: function (s) {
      return `
        <tr>
          <td class="fw-semibold">${esc(s.name)}</td>
          <td class="small text-body-secondary">${esc(s.type)}</td>
          <td class="small">per ${esc(s.unit)}</td>
          <td class="text-end">${esc(s.price_fmt)}</td>
          <td class="text-end">${s.orders}</td>
          <td class="text-end fw-semibold">${esc(s.revenue_fmt)}</td>
          ${rowButtons(s.id)}
        </tr>`;
    },

    fill: function (s) {
      $('#name').val(s.name);
      $('#unit').val(s.unit);
      $('#price').val(s.price);
      $('#addon').prop('checked', !!Number(s.addon));
    }
  });

});
</script>
</body>
</html>
