<?php
/**
 * Admin - Admin Users Management
 * 
 * Create, Read, Update, Delete admin accounts
 */

require_once '../includes/db.php';
require_once 'includes/auth.php';
requireAdmin();
require_once 'includes/flash.php';

// Generate CSRF token if not exists
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Read any flash message from a previous redirect (PRG pattern)
[$message, $message_type] = flash_get();

$admins = [];
$action = $_GET['action'] ?? 'list';
$admin  = null;

// Handle delete — PRG: always redirect after mutation
if ($action === 'delete' && isset($_GET['id'])) {
    try {
        if ((int)$_GET['id'] === (int)$_SESSION['admin_id']) {
            flash_set('You cannot delete your own account.', 'error');
        } else {
            $stmt = $pdo->prepare("DELETE FROM admins WHERE id = :id");
            $stmt->execute([':id' => $_GET['id']]);
            flash_set('User deleted successfully!');
        }
    } catch (PDOException $e) {
        error_log('Delete error: ' . $e->getMessage());
        flash_set('Error deleting user.', 'error');
    }
    header('Location: admins.php');
    exit;
}

// Handle POST submissions (Create & Update)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // CSRF Validation
    if (!hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'] ?? '')) {
        die('CSRF token validation failed. Request blocked.');
    }

    $is_edit = isset($_POST['admin_id']);
    
    try {
        $name = trim($_POST['name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $password = trim($_POST['password'] ?? '');
        $role = $_POST['role'] ?? 'staff';

        if ($is_edit) {
            $id = $_POST['admin_id'];
            // Demotion Protection: Force 'admin' if editing own account
            if ((int)$id === (int)$_SESSION['admin_id']) {
                $role = 'admin';
            }
        }

        if (empty($name) || empty($email)) {
            $message = 'Name and email are required.';
            $message_type = 'error';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $message = 'Invalid email format.';
            $message_type = 'error';
        } elseif (!$is_edit && empty($password)) {
            $message = 'Password is required for new accounts.';
            $message_type = 'error';
        } elseif (!empty($password) && strlen($password) < 8) {
            $message = 'Password must be at least 8 characters.';
            $message_type = 'error';
        } else {
            if ($is_edit) {
                // Check email uniqueness (excluding current admin)
                $stmt = $pdo->prepare("SELECT id FROM admins WHERE email = :email AND id != :id");
                $stmt->execute([':email' => $email, ':id' => $id]);
                if ($stmt->fetch()) {
                    $message = 'Email already in use by another user.';
                    $message_type = 'error';
                } else {
                    if (!empty($password)) {
                        $password_hash = password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);
                        $stmt = $pdo->prepare("UPDATE admins SET name = :name, email = :email, password_hash = :password_hash, role = :role WHERE id = :id");
                        $stmt->execute([
                            ':name' => $name,
                            ':email' => $email,
                            ':password_hash' => $password_hash,
                            ':role' => $role,
                            ':id' => $id,
                        ]);
                        flash_set('User updated and password reset successfully!');
                    } else {
                        $stmt = $pdo->prepare("UPDATE admins SET name = :name, email = :email, role = :role WHERE id = :id");
                        $stmt->execute([
                            ':name' => $name,
                            ':email' => $email,
                            ':role' => $role,
                            ':id' => $id,
                        ]);
                        flash_set('User updated successfully!');
                    }
                    header('Location: admins.php');
                    exit;
                }
            } else {
                // Check email uniqueness for new
                $stmt = $pdo->prepare("SELECT id FROM admins WHERE email = :email");
                $stmt->execute([':email' => $email]);
                if ($stmt->fetch()) {
                    $message = 'Email already in use.';
                    $message_type = 'error';
                } else {
                    $password_hash = password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);
                    $stmt = $pdo->prepare("INSERT INTO admins (name, email, password_hash, role, created_at) VALUES (:name, :email, :password_hash, :role, NOW())");
                    $stmt->execute([
                        ':name' => $name,
                        ':email' => $email,
                        ':password_hash' => $password_hash,
                        ':role' => $role
                    ]);
                    flash_set('New user created successfully!');
                    header('Location: admins.php');
                    exit;
                }
            }
        }
    } catch (PDOException $e) {
        error_log('Database error: ' . $e->getMessage());
        $message = 'A system error occurred. Please try again.';
        $message_type = 'error';
    }
}

