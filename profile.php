<?php
require 'config/database.php';
$pageTitle = "My Account";

// Authentication Guard
if (!isLogin()) {
    redirect('login.php?msg=auth');
}

$userId = $_SESSION['user_id'];
$message = '';
$msgType = ''; // success or danger

// Handle AJAX Form Submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json');
    $action = $_POST['action'] ?? '';
    $response = ['status' => 'error', 'message' => 'Invalid request'];

    if ($action === 'update_info') {
        $name = trim($_POST['name']);
        $email = trim($_POST['email'] ?? '');
        if (!empty($name)) {
            if (updateData('users', ['name' => $name, 'email' => $email], ['id' => $userId])) {
                $_SESSION['name'] = $name; // Update session
                $response = [
                    'status' => 'success', 
                    'message' => 'Personal information updated successfully.',
                    'newName' => $name
                ];
            } else {
                $response = ['status' => 'error', 'message' => 'Database update failed.'];
            }
        } else {
            $response = ['status' => 'error', 'message' => 'Name cannot be empty.'];
        }
    } elseif ($action === 'change_password') {
        $newPass = $_POST['new_password'];
        if (strlen($newPass) >= 6) {
            $hash = hashPassword($newPass);
            if (updateData('users', [
                'password' => $hash,
                'password_length' => strlen($newPass)
            ], ['id' => $userId])) {
                $response = ['status' => 'success', 'message' => 'Password updated successfully.'];
            } else {
                $response = ['status' => 'error', 'message' => 'Database update failed.'];
            }
        } else {
            $response = ['status' => 'error', 'message' => 'Password must be at least 6 characters.'];
        }
    }
    echo json_encode($response);
    exit; // Stop execution for AJAX
}

// Fetch User Data
$users = getData('users', ['where' => ['id' => $userId], 'limit' => 1]);
$user = $users[0] ?? null;

if (!$user) {
    // Should not happen if logged in, but safety check
    session_destroy();
    redirect('login.php');
}

// Determine Active Tab
$currentTab = $_GET['tab'] ?? 'overview';
if (!in_array($currentTab, ['overview', 'info', 'password', 'orders'])) {
    $currentTab = 'overview';
}

include 'includes/header.php';
?>

