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

    <div class="flex flex-col min-h-screen relative z-10">
        <?php require_once 'includes/sidebar.php'; ?>

        
        <!-- Mobile Top Bar -->
        <div class="md:hidden flex items-center justify-between p-6 bg-white/5 backdrop-blur-xl border-b border-white/10 sticky top-0 z-30 w-full">
            <h1 class="text-xl font-serif font-bold text-white"><span class="text-brand-cyan">MBH</span> Admin</h1>
            <button id="hamburger-btn" class="text-white hover:text-brand-cyan transition-colors">
                <i data-lucide="menu" class="w-6 h-6"></i>
            </button>
        </div>

        <!-- Main Content -->
        <main class="w-full flex-1 md:pl-80 p-6 md:py-8 md:pr-8 min-h-screen flex flex-col">
            <!-- Top Header -->
            <header class="bg-white/5 backdrop-blur-xl border border-white/10 rounded-2xl p-6 flex justify-between items-center mb-8 shadow-[0_4px_30px_rgba(0,0,0,0.1)]">
                <div class="flex items-center gap-6">
                    <h2 class="text-3xl font-serif text-white">Contact Inquiries</h2>
                    <?php if ($action === 'view'): ?>
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
                <div class="mb-6 p-4 rounded-xl backdrop-blur-xl bg-green-500/20 border border-green-500/50 text-green-200 shadow-[0_4px_30px_rgba(0,0,0,0.1)]">
                    <?php echo htmlspecialchars($message); ?>
                </div>
            <?php endif; ?>

            <!-- List View -->
            <?php if ($action === 'list'): ?>
                <div class="bg-white/5 backdrop-blur-xl rounded-2xl shadow-[0_4px_30px_rgba(0,0,0,0.1)] border border-white/10 flex-1">
                    <?php if (!empty($inquiries)): ?>
                        <div class="overflow-x-auto">
                            <table class="w-full text-left border-collapse">
                                <thead>
                                    <tr>
                                        <th class="border-b border-white/10 py-4 px-6 text-white/50 text-xs tracking-wider uppercase font-semibold">Name</th>
                                        <th class="border-b border-white/10 py-4 px-6 text-white/50 text-xs tracking-wider uppercase font-semibold">Email</th>
                                        <th class="border-b border-white/10 py-4 px-6 text-white/50 text-xs tracking-wider uppercase font-semibold">Subject</th>
                                        <th class="border-b border-white/10 py-4 px-6 text-white/50 text-xs tracking-wider uppercase font-semibold">Date</th>
                                        <th class="border-b border-white/10 py-4 px-6 text-white/50 text-xs tracking-wider uppercase font-semibold">Status</th>
                                        <th class="border-b border-white/10 py-4 px-6 text-white/50 text-xs tracking-wider uppercase font-semibold">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($inquiries as $inq): ?>
                                        <tr class="hover:bg-white/5 transition-colors group <?php echo $inq['status'] === 'unread' ? 'bg-white/5' : ''; ?>">
                                            <td class="py-4 px-6 font-medium text-white/90 border-b border-white/5 group-last:border-none"><?php echo htmlspecialchars($inq['full_name']); ?></td>
                                            <td class="py-4 px-6 text-sm text-white/70 border-b border-white/5 group-last:border-none"><?php echo htmlspecialchars($inq['email']); ?></td>
                                            <td class="py-4 px-6 text-sm text-white/70 border-b border-white/5 group-last:border-none"><?php echo htmlspecialchars(substr($inq['subject'], 0, 40)); ?></td>
                                            <td class="py-4 px-6 text-sm text-white/70 border-b border-white/5 group-last:border-none"><?php echo date('M d, Y', strtotime($inq['created_at'])); ?></td>
                                            <td class="py-4 px-6 text-sm border-b border-white/5 group-last:border-none">
                                                <?php if ($inq['status'] === 'unread'): ?>
                                                    <span class="px-3 py-1 rounded-full text-[10px] uppercase tracking-wider font-bold bg-red-500/20 border border-red-500/50 text-red-300 shadow-[0_0_10px_rgba(239,68,68,0.2)]">Unread</span>
                                                <?php elseif ($inq['status'] === 'read'): ?>
                                                    <span class="px-3 py-1 rounded-full text-[10px] uppercase tracking-wider font-bold bg-yellow-500/20 border border-yellow-500/50 text-yellow-300 shadow-[0_0_10px_rgba(234,179,8,0.2)]">Read</span>
                                                <?php else: ?>
                                                    <span class="px-3 py-1 rounded-full text-[10px] uppercase tracking-wider font-bold bg-green-500/20 border border-green-500/50 text-green-300 shadow-[0_0_10px_rgba(34,197,94,0.2)]">Replied</span>
                                                <?php endif; ?>
                                            </td>
                                            <td class="py-4 px-6 text-sm border-b border-white/5 group-last:border-none">
                                                <div class="flex items-center gap-3">
                                                    <a href="?action=view&id=<?php echo $inq['id']; ?>" class="text-brand-cyan hover:text-white transition-colors p-2 hover:bg-white/10 rounded-lg inline-block">
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

            <!-- View Detail -->
            <?php elseif ($action === 'view' && $inquiry): ?>
                <div class="grid grid-cols-3 gap-6">
                    
        <!-- Mobile Top Bar -->
        <div class="md:hidden flex items-center justify-between p-6 bg-white/5 backdrop-blur-xl border-b border-white/10 sticky top-0 z-30 w-full">
            <h1 class="text-xl font-serif font-bold text-white"><span class="text-brand-cyan">MBH</span> Admin</h1>
            <button id="hamburger-btn" class="text-white hover:text-brand-cyan transition-colors">
                <i data-lucide="menu" class="w-6 h-6"></i>
            </button>
        </div>

        <!-- Main Content -->
                    <div class="col-span-2">
                        <div class="bg-white/5 backdrop-blur-xl border border-white/10 rounded-2xl p-8 shadow-[0_4px_30px_rgba(0,0,0,0.1)] h-full">
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
                    <div class="col-span-1">
                        <div class="bg-white/5 backdrop-blur-xl border border-white/10 rounded-2xl p-6 shadow-[0_4px_30px_rgba(0,0,0,0.1)] sticky top-6">
                            <h3 class="text-lg font-serif text-white mb-6">Update Status</h3>
                            
                            <form method="POST" action="?action=update-status" class="space-y-4">
                                <input type="hidden" name="inquiry_id" value="<?php echo $inquiry['id']; ?>">
                                
                                <div>
                                    <label class="block text-sm font-semibold mb-2 text-white/80">Status</label>
                                    <select name="status" class="w-full px-4 py-3 bg-[#002b48] border border-white/10 rounded-xl text-white focus:outline-none focus:border-brand-cyan focus:ring-1 focus:ring-brand-cyan transition-all appearance-none">
                                        <option value="unread" <?php echo $inquiry['status'] === 'unread' ? 'selected' : ''; ?>>Unread</option>
                                        <option value="read" <?php echo $inquiry['status'] === 'read' ? 'selected' : ''; ?>>Read</option>
                                        <option value="replied" <?php echo $inquiry['status'] === 'replied' ? 'selected' : ''; ?>>Replied</option>
                                    </select>
                                </div>

                                <button type="submit" class="w-full px-4 py-3 bg-gradient-to-r from-brand-cyan to-[#00aaff] text-white rounded-xl hover:shadow-[0_0_20px_rgba(0,130,202,0.4)] transition-all font-bold tracking-wide uppercase text-xs mt-4">
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
        </main>
    </div> <!-- Close flex wrapper -->

    <script>lucide.createIcons();</script>
</body>
</html>