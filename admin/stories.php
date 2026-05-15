<?php
/**
 * Admin - Blog Stories Management
 * 
 * CRUD operations for travel stories with WYSIWYG editor and image uploads
 */

require_once '../includes/db.php';
require_once 'includes/auth.php';
require_once 'includes/upload.php';
require_once 'includes/flash.php';

// Read any flash message from a previous redirect (PRG pattern)
[$message, $message_type] = flash_get();

$stories = [];
$action  = $_GET['action'] ?? 'list';
$story   = null;

// Handle delete — PRG: always redirect after mutation
if ($action === 'delete' && isset($_GET['id'])) {
    try {
        // Fetch story to delete associated images
        $stmt_fetch = $pdo->prepare("SELECT content, image_url FROM stories WHERE id = :id");
        $stmt_fetch->execute([':id' => $_GET['id']]);
        $story_to_delete = $stmt_fetch->fetch();
        
        if ($story_to_delete) {
            $upload_dir = realpath(__DIR__ . '/../assets/uploads');
            // Delete WYSIWYG images
            sync_wysiwyg_images($story_to_delete['content'], '', $upload_dir);
            // Delete main cover image
            if (!empty($story_to_delete['image_url'])) {
                $main_img_filename = basename($story_to_delete['image_url']);
                delete_image_file($main_img_filename, $upload_dir);
            }
        }

        $stmt = $pdo->prepare("DELETE FROM stories WHERE id = :id");
        $stmt->execute([':id' => $_GET['id']]);
        flash_set('Story deleted successfully!');
    } catch (PDOException $e) {
        error_log('Delete error: ' . $e->getMessage());
        flash_set('Error deleting story.', 'error');
    }
    header('Location: stories.php');
    exit;
}

// Handle edit form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['story_id'])) {
    try {
        $id = $_POST['story_id'];
        $tag = trim($_POST['tag']);
        $title = trim($_POST['title']);
        $slug = strtolower(str_replace(' ', '-', $title));
        $excerpt = trim($_POST['excerpt']);
        $content = trim($_POST['content']);
        $published_date = $_POST['published_date'];
        $is_published = isset($_POST['is_published']) ? 1 : 0;

        // Fetch existing image_url and content
        $stmt = $pdo->prepare("SELECT content, image_url FROM stories WHERE id = :id");
        $stmt->execute([':id' => $id]);
        $existing_story = $stmt->fetch();
        $image_url = $existing_story['image_url'];
        $old_content = $existing_story['content'] ?? '';

        // Handle image upload if file provided
        if (isset($_FILES['cover_image']) && $_FILES['cover_image']['size'] > 0) {
            $upload_result = handle_image_upload($_FILES['cover_image']);
            if (!$upload_result['success']) {
                $message = 'Image upload failed: ' . $upload_result['error'];
                $message_type = 'error';
            } else {
                $image_url = $upload_result['url'];
            }
        }

        if (empty($tag) || empty($title) || empty($excerpt) || empty($content) || empty($image_url) || empty($published_date)) {
            $message = 'Please fill all required fields.';
            $message_type = 'error';
        } elseif (!isset($upload_result) || $upload_result['success'] !== false) {
            $upload_dir = realpath(__DIR__ . '/../assets/uploads');
            sync_wysiwyg_images($old_content, $content, $upload_dir);

            $stmt = $pdo->prepare("
                UPDATE stories 
                SET tag = :tag, title = :title, slug = :slug, excerpt = :excerpt, 
                    content = :content, image_url = :image_url, published_date = :published_date, 
                    is_published = :is_published 
                WHERE id = :id
            ");

            $stmt->execute([
                ':tag' => $tag,
                ':title' => $title,
                ':slug' => $slug,
                ':excerpt' => $excerpt,
                ':content' => $content,
                ':image_url' => $image_url,
                ':published_date' => $published_date,
                ':is_published' => $is_published,
                ':id' => $id,
            ]);

            // PRG: flash success and redirect so F5 won't re-POST
            flash_set('Story updated successfully!');
            header('Location: stories.php');
            exit;
        }
    } catch (PDOException $e) {
        error_log('Update error: ' . $e->getMessage());
        $message = 'Error updating story.';
        $message_type = 'error';
    }
}