// Fetch admin for edit
if ($action === 'edit' && isset($_GET['id'])) {
    try {
        $stmt = $pdo->prepare("SELECT * FROM admins WHERE id = :id");
        $stmt->execute([':id' => $_GET['id']]);
        $admin = $stmt->fetch();
        if (!$admin) {
            $action = 'list';
            $message = 'User not found.';
            $message_type = 'error';
        }
    } catch (PDOException $e) {
        error_log('Fetch error: ' . $e->getMessage());
        $action = 'list';
    }
}

// Fetch all admins for list view
if ($action === 'list') {
    try {
        $stmt = $pdo->prepare("SELECT id, name, email, role, created_at FROM admins ORDER BY created_at DESC");
        $stmt->execute();
        $admins = $stmt->fetchAll();
    } catch (PDOException $e) {
        error_log('List fetch error: ' . $e->getMessage());
    }
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Admins | Admin - MBH Golden Global</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/lucide@latest"></script>
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
                    },
                    animation: {
                        'float': 'floatOrb 12s infinite ease-in-out alternate',
                        'float-delayed': 'floatOrb 15s infinite ease-in-out alternate-reverse',
                    },
                    keyframes: {
                        floatOrb: {
                            '0%': { transform: 'translateY(0) scale(1)' },
                            '100%': { transform: 'translateY(-20px) scale(1.05)' },
                        }
                    }
                }
            }
        }
    </script>
