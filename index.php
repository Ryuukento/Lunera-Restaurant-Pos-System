<?php
session_start();
include("connection.php");

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'cashier') {
    header("Location: login.php");
    exit();
}


$user_id = $_SESSION['user_id'];

// Safety tables
$conn->query("CREATE TABLE IF NOT EXISTS orders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    status VARCHAR(20) DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB");

$conn->query("CREATE TABLE IF NOT EXISTS order_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    menu_id INT NOT NULL,
    quantity INT DEFAULT 1,
    sugar_level VARCHAR(10) DEFAULT '100%',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB");

$category = isset($_GET['category']) ? $_GET['category'] : 'Main Courses';

$stmt = $conn->prepare("SELECT * FROM menu WHERE category = ?");
$stmt->bind_param("s", $category);
$stmt->execute();
$menu = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Restaurant Dashboard</title>
<link rel="stylesheet" href="assets/css/style.css">

</head>
<body>

<header><h1>Hotel Lunera Restaurant POS System</h1></header> 

<div class="top-buttons">
    <div class="nav-center">
        <a href="index.php?category=Main Courses" class="<?= ($category == 'Main Courses') ? 'active' : '' ?>">Main Courses</a>
        <a href="index.php?category=Appetizers" class="<?= ($category == 'Appetizers') ? 'active' : '' ?>">Appetizers</a>
        <a href="index.php?category=Dessert" class="<?= ($category == 'Dessert') ? 'active' : '' ?>">Dessert</a>
        <a href="index.php?category=Beverages" class="<?= ($category == 'Beverages') ? 'active' : '' ?>">Beverages</a>
    </div>
    <div class="nav-right">
        <a href="logout.php">🚪 Logout</a>
    </div>
</div>

<div class="dashboard">
    <div class="menu-section">
        <div class="menu-container">
        <?php while ($row = $menu->fetch_assoc()): ?>
            <div class="menu-card" data-category="<?= htmlspecialchars($row['category']) ?>">
                <img src="images/<?= htmlspecialchars($row['image']) ?>" alt="<?= htmlspecialchars($row['name']) ?>" onerror="this.src='images/placeholder.png'">
                <h3><?= htmlspecialchars($row['name']) ?></h3>
                <p><?= htmlspecialchars($row['description']) ?></p>
                <p class="price">₱<?= number_format($row['price'], 2) ?></p>
                <p>Status: <b style="color:<?= ($row['status'] === 'available') ? 'green' : 'red' ?>"><?= htmlspecialchars($row['status']) ?></b></p>

                <?php if ($row['category'] === 'Beverages'): ?>
                    <label><b>Sugar Level:</b></label>
                    <select id="sugar_level_<?= (int)$row['id'] ?>" class="sugar-select">
                        <option value="0%">0%</option>
                        <option value="25%">25%</option>
                        <option value="50%">50%</option>
                        <option value="75%">75%</option>
                        <option value="100%" selected>100%</option>
                    </select>
                <?php endif; ?>

                <button class="order-btn" onclick="addToOrder(<?= (int)$row['id'] ?>, '<?= htmlspecialchars($row['category']) ?>')" <?= ($row['status'] === 'available') ? '' : 'disabled' ?>>Add to Order</button>
            </div>
        <?php endwhile; ?>
        </div>
    </div>

    <div class="order-section">
        <h2>🛒 Current Order</h2>
        <div class="order-list">
            <p style="color:gray;">Loading order...</p>
        </div>
    </div>
</div>

<!-- Payment Modal -->
<div id="paymentModal" class="modal">
    <div class="modal-content">
        <h3>Select Payment Method</h3>
        <button class="payment-btn" onclick="selectPayment('Cash')">💵 Cash</button>
        <button class="payment-btn" onclick="selectPayment('Credit Card')">💳 Credit Card</button>
        <button class="payment-btn" onclick="selectPayment('Mobile Payment')">📱 Mobile Payment</button>
        <br>
        <button class="close-btn" onclick="closePaymentPopup()">Close</button>
    </div>
</div>

<!-- Cash Modal -->
<div id="cashModal" class="modal">
    <div class="modal-content">
        <h3>💵 Cash Payment</h3>
        <label><b>Enter Amount Tendered:</b></label>
        <input type="number" id="cashAmount" placeholder="Enter amount" style="width:90%;padding:8px;margin:10px 0;border-radius:6px;border:1px solid #ccc;">
        <button class="payment-btn" onclick="processCashPayment()">Confirm Payment</button>
        <button class="close-btn" onclick="closeCashModal()">Cancel</button>
    </div>
</div>

<!-- Receipt Modal -->
<div id="receiptModal" class="modal">
    <div class="modal-content">
        <h2>Receipt</h2>
        <div id="receiptContent"></div>
        <button class="close-btn" onclick="closeReceipt()">Close</button>
    </div>
</div>

<script>
function addToOrder(menuId, category) {
    let sugarLevel = "100%";
    if (category === "Beverages") {
        sugarLevel = document.getElementById('sugar_level_' + menuId)?.value || "100%";
    }
    fetch('add_to_order.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: 'menu_id=' + menuId + '&sugar_level=' + sugarLevel
    }).then(res => res.json()).then(data => {
        if (data.status === 'success') loadOrders();
        else alert(data.message);
    });
}

