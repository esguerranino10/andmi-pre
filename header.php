<?php
require_once __DIR__ . '/store.php';

/* Numbers for the sidebar badge and alert button (js/app.js keeps them fresh) */
$counts = store_counts();

/* Sidebar links: [key, label, href] or a section label */
$nav = [
    ['label', 'Main'],
    ['dashboard',   'Dashboard',   'oks.php'],
    ['orders',      'Orders',      'orders.php'],
    ['queue',       'Queue',       'queue.php'],
    ['label', 'Management'],
    ['customers',   'Customers',   'customers.php'],
    ['services',    'Services',    'services.php'],
    ['machines',    'Machines',    'machines.php'],
    ['inventory',   'Inventory',   'inventory.php'],
    ['maintenance', 'Maintenance', 'maintenance.php'],
    ['label', 'Insights'],
    ['reports',     'Reports',     'reports.php'],
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= e($pageTitle ?? 'House of Wash') ?> — House of Wash</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="style.css">
</head>
<body>
<div class="app">

  <!-- ================= SIDEBAR ================= -->
  <aside class="sidebar" id="sidebar">
    <div class="brand">
      <img class="logo" src="isa.jpg" alt="Golden House of Wash Laundry">
      <div>
        <h1>House of Wash</h1>
        <small>Laundry Management</small>
      </div>
    </div>

    <nav class="nav-scroll">
<?php foreach ($nav as $n): ?>
<?php if ($n[0] === 'label'): ?>
      <div class="nav-label"><?= e($n[1]) ?></div>
<?php else: ?>
      <a class="nav-item<?= ($activePage ?? '') === $n[0] ? ' active' : '' ?>" href="<?= e($n[2]) ?>"><?= e($n[1]) ?><?php if ($n[0] === 'orders'): ?><span class="badge text-bg-primary" id="navOrdersBadge"<?= $counts['active'] > 0 ? '' : ' hidden' ?>><?= $counts['active'] ?></span><?php endif; ?></a>
<?php endif; ?>
<?php endforeach; ?>
    </nav>

    <div class="footer">
      <button type="button" class="btn btn-sm btn-outline-light w-100" id="btnReset">Reset Demo Data</button>
    </div>
  </aside>
  <div class="sidebar-backdrop" id="sidebarBackdrop"></div>

  <!-- ================= MAIN ================= -->
  <div class="main">
    <header class="topbar">
      <button class="btn btn-light d-lg-none" id="menuToggle">Menu</button>
      <div class="clock">
        <div class="clock-time" id="clockTime"></div>
        <div class="clock-date" id="clockDate"></div>
      </div>
      <div class="ms-auto d-flex align-items-center gap-3">
        <a class="btn btn-light position-relative" href="orders.php">Alerts
          <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill text-bg-danger" id="alertBadge"<?= $counts['alerts'] > 0 ? '' : ' hidden' ?>><?= $counts['alerts'] ?></span>
        </a>
        <div class="d-flex align-items-center gap-2">
          <div class="avatar">AD</div>
          <div class="d-none d-md-block lh-1">
            <div style="font-size:.83rem;font-weight:700">Admin Sarah</div>
            <div style="font-size:.7rem;color:var(--muted)">Administrator</div>
          </div>
        </div>
      </div>
    </header>

    <div class="page" id="pageContainer">
<?php /* header.php ends here on purpose: .app, .main and .page stay open. Each page adds its content, then includes footer.php, which closes them. */ ?>
