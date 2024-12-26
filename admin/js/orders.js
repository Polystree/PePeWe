function viewOrderDetails(orderNumber) {
    fetch(`/admin/api/orders.php?order_number=${orderNumber}`)
        .then(response => response.json())
        .then(data => {
            const content = document.getElementById('orderDetailsContent');
            content.innerHTML = `
                <div class="order-header-info">
                    <div class="info-group">
                        <label>Order Number:</label>
                        <span>${data.order_number}</span>
                    </div>
                    <div class="info-group">
                        <label>Date:</label>
                        <span>${new Date(data.created_at).toLocaleString()}</span>
                    </div>
                </div>
                
                <div class="order-customer-info">
                    <h3>Customer Information</h3>
                    <div class="info-group">
                        <label>Name:</label>
                        <span>${data.customer.username}</span>
                    </div>
                    <div class="info-group">
                        <label>Email:</label>
                        <span>${data.customer.email}</span>
                    </div>
                    <div class="info-group">
                        <label>Shipping Address:</label>
                        <span>${data.shipping_address}</span>
                    </div>
                </div>
                
                <div class="order-items-details">
                    <h3>Items Ordered</h3>
                    ${data.items.map(item => `
                        <div class="order-detail-item">
                            <img src="/${item.image_path}" alt="${item.name}">
                            <div class="item-details">
                                <h4>${item.name}</h4>
                                <p>Quantity: ${item.quantity}</p>
                                <p>Price: Rp ${parseInt(item.price).toLocaleString()}</p>
                                <p>Subtotal: Rp ${(item.price * item.quantity).toLocaleString()}</p>
                            </div>
                        </div>
                    `).join('')}
                </div>
                
                <div class="order-summary">
                    <h3>Order Summary</h3>
                    <div class="info-group">
                        <label>Subtotal:</label>
                        <span>Rp ${parseInt(data.total_amount - data.shipping_cost + data.discount_amount).toLocaleString()}</span>
                    </div>
                    ${data.shipping_cost > 0 ? `
                    <div class="info-group">
                        <label>Shipping:</label>
                        <span>Rp ${parseInt(data.shipping_cost).toLocaleString()}</span>
                    </div>
                    ` : ''}
                    ${data.discount_amount > 0 ? `
                    <div class="info-group">
                        <label>Discount:</label>
                        <span>-Rp ${parseInt(data.discount_amount).toLocaleString()}</span>
                    </div>
                    ` : ''}
                    <div class="info-group total">
                        <label>Total Amount:</label>
                        <span class="total-amount">Rp ${parseInt(data.total_amount).toLocaleString()}</span>
                    </div>
                </div>
            `;
            
            document.getElementById('orderDetailsModal').style.display = 'block';
        });
}

// Modal close functionality
document.querySelectorAll('.modal .close').forEach(close => {
    close.onclick = function() {
        this.closest('.modal').style.display = 'none';
    }
});
