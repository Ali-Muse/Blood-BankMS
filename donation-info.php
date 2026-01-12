<?php require_once 'includes/public-header.php'; ?>

<!-- Page Header -->
<div class="bg-gray-50 py-12 sm:py-20 border-b border-gray-100">
    <div class="container mx-auto px-4 sm:px-6 lg:px-8 text-center">
        <h1 class="text-4xl font-extrabold text-gray-900 mb-4 tracking-tight">Blood Donation Guidelines</h1>
        <p class="text-xl text-gray-600 max-w-2xl mx-auto font-light">Everything you need to know about becoming a donor and saving lives.</p>
    </div>
</div>

<div class="container mx-auto px-4 sm:px-6 lg:px-8 py-16">
    <!-- Eligibility Grid -->
    <div class="grid md:grid-cols-2 gap-8 mb-16">
        <!-- Eligible -->
        <div class="bg-white rounded-3xl p-8 shadow-sm border border-gray-100">
            <h2 class="text-2xl font-bold text-green-700 mb-6 flex items-center gap-3">
                <i class="fa-solid fa-circle-check"></i> Eligibility Criteria
            </h2>
            <ul class="space-y-4">
                <li class="flex items-center gap-3 text-gray-700">
                    <span class="w-2 h-2 rounded-full bg-green-500"></span>
                    <span><strong>Age:</strong> 18-65 years</span>
                </li>
                <li class="flex items-center gap-3 text-gray-700">
                    <span class="w-2 h-2 rounded-full bg-green-500"></span>
                    <span><strong>Weight:</strong> Minimum 50 kg</span>
                </li>
                <li class="flex items-center gap-3 text-gray-700">
                    <span class="w-2 h-2 rounded-full bg-green-500"></span>
                    <span><strong>Hemoglobin:</strong> Minimum 12.5 g/dL</span>
                </li>
                 <li class="flex items-center gap-3 text-gray-700">
                    <span class="w-2 h-2 rounded-full bg-green-500"></span>
                    <span><strong>Blood Pressure:</strong> Normal range</span>
                </li>
                 <li class="flex items-center gap-3 text-gray-700">
                    <span class="w-2 h-2 rounded-full bg-green-500"></span>
                    <span><strong>Frequency:</strong> Every 3 months (Men) / 4 months (Women)</span>
                </li>
            </ul>
        </div>

        <!-- Not Eligible -->
        <div class="bg-white rounded-3xl p-8 shadow-sm border border-gray-100">
            <h2 class="text-2xl font-bold text-red-600 mb-6 flex items-center gap-3">
                <i class="fa-solid fa-circle-xmark"></i> Who Cannot Donate
            </h2>
            <ul class="space-y-4">
                <li class="flex items-center gap-3 text-gray-700">
                    <span class="w-2 h-2 rounded-full bg-red-400"></span>
                    <span>Recent illness, infection, or surgery</span>
                </li>
                <li class="flex items-center gap-3 text-gray-700">
                    <span class="w-2 h-2 rounded-full bg-red-400"></span>
                    <span>Pregnant or breastfeeding women</span>
                </li>
                <li class="flex items-center gap-3 text-gray-700">
                    <span class="w-2 h-2 rounded-full bg-red-400"></span>
                    <span>Positive for HIV, Hepatitis B/C, or Syphilis</span>
                </li>
                <li class="flex items-center gap-3 text-gray-700">
                    <span class="w-2 h-2 rounded-full bg-red-400"></span>
                    <span>Chronic conditions like diabetes (on insulin)</span>
                </li>
                <li class="flex items-center gap-3 text-gray-700">
                    <span class="w-2 h-2 rounded-full bg-red-400"></span>
                    <span>Recent tattoo or piercing (within 6 months)</span>
                </li>
            </ul>
        </div>
    </div>

    <!-- Process Section -->
    <div class="mb-16">
        <h2 class="text-3xl font-bold text-gray-900 text-center mb-12">The Donation Process</h2>
        <div class="grid grid-cols-1 md:grid-cols-4 gap-8">
            <div class="text-center group">
                <div class="w-24 h-24 mx-auto bg-blue-50 rounded-full flex items-center justify-center text-4xl text-blue-500 mb-6 group-hover:scale-110 transition-transform">
                    <i class="fa-solid fa-file-signature"></i>
                </div>
                <h3 class="text-xl font-bold text-gray-900 mb-2">1. Registration</h3>
                <p class="text-gray-500 text-sm">Fill out donor registration form with valid ID.</p>
            </div>
            <div class="text-center group">
                <div class="w-24 h-24 mx-auto bg-purple-50 rounded-full flex items-center justify-center text-4xl text-purple-500 mb-6 group-hover:scale-110 transition-transform">
                    <i class="fa-solid fa-stethoscope"></i>
                </div>
                <h3 class="text-xl font-bold text-gray-900 mb-2">2. Screening</h3>
                <p class="text-gray-500 text-sm">Mini-health checkup to ensure you are fit to donate.</p>
            </div>
            <div class="text-center group">
                <div class="w-24 h-24 mx-auto bg-blood-50 rounded-full flex items-center justify-center text-4xl text-blood-500 mb-6 group-hover:scale-110 transition-transform">
                    <i class="fa-solid fa-hand-holding-droplet"></i>
                </div>
                <h3 class="text-xl font-bold text-gray-900 mb-2">3. Donation</h3>
                <p class="text-gray-500 text-sm">Safe blood collection process taking 10-15 minutes.</p>
            </div>
            <div class="text-center group">
                <div class="w-24 h-24 mx-auto bg-orange-50 rounded-full flex items-center justify-center text-4xl text-orange-500 mb-6 group-hover:scale-110 transition-transform">
                    <i class="fa-solid fa-mug-hot"></i>
                </div>
                 <h3 class="text-xl font-bold text-gray-900 mb-2">4. Refreshment</h3>
                <p class="text-gray-500 text-sm">Rest for a few minutes and enjoy some snacks.</p>
            </div>
        </div>
    </div>

    <!-- Benefits Section -->
    <div class="bg-blood-50 rounded-3xl p-8 lg:p-12">
        <div class="text-center mb-10">
            <h2 class="text-3xl font-bold text-gray-900 mb-4">Why Donate?</h2>
            <p class="text-gray-600">Apart from saving lives, donating blood has health benefits for you too.</p>
        </div>
        <div class="grid md:grid-cols-3 gap-8">
            <div class="bg-white p-6 rounded-2xl shadow-sm">
                <i class="fa-solid fa-heart-pulse text-3xl text-blood-500 mb-4"></i>
                <h3 class="font-bold text-gray-900 mb-2">Heart Health</h3>
                <p class="text-gray-500 text-sm">Regular donation helps maintain healthy iron levels and reduces risk of heart disease.</p>
            </div>
            <div class="bg-white p-6 rounded-2xl shadow-sm">
                <i class="fa-solid fa-weight-scale text-3xl text-blood-500 mb-4"></i>
                <h3 class="font-bold text-gray-900 mb-2">Free Checkup</h3>
                <p class="text-gray-500 text-sm">Get a mini-physical including blood pressure, hemoglobin, and disease screening.</p>
            </div>
             <div class="bg-white p-6 rounded-2xl shadow-sm">
                <i class="fa-solid fa-face-smile-beam text-3xl text-blood-500 mb-4"></i>
                <h3 class="font-bold text-gray-900 mb-2">Emotional Joy</h3>
                <p class="text-gray-500 text-sm">The satisfaction of knowing you've saved up to 3 lives is incomparable.</p>
            </div>
        </div>
        
        <div class="text-center mt-12">
            <a href="register-donor.php" class="inline-flex items-center justify-center px-8 py-4 bg-blood-600 text-white font-bold rounded-full shadow-lg hover:bg-blood-700 transform hover:-translate-y-1 transition-all">
                Become a Donor Now
            </a>
        </div>
    </div>
</div>

<?php require_once 'includes/public-footer.php'; ?>
