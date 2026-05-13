<?php
/**
 * Admin Login Page
 * * Secure authentication for admin dashboard with session management
 */

require_once '../includes/db.php';

// Start session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Redirect if already logged in
if (isset($_SESSION['admin_id'])) {
    header('Location: index.php');
    exit;
}

// Handle login form submission
$loginError = '';
$redirectUrl = $_GET['redirect'] ?? 'index.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';

        // Validation
        if (empty($email) || empty($password)) {
            $loginError = 'Email and password are required.';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $loginError = 'Please enter a valid email address.';
        } else {
            // Fetch admin from database
            $stmt = $pdo->prepare("
                SELECT id, name, email, password_hash 
                FROM admins 
                WHERE email = :email 
                LIMIT 1
            ");
            
            $stmt->execute([':email' => $email]);
            $admin = $stmt->fetch();

            if ($admin && password_verify($password, $admin['password_hash'])) {
                // Login successful - set session
                $_SESSION['admin_id'] = $admin['id'];
                $_SESSION['admin_email'] = $admin['email'];
                $_SESSION['admin_name'] = $admin['name'];
                $_SESSION['last_activity'] = time();

                // Redirect to dashboard or originally requested page
                header('Location: ' . $redirectUrl);
                exit;
            } else {
                $loginError = 'Invalid email or password.';
            }
        }
    } catch (PDOException $e) {
        error_log('Login error: ' . $e->getMessage());
        $loginError = 'An error occurred. Please try again later.';
    }
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Area | MBH Golden Global</title>
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
                        'fade-in-up': 'fadeInUp 0.8s cubic-bezier(0.16, 1, 0.3, 1) forwards',
                    },
                    keyframes: {
                        floatOrb: {
                            '0%': { transform: 'translateY(0) scale(1)' },
                            '100%': { transform: 'translateY(-20px) scale(1.05)' },
                        },
                        fadeInUp: {
                            '0%': { opacity: '0', transform: 'translateY(30px)' },
                            '100%': { opacity: '1', transform: 'translateY(0)' },
                        }
                    }
                }
            }
        }
    </script>
</head>
<body class="bg-brand-navy min-h-screen flex items-center justify-center p-4 relative overflow-hidden">
    
    <div class="absolute inset-0 z-0 pointer-events-none overflow-hidden">
        <div class="absolute rounded-full w-[30rem] h-[30rem] top-[-10%] -left-20 animate-float mix-blend-screen opacity-40" style="background: radial-gradient(circle at center, rgba(0,130,202,0.5), transparent 70%);"></div>
        <div class="absolute rounded-full w-[45rem] h-[45rem] bottom-[-20%] -right-20 animate-float-delayed mix-blend-screen opacity-20" style="background: radial-gradient(circle at center, rgba(255,255,255,0.4), transparent 70%);"></div>
        
        <div class="absolute inset-0 bg-[url('https://images.unsplash.com/photo-1436491865332-7a61a109cc05?auto=format&fit=crop&w=1920&q=80')] bg-cover bg-center opacity-[0.15] mix-blend-overlay"></div>
    </div>

    <div class="w-full max-w-md relative z-10 animate-fade-in-up">
        
        <div class="text-center mb-8">
            <img src="../assets/img/logo.png" alt="MBH Golden Global" class="h-20 md:h-28 object-contain mx-auto brightness-0 invert drop-shadow-[0_0_15px_rgba(255,255,255,0.3)]">
            <p class="text-brand-cyan text-xs tracking-[0.3em] font-bold uppercase mt-4">Command Center</p>
        </div>

        <div class="bg-white/10 backdrop-blur-2xl rounded-[2.5rem] shadow-[0_20px_50px_rgba(0,0,0,0.5),inset_0_1px_0_rgba(255,255,255,0.2)] border border-white/20 p-8 md:p-10">
            <h2 class="text-3xl font-serif text-white mb-6 text-center">Secure <i class="text-brand-cyan font-light">Login</i></h2>

            <?php if ($loginError): ?>
                <div class="mb-6 p-4 bg-red-500/20 border border-red-500/50 rounded-xl backdrop-blur-sm">
                    <p class="text-red-200 text-sm font-medium flex items-center gap-2">
                        <i data-lucide="alert-circle" class="w-4 h-4"></i> <?php echo htmlspecialchars($loginError); ?>
                    </p>
                </div>
            <?php endif; ?>

            <?php if (isset($_GET['timeout'])): ?>
                <div class="mb-6 p-4 bg-yellow-500/20 border border-yellow-500/50 rounded-xl backdrop-blur-sm">
                    <p class="text-yellow-200 text-sm font-medium flex items-center gap-2">
                        <i data-lucide="clock" class="w-4 h-4"></i> Session expired. Please log in again.
                    </p>
                </div>
            <?php endif; ?>

            <form method="POST" class="space-y-6">
                <div class="relative group">
                    <i data-lucide="mail" class="absolute left-4 top-1/2 -translate-y-1/2 w-5 h-5 text-white/50 group-focus-within:text-brand-cyan transition-colors"></i>
                    <input 
                        type="email" 
                        name="email" 
                        value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>"
                        required 
                        autocomplete="email"
                        class="w-full bg-white/5 border border-white/10 text-white rounded-xl py-3.5 pl-12 pr-4 focus:outline-none focus:border-brand-cyan focus:bg-white/10 focus:ring-1 focus:ring-brand-cyan transition-all placeholder-white/40"
                        placeholder="Email Address"
                    >
                </div>

                <div class="relative group">
                    <i data-lucide="lock" class="absolute left-4 top-1/2 -translate-y-1/2 w-5 h-5 text-white/50 group-focus-within:text-brand-cyan transition-colors"></i>
                    <input 
                        type="password" 
                        name="password" 
                        required 
                        autocomplete="current-password"
                        class="w-full bg-white/5 border border-white/10 text-white rounded-xl py-3.5 pl-12 pr-4 focus:outline-none focus:border-brand-cyan focus:bg-white/10 focus:ring-1 focus:ring-brand-cyan transition-all placeholder-white/40"
                        placeholder="Password"
                    >
                </div>

                <button 
                    type="submit" 
                    class="w-full py-4 bg-gradient-to-b from-[#00a2ff] to-[#0082CA] border border-[#00aaff] text-white font-bold tracking-[0.15em] uppercase text-xs rounded-xl hover:shadow-[0_0_20px_rgba(0,130,202,0.4)] hover:-translate-y-0.5 active:translate-y-0 active:shadow-none transition-all duration-300 mt-2"
                >
                    Authenticate
                </button>
            </form>

            <div class="mt-8 pt-6 border-t border-white/10">
                <p class="text-center text-[10px] uppercase tracking-[0.15em] text-white/40 flex justify-center items-center gap-2">
                    <i data-lucide="shield-check" class="w-3 h-3"></i> 256-Bit Encrypted Portal
                </p>
            </div>
        </div>

        <div class="text-center mt-8">
            <a href="../index.php" class="inline-flex items-center gap-2 text-white/60 hover:text-white text-xs font-bold tracking-[0.15em] uppercase transition-colors">
                <i data-lucide="arrow-left" class="w-4 h-4"></i> Return to Main Site
            </a>
        </div>
    </div>

    <script>
        lucide.createIcons();
    </script>
</body>
</html>