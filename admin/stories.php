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

    <div class="flex min-h-screen relative z-10">
        <!-- Sidebar -->
        <aside class="w-64 fixed top-6 left-6 bottom-6 rounded-3xl bg-white/10 backdrop-blur-2xl border border-white/20 shadow-[0_0_30px_rgba(0,130,202,0.15)] flex flex-col z-20">
            <!-- Logo -->
            <div class="p-8 pb-6 border-b border-white/10 text-center">
                <img src="../assets/img/logo.png" alt="MBH" class="h-12 object-contain mx-auto brightness-0 invert drop-shadow-[0_0_15px_rgba(255,255,255,0.3)] mb-2">
                <h1 class="text-xl font-serif text-white tracking-widest uppercase text-xs opacity-80">
                    Admin
                </h1>
            </div>
            
            <!-- Navigation -->
            <nav class="flex-1 overflow-y-auto py-6 px-4 flex flex-col gap-2">
                <a href="index.php" class="flex items-center gap-3 px-4 py-3 rounded-xl text-white/70 hover:text-white hover:bg-white/10 hover:translate-x-2 transition-all">
                    <i data-lucide="layout-dashboard" class="w-5 h-5"></i>
                    <span class="font-medium text-sm">Dashboard</span>
                </a>
                <a href="packages.php" class="flex items-center gap-3 px-4 py-3 rounded-xl text-white/70 hover:text-white hover:bg-white/10 hover:translate-x-2 transition-all">
                    <i data-lucide="package" class="w-5 h-5"></i>
                    <span class="font-medium text-sm">Packages</span>
                </a>
                <a href="stories.php" class="flex items-center gap-3 px-4 py-3 rounded-xl bg-gradient-to-r from-brand-cyan/20 to-transparent border border-brand-cyan/30 text-white shadow-[0_0_15px_rgba(0,130,202,0.2)] transition-all">
                    <i data-lucide="pen-tool" class="w-5 h-5 text-brand-cyan"></i>
                    <span class="font-medium text-sm">Stories</span>
                </a>
                <a href="inquiries.php" class="flex items-center gap-3 px-4 py-3 rounded-xl text-white/70 hover:text-white hover:bg-white/10 hover:translate-x-2 transition-all">
                    <i data-lucide="mail" class="w-5 h-5"></i>
                    <span class="font-medium text-sm">Inquiries</span>
                </a>
                <?php if (isset($_SESSION['admin_role']) && $_SESSION['admin_role'] === 'admin'): ?>
