<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Blood Banking Management System - Save Lives</title>
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
                            700: '#c0392b', // Custom brand red
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
    <style>
        body { font-family: 'Inter', sans-serif; }
    </style>
</head>
<body class="bg-gray-50 text-gray-800 antialiased flex flex-col min-h-screen">
    <!-- Navigation -->
    <nav class="bg-white shadow-md sticky top-0 z-50">
        <div class="container mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16">
                <!-- Logo -->
                <div class="flex items-center">
                    <a href="index.php" class="flex items-center gap-2">
                        <span class="text-3xl text-blood-600"><i class="fa-solid fa-droplet px-1"></i></span>
                        <span class="text-xl font-bold text-gray-900 tracking-tight">BBMS</span>
                    </a>
                </div>
                
                <!-- Desktop Menu -->
                <div class="hidden md:flex items-center space-x-8">
                    <a href="index.php" class="text-gray-600 hover:text-blood-700 font-medium transition-colors duration-200">Home</a>
                    <a href="about.php" class="text-gray-600 hover:text-blood-700 font-medium transition-colors duration-200">About</a>
                    <a href="donation-info.php" class="text-gray-600 hover:text-blood-700 font-medium transition-colors duration-200">Donation Info</a>
                    <a href="contact.php" class="text-gray-600 hover:text-blood-700 font-medium transition-colors duration-200">Contact</a>
                    <div class="flex items-center gap-4 ml-4 border-l pl-6 border-gray-200">
                         <a href="register-donor.php" class="text-gray-600 hover:text-blood-700 font-medium transition-colors duration-200">Register</a>
                        <a href="login.php" class="bg-blood-700 hover:bg-blood-800 text-white px-5 py-2 rounded-full font-medium transition-all shadow-md hover:shadow-lg transform hover:-translate-y-0.5">
                            Login
                        </a>
                    </div>
                </div>

                <!-- Mobile Menu Button -->
                <div class="md:hidden flex items-center">
                    <button class="text-gray-500 hover:text-gray-700 focus:outline-none">
                         <i class="fa-solid fa-bars text-2xl"></i>
                    </button>
                </div>
            </div>
        </div>
    </nav>
    
    <!-- Main Content Wrapper -->
    <main class="flex-grow">