// Handle new story form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !isset($_POST['story_id'])) {
    try {
        $tag = trim($_POST['tag']);
        $title = trim($_POST['title']);
        $slug = strtolower(str_replace(' ', '-', $title));
        $excerpt = trim($_POST['excerpt']);
        $content = trim($_POST['content']);
        $published_date = $_POST['published_date'];
        $is_published = isset($_POST['is_published']) ? 1 : 0;
        $image_url = '';

        // Handle image upload
        if (!isset($_FILES['cover_image']) || $_FILES['cover_image']['size'] === 0) {
            $message = 'Please upload a cover image.';
            $message_type = 'error';
        } else {
            $upload_result = handle_image_upload($_FILES['cover_image']);
            if (!$upload_result['success']) {
                $message = 'Image upload failed: ' . $upload_result['error'];
                $message_type = 'error';
            } else {
                $image_url = $upload_result['url'];
            }
        }

        if (empty($tag) || empty($title) || empty($excerpt) || empty($content) || empty($published_date) || empty($image_url)) {
            if (empty($message)) {
                $message = 'Please fill all required fields.';
                $message_type = 'error';
            }
        } elseif (isset($upload_result) && $upload_result['success']) {
            $stmt = $pdo->prepare("
                INSERT INTO stories (tag, title, slug, excerpt, content, image_url, published_date, is_published, created_at) 
                VALUES (:tag, :title, :slug, :excerpt, :content, :image_url, :published_date, :is_published, NOW())
            ");

            $stmt->execute([
                ':tag' => $tag,
                ':title' => $title,
                ':slug' => $slug,
                ':excerpt' => $excerpt,
                ':content' => $content,
                ':image_url' => $image_url,
                ':published_date' => $published_date,
                ':is_published' => $is_published,
            ]);

            // PRG: flash success and redirect so F5 won't re-POST
            flash_set('Story created successfully!');
            header('Location: stories.php');
            exit;
        }
    } catch (PDOException $e) {
        error_log('Insert error: ' . $e->getMessage());
        $message = 'Error creating story.';
        $message_type = 'error';
    }
}

// Fetch story for edit
if ($action === 'edit' && isset($_GET['id'])) {
    try {
        $stmt = $pdo->prepare("SELECT * FROM stories WHERE id = :id");
        $stmt->execute([':id' => $_GET['id']]);
        $story = $stmt->fetch();
        if (!$story) {
            $action = 'list';
            $message = 'Story not found.';
            $message_type = 'error';
        }
    } catch (PDOException $e) {
        error_log('Fetch error: ' . $e->getMessage());
        $action = 'list';
    }
}

