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
    $stmt = $pdo->prepare("SELECT setting_key, setting_value FROM settings");
    $stmt->execute();
    $rows = $stmt->fetchAll();
    foreach ($rows as $row) {
        $settings[$row['setting_key']] = $row['setting_value'];
    }
} catch (PDOException $e) {
    error_log('Settings fetch error: ' . $e->getMessage());
}

$social_links_arr = [];
if (!empty($settings['social_links'])) {
    $decoded = json_decode($settings['social_links'], true);
    if (is_array($decoded)) {
        $social_links_arr = $decoded;
    }
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // 1. Verify CSRF Token (Assuming verify_csrf_token() exists in auth.php)
    if (isset($_POST['csrf_token'])) {
        verify_csrf_token($_POST['csrf_token']);
    }

    try {
        // 2. Update Standard Text Settings
        $standard_keys = ['phone_1', 'phone_2', 'email_1', 'email_2', 'address'];
        $stmt = $pdo->prepare("UPDATE settings SET setting_value = ? WHERE setting_key = ?");
        foreach ($standard_keys as $key) {
            if (isset($_POST[$key])) {
                $stmt->execute([trim($_POST[$key]), $key]);
            }
        }

        // 3. Safely Build Social Links Array
        $social_links = [];
        if (isset($_POST['social_platform']) && is_array($_POST['social_platform'])) {
            $count = count($_POST['social_platform']);
            for ($i = 0; $i < $count; $i++) {
                // Only add if the platform name isn't empty
                if (!empty(trim($_POST['social_platform'][$i]))) {
                    $social_links[] = [
                        'platform' => trim($_POST['social_platform'][$i]),
                        'icon'     => trim($_POST['social_icon'][$i] ?? 'link'),
                        'url'      => trim($_POST['social_url'][$i] ?? '#')
                    ];
                }
            }
        }
        
        // 4. Save Social Links (Upsert: Insert if missing, Update if exists)
        $social_json = json_encode($social_links);
        $stmtSocial = $pdo->prepare("INSERT INTO settings (setting_key, setting_value) VALUES ('social_links', ?) ON DUPLICATE KEY UPDATE setting_value = ?");
        $stmtSocial->execute([$social_json, $social_json]);

        // 5. Success Redirect
        flash_set("Settings updated successfully!");
        header("Location: settings.php");
        exit;

    } catch (PDOException $e) {
        // If it fails, show the EXACT database error so we know why
        $message = "Database Error: " . $e->getMessage();
        $message_type = "error";
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

            <div class="w-full max-w-4xl bg-white/5 backdrop-blur-xl border border-white/10 rounded-2xl p-5 md:p-8 shadow-[0_4px_30px_rgba(0,0,0,0.1)]">
                <p class="text-white/60 mb-8">Update your website's contact information and other key settings. These values are displayed across the website.</p>

                <form method="POST" class="space-y-10">
                    <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                    
                    <!-- Contact Section -->
                    <div>
                        <h3 class="text-lg font-serif text-white mb-6 flex items-center gap-2 border-b border-white/10 pb-3">
                            <i data-lucide="contact" class="w-5 h-5 text-brand-cyan"></i>
                            Contact Information
                        </h3>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-sm font-semibold mb-2 text-white/80">Primary Phone</label>
                                <input type="tel" name="phone_1" class="w-full px-4 py-3 bg-white/5 border border-white/10 rounded-xl text-white focus:outline-none focus:border-brand-cyan focus:bg-white/10 focus:ring-1 focus:ring-brand-cyan transition-all placeholder-white/30" value="<?php echo htmlspecialchars($settings['phone_1'] ?? ''); ?>" placeholder="+966-50-1234567">
                            </div>
                            <div>
                                <label class="block text-sm font-semibold mb-2 text-white/80">Secondary Phone</label>
                                <input type="tel" name="phone_2" class="w-full px-4 py-3 bg-white/5 border border-white/10 rounded-xl text-white focus:outline-none focus:border-brand-cyan focus:bg-white/10 focus:ring-1 focus:ring-brand-cyan transition-all placeholder-white/30" value="<?php echo htmlspecialchars($settings['phone_2'] ?? ''); ?>" placeholder="+966-50-9876543">
                            </div>
                            <div>
                                <label class="block text-sm font-semibold mb-2 text-white/80">Primary Email</label>
                                <input type="email" name="email_1" class="w-full px-4 py-3 bg-white/5 border border-white/10 rounded-xl text-white focus:outline-none focus:border-brand-cyan focus:bg-white/10 focus:ring-1 focus:ring-brand-cyan transition-all placeholder-white/30" value="<?php echo htmlspecialchars($settings['email_1'] ?? ''); ?>" placeholder="info@mbhgolden.com">
                            </div>
                            <div>
                                <label class="block text-sm font-semibold mb-2 text-white/80">Secondary Email</label>
                                <input type="email" name="email_2" class="w-full px-4 py-3 bg-white/5 border border-white/10 rounded-xl text-white focus:outline-none focus:border-brand-cyan focus:bg-white/10 focus:ring-1 focus:ring-brand-cyan transition-all placeholder-white/30" value="<?php echo htmlspecialchars($settings['email_2'] ?? ''); ?>" placeholder="support@mbhgolden.com">
                            </div>
                            <div class="md:col-span-2">
                                <label class="block text-sm font-semibold mb-2 text-white/80">Office Address</label>
                                <textarea name="address" rows="3" class="w-full px-4 py-3 bg-white/5 border border-white/10 rounded-xl text-white focus:outline-none focus:border-brand-cyan focus:bg-white/10 focus:ring-1 focus:ring-brand-cyan transition-all placeholder-white/30" placeholder="Enter your complete office address"><?php echo htmlspecialchars($settings['address'] ?? ''); ?></textarea>
                            </div>
                        </div>
                    </div>

                    <!-- Social Media Links Section -->
                    <div>
                        <div class="flex items-center justify-between border-b border-white/10 pb-3 mb-6">
                            <h3 class="text-lg font-serif text-white flex items-center gap-2">
                                <i data-lucide="share-2" class="w-5 h-5 text-brand-cyan"></i>
                                Social Media Links
                            </h3>
                            <button type="button" id="add-social-btn"
                                class="px-4 py-2 bg-brand-cyan/20 border border-brand-cyan/50 text-brand-cyan rounded-xl hover:bg-brand-cyan hover:text-white transition-all font-medium text-xs flex items-center gap-2">
                                <i data-lucide="plus" class="w-3 h-3"></i> Add New Link
                            </button>
                        </div>

                        <div id="social-repeater" class="space-y-4">
                            <?php if (empty($social_links_arr)): ?>
                                <!-- Blank Row if empty -->
                                <div class="social-row flex flex-col md:flex-row gap-4 items-end bg-white/5 p-4 rounded-xl border border-white/10">
                                    <div class="flex-1 w-full">
                                        <label class="block text-xs font-semibold mb-1 text-white/60">Platform Name</label>
                                        <input type="text" name="social_platform[]" placeholder="Facebook" class="w-full px-3 py-2 text-sm bg-white/5 border border-white/10 rounded-lg text-white focus:outline-none focus:border-brand-cyan">
                                    </div>
                                    <div class="flex-1 w-full">
                                        <label class="block text-xs font-semibold mb-1 text-white/60">Lucide Icon Name</label>
                                        <input type="text" name="social_icon[]" placeholder="facebook" class="w-full px-3 py-2 text-sm bg-white/5 border border-white/10 rounded-lg text-white focus:outline-none focus:border-brand-cyan">
                                    </div>
                                    <div class="flex-1 w-full md:flex-[2]">
                                        <label class="block text-xs font-semibold mb-1 text-white/60">URL</label>
                                        <input type="url" name="social_url[]" placeholder="https://facebook.com/..." class="w-full px-3 py-2 text-sm bg-white/5 border border-white/10 rounded-lg text-white focus:outline-none focus:border-brand-cyan">
                                    </div>
                                    <button type="button" class="remove-btn p-2.5 bg-red-500/20 text-red-400 border border-red-500/50 hover:bg-red-500 hover:text-white rounded-lg transition-colors" title="Remove Link">
                                        <i data-lucide="trash-2" class="w-4 h-4"></i>
                                    </button>
                                </div>
                            <?php else: ?>
                                <?php foreach ($social_links_arr as $link): ?>
                                    <div class="social-row flex flex-col md:flex-row gap-4 items-end bg-white/5 p-4 rounded-xl border border-white/10">
                                        <div class="flex-1 w-full">
                                            <label class="block text-xs font-semibold mb-1 text-white/60">Platform Name</label>
                                            <input type="text" name="social_platform[]" value="<?php echo htmlspecialchars($link['platform'] ?? ''); ?>" required class="w-full px-3 py-2 text-sm bg-white/5 border border-white/10 rounded-lg text-white focus:outline-none focus:border-brand-cyan">
                                        </div>
                                        <div class="flex-1 w-full">
                                            <label class="block text-xs font-semibold mb-1 text-white/60">Lucide Icon Name</label>
                                            <input type="text" name="social_icon[]" value="<?php echo htmlspecialchars($link['icon'] ?? ''); ?>" required class="w-full px-3 py-2 text-sm bg-white/5 border border-white/10 rounded-lg text-white focus:outline-none focus:border-brand-cyan">
                                        </div>
                                        <div class="flex-1 w-full md:flex-[2]">
                                            <label class="block text-xs font-semibold mb-1 text-white/60">URL</label>
                                            <input type="url" name="social_url[]" value="<?php echo htmlspecialchars($link['url'] ?? ''); ?>" required class="w-full px-3 py-2 text-sm bg-white/5 border border-white/10 rounded-lg text-white focus:outline-none focus:border-brand-cyan">
                                        </div>
                                        <button type="button" class="remove-btn p-2.5 bg-red-500/20 text-red-400 border border-red-500/50 hover:bg-red-500 hover:text-white rounded-lg transition-colors" title="Remove Link">
                                            <i data-lucide="trash-2" class="w-4 h-4"></i>
                                        </button>
                                    </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                        <p class="text-xs text-white/40 mt-3"><i data-lucide="info" class="w-3 h-3 inline"></i> Icon names must exactly match <a href="https://lucide.dev/icons/" target="_blank" class="text-brand-cyan hover:underline">Lucide icon names</a> (e.g., 'facebook', 'instagram', 'twitter', 'linkedin').</p>
                    </div>

                    <!-- Submit Button -->
                    <div class="flex gap-4 pt-8 border-t border-white/10">
                        <button type="submit" class="px-8 py-3 bg-gradient-to-r from-brand-cyan to-brand-cyanLight text-white rounded-xl hover:shadow-[0_0_20px_rgba(0,130,202,0.4)] transition-all font-bold tracking-wide uppercase text-xs">
                            Save Settings
                        </button>
                    </div>
                </form>
            </div>

            <script>
            document.addEventListener('DOMContentLoaded', function() {
                const repeater = document.getElementById('social-repeater');
                const addBtn = document.getElementById('add-social-btn');

                // Add new row logic
                addBtn.addEventListener('click', () => {
                    const row = document.createElement('div');
                    row.className = 'social-row flex flex-col md:flex-row gap-4 items-end bg-white/5 p-4 rounded-xl border border-white/10 mb-4 opacity-0 transition-opacity duration-300';
                    row.innerHTML = `
                        <div class="flex-1 w-full">
                            <label class="block text-xs font-semibold mb-1 text-white/60">Platform Name</label>
                            <input type="text" name="social_platform[]" placeholder="New Platform" class="w-full px-3 py-2 text-sm bg-white/5 border border-white/10 rounded-lg text-white focus:outline-none focus:border-brand-cyan">
                        </div>
                        <div class="flex-1 w-full">
                            <label class="block text-xs font-semibold mb-1 text-white/60">Lucide Icon Name</label>
                            <input type="text" name="social_icon[]" placeholder="icon-name" class="w-full px-3 py-2 text-sm bg-white/5 border border-white/10 rounded-lg text-white focus:outline-none focus:border-brand-cyan">
                        </div>
                        <div class="flex-1 w-full md:flex-[2]">
                            <label class="block text-xs font-semibold mb-1 text-white/60">URL</label>
                            <input type="url" name="social_url[]" placeholder="https://..." class="w-full px-3 py-2 text-sm bg-white/5 border border-white/10 rounded-lg text-white focus:outline-none focus:border-brand-cyan">
                        </div>
                        <button type="button" class="remove-btn p-2.5 bg-red-500/20 text-red-400 border border-red-500/50 hover:bg-red-500 hover:text-white rounded-lg transition-colors" title="Remove Link">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-trash-2 w-4 h-4"><path d="M3 6h18"/><path d="M19 6v14c0 1-1 2-2 2H7c-1 0-2-1-2-2V6"/><path d="M8 6V4c0-1 1-2 2-2h4c1 0 2 1 2 2v2"/><line x1="10" x2="10" y1="11" y2="17"/><line x1="14" x2="14" y1="11" y2="17"/></svg>
                        </button>
                    `;
                    repeater.appendChild(row);
                    
                    // Trigger reflow for fade-in effect
                    void row.offsetWidth;
                    row.classList.remove('opacity-0');
                });

                // Event delegation for remove button
                repeater.addEventListener('click', (e) => {
                    const btn = e.target.closest('.remove-btn');
                    if (btn) {
                        const row = btn.closest('.social-row');
                        row.classList.add('opacity-0', 'scale-95');
                        setTimeout(() => row.remove(), 300);
                    }
                });
            });
            </script>

        <?php require_once 'includes/footer.php'; ?>
