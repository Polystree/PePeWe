<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

include 'database.php';

$loginFail = false;
$registerFail = false;
$registerSuccess = false;
$captchaFail = false;

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['login'])) {
        handleLogin($connect);
    } elseif (isset($_POST['register'])) {
        handleRegistration($connect);
    }
}

function handleLogin($connect)
{
    global $loginFail, $captchaFail;
    $username = $_POST['username'];
    $password = $_POST['password'];
    $captchaAnswer = $_POST['captcha'];

    if ($captchaAnswer != $_SESSION['captcha_answer']) {
        $captchaFail = true;
        return;
    }

    $stmt = $connect->prepare("SELECT * FROM users WHERE username=? AND password=?");
    $stmt->bind_param("ss", $username, $password);
    $stmt->execute();
    $loginResult = $stmt->get_result();

    if ($loginResult->num_rows > 0) {
        $_SESSION['username'] = $username;
        if (isset($_SESSION['username']) && $_SESSION['username'] == 'admin') {
            header('Location: /admin');
            exit();
        } else {
            header('Location: /');
            exit();
        }
    } else {
        $loginFail = true;
    }
}

function handleRegistration($connect)
{
    global $registerFail, $registerSuccess, $captchaFail;
    $username = $_POST['username'];
    $email = $_POST['email'];
    $password = $_POST['password'];

    $stmt = $connect->prepare("SELECT * FROM users WHERE username=? OR email=?");
    $stmt->bind_param("ss", $username, $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $registerFail = true;
    } else {
        $stmt = $connect->prepare("INSERT INTO users (username, email, password) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $username, $email, $password);
        $stmt->execute();
        $registerSuccess = true;
    }
}
?>
<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="initial-scale=1, width=device-width" />
    <title>iniGadget</title>
    <link rel="icon" type="image/x-icon" href="../assets/img/logo-light.svg" />
    <link rel="stylesheet" href="../assets/css/style.css" />
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Smooch+Sans:wght@700&display=swap" />
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;700&display=swap" />
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600&display=swap" />
</head>

<body>
    <?php include '../header.php'; ?>
    <div class="main">
        <div class="login-main">
            <div class="account-page">
                <input type="radio" id="show-login" name="toggle" <?php if (!$registerFail)
                    echo 'checked'; ?> />
                <input type="radio" id="show-register" name="toggle" <?php if ($registerFail)
                    echo 'checked'; ?> />
                <div class="account-form">
                    <b id="welcome">Welcome</b>
                    <div id="caption">We are glad to see you back with us</div>
                    <div class="account-credential">
                        <?php
                        $number1 = rand(1, 10);
                        $number2 = rand(1, 10);
                        $captchaQuestion = "$number1 + $number2";
                        $_SESSION['captcha_answer'] = $number1 + $number2; ?>
                        <div id="login-register-changer">
                            <label for="show-login">Login</label>
                            <label for="show-register">Register</label>
                        </div>
                        <form class="register" method="POST">
                            <div class="credential-form">
                                <img alt="" src="../assets/img/user.svg" />
                                <input type="text" placeholder="Username" name="username" required />
                            </div>
                            <div class="credential-form">
                                <img alt="" src="../assets/img/email.svg" />
                                <input type="email" placeholder="Email" name="email" required />
                            </div>
                            <div class="credential-form">
                                <img alt="" src="../assets/img/password.svg" />
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
                            <div class="captcha">
                                <span>Captcha</span>
                                <div id="captcha">
                                    <label for='captcha' id="captcha-question"><?php echo $captchaQuestion; ?></label>
                                    <input type='number' name='captcha' id="captcha-answer" required>
                                </div>
                                <?php if ($captchaFail): ?>
                                    <div id="captcha-fail" style="color:red">Invalid Captcha!</div>
                                <?php elseif ($registerFail): ?>
                                    <div id="register-fail" style="color:red">Email or username already exist!</div>
                                <?php elseif ($registerSuccess): ?>
                                    <div id="register-success">Registration Success</div>
                                <?php endif; ?>
                            </div>
                            <button class="next-button" name="register">Submit</button>
                        </form>
                        <form class="login" method="POST">
                            <div class="credential-form">
                                <img alt="" src="../assets/img/user.svg" />
                                <input type="text" placeholder="Username" name="username" required />
                            </div>
                            <div class="credential-form">
                                <img alt="" src="../assets/img/password.svg" />
                                <input type="password" placeholder="Password" name="password" required />
                            </div>
                            <div class="remember-and-forgot">
                                <div id="remember-me">
                                    <input type="checkbox" />
                                    <span>Remember Me</span>
                                </div>
                                <div>
                                    <a href="http://localhost/login/recover.php" id="forgot-password">Forgot Password?</a>
                                </div>
                            </div>
                            <div class="captcha">
                                <span>Captcha</span>
                                <div id="captcha">
                                    <label for='captcha' id="captcha-question"><?php echo $captchaQuestion; ?></label>
                                    <input type='text' name='captcha' id="captcha-answer" required>
                                </div>
                                <?php if ($captchaFail): ?>
                                    <div id="captcha-fail" style="color:red">Invalid Captcha!</div>
                                <?php elseif ($loginFail): ?>
                                    <div id="login-fail" style="color:red">Invalid username or password!</div>
                                <?php endif; ?>
                            </div>
                            <button class="next-button" name="login">Submit</button>
                        </form>
                    </div>
                    <span id="others-form"><b>Login</b> with Others</span>
                    <a href="glogin.php" class="glogin">
                        <img class="google-1-icon" alt="" src="../assets/img/google.png" height="24px" />
                        <div>
                            <span>Login with </span>
                            <b>Google</b>
                        </div>
                    </a>
                </div>
            </div>
            <div class="sign-up-wp">
                <img loading="lazy" src="../assets/img/logo.svg" alt="background" height="400px" />
            </div>
        </div>
    </div>
</body>

</html>