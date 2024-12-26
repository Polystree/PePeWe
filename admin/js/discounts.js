function showAddDiscountModal() {
    document.getElementById('modalTitle').textContent = 'Add Discount';
    document.getElementById('discountForm').reset();
    document.getElementById('discount_id').value = '';
    
    const today = new Date().toISOString().slice(0, 16);
    document.getElementById('start_date').min = today;
    document.getElementById('end_date').min = today;
    
    document.getElementById('discountModal').style.display = 'block';
}

function editDiscount(discount) {
    document.getElementById('modalTitle').textContent = 'Edit Discount';
    document.getElementById('discount_id').value = discount.id;
    document.getElementById('product_id').value = discount.product_id;
    document.getElementById('discount_percent').value = discount.discount_percent;
    document.getElementById('is_flash_sale').value = discount.is_flash_sale;
    document.getElementById('start_date').value = discount.start_date.slice(0, 16);
    document.getElementById('end_date').value = discount.end_date.slice(0, 16);
    
    document.getElementById('discountModal').style.display = 'block';
}

function deleteDiscount(id) {
    if (!confirm('Are you sure you want to delete this discount?')) return;
    
    fetch('/admin/api/discounts.php', {
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
            alert('Error deleting discount: ' + data.message);
        }
    });
}
