<?php
session_start();
$title = 'Payment Failed - iniGadget';
include __DIR__ . '/../templates/header.php';
?>
<link rel="stylesheet" href="/assets/css/payment.css">
<div class="container payment-result">
    <div class="error-box">
        <div class="icon">✕</div>
        <h1>Payment Failed</h1>
        <p>We're sorry, but there was a problem processing your payment.</p>
        
        <div class="error-details">
            <h3>What Can You Do?</h3>
            <ul>
                <li>Check your payment details and try again</li>
                <li>Make sure you have sufficient funds</li>
                <li>Try a different payment method</li>
                <li>Contact your bank if the problem persists</li>
            </ul>
        </div>

        <div class="actions">
            <a href="/cart" class="btn">Try Again</a>
            <a href="/contact" class="btn btn-secondary">Need Help?</a>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../templates/footer.php'; ?>