<a href="admins.php" class="flex items-center gap-3 px-4 py-3 rounded-xl text-white/70 hover:text-white hover:bg-white/10 hover:translate-x-2 transition-all">
                    <i data-lucide="users" class="w-5 h-5"></i>
                    <span class="font-medium text-sm">Admins</span>
                </a>
                <a href="settings.php" class="flex items-center gap-3 px-4 py-3 rounded-xl text-white/70 hover:text-white hover:bg-white/10 hover:translate-x-2 transition-all">
                    <i data-lucide="settings" class="w-5 h-5"></i>
                    <span class="font-medium text-sm">Settings</span>
                </a>
            <?php endif; ?>
            </nav>
        </aside>

        <!-- Main Content -->
        <main class="flex-1 ml-80 py-6 pr-6 min-h-screen flex flex-col">
            <!-- Top Header -->
            <header class="bg-white/5 backdrop-blur-xl border border-white/10 rounded-2xl p-6 flex justify-between items-center mb-8 shadow-[0_4px_30px_rgba(0,0,0,0.1)]">
                <div class="flex items-center gap-6">
                    <h2 class="text-3xl font-serif text-white">Travel Stories</h2>
                    <?php if ($action === 'list'): ?>
                        <a href="?action=create" class="px-5 py-2 bg-brand-cyan/20 border border-brand-cyan/50 text-brand-cyan rounded-xl hover:bg-brand-cyan hover:text-white hover:shadow-[0_0_15px_rgba(0,130,202,0.4)] transition-all font-medium text-sm flex items-center gap-2">
                            <i data-lucide="plus" class="w-4 h-4"></i> New Story
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
                    <?php if (!empty($stories)): ?>
                        <div class="overflow-x-auto">
                            <table class="w-full text-left border-collapse">
                                <thead>
                                    <tr>
                                        <th class="border-b border-white/10 py-4 px-6 text-white/50 text-xs tracking-wider uppercase font-semibold">Title</th>
                                        <th class="border-b border-white/10 py-4 px-6 text-white/50 text-xs tracking-wider uppercase font-semibold">Tag</th>
                                        <th class="border-b border-white/10 py-4 px-6 text-white/50 text-xs tracking-wider uppercase font-semibold">Date</th>
                                        <th class="border-b border-white/10 py-4 px-6 text-white/50 text-xs tracking-wider uppercase font-semibold">Status</th>
                                        <th class="border-b border-white/10 py-4 px-6 text-white/50 text-xs tracking-wider uppercase font-semibold">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($stories as $s): ?>
                                        <tr class="hover:bg-white/5 transition-colors group">
                                            <td class="py-4 px-6 font-medium text-white/90 border-b border-white/5 group-last:border-none"><?php echo htmlspecialchars(substr($s['title'], 0, 40)); ?></td>
                                            <td class="py-4 px-6 border-b border-white/5 group-last:border-none"><span class="px-2 py-1 bg-brand-cyan/20 text-brand-cyan border border-brand-cyan/30 rounded text-xs"><?php echo htmlspecialchars($s['tag']); ?></span></td>
                                            <td class="py-4 px-6 text-sm text-white/70 border-b border-white/5 group-last:border-none"><?php echo date('M d, Y', strtotime($s['published_date'])); ?></td>
                                            <td class="py-4 px-6 border-b border-white/5 group-last:border-none">
                                                <span class="px-3 py-1 rounded-full text-[10px] uppercase tracking-wider font-bold <?php echo $s['is_published'] ? 'bg-green-500/20 text-green-300 border border-green-500/50 shadow-[0_0_10px_rgba(34,197,94,0.2)]' : 'bg-white/10 border border-white/20 text-white/50'; ?>"><?php echo $s['is_published'] ? 'Published' : 'Draft'; ?></span>
                                            </td>
                                            <td class="py-4 px-6 text-sm border-b border-white/5 group-last:border-none">
                                                <div class="flex items-center gap-3">
                                                    <a href="?action=edit&id=<?php echo $s['id']; ?>" class="text-brand-cyan hover:text-white transition-colors p-2 hover:bg-white/10 rounded-lg inline-block">
                                                        <i data-lucide="edit" class="w-4 h-4"></i>
                                                    </a>
                                                    <a href="?action=delete&id=<?php echo $s['id']; ?>" onclick="return confirm('Delete this story?')" class="text-red-400 hover:text-red-300 transition-colors p-2 hover:bg-red-500/20 rounded-lg inline-block">
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
                            <i data-lucide="pen-tool" class="w-12 h-12 text-white/20 mx-auto mb-3"></i>
                            <p class="text-white/50 text-sm mb-4">No stories found.</p>
                            <a href="?action=create" class="inline-flex items-center gap-2 text-brand-cyan hover:text-white transition-colors text-sm font-medium">Create your first story <i data-lucide="arrow-right" class="w-4 h-4"></i></a>
                        </div>
                    <?php endif; ?>
                </div>

            <!-- Create/Edit Form -->
            <?php else: ?>
                <div class="bg-white/5 backdrop-blur-xl border border-white/10 rounded-2xl p-8 max-w-3xl shadow-[0_4px_30px_rgba(0,0,0,0.1)]">
                    <h3 class="text-2xl font-serif text-white mb-6"><?php echo $action === 'create' ? 'Create New Story' : 'Edit Story'; ?></h3>

                    <form method="POST" enctype="multipart/form-data" class="space-y-6">
                        <?php if ($action === 'edit' && $story): ?>
                            <input type="hidden" name="story_id" value="<?php echo $story['id']; ?>">
                        <?php endif; ?>

                        <div class="grid grid-cols-2 gap-6">
                            <div>
                                <label class="block text-sm font-semibold mb-2 text-white/80">Title *</label>
                                <input type="text" name="title" required class="w-full px-4 py-3 bg-white/5 border border-white/10 rounded-xl text-white focus:outline-none focus:border-brand-cyan focus:bg-white/10 focus:ring-1 focus:ring-brand-cyan transition-all placeholder-white/30" value="<?php echo htmlspecialchars($story['title'] ?? ''); ?>" placeholder="Story title">
                            </div>
                            <div>
                                <label class="block text-sm font-semibold mb-2 text-white/80">Tag *</label>
                                <input type="text" name="tag" required class="w-full px-4 py-3 bg-white/5 border border-white/10 rounded-xl text-white focus:outline-none focus:border-brand-cyan focus:bg-white/10 focus:ring-1 focus:ring-brand-cyan transition-all placeholder-white/30" value="<?php echo htmlspecialchars($story['tag'] ?? ''); ?>" placeholder="Tips, Guide, News">
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-semibold mb-2 text-white/80">Excerpt *</label>
                            <textarea name="excerpt" required rows="3" class="w-full px-4 py-3 bg-white/5 border border-white/10 rounded-xl text-white focus:outline-none focus:border-brand-cyan focus:bg-white/10 focus:ring-1 focus:ring-brand-cyan transition-all placeholder-white/30" placeholder="Brief summary..."><?php echo htmlspecialchars($story['excerpt'] ?? ''); ?></textarea>
                        </div>

                        <div>
                            <label class="block text-sm font-semibold mb-2 text-white/80">Content *</label>
                            <div id="quill-editor" class="bg-white/90 border border-white/40 text-[#003355] rounded-b-xl backdrop-blur-2xl" style="min-height: 300px;"></div>
                            <input type="hidden" name="content" id="content-input" value="<?php echo htmlspecialchars($story['content'] ?? ''); ?>">
                        </div>

                        <div>
                            <label class="block text-sm font-semibold mb-2 text-white/80">Cover Image *</label>
                            <?php if ($action === 'edit' && !empty($story['image_url'])): ?>
                                <div class="mb-4">
                                    <img src="<?php echo htmlspecialchars($story['image_url']); ?>" alt="Current cover image" class="w-full max-h-60 object-cover rounded-xl border border-white/20 shadow-[0_4px_30px_rgba(0,0,0,0.1)]">
                                    <input type="hidden" name="existing_image_url" value="<?php echo htmlspecialchars($story['image_url']); ?>">
                                </div>
                            <?php endif; ?>
                            <input type="file" name="cover_image" accept=".jpg,.jpeg,.png,.webp" class="w-full text-sm text-white/60 file:mr-4 file:py-2.5 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-brand-cyan/20 file:text-brand-cyan hover:file:bg-brand-cyan/30 border border-white/10 rounded-xl bg-white/5 focus:outline-none transition-all cursor-pointer" <?php echo $action === 'create' ? 'required' : ''; ?>>
                            <p class="text-xs text-white/40 mt-2">Upload JPG, PNG, or WEBP. Leave blank to preserve the current image.</p>
                        </div>

                        <div>
                            <label class="block text-sm font-semibold mb-2 text-white/80">Publish Date *</label>
                            <input type="date" name="published_date" required class="w-full px-4 py-3 bg-white/5 border border-white/10 rounded-xl text-white focus:outline-none focus:border-brand-cyan focus:bg-white/10 focus:ring-1 focus:ring-brand-cyan transition-all placeholder-white/30" style="color-scheme: dark;" value="<?php echo htmlspecialchars($story['published_date'] ?? date('Y-m-d')); ?>">
                        </div>

                        <div class="flex items-center gap-3 bg-white/5 p-4 rounded-xl border border-white/10">
                            <input type="checkbox" id="is_published" name="is_published" <?php echo ($action === 'create' || $story['is_published']) ? 'checked' : ''; ?> class="w-5 h-5 accent-brand-cyan bg-white/10 border-white/20 rounded">
                            <label for="is_published" class="text-sm font-medium text-white/90 cursor-pointer">Published</label>
                        </div>

                        <div class="flex gap-4 pt-4 border-t border-white/10">
                            <button type="submit" class="px-8 py-3 bg-gradient-to-r from-brand-cyan to-[#00aaff] text-white rounded-xl hover:shadow-[0_0_20px_rgba(0,130,202,0.4)] transition-all font-bold tracking-wide uppercase text-xs">
                                <?php echo $action === 'create' ? 'Create Story' : 'Save Changes'; ?>
                            </button>
                            <a href="?action=list" class="px-8 py-3 bg-white/5 text-white/80 border border-white/10 rounded-xl hover:bg-white/10 transition-all font-bold tracking-wide uppercase text-xs">Cancel</a>
                        </div>
                    </form>
                </div>
            <?php endif; ?>
        <?php require_once 'includes/footer.php'; ?>
