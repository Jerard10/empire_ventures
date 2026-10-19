<?php
require_once 'db.php';
requireLogin();
 
$userId = $_SESSION['user_id'];
$userName = $_SESSION['user_name'];
$userEmail = $_SESSION['user_email'];
 
// Fetch User's Orders
$orderStmt = $pdo->prepare("
    SELECT o.*, v.name as variety_name 
    FROM orders o 
    JOIN rice_varieties v ON o.rice_variety_id = v.id 
    WHERE o.user_id = ? 
    ORDER BY o.created_at DESC
");
$orderStmt->execute([$userId]);
$userOrders = $orderStmt->fetchAll();
 
// Fetch User's Wholesale Applications
$wsStmt = $pdo->prepare("
    SELECT w.*, v.name as variety_name 
    FROM wholesale_registrations w 
    JOIN rice_varieties v ON w.rice_variety_id = v.id 
    WHERE w.user_id = ? OR w.email = ?
    ORDER BY w.created_at DESC
");
$wsStmt->execute([$userId, $userEmail]);
$userRegistrations = $wsStmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Customer Dashboard | Empire Ventures</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300..700&family=Fraunces:opsz,wght@9..144,400..600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <?php renderNav(''); ?>
 
    <main class="dashboard-main">
        <div class="container" style="padding-top: 40px;">
            <div class="dashboard-header">
                <div>
                    <h1>Welcome back, <?= sanitize($userName) ?></h1>
                    <p class="section-lede">Manage your rice orders and wholesale application status.</p>
                </div>
                <a href="order.php" class="btn btn-primary">+ Order Sacks of Rice</a>
            </div>
 
            <!-- Orders Table -->
            <section class="dashboard-card">
                <h2>Your Sack Orders</h2>
                <div class="table-wrap" style="margin-top: 20px;">
                    <table>
                        <thead>
                            <tr>
                                <th>Order #</th>
                                <th>Rice Variety</th>
                                <th>Quantity</th>
                                <th>Total Amount</th>
                                <th>Payment Method</th>
                                <th>Status</th>
                                <th>Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($userOrders)): ?>
                                <tr><td colspan="7" class="empty-row">You haven't placed any sack orders yet. <a href="order.php" style="color: var(--grain);">Place an order now</a>.</td></tr>
                            <?php else: ?>
                                <?php foreach ($userOrders as $o): ?>
                                    <tr>
                                        <td class="cell-strong"><?= sanitize($o['order_number']) ?></td>
                                        <td><?= sanitize($o['variety_name']) ?></td>
                                        <td><?= $o['quantity_sacks'] ?> Sacks (<?= $o['quantity_sacks'] * $o['sack_weight_kg'] ?>kg)</td>
                                        <td>₱<?= number_format($o['total_amount'], 2) ?></td>
                                        <td><?= strtoupper(sanitize($o['payment_method'])) ?></td>
                                        <td>
                                            <span class="badge-status badge-<?= strtolower($o['order_status']) ?>">
                                                <?= ucfirst(str_replace('_', ' ', $o['order_status'])) ?>
                                            </span>
                                        </td>
                                        <td><?= date('M d, Y', strtotime($o['created_at'])) ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </section>
 
            <!-- Wholesale Applications -->
            <section class="dashboard-card" style="margin-top: 40px;">
                <h2>Wholesale Partner Registrations</h2>
                <div class="table-wrap" style="margin-top: 20px;">
                    <table>
                        <thead>
                            <tr>
                                <th>Business Name</th>
                                <th>Contact Person</th>
                                <th>Primary Variety</th>
                                <th>Weekly Volume</th>
                                <th>Status</th>
                                <th>Applied Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($userRegistrations)): ?>
                                <tr><td colspan="6" class="empty-row">No wholesale applications on record. <a href="registration.php" style="color: var(--grain);">Apply as a partner</a>.</td></tr>
                            <?php else: ?>
                                <?php foreach ($userRegistrations as $r): ?>
                                    <tr>
                                        <td class="cell-strong"><?= sanitize($r['primary_name']) ?></td>
                                        <td><?= sanitize($r['full_name']) ?></td>
                                        <td><?= sanitize($r['variety_name']) ?></td>
                                        <td><?= sanitize($r['supply_volume']) ?></td>
                                        <td>
                                            <span class="badge-status badge-<?= strtolower($r['status']) ?>">
                                                <?= ucfirst($r['status']) ?>
                                            </span>
                                        </td>
                                        <td><?= date('M d, Y', strtotime($r['created_at'])) ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </section>
        </div>
    </main>
<?php renderFooter(); ?>
</body>
</html>