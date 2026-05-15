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


$page_title = 'Inquiries | Admin - MBH Golden Global';

$page_heading = 'Contact Inquiries';
ob_start(); ?><?php if ($action === 'view'): ?><a href="?action=list" class="px-5 py-2 bg-white/5 border border-white/10 text-white/80 rounded-xl hover:bg-white/10 hover:text-white transition-all font-medium text-sm flex items-center gap-2"><i data-lucide="arrow-left" class="w-4 h-4"></i> Back to List</a><?php endif; ?><?php $page_actions = ob_get_clean();
require_once 'includes/header.php';
?>

            <!-- Messages -->
            <?php if ($message): ?>
                <div class="mb-6 p-4 rounded-xl backdrop-blur-xl bg-green-500/20 border border-green-500/50 text-green-200 shadow-[0_4px_30px_rgba(0,0,0,0.1)]">
                    <?php echo htmlspecialchars($message); ?>
                </div>
            <?php endif; ?>

            <!-- List View -->
            <?php if ($action === 'list'): ?>
                <div class="bg-white/5 backdrop-blur-xl rounded-2xl shadow-[0_4px_30px_rgba(0,0,0,0.1)] border border-white/10 flex-1">
                    <?php if (!empty($inquiries)): ?>
                        <div class="w-full overflow-x-auto overflow-y-hidden rounded-xl border border-white/10">
                            <table class="w-full min-w-max text-left border-collapse">
                                <thead>
                                    <tr>
                                        <th class="py-4 px-4 first:pl-6 last:pr-6 border-b border-white/10 text-xs font-semibold text-white/50 uppercase tracking-wider whitespace-nowrap">Name</th>
                                        <th class="py-4 px-4 first:pl-6 last:pr-6 border-b border-white/10 text-xs font-semibold text-white/50 uppercase tracking-wider whitespace-nowrap">Email</th>
                                        <th class="py-4 px-4 first:pl-6 last:pr-6 border-b border-white/10 text-xs font-semibold text-white/50 uppercase tracking-wider whitespace-nowrap">Subject</th>
                                        <th class="py-4 px-4 first:pl-6 last:pr-6 border-b border-white/10 text-xs font-semibold text-white/50 uppercase tracking-wider whitespace-nowrap">Date</th>
                                        <th class="py-4 px-4 first:pl-6 last:pr-6 border-b border-white/10 text-xs font-semibold text-white/50 uppercase tracking-wider whitespace-nowrap">Status</th>
                                        <th class="py-4 px-4 first:pl-6 last:pr-6 border-b border-white/10 text-xs font-semibold text-white/50 uppercase tracking-wider whitespace-nowrap">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($inquiries as $inq): ?>
                                        <tr class="hover:bg-white/5 transition-colors <?php echo $inq['status'] === 'unread' ? 'bg-white/5' : ''; ?>">
                                            <td class="py-4 px-4 first:pl-6 last:pr-6 border-b border-white/5 text-sm text-white/80 whitespace-nowrap font-medium"><?php echo htmlspecialchars($inq['full_name']); ?></td>
                                            <td class="py-4 px-4 first:pl-6 last:pr-6 border-b border-white/5 text-sm text-white/80 whitespace-nowrap"><?php echo htmlspecialchars($inq['email']); ?></td>
                                            <td class="py-4 px-4 first:pl-6 last:pr-6 border-b border-white/5 text-sm text-white/80 whitespace-nowrap"><?php echo htmlspecialchars(substr($inq['subject'], 0, 40)); ?></td>
                                            <td class="py-4 px-4 first:pl-6 last:pr-6 border-b border-white/5 text-sm text-white/80 whitespace-nowrap"><?php echo date('M d, Y', strtotime($inq['created_at'])); ?></td>
                                            <td class="py-4 px-4 first:pl-6 last:pr-6 border-b border-white/5 text-sm text-white/80 whitespace-nowrap">
                                                <?php if ($inq['status'] === 'unread'): ?>
                                                    <span class="inline-flex items-center justify-center px-2.5 py-1 rounded-full text-[10px] uppercase tracking-wider font-bold bg-red-500/20 border border-red-500/50 text-red-300 shadow-[0_0_10px_rgba(239,68,68,0.2)]">Unread</span>
                                                <?php elseif ($inq['status'] === 'read'): ?>
                                                    <span class="inline-flex items-center justify-center px-2.5 py-1 rounded-full text-[10px] uppercase tracking-wider font-bold bg-yellow-500/20 border border-yellow-500/50 text-yellow-300 shadow-[0_0_10px_rgba(234,179,8,0.2)]">Read</span>
                                                <?php else: ?>
                                                    <span class="inline-flex items-center justify-center px-2.5 py-1 rounded-full text-[10px] uppercase tracking-wider font-bold bg-green-500/20 border border-green-500/50 text-green-300 shadow-[0_0_10px_rgba(34,197,94,0.2)]">Replied</span>
                                                <?php endif; ?>
                                            </td>
                                            <td class="py-4 px-4 first:pl-6 last:pr-6 border-b border-white/5 text-sm text-white/80 whitespace-nowrap">
                                                <div class="flex items-center gap-3">
                                                    <a href="?action=view&id=<?php echo $inq['id']; ?>" class="text-brand-cyan hover:text-white transition-colors p-2 hover:bg-white/10 rounded-lg inline-flex items-center justify-center">
                                                        <i data-lucide="eye" class="w-4 h-4"></i>
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
                            <i data-lucide="mail-open" class="w-12 h-12 text-white/20 mx-auto mb-3"></i>
                            <p class="text-white/50 text-sm">No inquiries yet.</p>
                        </div>
                    <?php endif; ?>
                </div>

            <?php elseif ($action === 'view' && $inquiry): ?>
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    
                    <!-- Main Detail -->
                    <div class="lg:col-span-2">
                        <div class="bg-white/5 backdrop-blur-xl border border-white/10 rounded-2xl p-5 md:p-8 shadow-[0_4px_30px_rgba(0,0,0,0.1)] h-full">
                            <div class="mb-8 pb-6 border-b border-white/10">
                                <h2 class="text-2xl font-serif text-white mb-3"><?php echo htmlspecialchars($inquiry['subject']); ?></h2>
                                <p class="text-white/70">From: <span class="text-white/90 font-medium"><?php echo htmlspecialchars($inquiry['full_name']); ?></span> (<?php echo htmlspecialchars($inquiry['email']); ?>)</p>
                                <p class="text-white/50 text-sm mt-2"><?php echo date('F d, Y \a\t H:i', strtotime($inquiry['created_at'])); ?></p>
                            </div>

                            <div class="mb-8">
                                <h3 class="text-sm font-semibold uppercase tracking-wider text-white/50 mb-4">Message</h3>
                                <div class="bg-white/5 border border-white/10 rounded-xl p-6">
                                    <p class="text-white/90 whitespace-pre-wrap leading-relaxed"><?php echo htmlspecialchars($inquiry['message']); ?></p>
                                </div>
                            </div>

                            <div class="pt-6 border-t border-white/10">
                                <p class="text-sm text-white/70">
                                    <strong class="text-white/90">Phone:</strong> <?php echo htmlspecialchars($inquiry['phone'] ?? 'Not provided'); ?><br>
                                    <strong class="text-white/90">Inquiry ID:</strong> #<?php echo $inquiry['id']; ?>
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Status Sidebar -->
                    <div class="lg:col-span-1">
                        <div class="bg-white/5 backdrop-blur-xl border border-white/10 rounded-2xl p-6 shadow-[0_4px_30px_rgba(0,0,0,0.1)] sticky top-6">
                            <h3 class="text-lg font-serif text-white mb-6">Update Status</h3>
                            
                            <form method="POST" action="?action=update-status" class="space-y-4">
                                <input type="hidden" name="inquiry_id" value="<?php echo $inquiry['id']; ?>">
                                
                                <div>
                                    <label class="block text-sm font-semibold mb-2 text-white/80">Status</label>
                                    <select name="status" class="w-full px-4 py-3 bg-brand-navy border border-white/10 rounded-xl text-white focus:outline-none focus:border-brand-cyan focus:ring-1 focus:ring-brand-cyan transition-all appearance-none">
                                        <option value="unread" <?php echo $inquiry['status'] === 'unread' ? 'selected' : ''; ?>>Unread</option>
                                        <option value="read" <?php echo $inquiry['status'] === 'read' ? 'selected' : ''; ?>>Read</option>
                                        <option value="replied" <?php echo $inquiry['status'] === 'replied' ? 'selected' : ''; ?>>Replied</option>
                                    </select>
                                </div>

                                <button type="submit" class="w-full px-4 py-3 bg-gradient-to-r from-brand-cyan to-brand-cyanLight text-white rounded-xl hover:shadow-[0_0_20px_rgba(0,130,202,0.4)] transition-all font-bold tracking-wide uppercase text-xs mt-4">
                                    Update Status
                                </button>
                            </form>

                            <div class="mt-6 pt-6 border-t border-white/10">
                                <p class="text-xs text-white/50">
                                    <strong class="text-white/70">Submitted:</strong><br>
                                    <?php echo date('M d, Y \a\t H:i', strtotime($inquiry['created_at'])); ?>
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        <?php require_once 'includes/footer.php'; ?>
