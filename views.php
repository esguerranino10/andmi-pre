<?php
/* views.php - turns the stored records into the rows each page shows.
   action.php sends these back to the pages as JSON. */
require_once __DIR__ . '/store.php';

function order_view($o) {
    $total   = order_total($o);
    $payment = order_payment($o);
    $names   = [];
    foreach ($o['items'] as $it) {
        $s = store_find('services', $it[0]);
        $names[] = $s ? $s['name'] : 'Service #' . $it[0];
    }
    $c = store_find('customers', $o['customer_id']);
    return [
        'id' => $o['id'], 'code' => order_code($o),
        'customer_id' => $o['customer_id'], 'customer' => $c['name'] ?? 'Unknown', 'phone' => $c['phone'] ?? '',
        'services' => implode(', ', $names),
        'service_id' => $o['items'][0][0] ?? '', 'qty' => $o['items'][0][1] ?? '',
        'total' => $total, 'total_fmt' => peso($total),
        'paid' => $o['paid'], 'balance' => max(0, $total - $o['paid']), 'balance_fmt' => peso(max(0, $total - $o['paid'])),
        'status' => $o['status'], 'status_label' => STATUS_LABELS[$o['status']] ?? $o['status'],
        'payment' => $payment, 'payment_label' => PAYMENT_LABELS[$payment],
        'created' => $o['created'], 'created_fmt' => fdate($o['created']),
        'pickup' => $o['pickup'], 'pickup_fmt' => fdate($o['pickup']),
    ];
}

function view_orders($q = '', $status = '', $payment = '') {
    $q = strtolower(trim($q));
    $rows = [];
    foreach (store_all('orders') as $o) {
        $v = order_view($o);
        if ($q !== '' && strpos(strtolower($v['code'] . ' ' . $v['customer'] . ' ' . $v['phone']), $q) === false) continue;
        if ($status !== '' && $v['status'] !== $status) continue;
        if ($payment !== '' && $v['payment'] !== $payment) continue;
        $rows[] = $v;
    }
    usort($rows, fn($a, $b) => $b['id'] <=> $a['id']);
    return $rows;
}

function view_customers($q = '') {
    $q = strtolower(trim($q));
    $rows = [];
    foreach (store_all('customers') as $c) {
        if ($q !== '' && strpos(strtolower($c['name'] . ' ' . $c['phone']), $q) === false) continue;
        $n = 0; $spent = 0; $bal = 0; $last = '';
        foreach (store_all('orders') as $o) {
            if ($o['customer_id'] !== $c['id'] || $o['status'] === 'cancelled') continue;
            $t = order_total($o);
            $n++; $spent += $t; $bal += max(0, $t - $o['paid']);
            if ($o['created'] > $last) $last = $o['created'];
        }
        $rows[] = ['id' => $c['id'], 'name' => $c['name'], 'phone' => $c['phone'], 'orders' => $n,
                   'spent' => $spent, 'spent_fmt' => peso($spent), 'balance' => $bal, 'balance_fmt' => peso($bal), 'last_fmt' => fdate($last)];
    }
    usort($rows, fn($a, $b) => $b['spent'] <=> $a['spent']);
    return $rows;
}

function view_services() {
    $rows = [];
    foreach (store_all('services') as $s) {
        $n = 0; $rev = 0;
        foreach (store_all('orders') as $o) {
            if ($o['status'] === 'cancelled') continue;
            $used = false;
            foreach ($o['items'] as $it) {
                if ($it[0] === $s['id']) { $rev += $it[1] * $it[2]; $used = true; }
            }
            if ($used) $n++;
        }
        $rows[] = ['id' => $s['id'], 'name' => $s['name'], 'unit' => $s['unit'], 'price' => $s['price'], 'price_fmt' => peso($s['price']),
                   'addon' => $s['addon'], 'type' => $s['addon'] ? 'Add-on' : 'Main service', 'orders' => $n, 'revenue_fmt' => peso($rev)];
    }
    return $rows;
}

function view_machines() {
    $rows = [];
    foreach (store_all('machines') as $m) {
        $rows[] = ['id' => $m['id'], 'name' => $m['name'], 'type' => strpos($m['name'], 'D') === 0 ? 'Dryer' : 'Washer',
                   'status' => $m['status'], 'status_label' => MACHINE_LABELS[$m['status']] ?? $m['status']];
    }
    return $rows;
}

function view_inventory() {
    $rows = [];
    foreach (store_all('inventory') as $i) {
        if ($i['stock'] <= 0)                 { $state = 'out'; $label = 'Out of stock'; }
        elseif ($i['stock'] <= $i['reorder']) { $state = 'low'; $label = 'Low stock'; }
        else                                  { $state = 'ok';  $label = 'In stock'; }
        $rows[] = $i + ['state' => $state, 'state_label' => $label];
    }
    return $rows;
}

function view_maintenance() {
    $rows = [];
    foreach (store_all('maintenance') as $m) {
        $rows[] = $m + ['date_fmt' => fdate($m['date']), 'status_label' => MAINTENANCE_LABELS[$m['status']] ?? $m['status'],
                        'pill' => MAINTENANCE_PILL[$m['status']] ?? 'pending'];
    }
    usort($rows, fn($a, $b) => strcmp($b['date'], $a['date']));
    return $rows;
}

