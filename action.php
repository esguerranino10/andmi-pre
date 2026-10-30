<?php
// "database" connection - there is no database, store.php keeps the demo records in the session
include 'includes/store.php';
include 'includes/views.php';

// header for javascript
header('Content-Type: application/json');

$action = $_REQUEST['action'] ?? '';
$entity = $_REQUEST['entity'] ?? '';

$response = array();
$error = '';

/* ---------------------------------------------------------------
   What every table accepts:  field => [type, required]
   types: text, int, float, date, bool, enum:a,b,c
---------------------------------------------------------------- */
$rules = [
	'orders'      => ['customer_id' => ['int', 1], 'service_id' => ['int', 0], 'qty' => ['float', 0],
	                  'status' => ['enum:' . implode(',', array_keys(STATUS_LABELS)), 1], 'paid' => ['float', 0], 'pickup' => ['date', 0]],
	'customers'   => ['name' => ['text', 1], 'phone' => ['text', 1]],
	'services'    => ['name' => ['text', 1], 'unit' => ['text', 1], 'price' => ['float', 1], 'addon' => ['bool', 0]],
	'machines'    => ['name' => ['text', 1], 'status' => ['enum:' . implode(',', array_keys(MACHINE_LABELS)), 1]],
	'inventory'   => ['item' => ['text', 1], 'unit' => ['text', 1], 'stock' => ['int', 1], 'reorder' => ['int', 1]],
	'maintenance' => ['machine' => ['text', 1], 'task' => ['text', 1], 'date' => ['date', 1],
	                  'status' => ['enum:' . implode(',', array_keys(MAINTENANCE_LABELS)), 1]],
];

$labels = ['orders' => 'Order', 'customers' => 'Customer', 'services' => 'Service',
           'machines' => 'Machine', 'inventory' => 'Inventory Item', 'maintenance' => 'Maintenance Task'];

/* clean and validate the posted fields (replaces the raw $_POST used before) */
function clean_input($fields, $src, &$error) {
	$out = [];
	foreach ($fields as $field => [$type, $required]) {
		if ($type === 'bool') { $out[$field] = !empty($src[$field]) ? 1 : 0; continue; }

		$raw = isset($src[$field]) && is_string($src[$field]) ? trim($src[$field]) : '';
		$name = ucfirst(str_replace('_', ' ', $field));

		if ($raw === '') {
			if ($required) { $error = "$name is required"; return null; }
			$out[$field] = null;
			continue;
		}

		if ($type === 'text')  { $out[$field] = mb_substr($raw, 0, 150); }
		elseif ($type === 'int') {
			if (!ctype_digit($raw)) { $error = "$name must be a whole number"; return null; }
			$out[$field] = (int)$raw;
		}
		elseif ($type === 'float') {
			if (!is_numeric($raw) || $raw < 0) { $error = "$name must be a number"; return null; }
			$out[$field] = (float)$raw;
		}
		elseif ($type === 'date') {
			$d = DateTime::createFromFormat('Y-m-d', $raw);
			if (!$d || $d->format('Y-m-d') !== $raw) { $error = "$name is not a valid date"; return null; }
			$out[$field] = $raw;
		}
		elseif (strpos($type, 'enum:') === 0) {
			if (!in_array($raw, explode(',', substr($type, 5)), true)) { $error = "$name is not valid"; return null; }
			$out[$field] = $raw;
		}
	}
	return $out;
}

/* orders need a little extra work: customer check, items, total */
function prepare_order($data, $existing, &$error) {
	if (!store_find('customers', (int)$data['customer_id'])) { $error = 'Customer not found'; return null; }
	$paid = max(0, (float)($data['paid'] ?? 0));

	if ($existing === null) {                       // new order
		$service = store_find('services', (int)$data['service_id']);
		$qty = (float)$data['qty'];
		if (!$service)  { $error = 'Please choose a service'; return null; }
		if ($qty <= 0)  { $error = 'Quantity must be greater than 0'; return null; }
		return ['customer_id' => (int)$data['customer_id'], 'items' => [[$service['id'], $qty, $service['price']]],
		        'status' => $data['status'], 'paid' => min($paid, $qty * $service['price']),
		        'created' => date('Y-m-d'), 'pickup' => $data['pickup'] ?: daysAgo(-2)];
	}

	return ['customer_id' => (int)$data['customer_id'], 'status' => $data['status'],      // existing order
	        'paid' => min($paid, order_total($existing)), 'pickup' => $data['pickup'] ?: $existing['pickup']];
}

