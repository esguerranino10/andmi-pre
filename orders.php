<?php
$pageTitle  = 'Orders';
$activePage = 'orders';
require __DIR__ . '/includes/header.php';
?>

      <div class="page-head">
        <div>
          <h2>Orders</h2>
          <p>Create, monitor and track all laundry transactions.</p>
        </div>
        <button type="button" class="btn btn-primary fw-semibold" id="btnOpenAddModal">New Order</button>
      </div>

      <?php include __DIR__ . '/modals/orders_modal.php'; ?>

      <div id="alertBox"></div>

      <!-- Filters -->
      <div class="card mb-3">
        <div class="card-body">
          <div class="row g-2 align-items-center">
            <div class="col-md-5">
              <input type="text" class="form-control" id="q" placeholder="Order code, customer or phone…">
            </div>
            <div class="col-md-3">
              <select class="form-select" id="fStatus">
                <option value="">All statuses</option>
                <?php foreach (STATUS_LABELS as $k => $l): ?>
                <option value="<?= e($k) ?>"><?= e($l) ?></option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="col-md-2">
              <select class="form-select" id="fPay">
                <option value="">All payments</option>
                <?php foreach (PAYMENT_LABELS as $k => $l): ?>
                <option value="<?= e($k) ?>"><?= e($l) ?></option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="col-md-2 text-md-end">
              <button type="button" class="btn btn-light btn-sm" id="btnReset2">Reset</button>
            </div>
          </div>
          <div class="text-body-secondary small mt-2" id="resultCount"></div>
        </div>
      </div>

      <!-- table -->
      <div class="card">
        <div class="table-responsive">
          <table class="table">
            <thead>
              <tr>
                <th>Order</th><th>Customer</th><th>Services</th>
                <th class="text-end">Total</th><th class="text-end">Balance</th>
                <th>Status</th><th>Payment</th><th>Pickup</th><th class="text-end">Action</th>
              </tr>
            </thead>
            <tbody id="userData"></tbody>
          </table>
        </div>
      </div>

<?php require __DIR__ . '/includes/footer.php'; ?>
<script>
$(document).ready(function () {

  // link from the dashboard: orders.php?q=HOW-...
  const q0 = new URLSearchParams(location.search).get('q');
  if (q0) $('#q').val(q0);

  const crud = initCrud({
    entity: 'orders',
    label: 'Order',
    tbody: '#userData',
    colspan: 9,
    empty: 'No orders match your filters',
    params: function () { return { q: $('#q').val(), status: $('#fStatus').val(), payment: $('#fPay').val() }; },
    onLoaded: function (rows) { $('#resultCount').text(rows.length + ' order(s)'); },

    renderRow: function (o) {
      return `
        <tr>
          <td>
            <div class="fw-semibold font-monospace" style="font-size:.8rem">${esc(o.code)}</div>
            <div class="text-body-secondary" style="font-size:.72rem">${esc(o.created_fmt)}</div>
          </td>
          <td>
            <div class="fw-semibold">${esc(o.customer)}</div>
            <div class="text-body-secondary" style="font-size:.72rem">${esc(o.phone)}</div>
          </td>
          <td class="small">${esc(o.services)}</td>
          <td class="text-end fw-semibold">${esc(o.total_fmt)}</td>
          <td class="text-end ${o.balance > 0 ? 'text-danger fw-semibold' : 'text-success'}">${o.balance > 0 ? esc(o.balance_fmt) : 'Settled'}</td>
          <td>${pill(o.status, o.status_label)}</td>
          <td>${pill(o.payment, o.payment_label)}</td>
          <td class="small text-body-secondary">${esc(o.pickup_fmt)}</td>
          ${rowButtons(o.id)}
        </tr>`;
    },

    // new order: service and quantity can be chosen
    onAdd: function () {
      $('#service_id').prop('disabled', false).prop('required', true);
      $('#qty').prop('disabled', false);
    },

    // existing order: items stay as they are, the rest can be changed
    fill: function (o) {
      $('#customer_id').val(o.customer_id);
      $('#service_id').val(o.service_id).prop('disabled', true).prop('required', false);
      $('#qty').val(o.qty).prop('disabled', true);
      $('#status').val(o.status);
      $('#paid').val(o.paid);
      $('#pickup').val(o.pickup);
    }
  });

  // filters reload the table through AJAX
  $('#q').on('input', debounce(crud.fetchRows, 250));
  $('#fStatus, #fPay').on('change', crud.fetchRows);
  $('#btnReset2').click(function () { $('#q').val(''); $('#fStatus, #fPay').val(''); crud.fetchRows(); });

});
</script>
</body>
</html>
