<?php
/**
 * Admin - Admin Users Management
 * 
 * Create, Read, Update, Delete admin accounts
 */

require_once '../includes/db.php';
require_once 'includes/auth.php';
require_once 'includes/flash.php';

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
            flash_set('Admin deleted successfully!');
        }
    } catch (PDOException $e) {
        error_log('Delete error: ' . $e->getMessage());
        flash_set('Error deleting admin.', 'error');
    }
    header('Location: admins.php');
    exit;
}

// Handle edit form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['admin_id'])) {
    try {
        $id = $_POST['admin_id'];
        $name = trim($_POST['name']);
        $email = trim($_POST['email']);
        $new_password = trim($_POST['password'] ?? '');

        if (empty($name) || empty($email)) {
            $message = 'Name and email are required.';
            $message_type = 'error';
        } else {
            // Check email uniqueness (excluding current admin)
            $stmt = $pdo->prepare("SELECT id FROM admins WHERE email = :email AND id != :id");
            $stmt->execute([':email' => $email, ':id' => $id]);
            if ($stmt->fetch()) {
                $message = 'Email already in use by another admin.';
                $message_type = 'error';
            } else {
                if (!empty($new_password)) {
                    if (strlen($new_password) < 8) {
                        $message = 'Password must be at least 8 characters.';
                        $message_type = 'error';
                    } else {
                        $password_hash = password_hash($new_password, PASSWORD_BCRYPT, ['cost' => 12]);
                        $stmt = $pdo->prepare("UPDATE admins SET name = :name, email = :email, password_hash = :password_hash WHERE id = :id");
                        $stmt->execute([
                            ':name' => $name,
                            ':email' => $email,
                            ':password_hash' => $password_hash,
                            ':id' => $id,
                        ]);
                        // PRG: flash success and redirect so F5 won't re-POST
                        flash_set('Admin updated and password reset successfully!');
                        header('Location: admins.php');
                        exit;
                    }
                } else {
                    $stmt = $pdo->prepare("UPDATE admins SET name = :name, email = :email WHERE id = :id");
                    $stmt->execute([
                        ':name' => $name,
                        ':email' => $email,
                        ':id' => $id,
                    ]);
                    // PRG: flash success and redirect so F5 won't re-POST
                    flash_set('Admin updated successfully!');
                    header('Location: admins.php');
                    exit;
                }
            }
        }
    } catch (PDOException $e) {
        error_log('Update error: ' . $e->getMessage());
        $message = 'Error updating admin.';
        $message_type = 'error';
    }
}

