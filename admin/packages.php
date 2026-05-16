<?php
/**
 * Admin - Tour Packages Management
 * 
 * CRUD operations for tour packages
 */

require_once '../includes/db.php';
require_once 'includes/auth.php';
require_once 'includes/upload.php';
require_once 'includes/flash.php';

// Read any flash message from a previous redirect (PRG pattern)
[$message, $message_type] = flash_get();

// Initialize variables
$packages = [];
$action = $_GET['action'] ?? 'list';
$package = null;

// Handle delete — PRG: always redirect after mutation
if ($action === 'delete' && isset($_GET['id'])) {
    verify_csrf_token($_GET['csrf_token'] ?? '');
    try {
        // Fetch package to delete associated images
        $stmt_fetch = $pdo->prepare("SELECT description, image_url FROM packages WHERE id = :id");
        $stmt_fetch->execute([':id' => $_GET['id']]);
        $pkg_to_delete = $stmt_fetch->fetch();

        if ($pkg_to_delete) {
            $upload_dir = realpath(__DIR__ . '/../assets/uploads');
            // Delete WYSIWYG images
            sync_wysiwyg_images($pkg_to_delete['description'], '', $upload_dir);
            // Delete main cover image
            if (!empty($pkg_to_delete['image_url'])) {
                $main_img_filename = basename($pkg_to_delete['image_url']);
                delete_image_file($main_img_filename, $upload_dir);
            }
        }

        $stmt = $pdo->prepare("DELETE FROM packages WHERE id = :id");
        $stmt->execute([':id' => $_GET['id']]);
        flash_set('Package deleted successfully!');
    } catch (PDOException $e) {
        error_log('Delete error: ' . $e->getMessage());
        flash_set('Error deleting package.', 'error');
    }
    header('Location: packages.php');
    exit;
}

// Handle edit form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['package_id'])) {
    verify_csrf_token($_POST['csrf_token'] ?? '');
    try {
        $id = $_POST['package_id'];
        $title = trim($_POST['title']);
        $location = trim($_POST['location']);
        $duration = trim($_POST['duration'] ?? '');
        $description = sanitize_wysiwyg_html(trim($_POST['description']));
        $price = (float) $_POST['price'];
        $existing_image_url = trim($_POST['existing_image_url'] ?? '');
        $image_url = $existing_image_url;
        $tag = $_POST['tag'] !== '' ? trim($_POST['tag']) : null;
        $is_active = isset($_POST['is_active']) ? 1 : 0;
        $uploadError = false;

        if (isset($_FILES['cover_image']) && $_FILES['cover_image']['size']> 0) {
            $upload_result = handle_image_upload($_FILES['cover_image']);
            if (!$upload_result['success']) {
                $message = 'Image upload failed: ' . $upload_result['error'];
                $message_type = 'error';
                $uploadError = true;
            } else {
                $image_url = $upload_result['url'];
            }
        }

        if (!$uploadError) {
            if (empty($title) || empty($location) || empty($description) || $price <= 0 || empty($image_url)) {
                $message = 'Please fill all required fields with valid data and upload a cover image.';
                $message_type = 'error';
            } else {
                $stmt_fetch = $pdo->prepare("SELECT description FROM packages WHERE id = :id");
                $stmt_fetch->execute([':id' => $id]);
                $old_record = $stmt_fetch->fetch();
                if ($old_record) {
                    $upload_dir = realpath(__DIR__ . '/../assets/uploads');
                    sync_wysiwyg_images($old_record['description'], $description, $upload_dir);
                }

                $stmt = $pdo->prepare("
                    UPDATE packages 
                    SET title = :title, location = :location, duration = :duration, 
                        description = :description, price = :price, image_url = :image_url, 
                        tag = :tag, is_active = :is_active 
                    WHERE id = :id
                ");

                $stmt->execute([
                    ':title' => $title,
                    ':location' => $location,
                    ':duration' => $duration,
                    ':description' => $description,
                    ':price' => $price,
                    ':image_url' => $image_url,
                    ':tag' => $tag,
                    ':is_active' => $is_active,
                    ':id' => $id,
                ]);

                // PRG: flash success and redirect so F5 won't re-POST
                flash_set('Package updated successfully!');
                header('Location: packages.php');
                exit;
            }
        }
    } catch (PDOException $e) {
        error_log('Update error: ' . $e->getMessage());
        $message = 'Error updating package.';
        $message_type = 'error';
    }
}

