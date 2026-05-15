<?php
/**
 * Admin - Categories Management
 * 
 * CRUD operations for categories
 */

require_once '../includes/db.php';
require_once 'includes/auth.php';
requireAdmin(); // only admins can manage categories
require_once 'includes/flash.php';

// Generate CSRF token if not exists
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Read any flash message from a previous redirect (PRG pattern)
[$message, $message_type] = flash_get();

$categories = [];
$action = $_GET['action'] ?? 'list';
$category = null;

// Handle delete — PRG: always redirect after mutation
if ($action === 'delete' && isset($_GET['id'])) {
    verify_csrf_token($_GET['csrf_token'] ?? '');
    try {
        // Fetch category to delete associated image
        $stmt_fetch = $pdo->prepare("SELECT image FROM categories WHERE id = :id");
        $stmt_fetch->execute([':id' => $_GET['id']]);
        $cat_to_delete = $stmt_fetch->fetch();

        if ($cat_to_delete && !empty($cat_to_delete['image'])) {
            $file_path = __DIR__ . '/../assets/uploads/categories/' . $cat_to_delete['image'];
            if (file_exists($file_path)) {
                unlink($file_path);
            }
        }

        $stmt = $pdo->prepare("DELETE FROM categories WHERE id = :id");
        $stmt->execute([':id' => $_GET['id']]);
        flash_set('Category deleted successfully!');
    } catch (PDOException $e) {
        error_log('Delete error: ' . $e->getMessage());
        flash_set('Error deleting category.', 'error');
    }
    header('Location: categories.php');
    exit;
}

// Handle POST submissions (Create & Update)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf_token($_POST['csrf_token'] ?? '');
    
    $is_edit = isset($_POST['category_id']);
    
    try {
        $name = trim($_POST['name'] ?? '');
        $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $name), '-'));
        $existing_image = trim($_POST['existing_image'] ?? '');
        $image = $existing_image;
        $uploadError = false;

        // Image Upload Logic
        if (isset($_FILES['image']) && $_FILES['image']['size'] > 0) {
            $file = $_FILES['image'];
            $allowed_extensions = ['jpg', 'jpeg', 'png', 'webp'];
            $file_ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
            
            if (!in_array($file_ext, $allowed_extensions)) {
                $message = 'Invalid file type. Allowed: JPG, PNG, WEBP.';
                $message_type = 'error';
                $uploadError = true;
            } elseif ($file['error'] !== UPLOAD_ERR_OK) {
                $message = 'Upload error code: ' . $file['error'];
                $message_type = 'error';
                $uploadError = true;
            } else {
                $upload_dir = __DIR__ . '/../assets/uploads/categories';
                if (!is_dir($upload_dir)) {
                    mkdir($upload_dir, 0755, true);
                }
                $unique_name = uniqid('cat_', true) . '.' . $file_ext;
                $destination = $upload_dir . DIRECTORY_SEPARATOR . $unique_name;
                
                if (move_uploaded_file($file['tmp_name'], $destination)) {
                    $image = $unique_name;
                    chmod($destination, 0644);
                } else {
                    $message = 'Failed to save uploaded file.';
                    $message_type = 'error';
                    $uploadError = true;
                }
            }
        }

        if (empty($name)) {
            $message = 'Category name is required.';
            $message_type = 'error';
        } elseif (!$is_edit && empty($image) && !$uploadError) {
            $message = 'Category image is required for new categories.';
            $message_type = 'error';
        } elseif (!$uploadError) {
            if ($is_edit) {
                $id = $_POST['category_id'];
                
                // If a new image was uploaded successfully, delete the old one
                if ($image !== $existing_image && !empty($existing_image)) {
                    $old_file_path = __DIR__ . '/../assets/uploads/categories/' . $existing_image;
                    if (file_exists($old_file_path)) {
                        unlink($old_file_path);
                    }
                }

                $stmt = $pdo->prepare("UPDATE categories SET name = :name, slug = :slug, image = :image WHERE id = :id");
                $stmt->execute([
                    ':name' => $name,
                    ':slug' => $slug,
                    ':image' => $image,
                    ':id' => $id,
                ]);
                flash_set('Category updated successfully!');
                header('Location: categories.php');
                exit;
            } else {
                $stmt = $pdo->prepare("INSERT INTO categories (name, slug, image, created_at) VALUES (:name, :slug, :image, NOW())");
                $stmt->execute([
                    ':name' => $name,
                    ':slug' => $slug,
                    ':image' => $image
                ]);
                flash_set('New category created successfully!');
                header('Location: categories.php');
                exit;
            }
        }
    } catch (PDOException $e) {
        error_log('Database error: ' . $e->getMessage());
        $message = 'A system error occurred. Please try again.';
        $message_type = 'error';
    }
}

// Fetch category for edit
if ($action === 'edit' && isset($_GET['id'])) {
    try {
        $stmt = $pdo->prepare("SELECT * FROM categories WHERE id = :id");
        $stmt->execute([':id' => $_GET['id']]);
        $category = $stmt->fetch();
        if (!$category) {
            $action = 'list';
            $message = 'Category not found.';
            $message_type = 'error';
        }
    } catch (PDOException $e) {
        error_log('Fetch error: ' . $e->getMessage());
        $action = 'list';
    }
}

