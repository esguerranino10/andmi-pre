<?php
$pageTitle  = 'Inventory';
$activePage = 'inventory';
require __DIR__ . '/includes/header.php';
?>

      <div class="page-head">
        <div>
          <h2>Inventory</h2>
          <p id="resultCount">&nbsp;</p>
        </div>
        <button type="button" class="btn btn-primary fw-semibold" id="btnOpenAddModal">Add Item</button>
      </div>

      <?php include __DIR__ . '/modals/inventory_modal.php'; ?>

      <div id="alertBox"></div>

      <div class="card">
        <div class="table-responsive">
          <table class="table">
            <thead>
              <tr>
                <th>Item</th><th>Unit</th><th class="text-end">In Stock</th>
                <th class="text-end">Reorder Level</th><th>Status</th><th class="text-end">Action</th>
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
    entity: 'inventory',
    label: 'Item',
    tbody: '#userData',
    colspan: 6,
    empty: 'No items yet',

    onLoaded: function (rows) {
      const low = rows.filter(function (i) { return i.state !== 'ok'; }).length;
      $('#resultCount').text(rows.length + ' items · ' + low + ' need restocking');
    },

    renderRow: function (i) {
      return `
        <tr>
          <td class="fw-semibold">${esc(i.item)}</td>
          <td class="small">${esc(i.unit)}</td>
          <td class="text-end fw-semibold">${i.stock}</td>
          <td class="text-end text-body-secondary">${i.reorder}</td>
          <td>${pill(i.state, i.state_label)}</td>
          ${rowButtons(i.id)}
        </tr>`;
    },

    fill: function (i) {
      $('#item').val(i.item);
      $('#unit').val(i.unit);
      $('#stock').val(i.stock);
      $('#reorder').val(i.reorder);
    }
  });

});
</script>
</body>
</html>
