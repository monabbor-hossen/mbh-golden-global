<?php
/**
 * Admin - Site Settings Management
 * 
 * Update global site settings
 */

require_once '../includes/db.php';
require_once 'includes/auth.php';
requireAdmin();
require_once 'includes/flash.php';

// Read any flash message from a previous redirect (PRG pattern)
[$message, $message_type] = flash_get();

$settings = [];

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

        // PRG: flash success and redirect so F5 won't re-save
        flash_set('Settings updated successfully!');
        header('Location: settings.php');
        exit;
    } catch (PDOException $e) {
        error_log('Update error: ' . $e->getMessage());
        $message = 'Error updating settings.';
        $message_type = 'error';
    }
}


$page_title = 'Site Settings | Admin - MBH Golden Global';

$page_heading = 'Site Settings';
$page_actions = '';
require_once 'includes/header.php';
?>

            <?php if ($message): ?>
                <div class="mb-6 p-4 rounded-xl backdrop-blur-xl <?php echo $message_type === 'success' ? 'bg-green-500/20 border border-green-500/50 text-green-200' : 'bg-red-500/20 border border-red-500/50 text-red-200'; ?> shadow-[0_4px_30px_rgba(0,0,0,0.1)]">
                    <?php echo htmlspecialchars($message); ?>
                </div>
            <?php endif; ?>

            <div class="w-full max-w-3xl bg-white/5 backdrop-blur-xl border border-white/10 rounded-2xl p-5 md:p-8 shadow-[0_4px_30px_rgba(0,0,0,0.1)]">
                <p class="text-white/60 mb-8">Update your website's contact information and other key settings. These values are displayed across the website.</p>

                <form method="POST" class="space-y-8">
                    <!-- Phone Numbers Section -->
                    <div class="border-b border-white/10 pb-8">
                        <h3 class="text-lg font-serif text-white mb-6 flex items-center gap-2">
                            <i data-lucide="phone" class="w-5 h-5 text-brand-cyan"></i>
                            Phone Numbers
                        </h3>

                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-semibold mb-2 text-white/80">Primary Phone</label>
                                <input type="tel" name="phone_1" class="w-full px-4 py-3 bg-white/5 border border-white/10 rounded-xl text-white focus:outline-none focus:border-brand-cyan focus:bg-white/10 focus:ring-1 focus:ring-brand-cyan transition-all placeholder-white/30" value="<?php echo htmlspecialchars($settings['phone_1'] ?? ''); ?>" placeholder="+966-50-1234567">
                            </div>
                            <div>
                                <label class="block text-sm font-semibold mb-2 text-white/80">Secondary Phone</label>
                                <input type="tel" name="phone_2" class="w-full px-4 py-3 bg-white/5 border border-white/10 rounded-xl text-white focus:outline-none focus:border-brand-cyan focus:bg-white/10 focus:ring-1 focus:ring-brand-cyan transition-all placeholder-white/30" value="<?php echo htmlspecialchars($settings['phone_2'] ?? ''); ?>" placeholder="+966-50-9876543">
                            </div>
                        </div>
                    </div>

                    <!-- Email Section -->
                    <div class="border-b border-white/10 pb-8">
                        <h3 class="text-lg font-serif text-white mb-6 flex items-center gap-2">
                            <i data-lucide="mail" class="w-5 h-5 text-brand-cyan"></i>
                            Email Addresses
                        </h3>

                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-semibold mb-2 text-white/80">Primary Email</label>
                                <input type="email" name="email_1" class="w-full px-4 py-3 bg-white/5 border border-white/10 rounded-xl text-white focus:outline-none focus:border-brand-cyan focus:bg-white/10 focus:ring-1 focus:ring-brand-cyan transition-all placeholder-white/30" value="<?php echo htmlspecialchars($settings['email_1'] ?? ''); ?>" placeholder="info@mbhgolden.com">
                            </div>
                            <div>
                                <label class="block text-sm font-semibold mb-2 text-white/80">Secondary Email</label>
                                <input type="email" name="email_2" class="w-full px-4 py-3 bg-white/5 border border-white/10 rounded-xl text-white focus:outline-none focus:border-brand-cyan focus:bg-white/10 focus:ring-1 focus:ring-brand-cyan transition-all placeholder-white/30" value="<?php echo htmlspecialchars($settings['email_2'] ?? ''); ?>" placeholder="support@mbhgolden.com">
                            </div>
                        </div>
                    </div>

                    <!-- Address Section -->
                    <div>
                        <h3 class="text-lg font-serif text-white mb-6 flex items-center gap-2">
                            <i data-lucide="map-pin" class="w-5 h-5 text-brand-cyan"></i>
                            Address
                        </h3>

                        <div>
                            <label class="block text-sm font-semibold mb-2 text-white/80">Office Address</label>
                            <textarea name="address" rows="4" class="w-full px-4 py-3 bg-white/5 border border-white/10 rounded-xl text-white focus:outline-none focus:border-brand-cyan focus:bg-white/10 focus:ring-1 focus:ring-brand-cyan transition-all placeholder-white/30" placeholder="Enter your complete office address"><?php echo htmlspecialchars($settings['address'] ?? ''); ?></textarea>
                            <p class="text-xs text-white/40 mt-2">Include street, city, postal code, and country</p>
                        </div>
                    </div>

                    <!-- Submit Button -->
                    <div class="flex gap-4 pt-8">
                        <button type="submit" class="px-8 py-3 bg-gradient-to-r from-brand-cyan to-[#00aaff] text-white rounded-xl hover:shadow-[0_0_20px_rgba(0,130,202,0.4)] transition-all font-bold tracking-wide uppercase text-xs">
                            Save Settings
                        </button>
                        <a href="index.php" class="px-8 py-3 bg-white/5 text-white/80 border border-white/10 rounded-xl hover:bg-white/10 transition-all font-bold tracking-wide uppercase text-xs">Cancel</a>
                    </div>
                </form>

                <!-- Info Box -->
                <div class="mt-12 p-4 bg-brand-cyan/10 border border-brand-cyan/20 rounded-xl">
                    <div class="flex gap-3">
                        <i data-lucide="info" class="w-5 h-5 text-brand-cyan flex-shrink-0 mt-0.5"></i>
                        <div>
                            <p class="font-semibold text-brand-cyan">How these settings are used</p>
                            <p class="text-white/60 text-sm mt-1">These contact details appear in the website footer and contact information sections. Update them whenever your contact information changes.</p>
                        </div>
                    </div>
                </div>
            </div>
        <?php require_once 'includes/footer.php'; ?>
