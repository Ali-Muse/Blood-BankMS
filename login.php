<?php
require_once 'includes/config.php';
require_once 'includes/auth.php';

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    
    $result = login_user($email, $password);
    
    if ($result['success']) {
        redirect($result['redirect']);
    } else {
        $error = $result['message'];
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Blood Banking Management System</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        blood: {
                            50: '#fef2f2',
                            100: '#fee2e2',
                            200: '#fecaca',
                            300: '#fca5a5',
                            400: '#f87171',
                            500: '#ef4444',
                            600: '#dc2626',
                            700: '#c0392b',
                            800: '#991b1b',
                            900: '#7f1d1d',
                        }
                    },
                    fontFamily: {
                        sans: ['Inter', 'sans-serif'],
                    }
                }
            }
        }
    </script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>body { font-family: 'Inter', sans-serif; }</style>
</head>
<body class="bg-gradient-to-br from-blood-800 to-gray-900 min-h-screen flex items-center justify-center p-4">
    <div class="bg-white rounded-3xl shadow-2xl w-full max-w-md overflow-hidden transform transition-all">
        <!-- Header -->
        <div class="bg-blood-50 p-8 text-center border-b border-blood-100">
            <a href="index.php" class="inline-flex items-center gap-2 mb-4 hover:scale-105 transition-transform">
                <span class="text-3xl text-blood-600"><i class="fa-solid fa-droplet px-1"></i></span>
                <span class="text-xl font-bold text-gray-900 tracking-tight">BBMS</span>
            </a>
            <h1 class="text-2xl font-bold text-gray-900">Welcome Back</h1>
            <p class="text-gray-500 text-sm mt-1">Sign in to access your dashboard</p>
        </div>

        <div class="p-8">
            <?php if ($error): ?>
                <div class="bg-red-50 border-l-4 border-red-500 text-red-700 p-4 rounded-md mb-6 flex items-start gap-3">
                    <i class="fa-solid fa-circle-exclamation mt-1"></i>
                    <p><?= htmlspecialchars($error) ?></p>
                </div>
            <?php endif; ?>

            <form method="POST" action="" class="space-y-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Email Address</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                            <i class="fa-solid fa-envelope text-gray-400"></i>
                        </div>
                        <input type="email" name="email" required 
                            class="w-full pl-11 pr-4 py-3 rounded-xl border border-gray-300 focus:ring-2 focus:ring-blood-200 focus:border-blood-500 transition-colors"
                            placeholder="Enter your email">
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Password</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                            <i class="fa-solid fa-lock text-gray-400"></i>
                        </div>
                        <input type="password" name="password" required 
                            class="w-full pl-11 pr-4 py-3 rounded-xl border border-gray-300 focus:ring-2 focus:ring-blood-200 focus:border-blood-500 transition-colors"
                            placeholder="Enter your password">
                    </div>
                </div>

                <button type="submit" class="w-full bg-blood-700 text-white font-bold py-3.5 rounded-xl hover:bg-blood-800 transition-colors shadow-lg hover:shadow-xl transform hover:-translate-y-0.5">
                    Sign In
                </button>
            </form>

            <div class="mt-8 pt-6 border-t border-gray-100 text-center">
                <p class="text-gray-600 mb-4">
                    Don't have an account? 
                    <a href="register-donor.php" class="text-blood-700 font-bold hover:underline">Register as Donor</a>
                </p>
                <a href="index.php" class="text-sm text-gray-500 hover:text-gray-900 flex items-center justify-center gap-2 transition-colors">
                    <i class="fa-solid fa-arrow-left"></i> Back to Home
                </a>
            </div>
            
            <!-- Demo Credentials (Remove in Production) -->
            <div class="mt-6 p-4 bg-gray-50 rounded-lg border border-gray-100 text-xs text-gray-500">
                <p class="font-bold mb-2">Test Credentials:</p>
                <div class="grid grid-cols-1 gap-1">
                    <p>Red Cross: redcross@example.com / password123</p>
                    <p>Minister: minister@example.com / password123</p>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
