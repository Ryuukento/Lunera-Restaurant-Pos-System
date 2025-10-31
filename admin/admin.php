<?php
session_start();
include 'connection.php';

if ($user['role'] === 'admin') {
    header("Location: admin/admin.php");
} else {
    header("Location: index.php");
}
exit();
// --- Profit cards queries ---
// 1) Today's revenue
$today = $conn->real_escape_string(date('Y-m-d'));
$resToday = $conn->query("SELECT IFNULL(SUM(oi.quantity * m.price),0) AS total
    FROM orders o
    JOIN order_items oi ON oi.order_id=o.id
    JOIN menu m ON m.id=oi.menu_id
    WHERE DATE(o.created_at)= '$today' AND o.status='completed'");
$todayTotal = $resToday->fetch_assoc()['total'] ?? 0;

// 2) This week's revenue (Mon-Sun)
$weekStart = date('Y-m-d', strtotime('monday this week'));
$resWeek = $conn->query("SELECT IFNULL(SUM(oi.quantity * m.price),0) AS total
    FROM orders o
    JOIN order_items oi ON oi.order_id=o.id
    JOIN menu m ON m.id=oi.menu_id
    WHERE DATE(o.created_at) >= '$weekStart' AND o.status='completed'");
$weekTotal = $resWeek->fetch_assoc()['total'] ?? 0;

// 3) This month's revenue
$month = date('Y-m');
$resMonth = $conn->query("SELECT IFNULL(SUM(oi.quantity * m.price),0) AS total
    FROM orders o
    JOIN order_items oi ON oi.order_id=o.id
    JOIN menu m ON m.id=oi.menu_id
    WHERE DATE_FORMAT(o.created_at, '%Y-%m') = '$month' AND o.status='completed'");
$monthTotal = $resMonth->fetch_assoc()['total'] ?? 0;

// 4) This year's revenue
$year = date('Y');
$resYear = $conn->query("SELECT IFNULL(SUM(oi.quantity * m.price),0) AS total
    FROM orders o
    JOIN order_items oi ON oi.order_id=o.id
    JOIN menu m ON m.id=oi.menu_id
    WHERE YEAR(o.created_at) = $year AND o.status='completed'");
$yearTotal = $resYear->fetch_assoc()['total'] ?? 0;

// --- Best sellers (top 5) ---
$resBest = $conn->query("SELECT m.name, SUM(oi.quantity) AS sold
    FROM order_items oi
    JOIN menu m ON m.id = oi.menu_id
    JOIN orders o ON o.id = oi.order_id
    WHERE o.status='completed'
    GROUP BY oi.menu_id
    ORDER BY sold DESC
    LIMIT 5");

// --- Total menu items ---
$resCount = $conn->query("SELECT COUNT(*) AS cnt FROM menu");
$totalMenu = $resCount->fetch_assoc()['cnt'] ?? 0;

// --- Fetch menu rows for management table ---
$resMenu = $conn->query("SELECT * FROM menu ORDER BY category, name");

?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<title>Admin Dashboard - Lunera</title>
<link rel="stylesheet" href="style_admin.css">
</head>
<body>
<header class="admin-header">
    <div class="logo">LUNERA</div>
    <div class="header-actions">
        <a class="btn" href="../index.php">Storefront</a>
        <a class="btn" href="logout.php">Logout</a>
    </div>
</header>

<main class="container">
    <section class="cards">
        <div class="card">
            <div class="card-title">Today's Revenue</div>
            <div class="card-value">₱<?= number_format($todayTotal,2) ?></div>
        </div>
        <div class="card">
            <div class="card-title">This Week</div>
            <div class="card-value">₱<?= number_format($weekTotal,2) ?></div>
        </div>
        <div class="card">
            <div class="card-title">This Month</div>
            <div class="card-value">₱<?= number_format($monthTotal,2) ?></div>
        </div>
        <div class="card">
            <div class="card-title">This Year</div>
            <div class="card-value">₱<?= number_format($yearTotal,2) ?></div>
        </div>
    </section>

    <section class="grid">
        <div class="left">
            <div class="panel">
                <div class="panel-header">
                    <h3>Menu Management</h3>
                    <button id="btnAdd" class="btn primary">Add Menu Item</button>
                </div>
                <table class="menu-table">
                    <thead>
                        <tr>
                            <th>PK</th><th>Name</th><th>Category</th><th>Status</th><th>Price</th><th>Action</th>
                        </tr>
                    </thead>
                    <tbody id="menuTbody">
                        <?php while($m = $resMenu->fetch_assoc()): ?>
                        <tr data-id="<?= $m['id'] ?>">
                            <td><?= $m['id'] ?></td>
                            <td><?= htmlspecialchars($m['name']) ?></td>
                            <td><?= htmlspecialchars($m['category']) ?></td>
                            <td>
                                <label class="switch">
                                  <input class="toggle-status" type="checkbox" <?= strtolower($m['status']) === 'available' ? 'checked' : '' ?>>
                                  <span class="slider"></span>
                                </label>
                            </td>
                            <td>₱<?= number_format($m['price'],2) ?></td>
                            <td><button class="btn small edit-btn">Edit</button></td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="right">
            <div class="panel">
                <h3>Best Sellers</h3>
                <ul class="best-list">
                    <?php while($b = $resBest->fetch_assoc()): ?>
                        <li><?= htmlspecialchars($b['name']) ?> <span class="right"><?= intval($b['sold']) ?></span></li>
                    <?php endwhile; ?>
                </ul>
                <div class="panel small">
                    <h4>Total Menu Items</h4>
                    <div class="big"><?= intval($totalMenu) ?></div>
                </div>
            </div>
        </div>
    </section>
</main>

<script src="asset/js/admin.js"></script>
</body>
</html>
