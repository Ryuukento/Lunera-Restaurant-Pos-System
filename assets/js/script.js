// Example JS for "Add to Order" (front-end only demo)
document.addEventListener("DOMContentLoaded", () => {
    const buttons = document.querySelectorAll(".add-btn");
    buttons.forEach(btn => {
        btn.addEventListener("click", () => {
            alert("Item ID " + btn.dataset.id + " added to order!");
        });
    });
});
// Open payment popup
function openPaymentPopup(total) {
    document.getElementById("paymentModal").style.display = "flex";
    document.getElementById("paymentTotal").innerText = "Total: ₱" + total.toFixed(2);
}

// Close payment popup
function closePaymentPopup() {
    document.getElementById("paymentModal").style.display = "none";
}

// Choose payment method
function choosePayment(method) {
    closePaymentPopup();

    if (method === "Cash") {
        document.getElementById("cashModal").style.display = "flex";
    } else {
        alert(method + " payment selected. (Pwede mo pa dagdagan ng form dito)");
    }
}

// Close cash modal
function closeCashModal() {
    document.getElementById("cashModal").style.display = "none";
}

// Confirm cash payment
function confirmCashPayment() {
    const cashTendered = parseFloat(document.getElementById("cashTendered").value);
    const totalText = document.getElementById("paymentTotal").innerText;
    const total = parseFloat(totalText.replace("Total: ₱", "").replace(",", ""));

    if (isNaN(cashTendered) || cashTendered < total) {
        alert("Invalid amount. Please enter cash greater than or equal to total.");
        return;
    }

    const change = cashTendered - total;
    const changeText = document.getElementById("cashChangeText");

    changeText.innerText = "Change: ₱" + change.toFixed(2);
    changeText.style.display = "block";

    // Example: dito mo pwedeng i-save payment sa database gamit AJAX
    // savePayment('Cash', cashTendered, change);

    alert("Cash Payment Successful!");
    closeCashModal();
}
