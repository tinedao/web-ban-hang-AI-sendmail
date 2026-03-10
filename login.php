<?php
require 'config/database.php';
$pageTitle = "Login";

$error = '';
$infoMessage = '';

// Friendly Auth Messages
$messages = [
    'auth' => 'Please log in to continue.',
    'cart' => 'Please log in to add items to your cart.',
    'checkout' => 'Please log in to proceed to checkout.',
    'logout' => 'You have been logged out successfully.'
];
if (isset($_GET['msg']) && array_key_exists($_GET['msg'], $messages)) {
    $infoMessage = $messages[$_GET['msg']];
}

// Redirect if already logged in
if (isLogin()) {
    redirect('index.php');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $phone = trim($_POST['phone']);
    $password = $_POST['password'];

    if (empty($phone) || empty($password)) {
        $error = "Please enter your phone number and password.";
    } else {
        // Check user
        $users = getData('users', ['where' => ['phone' => $phone], 'limit' => 1]);
        
        if ($users && count($users) > 0) {
            $user = $users[0];
            if (verifyPassword($password, $user['password'])) {
                // Login Success
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['name'] = !empty($user['name']) ? $user['name'] : $user['phone'];
                $_SESSION['flash_message'] = [
                    'type' => 'success',
                    'message' => 'Đăng nhập thành công!'
                ];
                redirect('index.php');
            } else {
                $error = "Incorrect password.";
            }
        } else {
            $error = "Account not found with this phone number.";
        }
    }
}

include 'includes/header.php';
?>

<div class="auth-wrapper fade-in-page">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-xl-10 col-lg-11">
                <div class="auth-card-shadow">
                    <div class="row g-0">
                        <!-- Left: Image (Hidden on Mobile) -->
                        <div class="col-lg-6 auth-image-col">
                            <div class="auth-overlay-gradient"></div>
                            <div class="position-absolute bottom-0 start-0 p-5 text-white z-2">
                                <h3 class="font-heading mb-2">Welcome Back</h3>
                                <p class="small opacity-75 mb-0" style="letter-spacing: 0.5px;">Sign in to access your event souvenirs and seasonal collections.</p>
                            </div>
                        </div>

                        <!-- Right: Form -->
                        <div class="col-lg-6 auth-form-col">
                            <div class="text-center mb-5">
                                <h2 class="font-heading mb-2">Member Login</h2>
                                <p class="text-muted small">Please enter your details</p>
                            </div>

                            <?php include 'includes/alert.php'; ?>

                            <form action="" method="POST">
                                <div class="mb-4">
                                    <label for="phone" class="form-label-luxury">Phone Number</label>
                                    <input type="text" class="form-control form-control-luxury" id="phone" name="phone" placeholder="0912345678" value="<?= isset($_POST['phone']) ? htmlspecialchars($_POST['phone']) : '' ?>" required autofocus>
                                </div>
                                <div class="mb-5">
                                    <label for="password" class="form-label-luxury">Password</label>
                                    <input type="password" class="form-control form-control-luxury" id="password" name="password" placeholder="••••••••" required>
                                </div>
                                <button type="submit" class="btn btn-luxury w-100 mb-4">Sign In</button>
                                <div class="text-center">
                                    <span class="text-muted small">Not a member?</span> <a href="register.php" class="text-decoration-none ms-1" style="color: var(--accent-color); font-weight: 500;">Create Account</a>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
