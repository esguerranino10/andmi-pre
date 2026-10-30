<?php
/* data.php - labels, small helper functions and the starting (demo) records.
   There is no database: store.php copies these records into the session. */

date_default_timezone_set('Asia/Manila');

function daysAgo($n) { return date('Y-m-d', strtotime(($n >= 0 ? "-$n" : '+' . abs($n)) . ' days')); }
function e($s)       { return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }
function peso($n)    { return '₱' . number_format((float)$n, 2); }
function fdate($d)   { return $d ? date('M d, Y', strtotime($d)) : '—'; }

const STATUS_LABELS      = ['pending' => 'Pending', 'washing' => 'Washing', 'drying' => 'Drying', 'ready' => 'Ready', 'delivered' => 'Delivered', 'cancelled' => 'Cancelled'];
const PAYMENT_LABELS     = ['unpaid' => 'Unpaid', 'partial' => 'Partial', 'paid' => 'Paid'];
const MACHINE_LABELS     = ['available' => 'Available', 'in_use' => 'In Use', 'maintenance' => 'Maintenance', 'tub_clean' => 'Tub Cleaning'];
const MAINTENANCE_LABELS = ['scheduled' => 'Scheduled', 'in_progress' => 'In Progress', 'done' => 'Done'];
const MAINTENANCE_PILL   = ['scheduled' => 'pending', 'in_progress' => 'washing', 'done' => 'paid'];

/* The starting records. items = [service_id, qty, price] */
function seedData() {
    return [
        'customers' => [
            ['id' => 1, 'name' => 'Marc jonelle De villa', 'phone' => '0917 111 2233'],
            ['id' => 2, 'name' => 'Mark Jay Inumerable',   'phone' => '0917 222 3344'],
            ['id' => 3, 'name' => 'Ana Reyes',             'phone' => '0917 333 4455'],
            ['id' => 4, 'name' => 'Robert Lim',            'phone' => '0917 444 5566'],
            ['id' => 5, 'name' => 'Josefina Cruz',         'phone' => '0917 555 6677'],
        ],
        'services' => [
            ['id' => 1, 'name' => 'Wash & Fold',           'unit' => 'kg',    'price' => 70,  'addon' => 0],
            ['id' => 2, 'name' => 'Wash Only',             'unit' => 'kg',    'price' => 50,  'addon' => 0],
            ['id' => 4, 'name' => 'Iron & Fold',           'unit' => 'kg',    'price' => 40,  'addon' => 0],
            ['id' => 5, 'name' => 'Dry Clean',             'unit' => 'pc',    'price' => 150, 'addon' => 0],
            ['id' => 6, 'name' => 'Comforter / Blanket',   'unit' => 'pc',    'price' => 250, 'addon' => 0],
            ['id' => 7, 'name' => 'Extra Drying (10 min)', 'unit' => 'cycle', 'price' => 30,  'addon' => 1],
            ['id' => 8, 'name' => 'Stain Treatment',       'unit' => 'pc',    'price' => 80,  'addon' => 1],
        ],
        'orders' => [
            ['id' => 1, 'customer_id' => 1, 'status' => 'delivered', 'items' => [[1, 8, 70], [7, 1, 30]],  'paid' => 590, 'created' => daysAgo(5),  'pickup' => daysAgo(3)],
            ['id' => 2, 'customer_id' => 2, 'status' => 'ready',     'items' => [[6, 2, 250]],             'paid' => 0,   'created' => daysAgo(4),  'pickup' => daysAgo(1)],
            ['id' => 3, 'customer_id' => 3, 'status' => 'washing',   'items' => [[1, 5, 70]],              'paid' => 0,   'created' => daysAgo(1),  'pickup' => daysAgo(-1)],
            ['id' => 4, 'customer_id' => 4, 'status' => 'pending',   'items' => [[2, 6, 50], [4, 6, 40]],  'paid' => 200, 'created' => daysAgo(1),  'pickup' => daysAgo(-2)],
            ['id' => 5, 'customer_id' => 5, 'status' => 'ready',     'items' => [[6, 1, 250], [8, 1, 80]], 'paid' => 0,   'created' => daysAgo(22), 'pickup' => daysAgo(20)],
            ['id' => 6, 'customer_id' => 1, 'status' => 'ready',     'items' => [[5, 3, 150]],             'paid' => 450, 'created' => daysAgo(3),  'pickup' => daysAgo(1)],
        ],
        'machines' => [
            ['id' => 1, 'name' => 'W-01', 'status' => 'available'],
            ['id' => 2, 'name' => 'W-02', 'status' => 'available'],
            ['id' => 3, 'name' => 'W-03', 'status' => 'in_use'],
            ['id' => 4, 'name' => 'W-04', 'status' => 'available'],
            ['id' => 5, 'name' => 'W-05', 'status' => 'tub_clean'],
            ['id' => 6, 'name' => 'D-01', 'status' => 'available'],
            ['id' => 7, 'name' => 'D-02', 'status' => 'available'],
        ],
        'inventory' => [
            ['id' => 1, 'item' => 'Detergent',       'unit' => 'bottle', 'stock' => 25, 'reorder' => 10],
            ['id' => 2, 'item' => 'Fabric Softener', 'unit' => 'bottle', 'stock' => 18, 'reorder' => 10],
            ['id' => 3, 'item' => 'Bleach',          'unit' => 'bottle', 'stock' => 6,  'reorder' => 8],
            ['id' => 4, 'item' => 'Laundry Bags',    'unit' => 'pc',     'stock' => 40, 'reorder' => 20],
            ['id' => 5, 'item' => 'Hangers',         'unit' => 'pc',     'stock' => 60, 'reorder' => 30],
            ['id' => 6, 'item' => 'Stain Remover',   'unit' => 'bottle', 'stock' => 4,  'reorder' => 5],
        ],
        'maintenance' => [
            ['id' => 1, 'machine' => 'W-05', 'task' => 'Tub cleaning',           'date' => daysAgo(0),  'status' => 'in_progress'],
            ['id' => 2, 'machine' => 'W-03', 'task' => 'Routine inspection',     'date' => daysAgo(-3), 'status' => 'scheduled'],
            ['id' => 3, 'machine' => 'D-01', 'task' => 'Lint filter cleaning',   'date' => daysAgo(2),  'status' => 'done'],
            ['id' => 4, 'machine' => 'W-01', 'task' => 'Drain hose replacement', 'date' => daysAgo(9),  'status' => 'done'],
            ['id' => 5, 'machine' => 'D-02', 'task' => 'Belt check',             'date' => daysAgo(-7), 'status' => 'scheduled'],
        ],
    ];
}
