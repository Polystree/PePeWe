<div id="discountModal" class="modal">
    <div class="modal-content">
        <span class="close">&times;</span>
        <h2 id="modalTitle">Add Discount</h2>
        <form id="discountForm" method="POST" action="manage-discount.php">
            <input type="hidden" name="discount_id" id="discount_id">
            <div class="form-group">
                <label for="product_id">Product</label>
                <select name="product_id" id="product_id" required>
                    <?php
                    $stmt = $connect->prepare("SELECT productId, name FROM products WHERE status = 'active'");
                    $stmt->execute();
                    $products = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
                    foreach ($products as $product) {
                        echo "<option value='{$product['productId']}'>{$product['name']}</option>";
                    }
                    ?>
                </select>
            </div>
            <div class="form-group">
                <label for="discount_percent">Discount Percentage</label>
                <input type="number" name="discount_percent" id="discount_percent" min="1" max="99" required>
            </div>
            <div class="form-group">
                <label for="is_flash_sale">Sale Type</label>
                <select name="is_flash_sale" id="is_flash_sale">
                    <option value="0">Regular Discount</option>
                    <option value="1">Flash Sale</option>
                </select>
            </div>
            <div class="form-group">
                <label for="start_date">Start Date</label>
                <input type="datetime-local" name="start_date" id="start_date" required>
            </div>
            <div class="form-group">
                <label for="end_date">End Date</label>
                <input type="datetime-local" name="end_date" id="end_date" required>
            </div>
            <button type="submit" class="btn-primary">Save Discount</button>
        </form>
    </div>
</div>
