<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Initialize captcha numbers if not set
if (!isset($_SESSION['captcha_numbers'])) {
    $number1 = rand(1, 10);
    $number2 = rand(1, 10);
    $_SESSION['captcha_numbers'] = [
        'number1' => $number1,
        'number2' => $number2,
        'sum' => $number1 + $number2
    ];
} else {
    $number1 = $_SESSION['captcha_numbers']['number1'];
    $number2 = $_SESSION['captcha_numbers']['number2'];
}

// Initialize error and success variables if not set
$errors = $_SESSION['errors'] ?? [];
$success = $_SESSION['success'] ?? false;

// Clear session messages after use
unset($_SESSION['errors']);
unset($_SESSION['success']);
?>
<meta name="theme-color" content="#d8602b">
<link rel="stylesheet" href="/assets/css/login.css">

<div class="login-main">
    <div class="account-page">
        <input type="radio" id="show-login" name="toggle" <?php if (!isset($errors['register'])) echo 'checked'; ?> />
        <input type="radio" id="show-register" name="toggle" <?php if (isset($errors['register'])) echo 'checked'; ?> />
        <div class="account-form">
            <b id="welcome">Welcome</b>
            <div id="caption">We are glad to see you back with us</div>
            <div class="account-credential">
                <div id="login-register-changer">
                    <label for="show-login">Login</label>
                    <label for="show-register">Register</label>
                </div>

                <!-- Register Form -->
                <form class="register" method="POST" action="/login/register.php">
                    <div class="credential-form">
                        <img alt="" src="/assets/img/user.svg" />
                        <input type="text" placeholder="Username" name="username" required />
                    </div>
                    <div class="credential-form">
                        <img alt="" src="/assets/img/email.svg" />
                        <input type="email" placeholder="Email" name="email" required />
                    </div>
                    <div class="credential-form">
                        <img alt="" src="/assets/img/password.svg" />
                        <input type="password" placeholder="Password" name="password" required />
                    </div>
                    <div id="terms">
                        <input type="checkbox" required />
                        <span>I agree with
                            <a href="/" id="privacy-link">Privacy Policy</a>
                            and
                            <a href="/" id="terms-link">Terms of Service</a>
                        </span>
                    </div>
                    
                    <!-- Add captcha to register form -->
                    <div class="captcha">
                        <div id="captcha">
                            <label for="register-captcha" id="captcha-question"><?php echo $number1 ?> + <?php echo $number2 ?></label>
                            <input type="number" name="captcha" id="register-captcha" required>
                        </div>
                    </div>

                    <?php if (isset($errors['register'])): ?>
                        <div class="error-message">
                            <?php 
                            if (is_array($errors['register'])) {
                                echo implode('<br>', array_map('htmlspecialchars', $errors['register']));
                            } else {
                                echo htmlspecialchars($errors['register']);
                            }
                            ?>
                        </div>
                    <?php endif; ?>
                    <?php if ($success): ?>
                        <div class="success-message">Registration successful! Please login.</div>
                    <?php endif; ?>
                    <button class="next-button" name="register">Submit</button>
                </form>

                <!-- Login Form -->
                <form class="login" method="POST" action="/login/login.php">
                    <div class="credential-form">
                        <img alt="" src="/assets/img/user.svg" />
                        <input type="text" placeholder="Username" name="username" required />
                    </div>
                    <div class="credential-form">
                        <img alt="" src="/assets/img/password.svg" />
                        <input type="password" placeholder="Password" name="password" required />
                    </div>
                    <div class="remember-and-forgot">
                        <div id="remember-me">
                            <input type="checkbox" name="remember_me" id="remember-me-checkbox" />
                            <span>Remember Me</span>
                        </div>
                        <div>
                            <a href="/login/recover.php" id="forgot-password">Forgot Password?</a>
                        </div>
                    </div>
                    <div class="captcha">
                        <div id="captcha">
                            <label for="captcha" id="captcha-question"><?php echo $number1 ?> + <?php echo $number2 ?></label>
                            <input type="number" name="captcha" id="captcha-answer" required>
                        </div>
                    </div>
                    <?php if (isset($errors['login'])): ?>
                        <div class="error-message"><?php echo htmlspecialchars($errors['login']); ?></div>
                    <?php endif; ?>
                    <button class="next-button" name="login">Submit</button>
                </form>
            </div>

            <span id="others-form"><b>Login</b> with Others</span>
            <a href="/login/glogin.php" class="glogin">
                <img class="google-1-icon" alt="" src="/assets/img/google.png" height="24px" />
                <div>
                    <span>Login with </span>
                    <b>Google</b>
                </div>
            </a>
        </div>
    </div>
    <div class="sign-up-wp" aria-hidden="true">
        <img loading="lazy" src="/assets/img/logo.svg" alt="" height="400px" />
        <div class="background-shapes">
            <div class="shape-1"></div>
            <div class="shape-2"></div>
            <div class="shape-3"></div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const tabs = document.querySelectorAll('.tab-btn');
    const contents = document.querySelectorAll('.tab-content');

    tabs.forEach(tab => {
        tab.addEventListener('click', () => {
            const target = tab.dataset.tab;
            
            tabs.forEach(t => t.classList.remove('active'));
            tab.classList.add('active');
            
            contents.forEach(content => {
                content.id === `${target}-tab` 
                    ? content.classList.remove('hidden')
                    : content.classList.add('hidden');
            });
        });
    });

    <?php if (isset($errors['register']) || $success): ?>
        document.querySelector('[data-tab="register"]').click();
    <?php endif; ?>
});
</script>