// Fetch all stories for list view
if ($action === 'list') {
    try {
        $stmt = $pdo->prepare("SELECT * FROM stories ORDER BY published_date DESC");
        $stmt->execute();
        $stories = $stmt->fetchAll();
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
    <title>Manage Stories | Admin - MBH Golden Global</title>
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
                    <a href="stories.php" class="text-brand-cyan font-semibold border-b-2 border-brand-cyan pb-1">Stories</a>
                    <a href="inquiries.php" class="hover:text-brand-cyan">Inquiries</a>
                    <a href="settings.php" class="hover:text-brand-cyan">Settings</a>
                </div>
            </div>
            <a href="logout.php" class="text-red-600 hover:bg-red-50 px-4 py-2 rounded text-sm">Logout</a>
        </div>
    </nav>

    <!-- Main Content -->
    <main class="max-w-7xl mx-auto px-6 py-12">
        <div class="flex justify-between items-center mb-8">
            <h2 class="text-3xl font-serif font-bold">Travel Stories</h2>
            <?php if ($action === 'list'): ?>
                <a href="?action=create" class="px-6 py-2 bg-brand-cyan text-white rounded-lg font-medium">+ New Story</a>
            <?php else: ?>
                <a href="?action=list" class="px-6 py-2 bg-gray-300 text-gray-800 rounded-lg font-medium">← Back</a>
            <?php endif; ?>
        </div>

        <?php if ($message): ?>
            <div class="mb-6 p-4 rounded-lg <?php echo $message_type === 'success' ? 'bg-green-100 border border-green-300' : 'bg-red-100 border border-red-300'; ?>">
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>

        <?php if ($action === 'list'): ?>
            <div class="bg-white rounded-xl shadow-sm border border-gray-100">
                <?php if (!empty($stories)): ?>
                    <div class="overflow-x-auto">
                        <table class="w-full">
                            <thead>
                                <tr class="border-b border-gray-200">
                                    <th class="text-left py-4 px-6 text-xs font-semibold uppercase">Title</th>
                                    <th class="text-left py-4 px-6 text-xs font-semibold uppercase">Tag</th>
                                    <th class="text-left py-4 px-6 text-xs font-semibold uppercase">Date</th>
                                    <th class="text-left py-4 px-6 text-xs font-semibold uppercase">Status</th>
                                    <th class="text-left py-4 px-6 text-xs font-semibold uppercase">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($stories as $s): ?>
                                    <tr class="border-b border-gray-100 hover:bg-gray-50">
                                        <td class="py-4 px-6 font-medium"><?php echo htmlspecialchars(substr($s['title'], 0, 40)); ?></td>
                                        <td class="py-4 px-6"><span class="px-2 py-1 bg-blue-100 text-blue-700 rounded text-xs"><?php echo htmlspecialchars($s['tag']); ?></span></td>
                                        <td class="py-4 px-6 text-sm"><?php echo date('M d, Y', strtotime($s['published_date'])); ?></td>
                                        <td class="py-4 px-6"><span class="px-3 py-1 rounded-full text-xs font-semibold <?php echo $s['is_published'] ? 'bg-green-100 text-green-700' : 'bg-gray-100'; ?>"><?php echo $s['is_published'] ? 'Published' : 'Draft'; ?></span></td>
                                        <td class="py-4 px-6 text-sm">
                                            <a href="?action=edit&id=<?php echo $s['id']; ?>" class="text-brand-cyan mr-3">Edit</a>
                                            <a href="?action=delete&id=<?php echo $s['id']; ?>" onclick="return confirm('Delete?')" class="text-red-600">Delete</a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <p class="text-center py-12 text-gray-500">No stories found.</p>
                <?php endif; ?>
            </div>

        <?php else: ?>
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-8 max-w-3xl">
                <h3 class="text-2xl font-bold mb-6"><?php echo $action === 'create' ? 'Create New Story' : 'Edit Story'; ?></h3>

                <form method="POST" enctype="multipart/form-data" class="space-y-6">
                    <?php if ($action === 'edit' && $story): ?>
                        <input type="hidden" name="story_id" value="<?php echo $story['id']; ?>">
                    <?php endif; ?>

                    <div class="grid grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-semibold mb-2">Title *</label>
                            <input type="text" name="title" required class="w-full px-4 py-2 border border-gray-200 rounded-lg focus:ring-2 focus:ring-brand-cyan" value="<?php echo htmlspecialchars($story['title'] ?? ''); ?>" placeholder="Story title">
                        </div>
                        <div>
                            <label class="block text-sm font-semibold mb-2">Tag *</label>
                            <input type="text" name="tag" required class="w-full px-4 py-2 border border-gray-200 rounded-lg focus:ring-2 focus:ring-brand-cyan" value="<?php echo htmlspecialchars($story['tag'] ?? ''); ?>" placeholder="Tips, Guide, News">
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-semibold mb-2">Excerpt *</label>
                        <textarea name="excerpt" required rows="3" class="w-full px-4 py-2 border border-gray-200 rounded-lg focus:ring-2 focus:ring-brand-cyan" placeholder="Brief summary..."><?php echo htmlspecialchars($story['excerpt'] ?? ''); ?></textarea>
                    </div>

                    <div>
                        <label class="block text-sm font-semibold mb-2">Content *</label>
                        <div id="quill-editor" class="bg-white/80 border border-white/40 text-[#003355] rounded-b-xl backdrop-blur-2xl" style="min-height: 300px;"></div>
                        <input type="hidden" name="content" id="content-input" value="<?php echo htmlspecialchars($story['content'] ?? ''); ?>">
                    </div>

                    <div>
                        <label class="block text-sm font-semibold mb-2">Cover Image *</label>
                        <?php if ($action === 'edit' && !empty($story['image_url'])): ?>
                            <div class="mb-4">
                                <img src="<?php echo htmlspecialchars($story['image_url']); ?>" alt="Current cover image" class="w-full max-h-60 object-cover rounded-lg border border-gray-200">
                                <input type="hidden" name="existing_image_url" value="<?php echo htmlspecialchars($story['image_url']); ?>">
                            </div>
                        <?php endif; ?>
                        <input type="file" name="cover_image" accept=".jpg,.jpeg,.png,.webp" class="w-full text-sm text-gray-600 file:border-0 file:bg-brand-cyan file:text-white file:px-4 file:py-2 rounded-lg border border-gray-200 focus:outline-none focus:ring-2 focus:ring-brand-cyan" <?php echo $action === 'create' ? 'required' : ''; ?>>
                        <p class="text-xs text-gray-500 mt-2">Upload JPG, PNG, or WEBP. Leave blank to preserve the current image.</p>
                    </div>

                    <div>
                        <label class="block text-sm font-semibold mb-2">Publish Date *</label>
                        <input type="date" name="published_date" required class="w-full px-4 py-2 border border-gray-200 rounded-lg focus:ring-2 focus:ring-brand-cyan" value="<?php echo htmlspecialchars($story['published_date'] ?? date('Y-m-d')); ?>">
                    </div>

                    <div class="flex items-center gap-2">
                        <input type="checkbox" id="is_published" name="is_published" <?php echo ($action === 'create' || $story['is_published']) ? 'checked' : ''; ?> class="w-4 h-4">
                        <label for="is_published" class="text-sm font-medium">Published</label>
                    </div>

                    <div class="flex gap-4 pt-4">
                        <button type="submit" class="px-6 py-2 bg-brand-cyan text-white rounded-lg font-medium">
                            <?php echo $action === 'create' ? 'Create Story' : 'Save'; ?>
                        </button>
                        <a href="?action=list" class="px-6 py-2 bg-gray-300 rounded-lg">Cancel</a>
                    </div>
                </form>
            </div>
        <?php endif; ?>
    <?php require_once 'includes/footer.php'; ?>