function loadOrders() {
    fetch('fetch_order.php').then(res => res.text()).then(html => {
        document.querySelector('.order-list').innerHTML = html;
    });
}

function openPaymentPopup() {
    document.getElementById('paymentModal').style.display = 'flex';
}
function closePaymentPopup() {
    document.getElementById('paymentModal').style.display = 'none';
}
function selectPayment(method) {
    closePaymentPopup();
    if (method === 'Cash') {
        document.getElementById('cashModal').style.display = 'flex';
    } else {
        showReceipt(method);
    }
}
function closeCashModal() {
    document.getElementById('cashModal').style.display = 'none';
}
function processCashPayment() {
    const amount = parseFloat(document.getElementById('cashAmount').value);
    if (isNaN(amount) || amount <= 0) {
        alert("Please enter a valid cash amount.");
        return;
    }
    closeCashModal();
    showReceipt('Cash', amount);
}

// ✅ UPDATED showReceipt (now shows sugar level properly)
function showReceipt(method, cashAmount = 0) {
    fetch('fetch_order.php').then(res => res.text()).then(html => {
        const temp = document.createElement('div');
        temp.innerHTML = html;
        const items = temp.querySelectorAll('.order-item');
        let content = '', total = 0;

        items.forEach(item => {
            const name = item.querySelector('.item-name')?.textContent.trim();
            const price = parseFloat(item.querySelector('.item-price')?.textContent.replace('₱','')) || 0;
            const qty = parseInt(item.querySelector('.qty-display')?.textContent) || 1;

            // ✅ Get sugar level if available
            const sugarInfo = item.querySelector('.sugar-info')?.textContent.trim() || '';

            const sub = price * qty;
            total += sub;

            content += `
                <div class='receipt-item'>
                    <span>${qty}x ${name}</span>
                    <span>₱${sub.toFixed(2)}</span>
                </div>
                ${sugarInfo ? `<div class='receipt-item' style='font-size:13px;color:#a33;margin-left:10px;'>${sugarInfo}</div>` : ''}
            `;
        });

        let change = '';
        if (method === 'Cash' && cashAmount > 0) {
            const diff = cashAmount - total;
            change = `<div class='receipt-item'><span>Cash:</span><span>₱${cashAmount.toFixed(2)}</span></div>
                      <div class='receipt-item'><span>Change:</span><span>₱${diff.toFixed(2)}</span></div>`;
        }

        document.getElementById('receiptContent').innerHTML = `
            <p><b>Payment Method:</b> ${method}</p>
            ${content}
            <div class='receipt-total'>Total: ₱${total.toFixed(2)}</div>
            ${change}
            <p style='text-align:center;margin-top:10px;'>Thank you for your order!</p>
        `;
        document.getElementById('receiptModal').style.display = 'flex';

        // ✅ Clear order after payment
        fetch('clear_order.php', { method: 'POST' })
            .then(() => setTimeout(loadOrders, 500));
    });
}

