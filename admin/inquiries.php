<?php
/**
 * Admin - Contact Inquiries Management
 * 
 * View and manage customer inquiries
 */

require_once '../includes/db.php';
require_once 'includes/auth.php';

$inquiries = [];
$inquiry = null;
$message = '';
$action = $_GET['action'] ?? 'list';

// Handle status update
if ($action === 'update-status' && isset($_POST['inquiry_id'], $_POST['status'])) {
    try {
        $stmt = $pdo->prepare("UPDATE inquiries SET status = :status WHERE id = :id");
        $stmt->execute([
            ':status' => $_POST['status'],
            ':id' => $_POST['inquiry_id']
        ]);
        $message = 'Status updated successfully!';
        $action = 'view';
        $_GET['id'] = $_POST['inquiry_id'];
    } catch (PDOException $e) {
        error_log('Update error: ' . $e->getMessage());
        $message = 'Error updating status.';
    }
}

// Fetch single inquiry
if ($action === 'view' && isset($_GET['id'])) {
    try {
        $stmt = $pdo->prepare("SELECT * FROM inquiries WHERE id = :id");
        $stmt->execute([':id' => $_GET['id']]);
        $inquiry = $stmt->fetch();
        if (!$inquiry) {
            $action = 'list';
            $message = 'Inquiry not found.';
        }
    } catch (PDOException $e) {
        error_log('Fetch error: ' . $e->getMessage());
        $action = 'list';
    }
}

