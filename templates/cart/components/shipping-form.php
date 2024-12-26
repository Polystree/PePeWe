<?php
$userId = $_SESSION['userId'];
$stmt = $connect->prepare("SELECT * FROM user_addresses WHERE user_id = ? ORDER BY is_default DESC, created_at DESC");
$stmt->bind_param("i", $userId);
$stmt->execute();
$addresses = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
?>

<div class="shipping-box">
    <h3>Shipping Details</h3>
    <div class="form-group">
        <label for="savedAddress">Shipping Address</label>
        <select id="savedAddress" class="address-select" required>
            <option value="">Select a saved address</option>
            <?php foreach ($addresses as $index => $address): ?>
                <option value="<?php echo $address['id']; ?>" 
                        data-recipient="<?php echo htmlspecialchars($address['recipient_name']); ?>"
                        data-phone="<?php echo htmlspecialchars($address['phone']); ?>"
                        data-address="<?php echo htmlspecialchars($address['address']); ?>"
                        data-city="<?php echo htmlspecialchars($address['city']); ?>"
                        data-postal="<?php echo htmlspecialchars($address['postal_code']); ?>"
                        data-label="<?php echo htmlspecialchars($address['address_label']); ?>"
                        data-default="<?php echo $address['is_default']; ?>"
                        <?php echo ($index === 0) ? 'selected' : ''; ?>>
                    <?php echo htmlspecialchars($address['address_label'] . ' - ' . $address['recipient_name'] . ' (' . $address['phone'] . ') - ' . $address['address'] . ', ' . $address['city'] . ' ' . $address['postal_code']); ?>
                    <?php echo $address['is_default'] ? ' [Default]' : ''; ?>
                </option>
            <?php endforeach; ?>
            <option value="new">+ Add New Address</option>
        </select>
        <div class="address-actions" style="display: none;">
            <button type="button" id="setDefaultAddress" class="btn-small btn-primary" style="display: none;">Set as Default</button>
            <button type="button" id="editAddress" class="btn-small">Edit</button>
        </div>
        <small class="help-text">Select a saved address or add a new one</small>
    </div>
    
    <div class="address-details" style="display: none;">
        <div class="form-group">
            <label for="addressLabel">Address Label</label>
            <input type="text" id="addressLabel" placeholder="e.g. Home, Office, etc." required />
        </div>

        <div class="form-group">
            <label for="recipientName">Recipient Name</label>
            <input type="text" id="recipientName" required />
        </div>
        
        <div class="form-group">
            <label for="phoneNumber">Phone Number</label>
            <input type="tel" id="phoneNumber" required />
        </div>
        
        <div class="form-group">
            <label for="address">Full Address</label>
            <textarea id="address" rows="3" required></textarea>
        </div>
        
        <div class="form-group">
            <label for="city">City</label>
            <input type="text" id="city" required />
        </div>

        <div class="form-group">
            <label for="postalCode">Postal Code</label>
            <input type="text" id="postalCode" required />
        </div>
        <div class="address-form-actions">
            <button type="button" id="cancelAddress" class="btn-small">Cancel</button>
            <button type="button" id="saveAddress" class="btn-small btn-primary">Save Address</button>
        </div>
    </div>

    <div class="form-group">
        <label for="shipping">Shipping Method</label>
        <select id="shipping" class="shipping-select" required>
            <option value="">Select shipping method</option>
            <optgroup label="JNE">
                <option value="jne_reg" data-price="12000" data-service="JNE Reguler (2-3 days)">JNE Reguler (2-3 days) - Rp 12.000</option>
                <option value="jne_yes" data-price="24000" data-service="JNE YES (1 day)">JNE YES (1 day) - Rp 24.000</option>
            </optgroup>
            <optgroup label="J&T Express">
                <option value="jnt_reg" data-price="11000" data-service="J&T Reguler (2-3 days)">J&T Reguler (2-3 days) - Rp 11.000</option>
                <option value="jnt_exp" data-price="22000" data-service="J&T Express (1 day)">J&T Express (1 day) - Rp 22.000</option>
            </optgroup>
            <optgroup label="SiCepat">
                <option value="sicepat_reg" data-price="11000" data-service="SiCepat Reguler (2-3 days)">SiCepat Reguler (2-3 days) - Rp 11.000</option>
                <option value="sicepat_best" data-price="23000" data-service="SiCepat BEST (1 day)">SiCepat BEST (1 day) - Rp 23.000</option>
            </optgroup>
        </select>
    </div>
</div>

<script>
const savedAddressSelect = document.getElementById('savedAddress');
const addressDetails = document.querySelector('.address-details');
const addressActions = document.querySelector('.address-actions');

function populateAddressFields(option) {
    if (!option || option.value === '' || option.value === 'new') return;
    
    document.getElementById('recipientName').value = option.dataset.recipient;
    document.getElementById('phoneNumber').value = option.dataset.phone;
    document.getElementById('address').value = option.dataset.address;
    document.getElementById('city').value = option.dataset.city;
    document.getElementById('postalCode').value = option.dataset.postal;
    document.getElementById('addressLabel').value = option.dataset.label;
}

