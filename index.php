<?php require_once 'includes/public-header.php'; ?>

<!-- Hero Section -->
<section class="relative bg-gradient-to-br from-blood-700 to-blood-900 text-white overflow-hidden">
    <!-- Decorative Pattern -->
    <div class="absolute inset-0 opacity-10">
        <div style="background-image: url('data:image/svg+xml,%3Csvg width=\'60\' height=\'60\' viewBox=\'0 0 60 60\' xmlns=\'http://www.w3.org/2000/svg\'%3E%3Cg fill=\'none\' fill-rule=\'evenodd\'%3E%3Cg fill=\'%23ffffff\' fill-opacity=\'1\'%3E%3Cpath d=\'M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z\'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E');" class="h-full w-full"></div>
    </div>

    <div class="container mx-auto px-4 py-24 sm:px-6 lg:px-8 relative z-10">
        <div class="max-w-3xl mx-auto text-center">
            <h1 class="text-4xl sm:text-5xl lg:text-6xl font-extrabold tracking-tight mb-6">
                Save Lives Through <br>
                <span class="text-blood-300">Blood Donation</span>
            </h1>
            <p class="text-xl sm:text-2xl text-blood-100 mb-10 leading-relaxed font-light">
                Join our mission to ensure a safe, accessible, and sustainable blood supply for everyone in need. Your contribution matters.
            </p>
            <div class="flex flex-col sm:flex-row gap-4 justify-center">
                <a href="register-donor.php" class="inline-flex justify-center items-center px-8 py-4 border border-transparent text-lg font-semibold rounded-full text-blood-700 bg-white hover:bg-blood-50 transition-all shadow-lg transform hover:-translate-y-1">
                    Become a Donor
                </a>
                <a href="donation-info.php" class="inline-flex justify-center items-center px-8 py-4 border-2 border-white text-lg font-semibold rounded-full text-white hover:bg-white/10 transition-all">
                    Learn More
                </a>
            </div>
        </div>
    </div>
    
    <!-- Wave Separator -->
    <div class="absolute bottom-0 w-full text-gray-50">
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1440 320" class="w-full h-auto">
            <path fill="currentColor" fill-opacity="1" d="M0,96L48,112C96,128,192,160,288,160C384,160,480,128,576,112C672,96,768,96,864,112C960,128,1056,160,1152,160C1248,160,1344,128,1392,112L1440,96L1440,320L1392,320C1344,320,1248,320,1152,320C1056,320,960,320,864,320C768,320,672,320,576,320C480,320,384,320,288,320C192,320,96,320,48,320L0,320Z"></path>
        </svg>
    </div>
</section>

<!-- Stats Section -->
<section class="py-16 bg-gray-50">
    <div class="container mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-2 md:grid-cols-4 gap-8">
            <div class="bg-white p-6 rounded-2xl shadow-sm text-center transform hover:scale-105 transition-transform duration-300">
                <div class="text-4xl lg:text-5xl font-extrabold text-blood-600 mb-2">10k+</div>
                <div class="text-gray-500 font-medium">Registered Donors</div>
            </div>
            <div class="bg-white p-6 rounded-2xl shadow-sm text-center transform hover:scale-105 transition-transform duration-300">
                <div class="text-4xl lg:text-5xl font-extrabold text-blood-600 mb-2">5k+</div>
                <div class="text-gray-500 font-medium">Blood Units Collected</div>
            </div>
            <div class="bg-white p-6 rounded-2xl shadow-sm text-center transform hover:scale-105 transition-transform duration-300">
                <div class="text-4xl lg:text-5xl font-extrabold text-blood-600 mb-2">50+</div>
                <div class="text-gray-500 font-medium">Partner Hospitals</div>
            </div>
            <div class="bg-white p-6 rounded-2xl shadow-sm text-center transform hover:scale-105 transition-transform duration-300">
                <div class="text-4xl lg:text-5xl font-extrabold text-blood-600 mb-2">15+</div>
                <div class="text-gray-500 font-medium">Branches</div>
            </div>
        </div>
    </div>
</section>

