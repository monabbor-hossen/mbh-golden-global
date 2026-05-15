<?php
/**
 * Admin Setup - Create Default Admin User
 * 
 * SECURITY NOTE: Delete this file after setup is complete!
 * Run this script ONCE to create the default admin account.
 */

require_once '../includes/db.php';

// Check if setup is already complete (admin exists)
try {
    $checkStmt = $pdo->prepare("SELECT COUNT(*) as count FROM admins");
    $checkStmt->execute();
    $result = $checkStmt->fetch();
    
    if ($result['count']> 0) {
        ?>
        <!DOCTYPE html>
        <html lang="en">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>Setup Complete | MBH Golden Global</title>
            <script src="https://cdn.tailwindcss.com"></script>
        </head>
        <body class="bg-green-50 min-h-screen flex items-center justify-center p-4">
            <div class="bg-white rounded-lg shadow-lg p-8 max-w-md">
                <div class="text-center">
                    <div class="text-5xl mb-4">✓</div>
                    <h1 class="text-2xl font-bold text-green-700 mb-2">Setup Complete</h1>
                    <p class="text-gray-600 mb-6">Admin account already exists. The setup process is not needed.</p>
                    <a href="login.php" class="inline-block px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">Go to Login</a>
                </div>
            </div>
        </body>
        </html>
        <?php
        exit;
    }
} catch (PDOException $e) {
    error_log('Setup check error: ' . $e->getMessage());
}

// Handle setup form
$setupMessage = '';
$setupSuccess = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $name = trim($_POST['name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $password_confirm = $_POST['password_confirm'] ?? '';

        // Validation
        $errors = [];

        if (empty($name)) {
            $errors[] = 'Name is required.';
        }

        if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Valid email is required.';
        }

        if (empty($password) || strlen($password) < 8) {
            $errors[] = 'Password must be at least 8 characters.';
        }

        if ($password !== $password_confirm) {
            $errors[] = 'Passwords do not match.';
        }

        if (empty($errors)) {
            // Hash password
            $passwordHash = password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);

            // Insert admin
            $stmt = $pdo->prepare("
                INSERT INTO admins (name, email, password_hash, created_at) 
                VALUES (:name, :email, :password_hash, NOW())
            ");

            $stmt->execute([
                ':name' => $name,
                ':email' => $email,
                ':password_hash' => $passwordHash,
            ]);

            $setupSuccess = true;
            $setupMessage = 'Admin account created successfully! You can now log in.';
        } else {
            $setupMessage = 'Errors: ' . implode(' ', $errors);
        }
    } catch (PDOException $e) {
        if (strpos($e->getMessage(), 'Duplicate') !== false) {
            $setupMessage = 'Email already exists. Admin setup may have already been completed.';
        } else {
            error_log('Setup error: ' . $e->getMessage());
            $setupMessage = 'An error occurred during setup. Please check the logs.';
        }
    }
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Setup | MBH Golden Global</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #003355 0%, #0082CA 100%);
            font-family: 'Inter', sans-serif;
        }
    </style>
</head>
<body class="min-h-screen flex items-center justify-center p-4">
    <div class="w-full max-w-md">
        <!-- Header -->
        <div class="text-center mb-8">
            <h1 class="text-4xl font-bold text-white mb-2">
                <span class="text-cyan-300">MBH</span> SETUP
            </h1>
            <p class="text-white/80 text-sm">Create Default Admin Account</p>
        </div>

        <!-- Setup Card -->
        <div class="bg-white rounded-2xl shadow-2xl p-8">
            <?php if ($setupSuccess): ?>
                <!-- Success Message -->
                <div class="mb-6 p-4 bg-green-50 border border-green-200 rounded-lg">
                    <p class="text-green-700 font-medium text-center"><?php echo htmlspecialchars($setupMessage); ?></p>
                </div>
                <div class="text-center">
                    <p class="text-gray-600 mb-4">Redirecting to login in 3 seconds...</p>
                    <a href="login.php" class="inline-block px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">Go to Login Now</a>
                </div>
                <script>
                    setTimeout(() => window.location.href = 'login.php', 3000);
                </script>
            <?php else: ?>
                <h2 class="text-2xl font-bold text-gray-800 mb-6">Create Admin Account</h2>

                <?php if ($setupMessage): ?>
                    <div class="mb-6 p-4 bg-red-50 border border-red-200 rounded-lg">
                        <p class="text-red-700 text-sm"><?php echo htmlspecialchars($setupMessage); ?></p>
                    </div>
                <?php endif; ?>

                <form method="POST" class="space-y-5">
                    <!-- Name -->
                    <div>
                        <label for="name" class="block text-sm font-semibold text-gray-700 mb-2">Full Name</label>
                        <input 
                            type="text" 
                            id="name" 
                            name="name" 
                            required 
                            class="w-full px-4 py-2 border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                            placeholder="Administrator">
                    </div>

                    <!-- Email -->
                    <div>
                        <label for="email" class="block text-sm font-semibold text-gray-700 mb-2">Email Address</label>
                        <input 
                            type="email" 
                            id="email" 
                            name="email" 
                            required 
                            class="w-full px-4 py-2 border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                            placeholder="admin@mbh.com">
                    </div>

                    <!-- Password -->
                    <div>
                        <label for="password" class="block text-sm font-semibold text-gray-700 mb-2">Password</label>
                        <input 
                            type="password" 
                            id="password" 
                            name="password" 
                            required 
                            minlength="8"
                            class="w-full px-4 py-2 border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                            placeholder="Minimum 8 characters">
                        <p class="text-xs text-gray-500 mt-1">Min 8 characters recommended for security</p>
                    </div>

                    <!-- Confirm Password -->
                    <div>
                        <label for="password_confirm" class="block text-sm font-semibold text-gray-700 mb-2">Confirm Password</label>
                        <input 
                            type="password" 
                            id="password_confirm" 
                            name="password_confirm" 
                            required 
                            minlength="8"
                            class="w-full px-4 py-2 border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                            placeholder="Re-enter password">
                    </div>

                    <!-- Submit -->
                    <button 
                        type="submit" 
                        class="w-full py-3 px-4 bg-gradient-to-b from-[#00a2ff] to-[#0082CA] text-white font-bold rounded-lg hover:shadow-lg transition mt-6 uppercase tracking-[0.1em] text-sm">
                        Create Admin Account
                    </button>
                </form>

                <!-- Warning -->
                <div class="mt-6 pt-6 border-t border-gray-100">
                    <p class="text-xs text-gray-500 text-center">
                        ⚠️ After setup, delete this <code class="bg-gray-100 px-2 py-1 rounded">setup.php</code> file for security.
                    </p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
