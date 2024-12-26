<div id="couponModal" class="modal">
    <div class="modal-content">
        <span class="close" onclick="closeModal('couponModal')">&times;</span>
        <h2 id="couponModalTitle">Add New Coupon</h2>
        <form id="couponForm">
            <input type="hidden" name="couponId" id="couponId">
            <div class="form-group">
                <label for="code">Coupon Code</label>
                <input type="text" id="code" name="code" required 
                       pattern="[A-Za-z0-9]+" 
                       title="Only letters and numbers allowed"
                       placeholder="Enter coupon code">
            </div>
            <div class="form-group">
                <label for="discount">Discount Percentage</label>
                <input type="number" id="discount" name="discount" 
                       min="1" max="100" required
                       placeholder="Enter discount percentage">
            </div>
            <div class="form-group">
                <label for="expiry_date">Expiry Date</label>
                <input type="date" id="expiry_date" name="expiry_date" required>
            </div>
            <button type="submit" class="btn btn-primary">Save Coupon</button>
        </form>
    </div>
</div>
