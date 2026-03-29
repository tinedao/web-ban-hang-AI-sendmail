<?php
require 'config/database.php';
$pageTitle = "Register";

$error = '';
$success = '';

if (isLogin()) {
    redirect('index.php');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $phone = trim($_POST['phone']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    if (empty($name) || empty($phone) || empty($password) || empty($confirm_password)) {
        $error = "Please fill in all fields.";
    } elseif ($password !== $confirm_password) {
        $error = "Passwords do not match.";
    } elseif (strlen($password) < 6) {
        $error = "Password must be at least 6 characters.";
    } else {
        // Check if phone exists
        $count = getCount('users', ['where' => ['phone' => $phone]]);
        if ($count > 0) {
            $error = "This phone number is already registered.";
        } else {
            // Create User
            $hash = hashPassword($password);
            $data = [
                'name' => $name,
                'phone' => $phone,
                'password' => $hash,
                'password_length' => strlen($password) // Required by schema
            ];
            
            if (insertData('users', $data)) {
                $_SESSION['flash_message'] = [
                    'type' => 'success',
                    'message' => 'Registration successful! Please login.'
                ];
                redirect('login.php');
            } else {
                $error = "Registration failed. Please try again.";
            }
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
                        <!-- Left: Image -->
                        <div class="col-lg-6 auth-image-col" style="background-image: url('https://images.unsplash.com/photo-1584302179602-e4c3d3fd629d?q=80&w=1920&auto=format&fit=crop');">
                            <div class="auth-overlay-gradient"></div>
                            <div class="position-absolute bottom-0 start-0 p-5 text-white z-2">
                                <h3 class="font-heading mb-2">Join the Elite</h3>
                                <p class="small opacity-75 mb-0" style="letter-spacing: 0.5px;">Create an account to enjoy personalized services.</p>
                            </div>
                        </div>

                        <!-- Right: Form -->
                        <div class="col-lg-6 auth-form-col">
                            <div class="text-center mb-5">
                                <h2 class="font-heading mb-2">Create Account</h2>
                                <p class="text-muted small">Begin your journey with us</p>
                            </div>

                            <?php include 'includes/alert.php'; ?>

                            <form action="" method="POST">
                                <div class="mb-3">
                                    <label for="name" class="form-label-luxury">Full Name</label>
                                    <input type="text" class="form-control form-control-luxury" id="name" name="name" placeholder="John Doe" value="<?= isset($_POST['name']) ? htmlspecialchars($_POST['name']) : '' ?>" required>
                                </div>
                                <div class="mb-3">
                                    <label for="phone" class="form-label-luxury">Phone Number</label>
                                    <input type="text" class="form-control form-control-luxury" id="phone" name="phone" placeholder="0912345678" value="<?= isset($_POST['phone']) ? htmlspecialchars($_POST['phone']) : '' ?>" required>
                                </div>
                                <div class="mb-3">
                                    <label for="password" class="form-label-luxury">Password</label>
                                    <input type="password" class="form-control form-control-luxury" id="password" name="password" placeholder="••••••••" required>
                                </div>
                                <div class="mb-5">
                                    <label for="confirm_password" class="form-label-luxury">Confirm Password</label>
                                    <input type="password" class="form-control form-control-luxury" id="confirm_password" name="confirm_password" placeholder="••••••••" required>
                                </div>
                                <button type="submit" class="btn btn-luxury w-100 mb-4">Register</button>
                                <div class="text-center">
                                    <span class="text-muted small">Already have an account?</span> <a href="login.php" class="text-decoration-none ms-1" style="color: var(--accent-color); font-weight: 500;">Login</a>
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
