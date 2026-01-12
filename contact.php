<?php require_once 'includes/public-header.php'; ?>

<!-- Page Header -->
<div class="bg-gray-50 py-12 sm:py-20 border-b border-gray-100">
    <div class="container mx-auto px-4 sm:px-6 lg:px-8 text-center">
        <h1 class="text-4xl font-extrabold text-gray-900 mb-4 tracking-tight">Contact Us</h1>
        <p class="text-xl text-gray-600 max-w-2xl mx-auto font-light">We're here to help. Reach out to us for any queries or emergency requests.</p>
    </div>
</div>

<div class="container mx-auto px-4 sm:px-6 lg:px-8 py-16">
    <div class="grid lg:grid-cols-2 gap-16">
        <!-- Contact Form -->
        <div>
            <div class="card bg-white p-8 rounded-3xl shadow-lg border border-gray-100">
                <h2 class="text-2xl font-bold text-gray-900 mb-8 border-l-4 border-blood-600 pl-4">Send Message</h2>
                <form>
                    <div class="space-y-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Your Name</label>
                            <input type="text" class="w-full px-4 py-3 rounded-xl border border-gray-300 focus:ring-2 focus:ring-blood-200 focus:border-blood-500 transition-colors" placeholder="John Doe">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Email Address</label>
                            <input type="email" class="w-full px-4 py-3 rounded-xl border border-gray-300 focus:ring-2 focus:ring-blood-200 focus:border-blood-500 transition-colors" placeholder="john@example.com">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Phone Number</label>
                            <input type="tel" class="w-full px-4 py-3 rounded-xl border border-gray-300 focus:ring-2 focus:ring-blood-200 focus:border-blood-500 transition-colors" placeholder="+1 (555) 000-0000">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Message</label>
                            <textarea class="w-full px-4 py-3 rounded-xl border border-gray-300 focus:ring-2 focus:ring-blood-200 focus:border-blood-500 transition-colors" rows="5" placeholder="How can we help you?"></textarea>
                        </div>
                        <button type="submit" class="w-full bg-blood-600 text-white font-bold py-4 rounded-xl hover:bg-blood-700 transition-colors shadow-md hover:shadow-lg transform hover:-translate-y-0.5">
                            Send Message
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Contact Information -->
        <div class="space-y-8">
            <div class="bg-white p-8 rounded-3xl shadow-sm border border-gray-100">
                <h2 class="text-2xl font-bold text-gray-900 mb-8 border-l-4 border-blood-600 pl-4">Get in Touch</h2>
                
                <div class="space-y-6">
                    <div class="flex items-start gap-4">
                        <div class="w-12 h-12 bg-blood-50 rounded-xl flex items-center justify-center flex-shrink-0 text-blood-600">
                            <i class="fa-solid fa-location-dot text-xl"></i>
                        </div>
                        <div>
                            <h3 class="font-bold text-gray-900 mb-1">Visit Us</h3>
                            <p class="text-gray-600">123 Blood Bank Street<br>Medical District, City, Country 12345</p>
                        </div>
                    </div>

                    <div class="flex items-start gap-4">
                        <div class="w-12 h-12 bg-blood-50 rounded-xl flex items-center justify-center flex-shrink-0 text-blood-600">
                            <i class="fa-solid fa-phone text-xl"></i>
                        </div>
                        <div>
                            <h3 class="font-bold text-gray-900 mb-1">Call Us</h3>
                            <p class="text-gray-600">
                                <span class="block mb-1">Main: +1 (555) 123-4567</span>
                                <span class="text-blood-700 font-semibold">Emergency: +1 (555) 999-0000</span>
                            </p>
                        </div>
                    </div>

                    <div class="flex items-start gap-4">
                        <div class="w-12 h-12 bg-blood-50 rounded-xl flex items-center justify-center flex-shrink-0 text-blood-600">
                            <i class="fa-solid fa-envelope text-xl"></i>
                        </div>
                        <div>
                            <h3 class="font-bold text-gray-900 mb-1">Email Us</h3>
                            <p class="text-gray-600">info@bloodbank.org<br>support@bloodbank.org</p>
                        </div>
                    </div>

                    <div class="flex items-start gap-4">
                        <div class="w-12 h-12 bg-blood-50 rounded-xl flex items-center justify-center flex-shrink-0 text-blood-600">
                            <i class="fa-solid fa-clock text-xl"></i>
                        </div>
                        <div>
                            <h3 class="font-bold text-gray-900 mb-1">Working Hours</h3>
                            <p class="text-gray-600">
                                Mon-Fri: 8:00 AM - 6:00 PM<br>
                                Sat-Sun: 9:00 AM - 4:00 PM<br>
                                <span class="text-blood-700 font-semibold italic">Emergency Services Available 24/7</span>
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Emergency Card -->
            <div class="bg-gradient-to-br from-blood-50 to-red-100 p-8 rounded-3xl border border-blood-200">
                <div class="flex items-center gap-4 mb-4">
                    <div class="w-12 h-12 bg-white rounded-full flex items-center justify-center text-blood-600 text-xl shadow-sm animate-pulse">
                        <i class="fa-solid fa-truck-medical"></i>
                    </div>
                    <h3 class="text-xl font-bold text-blood-800">Emergency Blood Request?</h3>
                </div>
                <p class="text-blood-900/80 mb-6">For urgent blood requirements, please call our dedicated emergency hotline immediately.</p>
                <a href="tel:+15559990000" class="inline-flex items-center justify-center w-full py-4 bg-white text-blood-700 font-bold rounded-xl hover:bg-blood-50 transition-colors shadow-sm">
                    Call Emergency Hotline
                </a>
            </div>
        </div>
    </div>
</div>

<?php require_once 'includes/public-footer.php'; ?>