function closeReceipt() {
    document.getElementById('receiptModal').style.display = 'none';
}

document.addEventListener('DOMContentLoaded', loadOrders);

// ✅ Update Order function (gumagana na sa update_order.php mo)
function updateOrder(orderItemId, action) {
    fetch('update_order.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: 'order_item_id=' + orderItemId + '&action=' + action
    })
    .then(res => res.json())
    .then(data => {
        if (data.status === 'success') {
            loadOrders(); // refresh the current order display
        } else {
            alert(data.message || 'Error updating order.');
        }
    })
    .catch(err => console.error('Update error:', err));
}


</script>

<style>
body { font-family: Arial, sans-serif; background: #f4f4f4; margin: 0; padding: 0; }
header { background: maroon; color: white; padding: 15px; text-align: center; }
.top-buttons { display: flex; justify-content: space-between; align-items: center; background: #f4f4f4; padding: 10px 20px; }
.nav-center { flex: 1; display: flex; justify-content: center; flex-wrap: wrap; }
.nav-center a, .nav-right a {
    text-decoration: none; background: maroon; color: white; padding: 10px 15px; border-radius: 5px; margin: 0 5px; transition: 0.3s;
}
.nav-center a.active { background: darkred; font-weight: bold; }
.nav-center a:hover, .nav-right a:hover { background: darkred; }
.dashboard { display: flex; gap: 20px; padding: 20px; }
.menu-section { flex: 3; }
.order-section {
    flex: 1; background: white; padding: 20px; border-radius: 12px;
    box-shadow: 0 4px 6px rgba(0,0,0,0.1); height: fit-content; position: sticky; top: 20px;
}
.menu-container { display: flex; flex-wrap: wrap; gap: 20px; }
.menu-card {
    background: white; border-radius: 10px; padding: 15px; width: 220px;
    box-shadow: 0 4px 6px rgba(0,0,0,0.1); text-align: center;
}
.menu-card img { width: 100%; height: 150px; object-fit: cover; border-radius: 8px; }
.price { font-weight: bold; color: maroon; }
.order-btn {
    border-radius: 6px; font-weight: bold; cursor: pointer; display: block;
    padding: 8px; background: darkgreen; color: white; text-decoration: none; margin-top: 10px; border: none;
}
.modal {
    display: none; position: fixed; z-index: 999; left: 0; top: 0; width: 100%; height: 100%;
    background: rgba(0,0,0,0.5); justify-content: center; align-items: center;
}
.modal-content {
    background: white; padding: 20px; border-radius: 10px; width: 320px;
    text-align: center; box-shadow: 0 4px 10px rgba(0,0,0,0.2);
}
.modal-content h3 { margin-bottom: 15px; color: maroon; }
.payment-btn {
    display: block; width: 100%; padding: 10px; margin: 8px 0; border: none;
    border-radius: 6px; background: maroon; color: white; font-weight: bold; cursor: pointer;
}
.payment-btn:hover { background: darkred; }
.close-btn {
    background: #555; color: white; border: none; padding: 8px 12px;
    border-radius: 5px; cursor: pointer;
}
.close-btn:hover { background: #333; }

#receiptModal .modal-content { width: 360px; text-align: left; }
#receiptModal h2 { text-align: center; color: maroon; }
.receipt-item { display: flex; justify-content: space-between; margin: 3px 0; }
.receipt-total {
    border-top: 1px solid #ccc; margin-top: 10px; padding-top: 5px; font-weight: bold;
}
</style>
</body>
</html>
