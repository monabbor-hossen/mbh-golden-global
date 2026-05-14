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
$packages  = [];
$categories = [];
$action    = $_GET['action'] ?? 'list';
$package   = null;

try {
    // Fetch all categories
    $stmt = $pdo->prepare("SELECT id, name FROM categories ORDER BY name");
    $stmt->execute();
    $categories = $stmt->fetchAll();
} catch (PDOException $e) {
    error_log('Categories fetch error: ' . $e->getMessage());
}

// Handle delete — PRG: always redirect after mutation
if ($action === 'delete' && isset($_GET['id'])) {
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
    try {
        $id = $_POST['package_id'];
        $category_id = $_POST['category_id'] !== '' ? $_POST['category_id'] : null;
        $title = trim($_POST['title']);
        $location = trim($_POST['location']);
        $description = trim($_POST['description']);
        $price = (float) $_POST['price'];
        $existing_image_url = trim($_POST['existing_image_url'] ?? '');
        $image_url = $existing_image_url;
        $tag = $_POST['tag'] !== '' ? trim($_POST['tag']) : null;
        $is_active = isset($_POST['is_active']) ? 1 : 0;
        $uploadError = false;

        if (isset($_FILES['cover_image']) && $_FILES['cover_image']['size'] > 0) {
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
                    SET category_id = :category_id, title = :title, location = :location, 
                        description = :description, price = :price, image_url = :image_url, 
                        tag = :tag, is_active = :is_active 
                    WHERE id = :id
                ");

                $stmt->execute([
                    ':category_id' => $category_id,
                    ':title' => $title,
                    ':location' => $location,
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
    try {
        $category_id = $_POST['category_id'] !== '' ? $_POST['category_id'] : null;
        $title = trim($_POST['title']);
        $location = trim($_POST['location']);
        $description = trim($_POST['description']);
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
                    INSERT INTO packages (category_id, title, location, description, price, image_url, tag, is_active, created_at) 
                    VALUES (:category_id, :title, :location, :description, :price, :image_url, :tag, :is_active, NOW())
                ");

                $stmt->execute([
                    ':category_id' => $category_id,
                    ':title' => $title,
                    ':location' => $location,
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
            SELECT p.*, c.name as category_name 
            FROM packages p 
            LEFT JOIN categories c ON p.category_id = c.id 
            ORDER BY p.created_at DESC
        ");
        $stmt->execute();
        $packages = $stmt->fetchAll();
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
    <title>Manage Packages | Admin - MBH Golden Global</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/lucide@latest"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Playfair+Display:ital,wght@0,400;0,500;0,600;0,700&display=swap" rel="stylesheet">
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
                <h1 class="text-2xl font-serif font-bold">
                    <span class="text-brand-cyan">MBH</span> Admin
                </h1>
                <div class="hidden md:flex gap-6">
                    <a href="index.php" class="hover:text-brand-cyan transition">Dashboard</a>
                    <a href="packages.php" class="text-brand-cyan font-semibold border-b-2 border-brand-cyan pb-1">Packages</a>
                    <a href="stories.php" class="hover:text-brand-cyan transition">Stories</a>
                    <a href="inquiries.php" class="hover:text-brand-cyan transition">Inquiries</a>
                    <a href="settings.php" class="hover:text-brand-cyan transition">Settings</a>
                </div>
            </div>
            <a href="logout.php" class="text-red-600 hover:bg-red-50 px-4 py-2 rounded text-sm">Logout</a>
        </div>
    </nav>

    <!-- Main Content -->
    <main class="max-w-7xl mx-auto px-6 py-12">
        <!-- Header -->
        <div class="flex justify-between items-center mb-8">
            <h2 class="text-3xl font-serif font-bold">Tour Packages</h2>
            <?php if ($action === 'list'): ?>
                <a href="?action=create" class="px-6 py-2 bg-brand-cyan text-white rounded-lg hover:bg-brand-cyan/90 font-medium">
                    + New Package
                </a>
            <?php else: ?>
                <a href="?action=list" class="px-6 py-2 bg-gray-300 text-gray-800 rounded-lg hover:bg-gray-400 font-medium">
                    ← Back to List
                </a>
            <?php endif; ?>
        </div>

        <!-- Messages -->
        <?php if ($message): ?>
            <div class="mb-6 p-4 rounded-lg <?php echo $message_type === 'success' ? 'bg-green-100 text-green-700 border border-green-300' : 'bg-red-100 text-red-700 border border-red-300'; ?>">
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>

        <!-- List View -->
        <?php if ($action === 'list'): ?>
            <div class="bg-white rounded-xl shadow-sm border border-gray-100">
                <?php if (!empty($packages)): ?>
                    <div class="overflow-x-auto">
                        <table class="w-full">
                            <thead>
                                <tr class="border-b border-gray-200">
                                    <th class="text-left py-4 px-6 text-xs font-semibold text-gray-600 uppercase">Title</th>
                                    <th class="text-left py-4 px-6 text-xs font-semibold text-gray-600 uppercase">Location</th>
                                    <th class="text-left py-4 px-6 text-xs font-semibold text-gray-600 uppercase">Category</th>
                                    <th class="text-left py-4 px-6 text-xs font-semibold text-gray-600 uppercase">Price</th>
                                    <th class="text-left py-4 px-6 text-xs font-semibold text-gray-600 uppercase">Status</th>
                                    <th class="text-left py-4 px-6 text-xs font-semibold text-gray-600 uppercase">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($packages as $pkg): ?>
                                    <tr class="border-b border-gray-100 hover:bg-gray-50 transition">
                                        <td class="py-4 px-6 font-medium"><?php echo htmlspecialchars($pkg['title']); ?></td>
                                        <td class="py-4 px-6 text-sm"><?php echo htmlspecialchars($pkg['location']); ?></td>
                                        <td class="py-4 px-6 text-sm"><?php echo htmlspecialchars($pkg['category_name'] ?? '-'); ?></td>
                                        <td class="py-4 px-6 font-semibold">SAR <?php echo number_format($pkg['price'], 0); ?></td>
                                        <td class="py-4 px-6 text-sm">
                                            <span class="px-3 py-1 rounded-full text-xs font-semibold <?php echo $pkg['is_active'] ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-700'; ?>">
                                                <?php echo $pkg['is_active'] ? 'Active' : 'Inactive'; ?>
                                            </span>
                                        </td>
                                        <td class="py-4 px-6 text-sm">
                                            <a href="?action=edit&id=<?php echo $pkg['id']; ?>" class="text-brand-cyan hover:underline mr-3">Edit</a>
                                            <a href="?action=delete&id=<?php echo $pkg['id']; ?>" onclick="return confirm('Delete this package?')" class="text-red-600 hover:underline">Delete</a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <p class="text-center py-12 text-gray-500">No packages found. <a href="?action=create" class="text-brand-cyan hover:underline">Create one</a></p>
                <?php endif; ?>
            </div>

        <!-- Create/Edit Form -->
        <?php else: ?>
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-8 max-w-2xl">
                <h3 class="text-2xl font-bold mb-6"><?php echo $action === 'create' ? 'Create New Package' : 'Edit Package'; ?></h3>

                <form method="POST" enctype="multipart/form-data" class="space-y-6">
                    <?php if ($action === 'edit' && $package): ?>
                        <input type="hidden" name="package_id" value="<?php echo $package['id']; ?>">
                    <?php endif; ?>

                    <!-- Title -->
                    <div>
                        <label class="block text-sm font-semibold mb-2">Package Title *</label>
                        <input type="text" name="title" required class="w-full px-4 py-2 border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-brand-cyan" value="<?php echo htmlspecialchars($package['title'] ?? ''); ?>" placeholder="e.g., Swiss Alps Adventure">
                    </div>

                    <!-- Location -->
                    <div>
                        <label class="block text-sm font-semibold mb-2">Location *</label>
                        <input type="text" name="location" required class="w-full px-4 py-2 border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-brand-cyan" value="<?php echo htmlspecialchars($package['location'] ?? ''); ?>" placeholder="e.g., Switzerland">
                    </div>

                    <!-- Category -->
                    <div>
                        <label class="block text-sm font-semibold mb-2">Category</label>
                        <select name="category_id" class="w-full px-4 py-2 border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-brand-cyan">
                            <option value="">Select a category</option>
                            <?php foreach ($categories as $cat): ?>
                                <option value="<?php echo $cat['id']; ?>" <?php echo ($package && $package['category_id'] == $cat['id']) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($cat['name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <!-- Description -->
                    <div>
                        <label class="block text-sm font-semibold mb-2">Description *</label>
                        <div id="quill-editor" class="bg-white/80 border border-white/40 text-[#003355] rounded-b-xl backdrop-blur-2xl" style="min-height: 300px;"><?php echo $package['description'] ?? ''; ?></div>
                        <input type="hidden" name="description" id="content-input">
                    </div>

                    <!-- Price -->
                    <div>
                        <label class="block text-sm font-semibold mb-2">Price (SAR) *</label>
                        <input type="number" name="price" required step="0.01" min="0" class="w-full px-4 py-2 border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-brand-cyan" value="<?php echo htmlspecialchars($package['price'] ?? ''); ?>" placeholder="5500.00">
                    </div>

                    <!-- Cover Image -->
                    <div>
                        <label class="block text-sm font-semibold mb-2">Cover Image *</label>
                        <?php if ($action === 'edit' && !empty($package['image_url'])): ?>
                            <div class="mb-4">
                                <img src="<?php echo htmlspecialchars($package['image_url']); ?>" alt="Current cover image" class="w-full max-h-60 object-cover rounded-lg border border-gray-200">
                                <input type="hidden" name="existing_image_url" value="<?php echo htmlspecialchars($package['image_url']); ?>">
                            </div>
                        <?php endif; ?>
                        <input type="file" name="cover_image" accept=".jpg,.jpeg,.png,.webp" class="w-full text-sm text-gray-600 file:border-0 file:bg-brand-cyan file:text-white file:px-4 file:py-2 rounded-lg border border-gray-200 focus:outline-none focus:ring-2 focus:ring-brand-cyan" <?php echo $action === 'create' ? 'required' : ''; ?>>
                        <p class="text-xs text-gray-500 mt-2">Upload JPG, PNG, or WEBP. Leave blank to preserve the current cover image.</p>
                    </div>

                    <!-- Tag -->
                    <div>
                        <label class="block text-sm font-semibold mb-2">Tag (Optional)</label>
                        <input type="text" name="tag" class="w-full px-4 py-2 border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-brand-cyan" value="<?php echo htmlspecialchars($package['tag'] ?? ''); ?>" placeholder="e.g., Best Seller, Trending">
                    </div>

                    <!-- Active Status -->
                    <div class="flex items-center gap-2">
                        <input type="checkbox" id="is_active" name="is_active" <?php echo ($action === 'create' || $package['is_active']) ? 'checked' : ''; ?> class="w-4 h-4">
                        <label for="is_active" class="text-sm font-medium">Active</label>
                    </div>

                    <!-- Buttons -->
                    <div class="flex gap-4 pt-4">
                        <button type="submit" class="px-6 py-2 bg-brand-cyan text-white rounded-lg hover:bg-brand-cyan/90 font-medium">
                            <?php echo $action === 'create' ? 'Create Package' : 'Save Changes'; ?>
                        </button>
                        <a href="?action=list" class="px-6 py-2 bg-gray-300 text-gray-800 rounded-lg hover:bg-gray-400 font-medium">Cancel</a>
                    </div>
                </form>
            </div>
        <?php endif; ?>
    <?php require_once 'includes/footer.php'; ?>