</head>
<body class="bg-[#003355] text-white overflow-x-hidden min-h-screen relative">

    <!-- Ambient Background Orbs -->
    <div class="fixed inset-0 z-0 pointer-events-none overflow-hidden">
        <div class="absolute rounded-full w-[30rem] h-[30rem] top-[-10%] -left-20 animate-float mix-blend-screen opacity-20" style="background: radial-gradient(circle at center, rgba(0,130,202,0.5), transparent 70%);"></div>
        <div class="absolute rounded-full w-[45rem] h-[45rem] bottom-[-20%] -right-20 animate-float-delayed mix-blend-screen opacity-20" style="background: radial-gradient(circle at center, rgba(255,255,255,0.4), transparent 70%);"></div>
        <div class="absolute inset-0 bg-[url('../assets/img/bg1.avif')] bg-cover bg-center opacity-[0.15] mix-blend-overlay"></div>
    </div>

    <div class="flex flex-col min-h-screen relative z-10">
        <?php require_once 'includes/sidebar.php'; ?>

        
        <!-- Mobile Top Bar -->
        <div class="md:hidden flex items-center justify-between p-6 bg-white/5 backdrop-blur-xl border-b border-white/10 sticky top-0 z-30 w-full">
            <h1 class="text-xl font-serif font-bold text-white"><span class="text-brand-cyan">MBH</span> Admin</h1>
            <button id="hamburger-btn" class="text-white hover:text-brand-cyan transition-colors">
                <i data-lucide="menu" class="w-6 h-6"></i>
            </button>
        </div>

        <!-- Main Content -->
        <main class="w-full flex-1 md:pl-80 p-6 md:py-8 md:pr-8 min-h-screen flex flex-col">
            <!-- Top Header -->
            <header class="bg-white/5 backdrop-blur-xl border border-white/10 rounded-2xl p-4 md:p-6 flex flex-wrap gap-4 justify-between items-center mb-6 md:mb-8 shadow-[0_4px_30px_rgba(0,0,0,0.1)]">
                <div class="flex items-center gap-6">
                    <h2 class="text-2xl md:text-3xl lg:text-4xl font-serif text-white">Manage Users</h2>
                    <?php if ($action === 'list'): ?>
                        <a href="?action=create" class="px-5 py-2 bg-brand-cyan/20 border border-brand-cyan/50 text-brand-cyan rounded-xl hover:bg-brand-cyan hover:text-white hover:shadow-[0_0_15px_rgba(0,130,202,0.4)] transition-all font-medium text-sm flex items-center gap-2">
                            <i data-lucide="plus" class="w-4 h-4"></i> New User
                        </a>
                    <?php else: ?>
                        <a href="?action=list" class="px-5 py-2 bg-white/5 border border-white/10 text-white/80 rounded-xl hover:bg-white/10 hover:text-white transition-all font-medium text-sm flex items-center gap-2">
                            <i data-lucide="arrow-left" class="w-4 h-4"></i> Back to List
                        </a>
                    <?php endif; ?>
                </div>
                <div class="flex items-center gap-4">
                    <a href="logout.php" class="flex items-center gap-2 px-5 py-2.5 bg-white/5 hover:bg-red-500/20 border border-white/10 hover:border-red-500/50 text-white/80 hover:text-red-400 hover:shadow-[0_0_15px_rgba(239,68,68,0.2)] rounded-xl transition-all font-medium text-sm">
                        <i data-lucide="log-out" class="w-4 h-4"></i>
                        Logout
                    </a>
                </div>
            </header>

            <!-- Messages -->
            <?php if ($message): ?>
                <div class="mb-6 p-4 rounded-xl backdrop-blur-xl <?php echo $message_type === 'success' ? 'bg-green-500/20 border border-green-500/50 text-green-200' : 'bg-red-500/20 border border-red-500/50 text-red-200'; ?> shadow-[0_4px_30px_rgba(0,0,0,0.1)]">
                    <?php echo htmlspecialchars($message); ?>
                </div>
            <?php endif; ?>

            <!-- List View -->
            <?php if ($action === 'list'): ?>
                <div class="bg-white/5 backdrop-blur-xl rounded-2xl shadow-[0_4px_30px_rgba(0,0,0,0.1)] border border-white/10 flex-1">
                    <?php if (!empty($admins)): ?>
                        <div class="overflow-x-auto rounded-xl">
                            <table class="w-full text-left border-collapse min-w-[800px]">
                                <thead>
                                    <tr>
                                        <th class="border-b border-white/10 py-4 px-6 text-white/50 text-xs tracking-wider uppercase font-semibold whitespace-nowrap">Name</th>
                                        <th class="border-b border-white/10 py-4 px-6 text-white/50 text-xs tracking-wider uppercase font-semibold whitespace-nowrap">Email</th>
                                        <th class="border-b border-white/10 py-4 px-6 text-white/50 text-xs tracking-wider uppercase font-semibold whitespace-nowrap">Role</th>
                                        <th class="border-b border-white/10 py-4 px-6 text-white/50 text-xs tracking-wider uppercase font-semibold whitespace-nowrap">Joined</th>
                                        <th class="border-b border-white/10 py-4 px-6 text-white/50 text-xs tracking-wider uppercase font-semibold whitespace-nowrap">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($admins as $adm): ?>
                                        <tr class="hover:bg-white/5 transition-colors group">
                                            <td class="py-4 px-6 font-medium text-white/90 border-b border-white/5 group-last:border-none whitespace-nowrap">
                                                <?php echo htmlspecialchars($adm['name']); ?>
                                                <?php if ((int)$adm['id'] === (int)$_SESSION['admin_id']): ?>
                                                    <span class="ml-2 px-2 py-1 bg-brand-cyan/20 text-brand-cyan border border-brand-cyan/30 text-[10px] uppercase font-bold rounded shadow-[0_0_10px_rgba(0,130,202,0.2)]">You</span>
                                                <?php endif; ?>
                                            </td>
                                            <td class="py-4 px-6 text-sm text-white/70 border-b border-white/5 group-last:border-none whitespace-nowrap"><?php echo htmlspecialchars($adm['email']); ?></td>
                                            <td class="py-4 px-6 text-sm border-b border-white/5 group-last:border-none whitespace-nowrap">
                                                <?php if ($adm['role'] === 'admin'): ?>
                                                    <span class="px-3 py-1 rounded-full text-[10px] font-bold uppercase tracking-wider bg-cyan-500/20 text-cyan-300 border border-cyan-500/50">Admin</span>
                                                <?php else: ?>
                                                    <span class="px-3 py-1 rounded-full text-[10px] font-bold uppercase tracking-wider bg-white/10 text-white/60 border border-white/20">Staff</span>
                                                <?php endif; ?>
                                            </td>
                                            <td class="py-4 px-6 text-sm text-white/70 border-b border-white/5 group-last:border-none whitespace-nowrap"><?php echo date('M d, Y', strtotime($adm['created_at'])); ?></td>
                                            <td class="py-4 px-6 text-sm border-b border-white/5 group-last:border-none whitespace-nowrap">
                                                <div class="flex items-center gap-3">
                                                    <a href="?action=edit&id=<?php echo $adm['id']; ?>" class="text-brand-cyan hover:text-white transition-colors p-2 hover:bg-white/10 rounded-lg inline-block">
                                                        <i data-lucide="edit" class="w-4 h-4"></i>
                                                    </a>
                                                    <?php if ((int)$adm['id'] !== (int)$_SESSION['admin_id']): ?>
                                                        <a href="?action=delete&id=<?php echo $adm['id']; ?>" onclick="return confirm('Delete this user? This cannot be undone.')" class="text-red-400 hover:text-red-300 transition-colors p-2 hover:bg-red-500/20 rounded-lg inline-block">
                                                            <i data-lucide="trash-2" class="w-4 h-4"></i>
                                                        </a>
                                                    <?php endif; ?>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <div class="text-center py-12 px-4 border border-white/5 rounded-xl bg-white/5 m-6">
                            <i data-lucide="users" class="w-12 h-12 text-white/20 mx-auto mb-3"></i>
                            <p class="text-white/50 text-sm mb-4">No users found.</p>
                        </div>
                    <?php endif; ?>
                </div>

            <!-- Create/Edit Form -->
            <?php else: ?>
                <div class="fixed inset-0 p-4 flex items-center justify-center z-50 bg-[#001a2d]/80 backdrop-blur-sm"><div class="w-full max-w-2xl bg-[#001a2d]/95 backdrop-blur-2xl border border-white/20 rounded-2xl p-6 md:p-8 max-h-[90vh] overflow-y-auto shadow-[0_4px_30px_rgba(0,0,0,0.1)]">
                    <h3 class="text-2xl font-serif text-white mb-6"><?php echo $action === 'create' ? 'Create New User' : 'Edit User'; ?></h3>

                    <form method="POST" class="space-y-6">
                        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
                        
                        <?php if ($action === 'edit' && $admin): ?>
                            <input type="hidden" name="admin_id" value="<?php echo $admin['id']; ?>">
                        <?php endif; ?>

                        <!-- Name -->
                        <div>
                            <label class="block text-sm font-semibold mb-2 text-white/80">Full Name *</label>
                            <input type="text" name="name" required class="w-full px-4 py-3 bg-white/5 border border-white/10 rounded-xl text-white focus:outline-none focus:border-brand-cyan focus:bg-white/10 focus:ring-1 focus:ring-brand-cyan transition-all placeholder-white/30" value="<?php echo htmlspecialchars($admin['name'] ?? ''); ?>" placeholder="John Doe">
                        </div>

                        <!-- Email -->
                        <div>
                            <label class="block text-sm font-semibold mb-2 text-white/80">Email *</label>
                            <input type="email" name="email" required class="w-full px-4 py-3 bg-white/5 border border-white/10 rounded-xl text-white focus:outline-none focus:border-brand-cyan focus:bg-white/10 focus:ring-1 focus:ring-brand-cyan transition-all placeholder-white/30" value="<?php echo htmlspecialchars($admin['email'] ?? ''); ?>" placeholder="admin@example.com">
                        </div>

                        <!-- Role -->
                        <div>
                            <label class="block text-sm font-semibold mb-2 text-white/80">Role *</label>
                            <select name="role" class="w-full px-4 py-3 bg-white/5 border border-white/10 text-white rounded-xl focus:outline-none focus:ring-1 focus:ring-cyan-500 [&>option]:bg-[#003355] transition-all">
                                <option value="staff" <?php echo ($action === 'edit' && $admin && $admin['role'] === 'staff') ? 'selected' : ''; ?>>Staff (Limited Access)</option>
                                <option value="admin" <?php echo ($action === 'edit' && $admin && $admin['role'] === 'admin') ? 'selected' : ''; ?>>Administrator (Full Access)</option>
                            </select>
                            <?php if ($action === 'edit' && $admin && (int)$admin['id'] === (int)$_SESSION['admin_id']): ?>
                                <p class="text-xs text-brand-cyan mt-2">You cannot demote your own account.</p>
                            <?php endif; ?>
                        </div>

                        <!-- Password -->
                        <div>
                            <label class="block text-sm font-semibold mb-2 text-white/80">
                                Password <?php echo $action === 'create' ? '*' : '<span class="text-xs text-white/40 font-normal">(Leave blank to keep current)</span>'; ?>
                            </label>
                            <div class="relative group">
                                <i data-lucide="lock" class="absolute left-4 top-1/2 -translate-y-1/2 w-5 h-5 text-white/50 group-focus-within:text-brand-cyan transition-colors pointer-events-none"></i>
                                
                                <input 
                                    type="password" 
                                    name="password" 
                                    id="adminPassword"
                                    <?php echo $action === 'create' ? 'required' : ''; ?>
                                    class="w-full bg-white/5 border border-white/10 text-white rounded-xl py-3.5 pl-12 pr-12 focus:outline-none focus:border-brand-cyan focus:bg-white/10 focus:ring-1 focus:ring-brand-cyan transition-all placeholder-white/30"
                                    placeholder="Minimum 8 characters"
                                >

                                <button 
                                    type="button" 
                                    id="togglePassword" 
                                    class="absolute right-4 top-1/2 -translate-y-1/2 text-white/50 hover:text-brand-cyan focus:outline-none transition-colors"
                                    tabindex="-1"
                                    aria-label="Toggle password visibility"
                                >
                                    <span id="iconShow"><i data-lucide="eye" class="w-5 h-5"></i></span>
                                    <span id="iconHide" class="hidden"><i data-lucide="eye-off" class="w-5 h-5"></i></span>
                                </button>
                            </div>
                            <p class="text-xs text-white/40 mt-2">Password must be at least 8 characters long.</p>
                        </div>

                        <!-- Info Box -->
                        <?php if ($action === 'edit'): ?>
                            <div class="p-4 bg-brand-cyan/10 border border-brand-cyan/20 rounded-xl text-sm">
                                <p class="text-brand-cyan text-sm"><strong>Note:</strong> Leave the password field empty to keep the current password. Only fill it if you want to reset the password.</p>
                            </div>
                        <?php endif; ?>

                        <!-- Buttons -->
                        <div class="flex gap-4 pt-4 border-t border-white/10">
                            <button type="submit" class="px-8 py-3 bg-gradient-to-r from-brand-cyan to-[#00aaff] text-white rounded-xl hover:shadow-[0_0_20px_rgba(0,130,202,0.4)] transition-all font-bold tracking-wide uppercase text-xs">
                                <?php echo $action === 'create' ? 'Create User' : 'Save Changes'; ?>
                            </button>
                            <a href="?action=list" class="px-8 py-3 bg-white/5 text-white/80 border border-white/10 rounded-xl hover:bg-white/10 transition-all font-bold tracking-wide uppercase text-xs">Cancel</a>
                        </div>
                    </form>
                </div>
                </div>
            <?php endif; ?>
        </main>
    </div> <!-- Close flex wrapper -->

    <script>
        lucide.createIcons();

        // Password Visibility Toggle Logic
        const togglePasswordBtn = document.getElementById('togglePassword');
        const passwordInput = document.getElementById('adminPassword');
        const iconShow = document.getElementById('iconShow');
        const iconHide = document.getElementById('iconHide');

        if (togglePasswordBtn && passwordInput) {
            togglePasswordBtn.addEventListener('click', function () {
                // Toggle the input type between password and text
                const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
                passwordInput.setAttribute('type', type);
                
                // Swap the icons
                iconShow.classList.toggle('hidden');
                iconHide.classList.toggle('hidden');
            });
        }
    </script>
</body>
</html>