// Fetch all categories for list view
if ($action === 'list') {
    try {
        $stmt = $pdo->prepare("SELECT * FROM categories ORDER BY created_at DESC");
        $stmt->execute();
        $categories = $stmt->fetchAll();
    } catch (PDOException $e) {
        error_log('List fetch error: ' . $e->getMessage());
    }
}

$page_title = 'Categories | Admin - MBH Golden Global';
$page_heading = 'Categories';
ob_start(); ?>
<?php if ($action === 'list'): ?>
    <a href="?action=create" class="px-5 py-2 bg-brand-cyan/20 border border-brand-cyan/50 text-brand-cyan rounded-xl hover:bg-brand-cyan hover:text-white hover:shadow-[0_0_15px_rgba(0,130,202,0.4)] transition-all font-medium text-sm flex items-center gap-2">
        <i data-lucide="plus" class="w-4 h-4"></i> New Category
    </a>
<?php else: ?>
    <a href="?action=list" class="px-5 py-2 bg-white/5 border border-white/10 text-white/80 rounded-xl hover:bg-white/10 hover:text-white transition-all font-medium text-sm flex items-center gap-2">
        <i data-lucide="arrow-left" class="w-4 h-4"></i> Back to List
    </a>
<?php endif; ?>
<?php $page_actions = ob_get_clean();

require_once 'includes/header.php';
?>

<!-- Messages -->
<?php if ($message): ?>
    <div class="mb-6 p-4 rounded-xl backdrop-blur-xl <?php echo $message_type === 'success' ? 'bg-green-500/20 border border-green-500/50 text-green-200' : 'bg-red-500/20 border border-red-500/50 text-red-200'; ?> shadow-[0_4px_30px_rgba(0,0,0,0.1)]">
        <?php echo htmlspecialchars($message); ?>
    </div>
<?php endif; ?>

<!-- List View -->
<?php if ($action === 'list'): ?>
    <div class="bg-white/5 backdrop-blur-xl rounded-2xl shadow-[0_4px_30px_rgba(0,0,0,0.1)] border border-white/10 flex-1">
        <?php if (!empty($categories)): ?>
            <div class="w-full overflow-x-auto overflow-y-hidden rounded-xl border border-white/10">
                <table class="w-full min-w-max text-left border-collapse">
                    <thead>
                        <tr>
                            <th class="py-4 px-4 first:pl-6 last:pr-6 border-b border-white/10 text-xs font-semibold text-white/50 uppercase tracking-wider whitespace-nowrap">ID</th>
                            <th class="py-4 px-4 first:pl-6 last:pr-6 border-b border-white/10 text-xs font-semibold text-white/50 uppercase tracking-wider whitespace-nowrap">Image</th>
                            <th class="py-4 px-4 first:pl-6 last:pr-6 border-b border-white/10 text-xs font-semibold text-white/50 uppercase tracking-wider whitespace-nowrap">Name</th>
                            <th class="py-4 px-4 first:pl-6 last:pr-6 border-b border-white/10 text-xs font-semibold text-white/50 uppercase tracking-wider whitespace-nowrap">Slug</th>
                            <th class="py-4 px-4 first:pl-6 last:pr-6 border-b border-white/10 text-xs font-semibold text-white/50 uppercase tracking-wider whitespace-nowrap">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($categories as $cat): ?>
                            <tr class="hover:bg-white/5 transition-colors">
                                <td class="py-4 px-4 first:pl-6 last:pr-6 border-b border-white/5 text-sm text-white/80 whitespace-nowrap">#<?php echo $cat['id']; ?></td>
                                <td class="py-4 px-4 first:pl-6 last:pr-6 border-b border-white/5 text-sm text-white/80 whitespace-nowrap">
                                    <?php if (!empty($cat['image'])): ?>
                                        <img src="../assets/uploads/categories/<?php echo htmlspecialchars($cat['image']); ?>" alt="Category" class="w-12 h-12 rounded object-cover border border-white/20 shadow-[0_4px_30px_rgba(0,0,0,0.1)]">
                                    <?php else: ?>
                                        <div class="w-12 h-12 rounded bg-white/10 flex items-center justify-center border border-white/20">
                                            <i data-lucide="image" class="w-5 h-5 text-white/50"></i>
                                        </div>
                                    <?php endif; ?>
                                </td>
                                <td class="py-4 px-4 first:pl-6 last:pr-6 border-b border-white/5 text-sm text-white/80 whitespace-nowrap font-medium"><?php echo htmlspecialchars($cat['name']); ?></td>
                                <td class="py-4 px-4 first:pl-6 last:pr-6 border-b border-white/5 text-sm text-white/80 whitespace-nowrap"><span class="px-2 py-1 bg-brand-cyan/20 text-brand-cyan border border-brand-cyan/30 rounded text-xs"><?php echo htmlspecialchars($cat['slug']); ?></span></td>
                                <td class="py-4 px-4 first:pl-6 last:pr-6 border-b border-white/5 text-sm text-white/80 whitespace-nowrap">
                                    <div class="flex items-center gap-3">
                                        <a href="?action=edit&id=<?php echo $cat['id']; ?>" class="text-brand-cyan hover:text-white transition-colors p-2 hover:bg-white/10 rounded-lg inline-flex items-center justify-center">
                                            <i data-lucide="edit" class="w-4 h-4"></i>
                                        </a>
                                        <a href="?action=delete&id=<?php echo $cat['id']; ?>&csrf_token=<?php echo $_SESSION['csrf_token']; ?>" onclick="return confirm('Delete this category?')" class="text-red-400 hover:text-red-300 transition-colors p-2 hover:bg-red-500/20 rounded-lg inline-flex items-center justify-center">
                                            <i data-lucide="trash-2" class="w-4 h-4"></i>
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
                <i data-lucide="folder-tree" class="w-12 h-12 text-white/20 mx-auto mb-3"></i>
                <p class="text-white/50 text-sm mb-4">No categories found.</p>
                <a href="?action=create" class="inline-flex items-center gap-2 text-brand-cyan hover:text-white transition-colors text-sm font-medium">Create your first category <i data-lucide="arrow-right" class="w-4 h-4"></i></a>
            </div>
        <?php endif; ?>
    </div>

