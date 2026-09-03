@extends('layouts.app')

@section('title', 'GIS Operations & Infrastructure Map')

@section('content')
<div class="p-6">
    <div class="flex flex-col md:flex-row md:items-center justify-between pb-4 border-b border-slate-200 dark:border-slate-800 gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-800 dark:text-white">GIS Infrastructure & Subscriber Operations Map</h1>
            <p class="text-sm text-slate-500 dark:text-slate-400">Real-time geographic mapping of nodes, sector APs, towers, fiber splitters, applications, and subscribers.</p>
        </div>
        <div class="flex flex-wrap gap-2">
            <a href="{{ route('admin.gis.dashboard') }}" class="px-3 py-1.5 bg-slate-800 hover:bg-slate-700 text-white rounded-lg text-xs font-bold transition">
                GIS Analytics
            </a>
            <a href="{{ route('admin.gis.towers.index') }}" class="px-3 py-1.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg text-xs font-bold transition">
                Towers
            </a>
            <a href="{{ route('admin.gis.distribution-points.index') }}" class="px-3 py-1.5 bg-purple-600 hover:bg-purple-700 text-white rounded-lg text-xs font-bold transition">
                Fiber Splitters
            </a>
            <a href="{{ route('admin.gis.import.form') }}" class="px-3 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg text-xs font-bold transition">
                Import GPS
            </a>
        </div>
    </div>

    <!-- Map & Sidebar Grid -->
    <div class="mt-4 grid grid-cols-1 lg:grid-cols-4 gap-4">
        <!-- Sidebar Controls & Layer Toggles -->
        <div class="bg-white dark:bg-slate-900 rounded-xl shadow-sm border border-slate-200 dark:border-slate-800 p-4 space-y-4">
            <h3 class="font-bold text-slate-800 dark:text-white text-sm pb-2 border-b">Map Layer Controls</h3>

            <div class="space-y-2 text-xs font-medium">
                <label class="flex items-center gap-2 cursor-pointer">
                    <input type="checkbox" id="layerNodes" checked class="rounded text-indigo-600">
                    <span class="w-3 h-3 rounded-full bg-indigo-600 inline-block"></span>
                    <span>Network Nodes & POPs</span>
                </label>

                <label class="flex items-center gap-2 cursor-pointer">
                    <input type="checkbox" id="layerAPs" checked class="rounded text-indigo-600">
                    <span class="w-3 h-3 rounded-full bg-purple-600 inline-block"></span>
                    <span>Access Points & Sectors</span>
                </label>

                <label class="flex items-center gap-2 cursor-pointer">
                    <input type="checkbox" id="layerTowers" checked class="rounded text-indigo-600">
                    <span class="w-3 h-3 rounded-full bg-amber-500 inline-block"></span>
                    <span>Telecom Towers</span>
                </label>

                <label class="flex items-center gap-2 cursor-pointer">
                    <input type="checkbox" id="layerDPs" checked class="rounded text-indigo-600">
                    <span class="w-3 h-3 rounded-full bg-emerald-600 inline-block"></span>
                    <span>Fiber Splitters & DPs</span>
                </label>

                <label class="flex items-center gap-2 cursor-pointer">
                    <input type="checkbox" id="layerCustomers" checked class="rounded text-indigo-600">
                    <span class="w-3 h-3 rounded-full bg-blue-500 inline-block"></span>
                    <span>Active Subscribers</span>
                </label>

                <label class="flex items-center gap-2 cursor-pointer">
                    <input type="checkbox" id="layerApps" checked class="rounded text-indigo-600">
                    <span class="w-3 h-3 rounded-full bg-rose-500 inline-block"></span>
                    <span>Pending Applications</span>
                </label>
            </div>

            <div class="pt-3 border-t text-xs space-y-2">
                <h4 class="font-bold text-slate-800 dark:text-white">Selected Map Location</h4>
                <div class="font-mono text-[11px] text-slate-500">Lat: <span id="selLat">14.6507</span>, Lng: <span id="selLng">121.0300</span></div>
                <button id="btnFindNearby" class="w-full py-1.5 bg-slate-800 text-white rounded text-xs font-bold hover:bg-slate-700">
                    🔍 Find Nearby Infrastructure
                </button>
            </div>

            <div id="nearbyResults" class="hidden text-xs space-y-2 pt-2 border-t max-h-48 overflow-y-auto">
                <h4 class="font-bold text-slate-800 dark:text-white">Nearby Network Nodes</h4>
                <div id="nearbyList" class="space-y-1"></div>
            </div>
        </div>

        <!-- Main Map Container -->
        <div class="lg:col-span-3 bg-white dark:bg-slate-900 rounded-xl shadow-sm border border-slate-200 dark:border-slate-800 p-2 min-h-[500px] flex flex-col">
            <div id="gisMap" class="w-full h-[550px] rounded-lg border border-slate-200 dark:border-slate-800 z-10"></div>
        </div>
    </div>
