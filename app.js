/* app.js - shared by every page.
   Page (orders.php ...)  ->  modal (modals/...)  ->  this file (initCrud)  ->  action.php  ->  includes/store.php */

function esc(s) {
  return String(s === null || s === undefined ? '' : s).replace(/[&<>"']/g, function (c) {
    return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c];
  });
}

function pill(cls, label) {
  return '<span class="pill pill-' + esc(cls) + '">' + esc(label) + '</span>';
}

function debounce(fn, ms) {
  let t;
  return function () { const a = arguments, c = this; clearTimeout(t); t = setTimeout(function () { fn.apply(c, a); }, ms || 250); };
}

function showAlert(type, message, target) {
  $(target || '#alertBox').html(
    '<div class="alert alert-' + type + ' alert-dismissible fade show" role="alert">' + esc(message) +
    '<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>'
  );
}

/* keep the sidebar badge and the alert button in step after every change */
function refreshCounts() {
  $.getJSON('action.php', { action: 'counts' }, function (res) {
    $('#navOrdersBadge').text(res.active).prop('hidden', res.active < 1);
    $('#alertBadge').text(res.alerts).prop('hidden', res.alerts < 1);
  });
}

/* The add / edit / delete flow, written once for every table.
   cfg = { entity, label, tbody, colspan, empty, renderRow(row), fill(row), onAdd(), params(), onLoaded(rows) } */
function initCrud(cfg) {
  const modal = new bootstrap.Modal(document.getElementById('crudModal'));
  const $form = $('#crudForm');

  // fetch the saved data from action.php
  function fetchRows() {
    $.ajax({
      url: 'action.php',
      type: 'GET',
      dataType: 'json',
      data: Object.assign({ action: 'read_all', entity: cfg.entity }, cfg.params ? cfg.params() : {}),
      success: function (res) {
        let rows = '';
        if (res.status === 'success' && res.data.length > 0) {
          res.data.forEach(function (row) { rows += cfg.renderRow(row); });
        } else {
          rows = '<tr><td colspan="' + cfg.colspan + '" class="empty">' + esc(cfg.empty || 'Nothing found') + '</td></tr>';
        }
        $(cfg.tbody).html(rows);
        if (cfg.onLoaded) cfg.onLoaded(res.data || []);
      },
      error: function () { showAlert('danger', 'Could not load the data.'); }
    });
  }

  // displaying the modal that has the FORM
  function openAdd() {
    $form[0].reset();
    $('#record_id').val('');
    $('#modalAlert').empty();
    $('#modalTitle').text('Add ' + cfg.label);
    if (cfg.onAdd) cfg.onAdd();
    modal.show();
  }
  $('#btnOpenAddModal').click(openAdd);

  // handles both insert and update
  $form.submit(function (e) {
    e.preventDefault();
    const action = $('#record_id').val() ? 'update' : 'create';

    $.ajax({
      url: 'action.php?action=' + action + '&entity=' + cfg.entity,
      type: 'POST',
      data: $(this).serialize(),
      dataType: 'json',
      success: function (res) {
        if (res.status === 1) {
          modal.hide();
          showAlert('success', res.msg);
          fetchRows();
          refreshCounts();
        } else {
          showAlert('danger', res.msg, '#modalAlert');
        }
      },
      error: function () { showAlert('danger', 'Server error. Please try again.', '#modalAlert'); }
    });
  });

  // fetch one record and fill the form
  $(document).on('click', '.btn-edit', function () {
    const id = $(this).data('id');
    $.ajax({
      url: 'action.php?action=read_one&entity=' + cfg.entity + '&id=' + id,
      type: 'GET',
      dataType: 'json',
      success: function (res) {
        if (res.status === 'success') {
          $form[0].reset();
          $('#modalAlert').empty();
          cfg.fill(res.data);
          $('#record_id').val(res.data.id);
          $('#modalTitle').text('Edit ' + cfg.label);
          modal.show();
        } else {
          showAlert('danger', res.msg);
        }
      }
    });
  });

  // Delete
  $(document).on('click', '.btn-delete', function () {
    if (confirm('Are you sure you want to delete this ' + cfg.label.toLowerCase() + '?')) {
      const id = $(this).data('id');
      $.ajax({
        url: 'action.php?action=delete&entity=' + cfg.entity,
        type: 'POST',
        data: { id: id },
        dataType: 'json',
        success: function (res) {
          if (res.status === 'success') {
            fetchRows();
            refreshCounts();
            showAlert('success', res.msg);
          } else {
            showAlert('danger', res.msg);
          }
        }
      });
    }
  });

  fetchRows();
  if (location.hash === '#new') openAdd();   // links such as orders.php#new open the form straight away
  return { fetchRows: fetchRows };
}

/* edit / delete buttons used in every table */
function rowButtons(id) {
  return '<td class="text-end text-nowrap">' +
    '<button class="btn btn-sm btn-warning btn-edit" data-id="' + id + '">Edit</button> ' +
    '<button class="btn btn-sm btn-danger btn-delete" data-id="' + id + '">Delete</button></td>';
}

$(function () {
  // live clock
  function updateClock() {
    const now = new Date();
    $('#clockTime').text(now.toLocaleTimeString('en-PH', { hour: '2-digit', minute: '2-digit', second: '2-digit' }));
    $('#clockDate').text(now.toLocaleDateString('en-PH', { weekday: 'long', month: 'long', day: 'numeric', year: 'numeric' }));
  }
  updateClock();
  setInterval(updateClock, 1000);

  // sidebar on small screens
  function closeSidebar() { $('#sidebar').removeClass('open'); $('#sidebarBackdrop').removeClass('show'); }
  $('#menuToggle').click(function () { $('#sidebar').toggleClass('open'); $('#sidebarBackdrop').toggleClass('show'); });
  $('#sidebarBackdrop').click(closeSidebar);

  // put the demo records back to how they started
  $('#btnReset').click(function () {
    if (confirm('Restore the original demo data?')) {
      $.post('action.php?action=reset', function () { location.reload(); });
    }
  });
});