// Handle new package form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !isset($_POST['package_id'])) {
    verify_csrf_token($_POST['csrf_token'] ?? '');
    try {
        $title = trim($_POST['title']);
        $location = trim($_POST['location']);
        $duration = trim($_POST['duration'] ?? '');
        $description = sanitize_wysiwyg_html(trim($_POST['description']));
        $price = (float) $_POST['price'];
        $image_url = '';
        $tag = $_POST['tag'] !== '' ? trim($_POST['tag']) : null;
        $is_active = isset($_POST['is_active']) ? 1 : 0;
        $uploadError = false;

        if (!isset($_FILES['cover_image']) || $_FILES['cover_image']['size'] === 0) {
            $message = 'Please upload a cover image.';
            $message_type = 'error';
            $uploadError = true;
        } else {
            $upload_result = handle_image_upload($_FILES['cover_image']);
            if (!$upload_result['success']) {
                $message = 'Image upload failed: ' . $upload_result['error'];
                $message_type = 'error';
                $uploadError = true;
            } else {
                $image_url = $upload_result['url'];
            }
        }

        if (!$uploadError) {
            if (empty($title) || empty($location) || empty($description) || $price <= 0 || empty($image_url)) {
                $message = 'Please fill all required fields with valid data and upload a cover image.';
                $message_type = 'error';
            } else {
                $stmt = $pdo->prepare("
                    INSERT INTO packages (title, location, duration, description, price, image_url, tag, is_active, created_at) 
                    VALUES (:title, :location, :duration, :description, :price, :image_url, :tag, :is_active, NOW())
                ");

                $stmt->execute([
                    ':title' => $title,
                    ':location' => $location,
                    ':duration' => $duration,
                    ':description' => $description,
                    ':price' => $price,
                    ':image_url' => $image_url,
                    ':tag' => $tag,
                    ':is_active' => $is_active,
                ]);

                // PRG: flash success and redirect so F5 won't re-POST
                flash_set('Package created successfully!');
                header('Location: packages.php');
                exit;
            }
        }
    } catch (PDOException $e) {
        error_log('Insert error: ' . $e->getMessage());
        $message = 'Error creating package.';
        $message_type = 'error';
    }
}

// Fetch package for edit
if ($action === 'edit' && isset($_GET['id'])) {
    try {
        $stmt = $pdo->prepare("SELECT * FROM packages WHERE id = :id");
        $stmt->execute([':id' => $_GET['id']]);
        $package = $stmt->fetch();
        if (!$package) {
            $action = 'list';
            $message = 'Package not found.';
            $message_type = 'error';
        }
    } catch (PDOException $e) {
        error_log('Fetch error: ' . $e->getMessage());
        $action = 'list';
    }
}