<!-- Create/Edit Form -->
<?php else: ?>
    <div class="fixed inset-0 p-4 flex items-center justify-center z-50 bg-brand-bg/80 backdrop-blur-sm">
        <div class="w-full max-w-lg bg-brand-bg/95 backdrop-blur-2xl border border-white/20 rounded-2xl p-6 md:p-8 max-h-[82vh] overflow-y-auto shadow-[0_4px_30px_rgba(0,0,0,0.1)]">
            <h3 class="text-2xl font-serif text-white mb-6">
                <?php echo $action === 'create' ? 'Create New Category' : 'Edit Category'; ?>
            </h3>

            <form method="POST" enctype="multipart/form-data" class="space-y-6">
                <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                <?php if ($action === 'edit' && $category): ?>
                    <input type="hidden" name="category_id" value="<?php echo $category['id']; ?>">
                <?php endif; ?>

                <div>
                    <label class="block text-sm font-semibold mb-2 text-white/80">Category Name *</label>
                    <input type="text" name="name" required
                        class="w-full px-4 py-3 bg-white/5 border border-white/10 rounded-xl text-white focus:outline-none focus:border-brand-cyan focus:bg-white/10 focus:ring-1 focus:ring-brand-cyan transition-all placeholder-white/30"
                        value="<?php echo htmlspecialchars($category['name'] ?? ''); ?>" placeholder="e.g., Adventure">
                    <p class="text-xs text-white/40 mt-2">The slug will be automatically generated from the name.</p>
                </div>

                <div>
                    <label class="block text-sm font-semibold mb-2 text-white/80">Category Image *</label>
                    <?php if ($action === 'edit' && !empty($category['image'])): ?>
                        <div class="mb-4">
                            <img src="../assets/uploads/categories/<?php echo htmlspecialchars($category['image']); ?>" alt="Current image"
                                class="w-full max-h-48 object-cover rounded-xl border border-white/20 shadow-[0_4px_30px_rgba(0,0,0,0.1)]">
                            <input type="hidden" name="existing_image" value="<?php echo htmlspecialchars($category['image']); ?>">
                        </div>
                    <?php endif; ?>
                    <input type="file" name="image" accept=".jpg,.jpeg,.png,.webp"
                        class="w-full text-sm text-white/60 file:mr-4 file:py-2.5 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-brand-cyan/20 file:text-brand-cyan hover:file:bg-brand-cyan/30 border border-white/10 rounded-xl bg-white/5 focus:outline-none transition-all cursor-pointer"
                        <?php echo $action === 'create' ? 'required' : ''; ?>>
                    <p class="text-xs text-white/40 mt-2">Upload JPG, PNG, or WEBP. Leave blank to preserve the current image when editing.</p>
                </div>

                <div class="flex gap-4 pt-4 border-t border-white/10">
                    <button type="submit"
                        class="px-8 py-3 bg-gradient-to-r from-brand-cyan to-brand-cyanLight text-white rounded-xl hover:shadow-[0_0_20px_rgba(0,130,202,0.4)] transition-all font-bold tracking-wide uppercase text-xs">
                        <?php echo $action === 'create' ? 'Create Category' : 'Save Changes'; ?>
                    </button>
                    <a href="?action=list"
                        class="px-8 py-3 bg-white/5 text-white/80 border border-white/10 rounded-xl hover:bg-white/10 transition-all font-bold tracking-wide uppercase text-xs">Cancel</a>
                </div>
            </form>
        </div>
    </div>
<?php endif; ?>

<?php require_once 'includes/footer.php'; ?>
