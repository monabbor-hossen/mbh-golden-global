<?php
/**
 * Admin - Site Settings Management
 * 
 * Update global site settings
 */

require_once '../includes/db.php';
require_once 'includes/auth.php';

$settings = [];
$message = '';
$message_type = '';

// Fetch current settings
try {
    $stmt = $pdo->prepare("SELECT key_name, value FROM settings");
    $stmt->execute();
    $rows = $stmt->fetchAll();
    foreach ($rows as $row) {
        $settings[$row['key_name']] = $row['value'];
    }
} catch (PDOException $e) {
    error_log('Settings fetch error: ' . $e->getMessage());
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $fields = ['phone_1', 'phone_2', 'email_1', 'email_2', 'address'];

        foreach ($fields as $field) {
            $value = trim($_POST[$field] ?? '');
            
            // Check if record exists
            $stmt = $pdo->prepare("SELECT id FROM settings WHERE key_name = :key");
            $stmt->execute([':key' => $field]);
            $exists = $stmt->fetch();

            if ($exists) {
                $stmt = $pdo->prepare("UPDATE settings SET value = :value WHERE key_name = :key");
                $stmt->execute([':value' => $value, ':key' => $field]);
            } else {
                $stmt = $pdo->prepare("INSERT INTO settings (key_name, value) VALUES (:key, :value)");
                $stmt->execute([':key' => $field, ':value' => $value]);
            }

            $settings[$field] = $value;
        }

        $message = 'Settings updated successfully!';
        $message_type = 'success';
    } catch (PDOException $e) {
        error_log('Update error: ' . $e->getMessage());
        $message = 'Error updating settings.';
        $message_type = 'error';
    }
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Site Settings | Admin - MBH Golden Global</title>
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
                    <a href="settings.php" class="text-brand-cyan font-semibold border-b-2 border-brand-cyan pb-1">Settings</a>
                </div>
            </div>
            <a href="logout.php" class="text-red-600 hover:bg-red-50 px-4 py-2 rounded text-sm">Logout</a>
        </div>
    </nav>

    <!-- Main Content -->
    <main class="max-w-3xl mx-auto px-6 py-12">
        <h2 class="text-3xl font-serif font-bold mb-8">Site Settings</h2>

        <?php if ($message): ?>
            <div class="mb-6 p-4 rounded-lg <?php echo $message_type === 'success' ? 'bg-green-100 border border-green-300' : 'bg-red-100 border border-red-300'; ?>">
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>

        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-8">
            <p class="text-gray-600 mb-8">Update your website's contact information and other key settings. These values are displayed across the website.</p>

            <form method="POST" class="space-y-8">
                <!-- Phone Numbers Section -->
                <div class="border-b pb-8">
                    <h3 class="text-lg font-bold mb-6 flex items-center gap-2">
                        <i data-lucide="phone" class="w-5 h-5 text-brand-cyan"></i>
                        Phone Numbers
                    </h3>

                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-semibold mb-2">Primary Phone</label>
                            <input type="tel" name="phone_1" class="w-full px-4 py-2 border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-brand-cyan" value="<?php echo htmlspecialchars($settings['phone_1'] ?? ''); ?>" placeholder="+966-50-1234567">
                        </div>
                        <div>
                            <label class="block text-sm font-semibold mb-2">Secondary Phone</label>
                            <input type="tel" name="phone_2" class="w-full px-4 py-2 border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-brand-cyan" value="<?php echo htmlspecialchars($settings['phone_2'] ?? ''); ?>" placeholder="+966-50-9876543">
                        </div>
                    </div>
                </div>

                <!-- Email Section -->
                <div class="border-b pb-8">
                    <h3 class="text-lg font-bold mb-6 flex items-center gap-2">
                        <i data-lucide="mail" class="w-5 h-5 text-brand-cyan"></i>
                        Email Addresses
                    </h3>

                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-semibold mb-2">Primary Email</label>
                            <input type="email" name="email_1" class="w-full px-4 py-2 border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-brand-cyan" value="<?php echo htmlspecialchars($settings['email_1'] ?? ''); ?>" placeholder="info@mbhgolden.com">
                        </div>
                        <div>
                            <label class="block text-sm font-semibold mb-2">Secondary Email</label>
                            <input type="email" name="email_2" class="w-full px-4 py-2 border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-brand-cyan" value="<?php echo htmlspecialchars($settings['email_2'] ?? ''); ?>" placeholder="support@mbhgolden.com">
                        </div>
                    </div>
                </div>

                <!-- Address Section -->
                <div>
                    <h3 class="text-lg font-bold mb-6 flex items-center gap-2">
                        <i data-lucide="map-pin" class="w-5 h-5 text-brand-cyan"></i>
                        Address
                    </h3>

                    <div>
                        <label class="block text-sm font-semibold mb-2">Office Address</label>
                        <textarea name="address" rows="4" class="w-full px-4 py-2 border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-brand-cyan" placeholder="Enter your complete office address"><?php echo htmlspecialchars($settings['address'] ?? ''); ?></textarea>
                        <p class="text-xs text-gray-500 mt-1">Include street, city, postal code, and country</p>
                    </div>
                </div>

                <!-- Submit Button -->
                <div class="flex gap-4 pt-8">
                    <button type="submit" class="px-8 py-3 bg-brand-cyan text-white rounded-lg hover:bg-brand-cyan/90 font-medium text-lg">
                        Save Settings
                    </button>
                    <a href="index.php" class="px-8 py-3 bg-gray-300 text-gray-800 rounded-lg hover:bg-gray-400 font-medium">Cancel</a>
                </div>
            </form>

            <!-- Info Box -->
            <div class="mt-12 p-4 bg-blue-50 border border-blue-200 rounded-lg">
                <div class="flex gap-3">
                    <i data-lucide="info" class="w-5 h-5 text-blue-600 flex-shrink-0 mt-0.5"></i>
                    <div>
                        <p class="font-semibold text-blue-900">How these settings are used</p>
                        <p class="text-blue-800 text-sm mt-1">These contact details appear in the website footer and contact information sections. Update them whenever your contact information changes.</p>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <script>lucide.createIcons();</script>
</body>
</html>
