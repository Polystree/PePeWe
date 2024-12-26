function showAddCouponModal() {
    document.getElementById('couponForm').reset();
    document.getElementById('couponId').value = '';
    document.getElementById('couponModal').style.display = 'block';
    
    const today = new Date().toISOString().split('T')[0];
    document.getElementById('expiry_date').min = today;
}

function editCoupon(coupon) {
    document.getElementById('couponId').value = coupon.id;
    document.getElementById('code').value = coupon.code;
    document.getElementById('discount').value = coupon.discount;
    document.getElementById('expiry_date').value = coupon.expiry_date;
    document.getElementById('couponModal').style.display = 'block';
}

function deleteCoupon(id) {
    if (!confirm('Are you sure you want to delete this coupon?')) return;
    
    fetch('/admin/api/coupons.php', {
        method: 'DELETE',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({ id })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload();
        } else {
            alert('Error deleting coupon: ' + data.message);
        }
    });
}

// Form submission handler
document.getElementById('couponForm')?.addEventListener('submit', function(e) {
    e.preventDefault();
    const formData = new FormData(this);
    const method = formData.get('couponId') ? 'PUT' : 'POST';
    
    fetch('/admin/api/coupons.php', {
        method,
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify(Object.fromEntries(formData))
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload();
        } else {
            alert(data.message || 'Error saving coupon');
        }
    });
});
