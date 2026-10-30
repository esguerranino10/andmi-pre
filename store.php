<?php
/* store.php - plays the role of dbcon.php, but WITHOUT a database.
   The demo records are kept in the PHP session, so add / edit / delete done through
   action.php stay saved while you browse. To use a real database later, only the
   store_* functions below need to be rewritten. */

if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/data.php';

if (!isset($_SESSION['how'])) $_SESSION['how'] = seedData();

function store_reset()          { $_SESSION['how'] = seedData(); }
function store_all($entity)     { return $_SESSION['how'][$entity] ?? []; }

function store_find($entity, $id) {
    foreach (store_all($entity) as $row) {
        if ($row['id'] === (int)$id) return $row;
    }
    return null;
}

function store_insert($entity, $row) {
    $ids = array_column(store_all($entity), 'id');
    $row['id'] = $ids ? max($ids) + 1 : 1;
    $_SESSION['how'][$entity][] = $row;
    return $row['id'];
}

function store_update($entity, $id, $changes) {
    foreach (store_all($entity) as $i => $row) {
        if ($row['id'] === (int)$id) {
            $_SESSION['how'][$entity][$i] = array_merge($row, $changes);
            return true;
        }
    }
    return false;
}

function store_delete($entity, $id) {
    $before = count(store_all($entity));
    $_SESSION['how'][$entity] = array_values(array_filter(store_all($entity), fn($r) => $r['id'] !== (int)$id));
    return count($_SESSION['how'][$entity]) < $before;
}

/* ---- order helpers (totals and payment status are worked out, not stored) ---- */
function order_total($o) {
    $t = 0;
    foreach ($o['items'] as $it) $t += $it[1] * $it[2];
    return $t;
}
function order_payment($o) {
    $t = order_total($o);
    if ($t > 0 && $o['paid'] >= $t) return 'paid';
    return $o['paid'] > 0 ? 'partial' : 'unpaid';
}
function order_code($o) {
    return 'HOW-' . date('ymd', strtotime($o['created'])) . '-' . str_pad($o['id'], 3, '0', STR_PAD_LEFT);
}

/* Numbers for the sidebar badge and the alert button */
function store_counts() {
    $active = $ready = $payPending = 0;
    foreach (store_all('orders') as $o) {
        if (in_array($o['status'], ['pending', 'washing', 'drying'])) $active++;
        if ($o['status'] === 'ready') $ready++;
        if ($o['status'] !== 'cancelled' && order_payment($o) !== 'paid') $payPending++;
    }
    return ['active' => $active, 'ready' => $ready, 'payPending' => $payPending, 'alerts' => $payPending + $ready];
}