<!-- Mission & Vision -->
<section class="py-20 bg-white">
    <div class="container mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid md:grid-cols-2 gap-12 lg:gap-20 items-stretch">
            <div class="bg-gray-50 rounded-3xl p-8 lg:p-12 shadow-sm border border-gray-100 flex flex-col">
                <div class="w-16 h-16 bg-blood-100 rounded-2xl flex items-center justify-center text-3xl mb-6 text-blood-600">
                    <i class="fa-solid fa-bullseye"></i>
                </div>
                <h2 class="text-3xl font-bold text-gray-900 mb-4">Our Mission</h2>
                <p class="text-lg text-gray-600 leading-relaxed flex-grow">
                    To ensure a safe, adequate, and sustainable blood supply for all patients in need through 
                    efficient collection, testing, storage, and distribution of blood and blood products. 
                    We strive to bridge the gap between donors and patients with technology and compassion.
                </p>
            </div>
            <div class="bg-gray-50 rounded-3xl p-8 lg:p-12 shadow-sm border border-gray-100 flex flex-col">
                 <div class="w-16 h-16 bg-blood-100 rounded-2xl flex items-center justify-center text-3xl mb-6 text-blood-600">
                    <i class="fa-solid fa-eye"></i>
                </div>
                <h2 class="text-3xl font-bold text-gray-900 mb-4">Our Vision</h2>
                <p class="text-lg text-gray-600 leading-relaxed flex-grow">
                    A nation where no patient dies due to lack of safe blood, and where voluntary blood 
                    donation is a way of life for every healthy citizen. We envision a future where 
                    every blood request is met instantly and safely.
                </p>
            </div>
        </div>
    </div>
</section>

<!-- Process Section -->
<section class="py-20 bg-gray-50">
    <div class="container mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-16">
            <span class="text-blood-600 font-semibold tracking-wider uppercase text-sm">How it works</span>
            <h2 class="text-3xl lg:text-4xl font-bold text-gray-900 mt-2">Saving Lives is Simple</h2>
        </div>
        
        <div class="grid md:grid-cols-3 gap-8">
            <div class="relative">
                <div class="bg-white rounded-2xl p-8 shadow-sm h-full text-center hover:shadow-md transition-shadow">
                    <div class="w-20 h-20 mx-auto bg-blue-50 rounded-full flex items-center justify-center text-3xl text-blue-500 mb-6">
                        <i class="fa-solid fa-user-plus"></i>
                    </div>
                    <h3 class="text-xl font-bold text-gray-900 mb-3">1. Register</h3>
                    <p class="text-gray-600">Sign up as a donor in less than 2 minutes. It's quick, easy, and secure.</p>
                </div>
                <!-- Connector Line (Desktop) -->
                <div class="hidden md:block absolute top-1/2 -right-4 w-8 h-1 bg-gray-200 transform -translate-y-1/2 z-0"></div>
            </div>
             <div class="relative">
                <div class="bg-white rounded-2xl p-8 shadow-sm h-full text-center hover:shadow-md transition-shadow relative z-10">
                     <div class="w-20 h-20 mx-auto bg-green-50 rounded-full flex items-center justify-center text-3xl text-green-500 mb-6">
                        <i class="fa-solid fa-hand-holding-medical"></i>
                    </div>
                    <h3 class="text-xl font-bold text-gray-900 mb-3">2. Donate</h3>
                    <p class="text-gray-600">Visit one of our centers or mobile camps. Our staff will guide you through the safe process.</p>
                </div>
                 <!-- Connector Line (Desktop) -->
                <div class="hidden md:block absolute top-1/2 -right-4 w-8 h-1 bg-gray-200 transform -translate-y-1/2 z-0"></div>
            </div>
             <div class="relative">
                <div class="bg-white rounded-2xl p-8 shadow-sm h-full text-center hover:shadow-md transition-shadow z-10">
                     <div class="w-20 h-20 mx-auto bg-red-50 rounded-full flex items-center justify-center text-3xl text-red-500 mb-6">
                        <i class="fa-solid fa-heart-pulse"></i>
                    </div>
                    <h3 class="text-xl font-bold text-gray-900 mb-3">3. Save Lives</h3>
                    <p class="text-gray-600">Your donation is processed and sent to patients in need. Be a hero today.</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Call to Action -->
<section class="py-20 relative overflow-hidden">
    <div class="absolute inset-0 bg-blood-900"></div>
    <div class="absolute inset-0 bg-blood-800 opacity-90"></div>
    <!-- Decor -->
    <div class="absolute top-0 right-0 -mt-20 -mr-20 w-80 h-80 bg-blood-700 rounded-full blur-3xl opacity-50"></div>
    <div class="absolute bottom-0 left-0 -mb-20 -ml-20 w-80 h-80 bg-blood-600 rounded-full blur-3xl opacity-50"></div>

    <div class="container mx-auto px-4 text-center relative z-10">
        <h2 class="text-3xl md:text-4xl font-bold text-white mb-6">Ready to Make a Difference?</h2>
        <p class="text-xl text-blood-100 max-w-2xl mx-auto mb-10">
            Every drop counts. Every donation is a step towards saving a life. 
            Join our community of heroes today.
        </p>
        <a href="register-donor.php" class="inline-flex items-center justify-center px-10 py-4 bg-white text-blood-800 text-lg font-bold rounded-full shadow-xl hover:bg-gray-100 transform hover:-translate-y-1 transition-all">
            Join the Cause
        </a>
    </div>
</section>

<?php require_once 'includes/public-footer.php'; ?>