// Fetch all inquiries
if ($action === 'list') {
    try {
        $stmt = $pdo->prepare("SELECT * FROM inquiries ORDER BY created_at DESC");
        $stmt->execute();
        $inquiries = $stmt->fetchAll();
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
    <title>Manage Inquiries | Admin - MBH Golden Global</title>
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
                    <a href="inquiries.php" class="text-brand-cyan font-semibold border-b-2 border-brand-cyan pb-1">Inquiries</a>
                    <a href="settings.php" class="hover:text-brand-cyan">Settings</a>
                </div>
            </div>
            <a href="logout.php" class="text-red-600 hover:bg-red-50 px-4 py-2 rounded text-sm">Logout</a>
        </div>
    </nav>

    <!-- Main Content -->
    <main class="max-w-7xl mx-auto px-6 py-12">
        <div class="flex justify-between items-center mb-8">
            <h2 class="text-3xl font-serif font-bold">Contact Inquiries</h2>
            <?php if ($action === 'view'): ?>
                <a href="?action=list" class="px-6 py-2 bg-gray-300 text-gray-800 rounded-lg font-medium">← Back</a>
            <?php endif; ?>
        </div>

        <?php if ($message): ?>
            <div class="mb-6 p-4 bg-green-100 border border-green-300 rounded-lg">
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>

        <!-- List View -->
        <?php if ($action === 'list'): ?>
            <div class="bg-white rounded-xl shadow-sm border border-gray-100">
                <?php if (!empty($inquiries)): ?>
                    <div class="overflow-x-auto">
                        <table class="w-full">
                            <thead>
                                <tr class="border-b border-gray-200">
                                    <th class="text-left py-4 px-6 text-xs font-semibold uppercase">Name</th>
                                    <th class="text-left py-4 px-6 text-xs font-semibold uppercase">Email</th>
                                    <th class="text-left py-4 px-6 text-xs font-semibold uppercase">Subject</th>
                                    <th class="text-left py-4 px-6 text-xs font-semibold uppercase">Date</th>
                                    <th class="text-left py-4 px-6 text-xs font-semibold uppercase">Status</th>
                                    <th class="text-left py-4 px-6 text-xs font-semibold uppercase">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($inquiries as $inq): ?>
                                    <tr class="border-b border-gray-100 hover:bg-gray-50">
                                        <td class="py-4 px-6 font-medium"><?php echo htmlspecialchars($inq['full_name']); ?></td>
                                        <td class="py-4 px-6 text-sm text-gray-600"><?php echo htmlspecialchars($inq['email']); ?></td>
                                        <td class="py-4 px-6 text-sm"><?php echo htmlspecialchars(substr($inq['subject'], 0, 40)); ?></td>
                                        <td class="py-4 px-6 text-sm"><?php echo date('M d, Y', strtotime($inq['created_at'])); ?></td>
                                        <td class="py-4 px-6">
                                            <span class="px-3 py-1 rounded-full text-xs font-semibold
                                                <?php 
                                                    if ($inq['status'] === 'unread') echo 'bg-red-100 text-red-700';
                                                    elseif ($inq['status'] === 'read') echo 'bg-yellow-100 text-yellow-700';
                                                    else echo 'bg-green-100 text-green-700';
                                                ?>
                                            ">
                                                <?php echo ucfirst($inq['status']); ?>
                                            </span>
                                        </td>
                                        <td class="py-4 px-6 text-sm">
                                            <a href="?action=view&id=<?php echo $inq['id']; ?>" class="text-brand-cyan hover:underline">View</a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <p class="text-center py-12 text-gray-500">No inquiries yet.</p>
                <?php endif; ?>
            </div>

        <!-- View Detail -->
        <?php elseif ($action === 'view' && $inquiry): ?>
            <div class="grid grid-cols-3 gap-6">
                <!-- Main Content -->
                <div class="col-span-2">
                    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-8">
                        <div class="mb-8 pb-6 border-b">
                            <h2 class="text-2xl font-bold mb-2"><?php echo htmlspecialchars($inquiry['subject']); ?></h2>
                            <p class="text-gray-600">From: <?php echo htmlspecialchars($inquiry['full_name']); ?> (<?php echo htmlspecialchars($inquiry['email']); ?>)</p>
                            <p class="text-gray-500 text-sm mt-2"><?php echo date('F d, Y \a\t H:i', strtotime($inquiry['created_at'])); ?></p>
                        </div>

                        <div class="prose max-w-none mb-8">
                            <h3 class="font-semibold mb-4">Message</h3>
                            <p class="text-gray-700 whitespace-pre-wrap"><?php echo htmlspecialchars($inquiry['message']); ?></p>
                        </div>

                        <div class="pt-6 border-t">
                            <p class="text-sm text-gray-600">
                                <strong>Phone:</strong> <?php echo htmlspecialchars($inquiry['phone'] ?? 'Not provided'); ?><br>
                                <strong>Inquiry ID:</strong> #<?php echo $inquiry['id']; ?>
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Status Sidebar -->
                <div class="col-span-1">
                    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 sticky top-20">
                        <h3 class="font-bold mb-4">Update Status</h3>
                        
                        <form method="POST" action="?action=update-status" class="space-y-4">
                            <input type="hidden" name="inquiry_id" value="<?php echo $inquiry['id']; ?>">
                            
                            <div>
                                <label class="block text-sm font-semibold mb-2">Status</label>
                                <select name="status" class="w-full px-4 py-2 border border-gray-200 rounded-lg focus:ring-2 focus:ring-brand-cyan">
                                    <option value="unread" <?php echo $inquiry['status'] === 'unread' ? 'selected' : ''; ?>>Unread</option>
                                    <option value="read" <?php echo $inquiry['status'] === 'read' ? 'selected' : ''; ?>>Read</option>
                                    <option value="replied" <?php echo $inquiry['status'] === 'replied' ? 'selected' : ''; ?>>Replied</option>
                                </select>
                            </div>

                            <button type="submit" class="w-full px-4 py-2 bg-brand-cyan text-white rounded-lg font-medium hover:bg-brand-cyan/90">
                                Update Status
                            </button>
                        </form>

                        <div class="mt-6 pt-6 border-t">
                            <p class="text-xs text-gray-500">
                                <strong>Submitted:</strong><br>
                                <?php echo date('M d, Y \a\t H:i', strtotime($inquiry['created_at'])); ?>
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </main>

    <script>lucide.createIcons();</script>
</body>
</html>