function record_name($entity, $row) {
	if ($entity === 'orders')      return order_code($row);
	if ($entity === 'inventory')   return $row['item'];
	if ($entity === 'maintenance') return $row['machine'] . ' - ' . $row['task'];
	return $row['name'];
}

$crud = in_array($action, ['create', 'read_all', 'read_one', 'update', 'delete'], true);
if ($crud && !isset($rules[$entity])) {
	echo json_encode(['status' => 'error', 'msg' => 'Unknown table']);
	exit;
}

if ($action === 'create') {
	$data = clean_input($rules[$entity], $_POST, $error);
	if ($data !== null && $entity === 'orders') $data = prepare_order($data, null, $error);

	if ($data === null) {
		$response['status'] = 0;
		$response['msg'] = $error ?: 'Something went wrong!';
	} else {
		$id = store_insert($entity, $data);
		$response['status'] = 1;
		$response['msg'] = 'Successfully Added New ' . $labels[$entity] . ': ' . record_name($entity, store_find($entity, $id));
	}
	echo json_encode($response);
	exit;
}

// condition to fetch the data of a table
if ($action === 'read_all') {
	$q = $_GET['q'] ?? '';
	switch ($entity) {
		case 'orders':      $data = view_orders($q, $_GET['status'] ?? '', $_GET['payment'] ?? ''); break;
		case 'customers':   $data = view_customers($q); break;
		case 'services':    $data = view_services(); break;
		case 'machines':    $data = view_machines(); break;
		case 'inventory':   $data = view_inventory(); break;
		case 'maintenance': $data = view_maintenance(); break;
	}
	$response['status'] = 'success';
	$response['data'] = $data;
	echo json_encode($response);
	exit;
}

//read one record by id
if ($action === 'read_one') {
	$id = (int)($_GET['id'] ?? 0);
	$row = store_find($entity, $id);

	if ($row) {
		$response['status'] = 'success';
		$response['data'] = $entity === 'orders' ? order_view($row) : $row;
	} else {
		$response['status'] = 'error';
		$response['msg'] = $labels[$entity] . ' not found';
	}
	echo json_encode($response);
	exit;
}

// UPDATE
if ($action === 'update') {
	$id = (int)($_POST['id'] ?? 0);
	$existing = store_find($entity, $id);
	$data = $existing ? clean_input($rules[$entity], $_POST, $error) : null;
	if (!$existing) $error = $labels[$entity] . ' not found';
	if ($data !== null && $entity === 'orders') $data = prepare_order($data, $existing, $error);

	if ($data === null) {
		$response['status'] = 0;
		$response['msg'] = 'Failed to update: ' . ($error ?: 'unknown error');
	} else {
		store_update($entity, $id, $data);
		$response['status'] = 1;
		$response['msg'] = $labels[$entity] . ' updated successfully!';
	}
	echo json_encode($response);
	exit;
}

// DELETE
if ($action === 'delete') {
	$id = (int)($_POST['id'] ?? 0);
	$error = '';

	if ($entity === 'customers') {
		foreach (store_all('orders') as $o) if ($o['customer_id'] === $id) $error = 'This customer still has orders.';
	}
	if ($entity === 'services') {
		foreach (store_all('orders') as $o) foreach ($o['items'] as $it) if ($it[0] === $id) $error = 'This service is used in an order.';
	}

	if ($error === '' && store_delete($entity, $id)) {
		$response['status'] = 'success';
		$response['msg'] = $labels[$entity] . ' deleted successfully!';
	} else {
		$response['status'] = 'error';
		$response['msg'] = 'Failed to delete ' . strtolower($labels[$entity]) . ($error ? ': ' . $error : '');
	}
	echo json_encode($response);
	exit;
}

/* ---------- read-only pages and small helpers ---------- */
if ($action === 'dashboard') { echo json_encode(['status' => 'success', 'data' => build_dashboard()]); exit; }
if ($action === 'queue')     { echo json_encode(['status' => 'success', 'data' => build_queue()]);     exit; }
if ($action === 'reports')   { echo json_encode(['status' => 'success', 'data' => build_reports()]);   exit; }
if ($action === 'counts')    { echo json_encode(['status' => 'success'] + store_counts());            exit; }
if ($action === 'reset')     { store_reset(); echo json_encode(['status' => 'success', 'msg' => 'Demo data restored']); exit; }

echo json_encode(['status' => 'error', 'msg' => 'Unknown action']);
?>
