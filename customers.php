<?php
$pageTitle  = 'Customers';
$activePage = 'customers';
require __DIR__ . '/includes/header.php';
?>

      <div class="page-head">
        <div>
          <h2>Customers</h2>
          <p id="resultCount">&nbsp;</p>
        </div>
        <button type="button" class="btn btn-primary fw-semibold" id="btnOpenAddModal">Add Customer</button>
      </div>

      <?php include __DIR__ . '/modals/customers_modal.php'; ?>

      <div id="alertBox"></div>

      <div class="card mb-3">
        <div class="card-body">
          <div class="row g-2 align-items-center">
            <div class="col-md-9">
              <input type="text" class="form-control" id="q" placeholder="Search by name or phone…">
            </div>
            <div class="col-md-3 text-md-end">
              <button type="button" class="btn btn-light btn-sm" id="btnReset2">Reset</button>
            </div>
          </div>
        </div>
      </div>

      <div class="card">
        <div class="table-responsive">
          <table class="table">
            <thead>
              <tr>
                <th>Customer</th><th>Phone</th><th class="text-end">Orders</th>
                <th class="text-end">Total Spent</th><th class="text-end">Balance Due</th>
                <th>Last Order</th><th class="text-end">Action</th>
              </tr>
            </thead>
            <tbody id="userData"></tbody>
          </table>
        </div>
      </div>

<?php require __DIR__ . '/includes/footer.php'; ?>
<script>
$(document).ready(function () {

  const crud = initCrud({
    entity: 'customers',
    label: 'Customer',
    tbody: '#userData',
    colspan: 7,
    empty: 'No customers found',
    params: function () { return { q: $('#q').val() }; },
    onLoaded: function (rows) { $('#resultCount').text(rows.length + ' customer(s)'); },

    renderRow: function (c) {
      return `
        <tr>
          <td class="fw-semibold">${esc(c.name)}</td>
          <td class="small">${esc(c.phone)}</td>
          <td class="text-end">${c.orders}</td>
          <td class="text-end fw-semibold">${esc(c.spent_fmt)}</td>
          <td class="text-end ${c.balance > 0 ? 'text-danger fw-semibold' : 'text-success'}">${c.balance > 0 ? esc(c.balance_fmt) : 'Settled'}</td>
          <td class="small text-body-secondary">${esc(c.last_fmt)}</td>
          ${rowButtons(c.id)}
        </tr>`;
    },

    fill: function (c) {
      $('#name').val(c.name);
      $('#phone').val(c.phone);
    }
  });

  $('#q').on('input', debounce(crud.fetchRows, 250));
  $('#btnReset2').click(function () { $('#q').val(''); crud.fetchRows(); });

});
</script>
</body>
</html>
