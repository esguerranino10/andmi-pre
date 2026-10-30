<?php $customers = store_all('customers'); $services = store_all('services'); ?>
<div class="modal fade" id="crudModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
      <div class="modal-content">
          <form id="crudForm">
              <div class="modal-header">
                  <h5 class="modal-title" id="modalTitle">Add Order</h5>
                  <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
              </div>
              <div class="modal-body">
                  <div id="modalAlert"></div>
                  <input type="hidden" id="record_id" name="id">
                  <div class="mb-3">
                      <label class="form-label">Customer</label>
                      <select class="form-select" id="customer_id" name="customer_id" required>
                          <option value="">Choose a customer…</option>
                          <?php foreach ($customers as $c): ?>
                          <option value="<?= $c['id'] ?>"><?= e($c['name']) ?></option>
                          <?php endforeach; ?>
                      </select>
                  </div>
                  <div class="mb-3">
                      <label class="form-label">Service</label>
                      <select class="form-select" id="service_id" name="service_id" >
                          <option value="">Choose a service…</option>
                          <?php foreach ($services as $s): ?>
                          <option value="<?= $s['id'] ?>"><?= e($s['name']) ?> — <?= peso($s['price']) ?>/<?= e($s['unit']) ?></option>
                          <?php endforeach; ?>
                      </select>
                  </div>
                  <div class="row">
                    <div class="col">
                  <div class="mb-3">
                      <label class="form-label">Quantity</label>
                      <input type="number" class="form-control" id="qty" name="qty" min="0.5" step="0.5" value="1">
                  </div>
                    </div>
                    <div class="col">
                  <div class="mb-3">
                      <label class="form-label">Paid so far (₱)</label>
                      <input type="number" class="form-control" id="paid" name="paid" min="0" step="0.01" value="0">
                  </div>
                    </div>
                  </div>
                  <div class="row">
                    <div class="col">
                  <div class="mb-3">
                      <label class="form-label">Status</label>
                      <select class="form-select" id="status" name="status" required>
                          <?php foreach (STATUS_LABELS as $k => $l): ?>
                          <option value="<?= e($k) ?>"><?= e($l) ?></option>
                          <?php endforeach; ?>
                      </select>
                  </div>
                    </div>
                    <div class="col">
                  <div class="mb-3">
                      <label class="form-label">Pickup date</label>
                      <input type="date" class="form-control" id="pickup" name="pickup" >
                  </div>
                    </div>
                  </div>
              </div>
              <div class="modal-footer">
                  <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                  <button type="submit" class="btn btn-primary" id="btnSave">Save</button>
              </div>
          </form>
      </div>
  </div>
</div>