savedAddressSelect.addEventListener('change', function() {
    const isNew = this.value === 'new';
    const hasSelection = this.value !== '';
    const selectedOption = this.selectedOptions[0];
    
    addressDetails.style.display = isNew ? 'block' : 'none';
    addressActions.style.display = (!isNew && hasSelection) ? 'flex' : 'none';
    
    if (!isNew && hasSelection) {
        populateAddressFields(selectedOption);
        const isDefault = selectedOption.dataset.default === '1';
        document.getElementById('setDefaultAddress').style.display = isDefault ? 'none' : 'inline-block';
    } else if (isNew) {
        ['recipientName', 'phoneNumber', 'address', 'city', 'postalCode', 'addressLabel'].forEach(id => {
            document.getElementById(id).value = '';
        });
    }
});

document.getElementById('editAddress').addEventListener('click', function() {
    addressDetails.style.display = 'block';
    populateAddressFields(savedAddressSelect.selectedOptions[0]);
});

document.getElementById('cancelAddress').addEventListener('click', function() {
    addressDetails.style.display = 'none';
    if (savedAddressSelect.value === 'new') {
        savedAddressSelect.value = '';
    }
});

document.getElementById('saveAddress').addEventListener('click', async function() {
    const formData = {
        recipient_name: document.getElementById('recipientName').value,
        phone: document.getElementById('phoneNumber').value,
        address: document.getElementById('address').value,
        city: document.getElementById('city').value,
        postal_code: document.getElementById('postalCode').value,
        address_label: document.getElementById('addressLabel').value,
        address_id: savedAddressSelect.value !== 'new' ? savedAddressSelect.value : null
    };

    for (let key in formData) {
        if (!formData[key] && key !== 'address_id') {
            alert('Please fill in all fields');
            return;
        }
    }

    try {
        const response = await fetch('/cart/save_address.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(formData)
        });

        const data = await response.json();
        if (data.success) {
            const displayText = `${formData.address_label} - ${formData.recipient_name} (${formData.phone}) - ${formData.address}, ${formData.city} ${formData.postal_code}${data.is_default ? ' [Default]' : ''}`;
            if (formData.address_id) {
                const option = savedAddressSelect.querySelector(`option[value="${formData.address_id}"]`);
                option.textContent = displayText;
                Object.keys(formData).forEach(key => {
                    option.dataset[key.replace('_', '')] = formData[key];
                });
            } else {
                const option = document.createElement('option');
                option.value = data.address_id;
                option.textContent = displayText;
                Object.keys(formData).forEach(key => {
                    option.dataset[key.replace('_', '')] = formData[key];
                });
                
                const newAddressOption = savedAddressSelect.querySelector('option[value="new"]');
                savedAddressSelect.insertBefore(option, newAddressOption);
            }

            savedAddressSelect.value = data.address_id;
            addressDetails.style.display = 'none';
            addressActions.style.display = 'flex';
            
            alert('Address saved successfully');
        } else {
            throw new Error(data.message || 'Failed to save address');
        }
    } catch (error) {
        alert(error.message || 'Error saving address');
    }
});

document.getElementById('setDefaultAddress').addEventListener('click', async function() {
    const addressId = savedAddressSelect.value;
    if (!addressId || addressId === 'new') return;

    try {
        const response = await fetch('/cart/set_default_address.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ address_id: addressId })
        });

        const data = await response.json();
        if (data.success) {
            const options = savedAddressSelect.options;
            for (let i = 0; i < options.length; i++) {
                const option = options[i];
                if (option.value !== 'new') {
                    option.textContent = option.textContent.replace(' [Default]', '');
                    option.dataset.default = '0';
                }
            }
            const selectedOption = savedAddressSelect.selectedOptions[0];
            selectedOption.textContent += ' [Default]';
            selectedOption.dataset.default = '1';
            
            this.style.display = 'none';
        } else {
            throw new Error(data.message || 'Failed to set default address');
        }
    } catch (error) {
        alert(error.message || 'Error setting default address');
    }
});

document.getElementById('shipping').addEventListener('change', function() {
    const selectedOption = this.selectedOptions[0];
    const shippingPrice = selectedOption ? parseFloat(selectedOption.dataset.price) : 0;
    const formattedPrice = shippingPrice ? 'Rp ' + shippingPrice.toLocaleString('id-ID') : '-';
    
    document.getElementById('shippingRow').querySelector('span:last-child').textContent = formattedPrice;

    const currentDiscount = parseFloat(document.getElementById('currentDiscount').value);
    updateTotal(currentDiscount);
});

document.addEventListener('DOMContentLoaded', function() {
    if (savedAddressSelect.value && savedAddressSelect.value !== 'new') {
        addressActions.style.display = 'flex';
        populateAddressFields(savedAddressSelect.selectedOptions[0]);
    }
});
</script>