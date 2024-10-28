<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="initial-scale=1, width=device-width" />
    <link rel="stylesheet" href="../assets/css/style.css" />
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Smooch Sans:wght@700&display=swap" />
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;700&display=swap" />
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600&display=swap" />
</head>

<body>
    <?php include '../header.php'; ?>
    <div class="login-main">
        <div class="account-page">
            <input type="radio" id="show-login" name="toggle" checked />
            <input type="radio" id="show-register" name="toggle" />
            <div class="account-form">
                <b id="welcome">Welcome</b>
                <div id="caption">We are glad to see you back with us</div>
                <div class="account-credential">
                    <div id="login-register-changer">
                        <label for="show-login">Login</label>
                        <label for="show-register">Register</label>
                    </div>
                    <form class="register">
                        <div class="credential-form">
                            <img alt="" src="../assets/img/user.svg" />
                            <input type="text" placeholder="Username" />
                        </div>
                        <div class="credential-form">
                            <img alt="" src="../assets/img/email.svg" />
                            <input type="email" placeholder="Email" />
                        </div>
                        <div class="credential-form">
                            <img alt="" src="../assets/img/password.svg" />
                            <input type="password" placeholder="Password" />
                        </div>
                        <div id="terms">
                            <input type="checkbox" />
                            <span>I agree with
                                <a href="/" id="privacy-link">Privacy Policy</a>
                                and
                                <a href="/" id="terms-link">Terms of Service</a>
                            </span>
                        </div>
                    </form>
                    <form class="login">
                        <div class="credential-form">
                            <img alt="" src="../assets/img/user.svg" />
                            <input type="text" placeholder="Username" />
                        </div>
                        <div class="credential-form">
                            <img alt="" src="../assets/img/password.svg" />
                            <input type="password" placeholder="Password" />
                        </div>
                        <div class="remember-and-forgot">
                            <div id="remember-me">
                                <input type="checkbox" />
                                <span>Remember Me</span>
                            </div>
                            <div>
                                <a href="" id="forgot-password">Forgot Password?</a>
                            </div>
                        </div>
                    </form>
                    <button class="next-button">Submit</button>
                </div>
                <span id="others-form"><b>Login</b> with Others</span>
                <div class="glogin">
                    <img class="google-1-icon" alt="" src="../assets/img/google.png" height="24px" />
                    <div>
                        <span>Login with </span>
                        <b>google</b>
                    </div>
                </div>
            </div>
        </div>
        <div class="sign-up-wp">
            <img loading="lazy" src="../assets/img/wp2.png" alt="background" height="400px" />
        </div>
    </div>
    <?php include '../footer.php'; ?>
</body>

</html>