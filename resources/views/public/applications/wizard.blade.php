<!DOCTYPE html>
<html lang="en" class="h-full bg-slate-100 dark:bg-slate-950">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Apply for Fiber Internet - Apex Broadband</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
</head>
<body class="h-full font-sans antialiased text-slate-800 dark:text-slate-100">
    <div class="min-h-full py-8 px-4 sm:px-6 lg:px-8 max-w-4xl mx-auto">
        <div class="bg-white dark:bg-slate-900 rounded-2xl shadow-xl border border-slate-200 dark:border-slate-800 p-6 sm:p-8">
            <div class="pb-6 border-b border-slate-200 dark:border-slate-800 text-center">
                <span class="text-2xl font-black tracking-wider text-indigo-600">APEX<span class="text-slate-800 dark:text-white">FIBER</span></span>
                <h1 class="text-xl font-bold mt-2 text-slate-800 dark:text-white">Online Internet Service Application</h1>
                <p class="text-xs text-slate-500">Check instant serviceability and apply for high-speed fiber broadband in minutes.</p>
            </div>

            <form method="POST" action="{{ route('public.applications.submit') }}" id="appWizardForm" class="mt-6 space-y-6">
                @csrf

                <!-- Step 1: Applicant Information -->
                <div class="space-y-4">
                    <h3 class="font-bold text-slate-800 dark:text-white text-sm border-b pb-2">1. Applicant Contact Details</h3>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300">Applicant Classification *</label>
                            <select name="applicant_type" id="applicantType" class="mt-1 w-full px-3 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-lg text-sm">
                                <option value="RESIDENTIAL">Residential Household</option>
                                <option value="INDIVIDUAL">Individual Subscriber</option>
                                <option value="BUSINESS">Business / Enterprise</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300">Primary Mobile Number *</label>
                            <input type="text" name="primary_phone" required placeholder="+63 917 000 0000" class="mt-1 w-full px-3 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-lg text-sm">
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300">First Name *</label>
                            <input type="text" name="first_name" required placeholder="Juan" class="mt-1 w-full px-3 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-lg text-sm">
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300">Last Name *</label>
                            <input type="text" name="last_name" required placeholder="Dela Cruz" class="mt-1 w-full px-3 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-lg text-sm">
                        </div>

                        <div class="sm:col-span-2">
                            <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300">Email Address</label>
                            <input type="email" name="email" placeholder="juan.delacruz@gmail.com" class="mt-1 w-full px-3 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-lg text-sm">
                        </div>
                    </div>
                </div>

                <!-- Step 2: Select Package -->
                <div class="space-y-4">
                    <h3 class="font-bold text-slate-800 dark:text-white text-sm border-b pb-2">2. Select Internet Plan</h3>
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300">Internet Service Package *</label>
                        <select name="service_package_id" id="packageSelect" required class="mt-1 w-full px-3 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-lg text-sm font-semibold">
                            @foreach($packages as $pkg)
                                <option value="{{ $pkg->id }}">
                                    {{ $pkg->name }} — ⚡ {{ $pkg->download_speed_formatted }} @ ₱{{ number_format($pkg->base_price, 2) }}/mo
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <!-- Step 3: Installation Address & Map Location Picker -->
                <div class="space-y-4">
                    <h3 class="font-bold text-slate-800 dark:text-white text-sm border-b pb-2">3. Installation Address & Exact GPS Pin</h3>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300">Complete House / Street Address *</label>
                        <textarea name="installation_address" required rows="2" placeholder="Block 1 Lot 2, Sampaguita Street, Central, Quezon City" class="mt-1 w-full px-3 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-lg text-sm"></textarea>
                    </div>

                    <div>
                        <div class="flex justify-between items-center mb-1">
                            <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300">Pin Installation Location on Map *</label>
                            <span class="text-[11px] text-indigo-600 dark:text-indigo-400 font-mono">Drag marker to adjust exact coordinates</span>
                        </div>
                        <div id="map" class="h-64 w-full rounded-xl border border-slate-300 dark:border-slate-700 z-10"></div>
                    </div>

                    <div class="grid grid-cols-2 gap-4 font-mono text-xs">
                        <div>
                            <label class="block text-[10px] text-slate-400">Latitude</label>
                            <input type="text" name="latitude" id="latInput" readonly value="14.6520000" class="w-full px-2 py-1 bg-slate-100 dark:bg-slate-800 border rounded font-bold">
                        </div>
                        <div>
                            <label class="block text-[10px] text-slate-400">Longitude</label>
                            <input type="text" name="longitude" id="lngInput" readonly value="121.0320000" class="w-full px-2 py-1 bg-slate-100 dark:bg-slate-800 border rounded font-bold">
                        </div>
                    </div>
                </div>

                <div class="pt-4 border-t border-slate-200 dark:border-slate-800 flex justify-end">
                    <button type="submit" class="px-6 py-3 bg-indigo-600 hover:bg-indigo-700 text-white font-bold rounded-xl shadow-lg transition text-sm">
                        Submit Internet Application 🚀
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            var defaultLat = 14.6520000;
            var defaultLng = 121.0320000;

            var map = L.map('map').setView([defaultLat, defaultLng], 15);

            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                maxZoom: 19,
                attribution: '© OpenStreetMap contributors'
            }).addTo(map);

            var marker = L.marker([defaultLat, defaultLng], { draggable: true }).addTo(map);

            marker.on('dragend', function (e) {
                var position = marker.getLatLng();
                document.getElementById('latInput').value = position.lat.toFixed(7);
                document.getElementById('lngInput').value = position.lng.toFixed(7);
            });

            map.on('click', function (e) {
                marker.setLatLng(e.latlng);
                document.getElementById('latInput').value = e.latlng.lat.toFixed(7);
                document.getElementById('lngInput').value = e.latlng.lng.toFixed(7);
            });
        });
    </script>
</body>
</html>
