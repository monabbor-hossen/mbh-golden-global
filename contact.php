<?php
require_once 'includes/db.php';
require_once 'includes/header.php';

// Handle form submission
$formMessage = '';
$formStatus = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $fullName = trim($_POST['full_name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $subject = trim($_POST['subject'] ?? '');
        $message = trim($_POST['message'] ?? '');

        // Validation
        if (empty($fullName) || empty($email) || empty($subject) || empty($message)) {
            $formMessage = 'All fields are required.';
            $formStatus = 'error';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $formMessage = 'Please enter a valid email address.';
            $formStatus = 'error';
        } else {
            // Insert inquiry into database
            $insertStmt = $pdo->prepare("
                INSERT INTO inquiries (full_name, email, subject, message, status, created_at)
                VALUES (:full_name, :email, :subject, :message, 'unread', NOW())
            ");
            
            $insertStmt->execute([
                ':full_name' => $fullName,
                ':email' => $email,
                ':subject' => $subject,
                ':message' => $message,
            ]);

            $formMessage = 'Thank you for your inquiry! We will get back to you shortly.';
            $formStatus = 'success';
        }
    } catch (PDOException $e) {
        error_log('Inquiry submission error: ' . $e->getMessage());
        $formMessage = 'An error occurred while submitting your inquiry. Please try again later.';
        $formStatus = 'error';
    }
}
?>

        <!-- ========================================== -->
        <!-- CONTACT PAGE                               -->
        <!-- ========================================== -->
        <header class="pt-48 pb-20 bg-white border-b border-gray-100 relative overflow-hidden">
            <img src="https://monabbor-hossen.github.io/mbh-golden-global/Logo%20png-01.png" class="absolute right-0 top-1/2 -translate-y-1/2 w-[600px] opacity-[0.02] pointer-events-none z-0" alt="">
            
            <div class="max-w-[90rem] mx-auto px-6 sm:px-8 lg:px-12 fade-up perspective-[1000px] relative z-10">
                <div class="inner-3d">
                    <span class="text-brand-cyan text-xs font-bold tracking-[0.3em] uppercase mb-6 block text-3d-light">Connect</span>
                    <h1 class="text-5xl md:text-8xl font-serif text-brand-navy mb-8 leading-tight text-3d-light">Get in <i class="font-bold text-brand-cyan">Touch</i></h1>
                    <p class="text-xl md:text-2xl text-gray-500 font-medium max-w-2xl leading-relaxed">We are here to make your travel dreams a reality.</p>
                </div>
            </div>
        </header>

        <section class="py-24 bg-brand-sand min-h-screen relative z-10">
            <div class="max-w-[90rem] mx-auto px-6 sm:px-8 lg:px-12">
                
                <div class="grid grid-cols-1 lg:grid-cols-12 gap-16 perspective-[1000px]">
                    
                    <!-- Contact Info Box (Lower Elevation) -->
                    <div class="lg:col-span-5 fade-up card-premium !bg-transparent !border-none !shadow-none">
                        <div class="bg-white rounded-[2.5rem] p-10 h-full border border-gray-100 shadow-sm inner-3d">
                            <h3 class="font-serif text-4xl mb-12 text-brand-navy font-bold">Contact Details</h3>
                            
                            <div class="flex flex-col gap-10">
                                <div class="bg-brand-sand p-6 rounded-[1.5rem] border border-white">
                                    <span class="text-brand-cyan text-[10px] font-bold uppercase tracking-[0.2em] mb-3 flex items-center gap-3"><div class="p-2 bg-white rounded-lg shadow-sm"><i data-lucide="map-pin" class="w-4 h-4"></i></div> Address</span>
                                    <p class="text-brand-navy font-medium leading-relaxed text-sm drop-shadow-sm pl-[3.25rem]"><?php echo htmlspecialchars($address); ?></p>
                                </div>
                                
                                <div class="bg-brand-sand p-6 rounded-[1.5rem] border border-white">
                                    <span class="text-brand-cyan text-[10px] font-bold uppercase tracking-[0.2em] mb-3 flex items-center gap-3"><div class="p-2 bg-white rounded-lg shadow-sm"><i data-lucide="phone" class="w-4 h-4"></i></div> Mobile</span>
                                    <p class="text-brand-navy font-medium leading-relaxed text-sm drop-shadow-sm pl-[3.25rem]"><?php echo htmlspecialchars($phone1); ?><br><?php echo htmlspecialchars($phone2); ?></p>
                                </div>

                                <div class="bg-brand-sand p-6 rounded-[1.5rem] border border-white">
                                    <span class="text-brand-cyan text-[10px] font-bold uppercase tracking-[0.2em] mb-3 flex items-center gap-3"><div class="p-2 bg-white rounded-lg shadow-sm"><i data-lucide="mail" class="w-4 h-4"></i></div> Email</span>
                                    <p class="text-brand-navy font-medium break-all leading-relaxed text-sm drop-shadow-sm pl-[3.25rem]"><?php echo htmlspecialchars($email1); ?><br><?php echo htmlspecialchars($email2); ?></p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Form Block -->
                    <div class="lg:col-span-7 fade-up delay-100 card-premium bg-white">
                        <div class="p-8 md:p-12 rounded-[2.5rem] inner-3d relative">
                            <h3 class="text-4xl font-serif text-brand-navy mb-12 font-bold text-3d-light">Send an Inquiry</h3>
                            
                            <?php if ($formMessage): ?>
                                <div class="mb-8 p-4 rounded-xl border <?php echo $formStatus === 'success' ? 'bg-green-50 border-green-200 text-green-700' : 'bg-red-50 border-red-200 text-red-700'; ?>">
                                    <?php echo htmlspecialchars($formMessage); ?>
                                </div>
                            <?php endif; ?>

                            <form method="POST" class="space-y-8">
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                                    <div>
                                        <input type="text" name="full_name" class="input-minimal text-sm" placeholder="Full Name" required>
                                    </div>
                                    <div>
                                        <input type="email" name="email" class="input-minimal text-sm" placeholder="Email Address" required>
                                    </div>
                                </div>
                                <div>
                                    <input type="text" name="subject" class="input-minimal text-sm" placeholder="Subject" required>
                                </div>
                                <div>
                                    <textarea rows="5" name="message" class="input-minimal text-sm resize-none" placeholder="Message" required></textarea>
                                </div>
                                <div class="pt-6">
                                    <button type="submit" class="btn-primary w-full md:w-auto !px-12 !py-4 text-xs !rounded-xl">Send Message</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </section>

<?php
require_once 'includes/footer.php';
?>