</div>

<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        var map = L.map('gisMap').setView([14.6507000, 121.0300000], 14);

        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            maxZoom: 19,
            attribution: '© OpenStreetMap contributors'
        }).addTo(map);

        var layerGroup = L.layerGroup().addTo(map);

        function loadMapData() {
            var bounds = map.getBounds();
            var url = `/api/gis/viewport?north=${bounds.getNorth()}&south=${bounds.getSouth()}&east=${bounds.getEast()}&west=${bounds.getWest()}`;

            fetch(url)
                .then(res => res.json())
                .then(res => {
                    if (res.status === 'success') {
                        layerGroup.clearLayers();

                        // Render Nodes
                        if (document.getElementById('layerNodes').checked && res.data.nodes) {
                            res.data.nodes.forEach(n => {
                                var marker = L.circleMarker([n.latitude, n.longitude], {
                                    radius: 8, color: '#4F46E5', fillColor: '#6366F1', fillOpacity: 0.9
                                }).addTo(layerGroup);
                                marker.bindPopup(`<b>Node: ${n.name}</b><br>Code: ${n.node_code}<br>Type: ${n.node_type}`);
                            });
                        }

                        // Render Towers
                        if (document.getElementById('layerTowers').checked && res.data.towers) {
                            res.data.towers.forEach(t => {
                                var marker = L.circleMarker([t.latitude, t.longitude], {
                                    radius: 9, color: '#D97706', fillColor: '#F59E0B', fillOpacity: 0.9
                                }).addTo(layerGroup);
                                marker.bindPopup(`<b>Tower: ${t.name}</b><br>Code: ${t.code}<br>Height: ${t.height_meters}m`);
                            });
                        }

                        // Render DPs
                        if (document.getElementById('layerDPs').checked && res.data.distribution_points) {
                            res.data.distribution_points.forEach(dp => {
                                var marker = L.circleMarker([dp.latitude, dp.longitude], {
                                    radius: 6, color: '#059669', fillColor: '#10B981', fillOpacity: 0.9
                                }).addTo(layerGroup);
                                marker.bindPopup(`<b>Fiber Splitter: ${dp.name}</b><br>Code: ${dp.code}<br>Capacity: ${dp.capacity} ports`);
                            });
                        }

                        // Render Applications
                        if (document.getElementById('layerApps').checked && res.data.applications) {
                            res.data.applications.forEach(a => {
                                if (a.latitude && a.longitude) {
                                    var marker = L.circleMarker([a.latitude, a.longitude], {
                                        radius: 6, color: '#E11D48', fillColor: '#F43F5E', fillOpacity: 0.9
                                    }).addTo(layerGroup);
                                    marker.bindPopup(`<b>App #: ${a.application_number}</b><br>Status: ${a.status}`);
                                }
                            });
                        }
                    }
                });
        }

        map.on('moveend', loadMapData);
        loadMapData();

        map.on('click', function (e) {
            document.getElementById('selLat').innerText = e.latlng.lat.toFixed(6);
            document.getElementById('selLng').innerText = e.latlng.lng.toFixed(6);
        });

        document.getElementById('btnFindNearby').addEventListener('click', function () {
            var lat = document.getElementById('selLat').innerText;
            var lng = document.getElementById('selLng').innerText;

            fetch(`/api/gis/nearby?latitude=${lat}&longitude=${lng}`)
                .then(res => res.json())
                .then(res => {
                    if (res.status === 'success') {
                        var list = document.getElementById('nearbyList');
                        list.innerHTML = '';
                        res.data.forEach(item => {
                            var div = document.createElement('div');
                            div.className = 'p-1.5 bg-slate-100 dark:bg-slate-800 rounded font-mono text-[10px]';
                            div.innerHTML = `<strong>${item.name}</strong> (${item.type}) — ${item.distance_meters}m`;
                            list.appendChild(div);
                        });
                        document.getElementById('nearbyResults').classList.remove('hidden');
                    }
                });
        });
    });
</script>
@endsection
