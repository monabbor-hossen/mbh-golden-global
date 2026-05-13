<?php
/**
 * Admin Login Page
 * 
 * Secure authentication for admin dashboard with session management
 */

require_once '../includes/db.php';

// Start session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Redirect if already logged in
if (isset($_SESSION['admin_id'])) {
    header('Location: index.php');
    exit;
}

// Handle login form submission
$loginError = '';
$redirectUrl = $_GET['redirect'] ?? 'index.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';

        // Validation
        if (empty($email) || empty($password)) {
            $loginError = 'Email and password are required.';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $loginError = 'Please enter a valid email address.';
        } else {
            // Fetch admin from database
            $stmt = $pdo->prepare("
                SELECT id, name, email, password_hash 
                FROM admins 
                WHERE email = :email 
                LIMIT 1
            ");
            
            $stmt->execute([':email' => $email]);
            $admin = $stmt->fetch();

            if ($admin && password_verify($password, $admin['password_hash'])) {
                // Login successful - set session
                $_SESSION['admin_id'] = $admin['id'];
                $_SESSION['admin_email'] = $admin['email'];
                $_SESSION['admin_name'] = $admin['name'];
                $_SESSION['last_activity'] = time();

                // Redirect to dashboard or originally requested page
                header('Location: ' . $redirectUrl);
                exit;
            } else {
                $loginError = 'Invalid email or password.';
            }
        }
    } catch (PDOException $e) {
        error_log('Login error: ' . $e->getMessage());
        $loginError = 'An error occurred. Please try again later.';
    }
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login | MBH Golden Global</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Playfair+Display:ital,wght@0,400;0,500;0,600;0,700;1,400;1,700&display=swap" rel="stylesheet">
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Inter', 'sans-serif'],
                        serif: ['Playfair Display', 'serif'],
                    },
                    colors: {
                        brand: {
                            navy: '#003355',
                            cyan: '#0082CA',
                            sand: '#F4F7F9',
                        }
                    }
                }
            }
        }
    </script>
    <style>
        body {
            background: linear-gradient(135deg, #003355 0%, #0082CA 100%);
            font-family: 'Inter', sans-serif;
        }
    </style>
</head>
<body class="min-h-screen flex items-center justify-center p-4">
    <div class="w-full max-w-md">
        <!-- Logo / Branding -->
        <div class="text-center mb-8">
            <img src="../assets/img/logo.png" alt="MBH Golden Global" class="h-20 md:h-32 object-contain mx-auto">
            <p class="text-white/80 text-sm tracking-wider">Admin Dashboard</p>
        </div>

        <!-- Login Card -->
        <div class="bg-white rounded-2xl shadow-2xl p-8 backdrop-blur-xl">
            <h2 class="text-2xl font-bold text-brand-navy mb-6">Welcome Back</h2>

            <!-- Error Message -->
            <?php if ($loginError): ?>
                <div class="mb-6 p-4 bg-red-50 border border-red-200 rounded-lg">
                    <p class="text-red-700 text-sm font-medium"><?php echo htmlspecialchars($loginError); ?></p>
                </div>
            <?php endif; ?>

            <!-- Timeout Message -->
            <?php if (isset($_GET['timeout'])): ?>
                <div class="mb-6 p-4 bg-yellow-50 border border-yellow-200 rounded-lg">
                    <p class="text-yellow-700 text-sm font-medium">Session expired due to inactivity. Please log in again.</p>
                </div>
            <?php endif; ?>

            <!-- Login Form -->
            <form method="POST" class="space-y-5">
                <!-- Email -->
                <div>
                    <label for="email" class="block text-sm font-semibold text-brand-navy mb-2">Email Address</label>
                    <input 
                        type="email" 
                        id="email" 
                        name="email" 
                        value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>"
                        required 
                        autocomplete="email"
                        class="w-full px-4 py-3 border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-brand-cyan focus:border-transparent transition"
                        placeholder="admin@mbh.com"
                    >
                </div>

                <!-- Password -->
                <div>
                    <label for="password" class="block text-sm font-semibold text-brand-navy mb-2">Password</label>
                    <input 
                        type="password" 
                        id="password" 
                        name="password" 
                        required 
                        autocomplete="current-password"
                        class="w-full px-4 py-3 border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-brand-cyan focus:border-transparent transition"
                        placeholder="••••••••"
                    >
                </div>

                <!-- Submit Button -->
                <button 
                    type="submit" 
                    class="w-full py-3 px-4 bg-gradient-to-b from-[#00a2ff] to-[#0082CA] text-white font-bold rounded-lg hover:shadow-lg transition-all duration-300 mt-6 uppercase tracking-[0.1em] text-sm"
                >
                    Sign In
                </button>
            </form>

            <!-- Footer -->
            <div class="mt-6 pt-6 border-t border-gray-100">
                <p class="text-center text-xs text-gray-500">
                    Protected area - Authorized personnel only
                </p>
            </div>
        </div>

        <!-- Back to Site -->
        <div class="text-center mt-6">
            <a href="../index.php" class="text-white/80 hover:text-white text-sm transition">
                ← Back to Website
            </a>
        </div>
    </div>
</body>
</html>