// Fetch all packages for list view
if ($action === 'list') {
    try {
        $stmt = $pdo->prepare("
            SELECT * 
            FROM packages 
            ORDER BY created_at DESC
        ");
        $stmt->execute();
        $packages = $stmt->fetchAll();
    } catch (PDOException $e) {
        error_log('List fetch error: ' . $e->getMessage());
    }
}


$page_title = 'Tour Packages | Admin - MBH Golden Global';

$page_heading = 'Tour Packages';
ob_start(); ?><?php if ($action === 'list'): ?><a href="?action=create"
        class="px-5 py-2 bg-brand-cyan/20 border border-brand-cyan/50 text-brand-cyan rounded-xl hover:bg-brand-cyan hover:text-white hover:shadow-[0_0_15px_rgba(0,130,202,0.4)] transition-all font-medium text-sm flex items-center gap-2"><i class="fas fa-plus w-4 h-4"></i> New Package</a><?php else: ?><a href="?action=list"
        class="px-5 py-2 bg-white/5 border border-white/10 text-white/80 rounded-xl hover:bg-white/10 hover:text-white transition-all font-medium text-sm flex items-center gap-2"><i class="fas fa-arrow-left w-4 h-4"></i> Back to List</a><?php endif; ?>
<?php $page_actions = ob_get_clean();
require_once 'includes/header.php';
?>

<!-- Messages -->
<?php if ($message): ?>
    <div
        class="mb-6 p-4 rounded-xl backdrop-blur-xl <?php echo $message_type === 'success' ? 'bg-green-500/20 border border-green-500/50 text-green-200' : 'bg-red-500/20 border border-red-500/50 text-red-200'; ?> shadow-[0_4px_30px_rgba(0,0,0,0.1)]">
        <?php echo htmlspecialchars($message); ?>
    </div>
<?php endif; ?>

<!-- List View -->
<?php if ($action === 'list'): ?>
    <div class="bg-white/5 backdrop-blur-xl rounded-2xl shadow-[0_4px_30px_rgba(0,0,0,0.1)] border border-white/10 flex-1">
        <?php if (!empty($packages)): ?>
            <div class="w-full overflow-x-auto overflow-y-hidden rounded-xl border border-white/10">
                <table class="w-full min-w-max text-left border-collapse">
                    <thead>
                        <tr>
                            <th
                                class="py-4 px-4 first:pl-6 last:pr-6 border-b border-white/10 text-xs font-semibold text-white/50 uppercase tracking-wider whitespace-nowrap">
                                Title</th>
                            <th
                                class="py-4 px-4 first:pl-6 last:pr-6 border-b border-white/10 text-xs font-semibold text-white/50 uppercase tracking-wider whitespace-nowrap">
                                Location</th>
                            <th
                                class="py-4 px-4 first:pl-6 last:pr-6 border-b border-white/10 text-xs font-semibold text-white/50 uppercase tracking-wider whitespace-nowrap">
                                Price</th>
                            <th
                                class="py-4 px-4 first:pl-6 last:pr-6 border-b border-white/10 text-xs font-semibold text-white/50 uppercase tracking-wider whitespace-nowrap">
                                Status</th>
                            <th
                                class="py-4 px-4 first:pl-6 last:pr-6 border-b border-white/10 text-xs font-semibold text-white/50 uppercase tracking-wider whitespace-nowrap">
                                Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($packages as $pkg): ?>
                            <tr class="hover:bg-white/5 transition-colors">
                                <td
                                    class="py-4 px-4 first:pl-6 last:pr-6 border-b border-white/5 text-sm text-white/80 whitespace-nowrap font-medium">
                                    <?php echo htmlspecialchars($pkg['title']); ?>
                                </td>
                                <td
                                    class="py-4 px-4 first:pl-6 last:pr-6 border-b border-white/5 text-sm text-white/80 whitespace-nowrap">
                                    <?php echo htmlspecialchars($pkg['location']); ?>
                                </td>
                                <td
                                    class="py-4 px-4 first:pl-6 last:pr-6 border-b border-white/5 text-sm text-white/80 whitespace-nowrap font-semibold text-brand-cyan">
                                    SAR <?php echo number_format($pkg['price'], 0); ?></td>
                                <td
                                    class="py-4 px-4 first:pl-6 last:pr-6 border-b border-white/5 text-sm text-white/80 whitespace-nowrap">
                                    <?php if ($pkg['is_active']): ?>
                                        <span
                                            class="inline-flex items-center justify-center px-2.5 py-1 rounded-full text-[10px] uppercase tracking-wider font-bold bg-green-500/20 border border-green-500/50 text-green-300 shadow-[0_0_10px_rgba(34,197,94,0.2)]">Active</span>
                                    <?php else: ?>
                                        <span
                                            class="inline-flex items-center justify-center px-2.5 py-1 rounded-full text-[10px] uppercase tracking-wider font-bold bg-white/10 border border-white/20 text-white/50">Inactive</span>
                                    <?php endif; ?>
                                </td>
                                <td
                                    class="py-4 px-4 first:pl-6 last:pr-6 border-b border-white/5 text-sm text-white/80 whitespace-nowrap">
                                    <div class="flex items-center gap-3">
                                        <a href="?action=edit&id=<?php echo $pkg['id']; ?>"
                                            class="text-brand-cyan hover:text-white transition-colors p-2 hover:bg-white/10 rounded-lg inline-flex items-center justify-center">
                                            <i class="fas fa-edit w-4 h-4"></i>
                                        </a>
                                        <a href="?action=delete&id=<?php echo $pkg['id']; ?>&csrf_token=<?php echo $_SESSION['csrf_token']; ?>"
                                            onclick="return confirm('Delete this package?')"
                                            class="text-red-400 hover:text-red-300 transition-colors p-2 hover:bg-red-500/20 rounded-lg inline-flex items-center justify-center">
                                            <i class="fas fa-trash-alt w-4 h-4"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div class="text-center py-12 px-4 border border-white/5 rounded-xl bg-white/5 m-6">
                <i class="fas fa-box w-12 h-12 text-white/20 mx-auto mb-3"></i>
                <p class="text-white/50 text-sm mb-4">No packages found.</p>
                <a href="?action=create"
                    class="inline-flex items-center gap-2 text-brand-cyan hover:text-white transition-colors text-sm font-medium">Create
                    your first package <i class="fas fa-arrow-right w-4 h-4"></i></a>
            </div>
        <?php endif; ?>
    </div>

    <!-- Create/Edit Form -->
<?php else: ?>
    <div class="fixed inset-0 p-4 flex items-center justify-center z-50 bg-brand-bg/80 backdrop-blur-sm">
        <div
            class="w-full max-w-3xl bg-brand-bg/95 backdrop-blur-2xl border border-white/20 rounded-2xl p-6 md:p-8 max-h-[82vh] overflow-y-auto shadow-[0_4px_30px_rgba(0,0,0,0.1)]">
            <h3 class="text-2xl font-serif text-white mb-6">
                <?php echo $action === 'create' ? 'Create New Package' : 'Edit Package'; ?>
            </h3>

            <form method="POST" enctype="multipart/form-data" class="space-y-6">
                <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                <?php if ($action === 'edit' && $package): ?>
                    <input type="hidden" name="package_id" value="<?php echo $package['id']; ?>">
                <?php endif; ?>

                <!-- Title -->
                <div>
                    <label class="block text-sm font-semibold mb-2 text-white/80">Package Title *</label>
                    <input type="text" name="title" required
                        class="w-full px-4 py-3 bg-white/5 border border-white/10 rounded-xl text-white focus:outline-none focus:border-brand-cyan focus:bg-white/10 focus:ring-1 focus:ring-brand-cyan transition-all placeholder-white/30"
                        value="<?php echo htmlspecialchars($package['title'] ?? ''); ?>"
                        placeholder="e.g., Swiss Alps Adventure">
                </div>

                <!-- Location -->
                <div>
                    <label class="block text-sm font-semibold mb-2 text-white/80">Location *</label>
                    <input type="text" name="location" required
                        class="w-full px-4 py-3 bg-white/5 border border-white/10 rounded-xl text-white focus:outline-none focus:border-brand-cyan focus:bg-white/10 focus:ring-1 focus:ring-brand-cyan transition-all placeholder-white/30"
                        value="<?php echo htmlspecialchars($package['location'] ?? ''); ?>" placeholder="e.g., Switzerland">
                </div>

                <!-- Duration -->
                <div>
                    <label class="block text-sm font-semibold mb-2 text-white/80">Duration (Optional)</label>
                    <input type="text" name="duration"
                        class="w-full px-4 py-3 bg-white/5 border border-white/10 rounded-xl text-white focus:outline-none focus:border-brand-cyan focus:bg-white/10 focus:ring-1 focus:ring-brand-cyan transition-all placeholder-white/30"
                        value="<?php echo htmlspecialchars($package['duration'] ?? ''); ?>" placeholder="e.g., 5 Days / 4 Nights">
                </div>

                <!-- Description -->
                <div>
                    <label class="block text-sm font-semibold mb-2 text-white/80">Description *</label>
                    <div id="quill-editor"
                        class="bg-white/90 border border-white/40 text-brand-navy rounded-b-xl backdrop-blur-2xl w-full min-h-[250px] md:min-h-[300px] overflow-hidden">
                    </div>
                    <input type="hidden" name="description" id="content-input"
                        value="<?php echo htmlspecialchars($package['description'] ?? ''); ?>">
                </div>

                <!-- Price -->
                <div>
                    <label class="block text-sm font-semibold mb-2 text-white/80">Price (SAR) *</label>
                    <input type="number" name="price" required step="0.01" min="0"
                        class="w-full px-4 py-3 bg-white/5 border border-white/10 rounded-xl text-white focus:outline-none focus:border-brand-cyan focus:bg-white/10 focus:ring-1 focus:ring-brand-cyan transition-all placeholder-white/30"
                        value="<?php echo htmlspecialchars($package['price'] ?? ''); ?>" placeholder="5500.00">
                </div>

                <!-- Cover Image -->
                <div>
                    <label class="block text-sm font-semibold mb-2 text-white/80">Cover Image *</label>
                    <?php if ($action === 'edit' && !empty($package['image_url'])): ?>
                        <div class="mb-4">
                            <img src="<?php echo htmlspecialchars($package['image_url']); ?>" alt="Current cover image"
                                class="w-full max-h-60 object-cover rounded-xl border border-white/20 shadow-[0_4px_30px_rgba(0,0,0,0.1)]">
                            <input type="hidden" name="existing_image_url"
                                value="<?php echo htmlspecialchars($package['image_url']); ?>">
                        </div>
                    <?php endif; ?>
                    <input type="file" name="cover_image" accept=".jpg,.jpeg,.png,.webp"
                        class="w-full text-sm text-white/60 file:mr-4 file:py-2.5 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-brand-cyan/20 file:text-brand-cyan hover:file:bg-brand-cyan/30 border border-white/10 rounded-xl bg-white/5 focus:outline-none transition-all cursor-pointer"
                        <?php echo $action === 'create' ? 'required' : ''; ?>>
                    <p class="text-xs text-white/40 mt-2">Upload JPG, PNG, or WEBP. Leave blank to preserve the current
                        cover image.</p>
                </div>

                <!-- Tag -->
                <div>
                    <label class="block text-sm font-semibold mb-2 text-white/80">Tag (Optional)</label>
                    <input type="text" name="tag"
                        class="w-full px-4 py-3 bg-white/5 border border-white/10 rounded-xl text-white focus:outline-none focus:border-brand-cyan focus:bg-white/10 focus:ring-1 focus:ring-brand-cyan transition-all placeholder-white/30"
                        value="<?php echo htmlspecialchars($package['tag'] ?? ''); ?>"
                        placeholder="e.g., Best Seller, Trending">
                </div>

                <!-- Active Status -->
                <div class="flex items-center gap-3 bg-white/5 p-4 rounded-xl border border-white/10">
                    <input type="checkbox" id="is_active" name="is_active" <?php echo ($action === 'create' || $package['is_active']) ? 'checked' : ''; ?>
                        class="w-5 h-5 accent-brand-cyan bg-white/10 border-white/20 rounded">
                    <label for="is_active" class="text-sm font-medium text-white/90 cursor-pointer">Active / Visible on
                        site</label>
                </div>

                <!-- Buttons -->
                <div class="flex gap-4 pt-4 border-t border-white/10">
                    <button type="submit"
                        class="px-8 py-3 bg-gradient-to-r from-brand-cyan to-brand-cyanLight text-white rounded-xl hover:shadow-[0_0_20px_rgba(0,130,202,0.4)] transition-all font-bold tracking-wide uppercase text-xs">
                        <?php echo $action === 'create' ? 'Create Package' : 'Save Changes'; ?>
                    </button>
                    <a href="?action=list"
                        class="px-8 py-3 bg-white/5 text-white/80 border border-white/10 rounded-xl hover:bg-white/10 transition-all font-bold tracking-wide uppercase text-xs">Cancel</a>
                </div>
            </form>
        </div>
    </div>
<?php endif; ?>
<?php require_once 'includes/footer.php'; ?>