function machine_counts() {
    $counts = array_count_values(array_column(store_all('machines'), 'status'));
    $out = [];
    foreach (MACHINE_LABELS as $key => $label) $out[] = ['key' => $key, 'label' => $label, 'count' => $counts[$key] ?? 0];
    return $out;
}

/* ---- read-only pages ---- */
function build_dashboard() {
    $orders = store_all('orders');
    $today = date('Y-m-d'); $month = date('Y-m');
    $todayRev = 0; $monthRev = 0;
    foreach ($orders as $o) {
        if ($o['created'] === $today)             $todayRev += $o['paid'];
        if (substr($o['created'], 0, 7) === $month) $monthRev += $o['paid'];
    }
    $customerCount = count(array_unique(array_column($orders, 'customer_id')));

    $unclaimed = [];
    foreach ($orders as $o) {
        if ($o['status'] !== 'ready') continue;
        $c = store_find('customers', $o['customer_id']);
        $unclaimed[] = ['name' => $c['name'] ?? 'Unknown', 'code' => order_code($o), 'created' => $o['created'],
                        'days' => (int) floor((strtotime($today) - strtotime($o['created'])) / 86400), 'total_fmt' => peso(order_total($o))];
    }
    usort($unclaimed, fn($a, $b) => strcmp($a['created'], $b['created']));

    $counts = store_counts();
    $recent = array_slice(view_orders(), 0, 6);

    $rev = []; $ord = [];
    foreach ($orders as $o) {
        foreach ($o['items'] as $it) {
            $s = store_find('services', $it[0]);
            if (!$s || $s['addon']) continue;
            $rev[$s['id']] = ($rev[$s['id']] ?? 0) + $it[1] * $it[2];
            $ord[$s['id']][$o['id']] = true;
        }
    }
    arsort($rev);
    $max = $rev ? max($rev) : 1;
    $top = [];
    foreach (array_slice($rev, 0, 5, true) as $sid => $r) {
        $top[] = ['name' => store_find('services', $sid)['name'], 'orders' => count($ord[$sid]), 'revenue_fmt' => peso($r), 'pct' => round($r / $max * 100, 1)];
    }

    return [
        'date' => date('l, F j, Y'),
        'stats' => [
            ['label' => 'Total Orders',     'value' => count($orders),   'sub' => $customerCount . ' customers',                'color' => 'primary'],
            ['label' => 'Active Orders',    'value' => $counts['active'], 'sub' => $counts['ready'] . ' ready for pickup',       'color' => 'info'],
            ['label' => "Today's Revenue",  'value' => peso($todayRev),  'sub' => peso($monthRev) . ' this month',              'color' => 'success'],
            ['label' => 'Pending Payments', 'value' => $counts['payPending'], 'sub' => count($unclaimed) . ' unclaimed order(s)', 'color' => 'warning'],
        ],
        'recent' => $recent, 'top' => $top, 'unclaimed' => $unclaimed, 'machines' => machine_counts(),
    ];
}

function build_queue() {
    $queue = [];
    foreach (store_all('orders') as $o) {
        if (in_array($o['status'], ['pending', 'washing', 'drying'])) $queue[] = order_view($o);
    }
    usort($queue, fn($a, $b) => strcmp($a['pickup'], $b['pickup']));
    return ['queue' => $queue, 'machines' => view_machines()];
}

function build_reports() {
    $valid = array_filter(store_all('orders'), fn($o) => $o['status'] !== 'cancelled');
    $sales = 0; $collected = 0; $byService = [];
    foreach ($valid as $o) {
        $sales += order_total($o); $collected += $o['paid'];
        foreach ($o['items'] as $it) $byService[$it[0]] = ($byService[$it[0]] ?? 0) + $it[1] * $it[2];
    }
    arsort($byService);
    $max = $byService ? max($byService) : 1;
    $services = [];
    foreach ($byService as $sid => $r) {
        $s = store_find('services', $sid);
        $services[] = ['name' => $s['name'] ?? 'Service #' . $sid, 'revenue_fmt' => peso($r), 'pct' => round($r / $max * 100, 1)];
    }
    $byStatus = array_count_values(array_column(store_all('orders'), 'status'));
    $statuses = [];
    foreach (STATUS_LABELS as $key => $label) $statuses[] = ['key' => $key, 'label' => $label, 'count' => $byStatus[$key] ?? 0];

    return [
        'cards' => [
            ['label' => 'Total Sales',  'value' => peso($sales),             'sub' => count($valid) . ' order(s)',  'color' => 'primary'],
            ['label' => 'Collected',    'value' => peso($collected),         'sub' => 'payments received',          'color' => 'success'],
            ['label' => 'Outstanding',  'value' => peso($sales - $collected), 'sub' => 'still to be paid',          'color' => 'warning'],
            ['label' => 'Customers',    'value' => count(store_all('customers')), 'sub' => 'on record',             'color' => 'info'],
        ],
        'services' => $services, 'statuses' => $statuses,
    ];
}
