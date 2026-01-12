    </main>

    <!-- Footer -->
    <footer class="bg-gray-900 text-white pt-12 pb-8">
        <div class="container mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-8 mb-8">
                <!-- Branding -->
                <div class="col-span-1 md:col-span-1">
                     <div class="flex items-center gap-2 mb-4">
                        <span class="text-2xl text-blood-500"><i class="fa-solid fa-droplet"></i></span>
                        <span class="text-xl font-bold tracking-tight">BBMS</span>
                    </div>
                    <p class="text-gray-400 text-sm leading-relaxed">
                        Connecting donors, hospitals, and patients to save lives through efficient blood management.
                    </p>
                </div>

                <!-- Quick Links -->
                <div>
                    <h3 class="text-sm font-semibold text-gray-200 uppercase tracking-wider mb-4">Quick Links</h3>
                    <ul class="space-y-2">
                        <li><a href="index.php" class="text-gray-400 hover:text-white transition-colors">Home</a></li>
                        <li><a href="about.php" class="text-gray-400 hover:text-white transition-colors">About Us</a></li>
                        <li><a href="donation-info.php" class="text-gray-400 hover:text-white transition-colors">Donation Process</a></li>
                        <li><a href="register-donor.php" class="text-gray-400 hover:text-white transition-colors">Become a Donor</a></li>
                    </ul>
                </div>

                <!-- Support -->
                <div>
                    <h3 class="text-sm font-semibold text-gray-200 uppercase tracking-wider mb-4">Support</h3>
                    <ul class="space-y-2">
                        <li><a href="contact.php" class="text-gray-400 hover:text-white transition-colors">Contact Us</a></li>
                        <li><a href="login.php" class="text-gray-400 hover:text-white transition-colors">Portal Login</a></li>
                        <li><a href="#" class="text-gray-400 hover:text-white transition-colors">Privacy Policy</a></li>
                    </ul>
                </div>

                <!-- Contact -->
                <div>
                    <h3 class="text-sm font-semibold text-gray-200 uppercase tracking-wider mb-4">Contact</h3>
                    <ul class="space-y-2 text-sm text-gray-400">
                        <li class="flex items-start gap-2">
                            <i class="fa-solid fa-location-dot mt-1 text-blood-500"></i>
                            <span>123 Medical District,<br>Health City, HC 12345</span>
                        </li>
                        <li class="flex items-center gap-2">
                            <i class="fa-solid fa-phone text-blood-500"></i>
                            <span>(555) 123-4567</span>
                        </li>
                        <li class="flex items-center gap-2">
                            <i class="fa-solid fa-envelope text-blood-500"></i>
                            <span>help@bbms.org</span>
                        </li>
                    </ul>
                </div>
            </div>
            
            <div class="border-t border-gray-800 pt-8 mt-8 text-center">
                <p class="text-gray-500 text-sm">
                    &copy; <?= date('Y') ?> Blood Banking Management System. All rights reserved. <br>
                    <span class="opacity-75 text-xs mt-1 block">University Final Year Project</span>
                </p>
            </div>
        </div>
    </footer>
</body>
</html>