<div class="container py-5 fade-in-page">
    <div class="row mt-2 mb-2">
        <!-- Sidebar Navigation -->
        <div class="col-lg-3 mb-4">
            <div class="dashboard-sidebar">
                <div class="p-4 border-bottom bg-light">
                    <h5 class="font-heading mb-0">My Account</h5>
                    <small class="text-muted">Member since <?= date('Y', strtotime($user['created_at'] ?? 'now')) ?></small>
                </div>
                <nav class="nav flex-column">
                    <a href="#" class="dashboard-nav-link <?= $currentTab === 'overview' ? 'active' : '' ?>" data-tab="overview">
                        <i class="fa-regular fa-user me-2"></i> Account Overview
                    </a>
                    <a href="#" class="dashboard-nav-link <?= $currentTab === 'info' ? 'active' : '' ?>" data-tab="info">
                        <i class="fa-regular fa-id-card me-2"></i> Personal Information
                    </a>
                    <a href="#" class="dashboard-nav-link <?= $currentTab === 'orders' ? 'active' : '' ?>" data-tab="orders">
                        <i class="fa-solid fa-box-open me-2"></i> Order History
                    </a>
                    <a href="#" class="dashboard-nav-link <?= $currentTab === 'password' ? 'active' : '' ?>" data-tab="password">
                        <i class="fa-solid fa-lock me-2"></i> Change Password
                    </a>
                    <a href="logout.php" class="dashboard-nav-link text-danger">
                        <i class="fa-solid fa-arrow-right-from-bracket me-2"></i> Logout
                    </a>
                </nav>
            </div>
        </div>

        <!-- Main Content Area -->
        <div class="col-lg-9">
            <!-- Alert Container for AJAX responses -->
            <?php include 'includes/alert.php'; ?>

            <div class="card dashboard-card border-0 h-100">
                <div class="card-body p-4 p-lg-5">
                    
                    <!-- TAB: OVERVIEW -->
                    <div id="tab-overview" class="tab-section <?= $currentTab === 'overview' ? '' : 'd-none' ?>">
                        <h3 class="font-heading mb-4">Welcome back, <span class="user-name-display"><?= htmlspecialchars($user['name'] ?? 'Client') ?></span></h3>
                        <p class="text-muted mb-5">From your account dashboard you can view your recent orders, manage your shipping and billing addresses, and edit your password and account details.</p>
                        
                        <div class="row g-4">
                            <div class="col-md-6">
                                <div class="p-4 bg-light rounded-3 h-100">
                                    <h6 class="text-uppercase letter-spacing-1 mb-3 text-muted">Contact Info</h6>
                                    <p class="mb-1"><strong class="user-name-display"><?= htmlspecialchars($user['name'] ?? 'N/A') ?></strong></p>
                                    <p class="mb-0 text-muted"><?= htmlspecialchars($user['phone']) ?></p>
                                    <p class="mb-0 text-muted"><?= htmlspecialchars($user['email'] ?? '') ?></p>
                                    <a href="#" class="btn btn-link px-0 text-decoration-none mt-3 tab-trigger" data-target="info" style="color: var(--accent-color)">Edit Details</a>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="p-4 bg-light rounded-3 h-100">
                                    <h6 class="text-uppercase letter-spacing-1 mb-3 text-muted">Security</h6>
                                    <p class="mb-1">Password: ************</p>
                                    <p class="mb-0 text-muted">Last changed: Never</p>
                                    <a href="#" class="btn btn-link px-0 text-decoration-none mt-3 tab-trigger" data-target="password" style="color: var(--accent-color)">Change Password</a>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- TAB: PERSONAL INFORMATION -->
                    <div id="tab-info" class="tab-section <?= $currentTab === 'info' ? '' : 'd-none' ?>">
                        <h4 class="font-heading mb-4">Personal Information</h4>
                        <form id="form-info" method="POST">
                            <input type="hidden" name="action" value="update_info">
                            
                            <div class="mb-4">
                                <label class="form-label-luxury">Phone Number</label>
                                <input type="text" class="form-control form-control-luxury bg-light" value="<?= htmlspecialchars($user['phone']) ?>" disabled readonly>
                                <div class="form-text">Phone number cannot be changed as it is your unique identifier.</div>
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-4">
                                    <label for="name" class="form-label-luxury">Full Name</label>
                                    <input type="text" class="form-control form-control-luxury" id="name" name="name" value="<?= htmlspecialchars($user['name'] ?? '') ?>" placeholder="Enter your full name">
                                </div>

                                <div class="col-md-6 mb-4">
                                    <label for="email" class="form-label-luxury">Email Address</label>
                                    <input type="email" class="form-control form-control-luxury" id="email" name="email" value="<?= htmlspecialchars($user['email'] ?? '') ?>" placeholder="name@example.com">
                                </div>
                            </div>

                            <button type="submit" class="btn btn-luxury">Save Changes</button>
                        </form>
                    </div>

                    <!-- TAB: ORDER HISTORY -->
                    <div id="tab-orders" class="tab-section <?= $currentTab === 'orders' ? '' : 'd-none' ?>">
                        <h4 class="font-heading mb-4">Order History</h4>
                        
                        <?php
                        // Fetch Orders
                        $orders = getData('orders', [
                            'where' => ['user_id' => $userId],
                            'order_by' => 'created_at DESC'
                        ]);
                        ?>

                        <?php if (empty($orders)): ?>
                            <div class="text-center py-5 bg-light rounded-3">
                                <i class="fa-solid fa-box-open fs-1 text-muted mb-3 opacity-50"></i>
                                <h6 class="text-muted">No orders found</h6>
                                <p class="small text-muted mb-4">You haven't placed any orders yet.</p>
                                <a href="category.php" class="btn btn-luxury btn-sm">Start Shopping</a>
                            </div>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table table-hover align-middle">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Order ID</th>
                                            <th>Date</th>
                                            <th>Status</th>
                                            <th>Total</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($orders as $ord): ?>
                                            <tr>
                                                <td>#<?= str_pad($ord['id'], 6, '0', STR_PAD_LEFT) ?></td>
                                                <td><?= date('M d, Y', strtotime($ord['created_at'])) ?></td>
                                                <td>
                                                    <?php if ($ord['status'] === 'paid'): ?>
                                                        <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill">Paid</span>
                                                    <?php else: ?>
                                                        <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle rounded-pill">Pending</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td class="fw-medium"><?= formatVND($ord['total']) ?></td>
                                                <td>
                                                    <a href="order_detail.php?id=<?= $ord['id'] ?>" class="btn btn-sm btn-outline-dark rounded-0">View</a>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php endif; ?>
                    </div>

                    <!-- TAB: CHANGE PASSWORD -->
                    <div id="tab-password" class="tab-section <?= $currentTab === 'password' ? '' : 'd-none' ?>">
                        <h4 class="font-heading mb-4">Change Password</h4>
                        <form id="form-password" method="POST">
                            <input type="hidden" name="action" value="change_password">
                            
                            <div class="mb-4">
                                <label for="new_password" class="form-label-luxury">New Password</label>
                                <div class="input-group">
                                    <?php 
                                        // Create a placeholder string of asterisks based on stored length
                                        $maskLength = $user['password_length'] ?? 8;
                                        $placeholder = str_repeat('*', $maskLength);
                                    ?>
                                    <input type="password" class="form-control form-control-luxury" id="new_password" name="new_password" placeholder="<?= $placeholder ?>" required>
                                </div>
                                <div class="form-text mt-2">Enter your new password to update. Minimum 6 characters.</div>
                            </div>

                            <button type="submit" class="btn btn-luxury">Update Password</button>
                        </form>
                    </div>

                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const handleFormSubmit = (formId) => {
        const form = document.getElementById(formId);
        if (!form) return;

        form.addEventListener('submit', function(e) {
            e.preventDefault();
            const formData = new FormData(form);
            const btn = form.querySelector('button[type="submit"]');
            const originalText = btn.innerHTML;
            
            // Loading state
            btn.disabled = true;
            btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin me-2"></i>Processing...';

            fetch('profile.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                btn.disabled = false;
                btn.innerHTML = originalText;

                // Use unified Toast Notification
                const type = data.status === 'success' ? 'success' : 'danger';
                if (window.showToast) window.showToast(type, data.message);

                if (data.status === 'success') {
                    if (data.newName) {
                        // Update name in Navbar and other places without reload
                        document.querySelectorAll('.user-name-display').forEach(el => el.textContent = data.newName);
                    }
                    if (formId === 'form-password') form.reset();
                }
            })
            .catch(error => console.error('Error:', error));
        });
    };

    handleFormSubmit('form-info');
    handleFormSubmit('form-password');

    // Handle Tab Switching without Reload
    const switchTab = (tabName) => {
        // Update Sidebar
        document.querySelectorAll('.dashboard-nav-link').forEach(link => {
            link.classList.remove('active');
            if (link.getAttribute('data-tab') === tabName) {
                link.classList.add('active');
            }
        });

        // Update Content
        document.querySelectorAll('.tab-section').forEach(section => {
            section.classList.add('d-none');
        });
        const targetSection = document.getElementById('tab-' + tabName);
        if (targetSection) targetSection.classList.remove('d-none');

        // Update URL
        const newUrl = '?tab=' + tabName;
        window.history.pushState({path: newUrl}, '', newUrl);
    };

    document.querySelectorAll('.dashboard-nav-link[data-tab], .tab-trigger').forEach(el => {
        el.addEventListener('click', function(e) {
            e.preventDefault();
            const tab = this.getAttribute('data-tab') || this.getAttribute('data-target');
            if (tab) switchTab(tab);
        });
    });
});
</script>
