function deleteProduct(productId) {
    if (!confirm('Are you sure you want to delete this product?')) return;
    
    fetch('/admin/api/products.php', {
        method: 'DELETE',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({ id: productId })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload();
        } else {
            alert('Error deleting product: ' + data.message);
        }
    });
}

function editProduct(productId) {
    window.location.href = `add-product.php?productId=${productId}`;
}