// Handle new admin form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !isset($_POST['admin_id'])) {
    try {
        $name = trim($_POST['name']);
        $email = trim($_POST['email']);
        $password = trim($_POST['password']);

        if (empty($name) || empty($email) || empty($password)) {
            $message = 'All fields are required.';
            $message_type = 'error';
        } elseif (strlen($password) < 8) {
            $message = 'Password must be at least 8 characters.';
            $message_type = 'error';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $message = 'Invalid email format.';
            $message_type = 'error';
        } else {
            // Check email uniqueness
            $stmt = $pdo->prepare("SELECT id FROM admins WHERE email = :email");
            $stmt->execute([':email' => $email]);
            if ($stmt->fetch()) {
                $message = 'Email already in use.';
                $message_type = 'error';
            } else {
                $password_hash = password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);
                $stmt = $pdo->prepare("INSERT INTO admins (name, email, password_hash, created_at) VALUES (:name, :email, :password_hash, NOW())");
                $stmt->execute([
                    ':name' => $name,
                    ':email' => $email,
                    ':password_hash' => $password_hash,
                ]);
                // PRG: flash success and redirect so F5 won't re-POST
                flash_set('New admin created successfully!');
                header('Location: admins.php');
                exit;
            }
        }
    } catch (PDOException $e) {
        error_log('Insert error: ' . $e->getMessage());
        $message = 'Error creating admin.';
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
            $message = 'Admin not found.';
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
        $stmt = $pdo->prepare("SELECT id, name, email, created_at FROM admins ORDER BY created_at DESC");
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
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <script>
        tailwind.config = {
            theme: {
                extend: {
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
</head>
<body class="bg-brand-sand text-brand-navy">

    <!-- Navigation -->
    <nav class="bg-white border-b border-gray-200">
        <div class="max-w-7xl mx-auto px-6 py-4 flex justify-between items-center">
            <div class="flex items-center gap-8">
                <h1 class="text-2xl font-serif font-bold"><span class="text-brand-cyan">MBH</span> Admin</h1>
                <div class="hidden md:flex gap-6">
                    <a href="index.php" class="hover:text-brand-cyan">Dashboard</a>
                    <a href="packages.php" class="hover:text-brand-cyan">Packages</a>
                    <a href="stories.php" class="hover:text-brand-cyan">Stories</a>
                    <a href="inquiries.php" class="hover:text-brand-cyan">Inquiries</a>
                    <a href="admins.php" class="text-brand-cyan font-semibold border-b-2 border-brand-cyan pb-1">Admins</a>
                    <a href="settings.php" class="hover:text-brand-cyan">Settings</a>
                </div>
            </div>
            <a href="logout.php" class="text-red-600 hover:bg-red-50 px-4 py-2 rounded text-sm">Logout</a>
        </div>
    </nav>

    <!-- Main Content -->
    <main class="max-w-7xl mx-auto px-6 py-12">
        <div class="flex justify-between items-center mb-8">
            <h2 class="text-3xl font-serif font-bold">Admin Users</h2>
            <?php if ($action === 'list'): ?>
                <a href="?action=create" class="px-6 py-2 bg-brand-cyan text-white rounded-lg font-medium">+ New Admin</a>
            <?php else: ?>
                <a href="?action=list" class="px-6 py-2 bg-gray-300 text-gray-800 rounded-lg font-medium">← Back</a>
            <?php endif; ?>
        </div>

        <?php if ($message): ?>
            <div class="mb-6 p-4 rounded-lg <?php echo $message_type === 'success' ? 'bg-green-100 border border-green-300' : 'bg-red-100 border border-red-300'; ?>">
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>

        <!-- List View -->
        <?php if ($action === 'list'): ?>
            <div class="bg-white rounded-xl shadow-sm border border-gray-100">
                <?php if (!empty($admins)): ?>
                    <div class="overflow-x-auto">
                        <table class="w-full">
                            <thead>
                                <tr class="border-b border-gray-200">
                                    <th class="text-left py-4 px-6 text-xs font-semibold text-gray-600 uppercase">Name</th>
                                    <th class="text-left py-4 px-6 text-xs font-semibold text-gray-600 uppercase">Email</th>
                                    <th class="text-left py-4 px-6 text-xs font-semibold text-gray-600 uppercase">Joined</th>
                                    <th class="text-left py-4 px-6 text-xs font-semibold text-gray-600 uppercase">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($admins as $adm): ?>
                                    <tr class="border-b border-gray-100 hover:bg-gray-50">
                                        <td class="py-4 px-6 font-medium">
                                            <?php echo htmlspecialchars($adm['name']); ?>
                                            <?php if ((int)$adm['id'] === (int)$_SESSION['admin_id']): ?>
                                                <span class="ml-2 px-2 py-1 bg-brand-cyan text-white text-xs rounded">You</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="py-4 px-6 text-sm text-gray-600"><?php echo htmlspecialchars($adm['email']); ?></td>
                                        <td class="py-4 px-6 text-sm"><?php echo date('M d, Y', strtotime($adm['created_at'])); ?></td>
                                        <td class="py-4 px-6 text-sm">
                                            <a href="?action=edit&id=<?php echo $adm['id']; ?>" class="text-brand-cyan hover:underline mr-3">Edit</a>
                                            <?php if ((int)$adm['id'] !== (int)$_SESSION['admin_id']): ?>
                                                <a href="?action=delete&id=<?php echo $adm['id']; ?>" onclick="return confirm('Delete this admin? This cannot be undone.')" class="text-red-600 hover:underline">Delete</a>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <p class="text-center py-12 text-gray-500">No admins found.</p>
                <?php endif; ?>
            </div>

        <!-- Create/Edit Form -->
        <?php else: ?>
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-8 max-w-2xl">
                <h3 class="text-2xl font-bold mb-6"><?php echo $action === 'create' ? 'Create New Admin' : 'Edit Admin'; ?></h3>

                <form method="POST" class="space-y-6">
                    <?php if ($action === 'edit' && $admin): ?>
                        <input type="hidden" name="admin_id" value="<?php echo $admin['id']; ?>">
                    <?php endif; ?>

                    <!-- Name -->
                    <div>
                        <label class="block text-sm font-semibold mb-2">Full Name *</label>
                        <input type="text" name="name" required class="w-full px-4 py-2 border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-brand-cyan" value="<?php echo htmlspecialchars($admin['name'] ?? ''); ?>" placeholder="John Doe">
                    </div>

                    <!-- Email -->
                    <div>
                        <label class="block text-sm font-semibold mb-2">Email *</label>
                        <input type="email" name="email" required class="w-full px-4 py-2 border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-brand-cyan" value="<?php echo htmlspecialchars($admin['email'] ?? ''); ?>" placeholder="admin@example.com">
                    </div>

                    <!-- Password -->
                    <div>
                        <label class="block text-sm font-semibold mb-2">
                            Password <?php echo $action === 'create' ? '*' : '(Leave blank to keep current)'; ?>
                        </label>
                        <input type="password" name="password" <?php echo $action === 'create' ? 'required' : ''; ?> class="w-full px-4 py-2 border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-brand-cyan" placeholder="Minimum 8 characters">
                        <p class="text-xs text-gray-500 mt-1">Password must be at least 8 characters long.</p>
                    </div>

                    <!-- Info Box -->
                    <?php if ($action === 'edit'): ?>
                        <div class="p-4 bg-blue-50 border border-blue-200 rounded-lg text-sm">
                            <p class="text-blue-900"><strong>Note:</strong> Leave the password field empty to keep the current password. Only fill it if you want to reset the password.</p>
                        </div>
                    <?php endif; ?>

                    <!-- Buttons -->
                    <div class="flex gap-4 pt-4">
                        <button type="submit" class="px-6 py-2 bg-brand-cyan text-white rounded-lg hover:bg-brand-cyan/90 font-medium">
                            <?php echo $action === 'create' ? 'Create Admin' : 'Save Changes'; ?>
                        </button>
                        <a href="?action=list" class="px-6 py-2 bg-gray-300 text-gray-800 rounded-lg hover:bg-gray-400 font-medium">Cancel</a>
                    </div>
                </form>
            </div>
        <?php endif; ?>
    </main>

    <script>lucide.createIcons();</script>
</body>
</html>
