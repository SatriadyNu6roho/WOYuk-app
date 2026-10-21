/**
 * WOYuk! Tracking & Celebration Engine
 * Engine utama untuk tracking GPS realtime dan efek animasi selebrasi target pencapaian harian.
 */

class WoyukCelebrationEngine {
    constructor(canvasId = 'glitter-canvas') {
        this.canvas = document.getElementById(canvasId);
        this.ctx = this.canvas ? this.canvas.getContext('2d') : null;
        this.animationFrame = null;
        this.particles = [];
        this.colors = ['#f59e0b', '#fbbf24', '#f97316', '#38bdf8', '#10b981', '#ffffff'];
    }

    initCanvas() {
        if (!this.canvas) return;
        this.canvas.width = window.innerWidth;
        this.canvas.height = window.innerHeight;
    }

    /**
     * Jalankan animasi selebrasi glitter sporty
     * @param {string} type - 'distance', 'calories', atau 'both'
     */
    fire(type = 'both') {
        if (!this.canvas) {
            this.canvas = document.getElementById('glitter-canvas');
            if (this.canvas) this.ctx = this.canvas.getContext('2d');
            else return;
        }

        this.initCanvas();
        if (this.animationFrame) cancelAnimationFrame(this.animationFrame);

        const count = type === 'both' ? 160 : 100;
        this.particles = [];

        for (let i = 0; i < count; i++) {
            this.particles.push({
                x: Math.random() * this.canvas.width,
                y: Math.random() * (this.canvas.height * 0.4),
                size: Math.random() * 7 + 2,
                color: this.colors[Math.floor(Math.random() * this.colors.length)],
                speedX: (Math.random() - 0.5) * 8,
                speedY: Math.random() * 5 + 2,
                rotation: Math.random() * 360,
                spin: (Math.random() - 0.5) * 12,
                opacity: 1
            });
        }

        this.animate();
    }

    animate() {
        if (!this.ctx) return;

        this.ctx.clearRect(0, 0, this.canvas.width, this.canvas.height);
        let activeCount = 0;

        this.particles.forEach(p => {
            if (p.opacity > 0) {
                activeCount++;
                p.x += p.speedX;
                p.y += p.speedY;
                p.rotation += p.spin;
                p.opacity -= 0.007;

                this.ctx.save();
                this.ctx.translate(p.x, p.y);
                this.ctx.rotate((p.rotation * Math.PI) / 180);
                this.ctx.globalAlpha = Math.max(0, p.opacity);
                this.ctx.fillStyle = p.color;
                
                // Variasi bentuk partikel glitter (persegi panjang / bintang sederhana)
                this.ctx.fillRect(-p.size / 2, -p.size / 2, p.size, p.size * 1.6);
                this.ctx.restore();
            }
        });

        if (activeCount > 0) {
            this.animationFrame = requestAnimationFrame(() => this.animate());
        } else {
            this.ctx.clearRect(0, 0, this.canvas.width, this.canvas.height);
            cancelAnimationFrame(this.animationFrame);
        }
    }
}

class WoyukTrackingEngine {
    constructor(config) {
        this.activityId = config.activityId;
        this.sportType = config.sportType;
        this.startTime = typeof config.startTime === 'number' ? new Date(config.startTime) : new Date(config.startTime);
        this.csrfToken = config.csrfToken;
        this.syncUrl = config.syncUrl || `/api/activity/${this.activityId}/sync`;

        // UI Elements
        this.timerElement = document.getElementById('live-timer');
        this.distanceElement = document.getElementById('live-distance');
        this.metricElement = document.getElementById('live-metric');
        this.caloriesElement = document.getElementById('live-calories');
        this.gpsStatusElement = document.getElementById('gps-status');

        // State Tracking
        this.timerInterval = null;
        this.syncInterval = null;
        this.watchId = null;
        this.lastPosition = null;
        this.totalDistanceMeters = 0.0;
        this.durationSeconds = 0;
        this.isSyncing = false;
        
        // Queue data untuk dikirim ke server (Batching)
        this.unsyncedPoints = [];

        // LEAFLET MAP STATE
        this.map = null;
        this.userMarker = null;
        this.routePolyline = null;
        this.pathCoordinates = [];

        this.init();
    }

    init() {
        this.initMap();
        this.startTimer();
        this.startGpsTracking();
        this.startSyncLoop();
    }

    /**
     * Inisialisasi Peta Leaflet (Dark Mode Tile Layer)
     */
    initMap() {
        const mapContainer = document.getElementById('map');
        if (!mapContainer || typeof L === 'undefined') return;

        this.map = L.map('map', {
            zoomControl: false,
            attributionControl: false
        }).setView([-6.200000, 106.816666], 15);

        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            maxZoom: 19,
            subdomains: ['a', 'b', 'c']
        }).addTo(this.map);

        this.routePolyline = L.polyline([], {
            color: '#f97316',
            weight: 5,
            opacity: 0.9,
            lineCap: 'round',
            lineJoin: 'round'
        }).addTo(this.map);
    }

    startTimer() {
        this.timerInterval = setInterval(() => {
            const now = new Date();
            this.durationSeconds = Math.floor((now - this.startTime) / 1000);

            const hours = String(Math.floor(this.durationSeconds / 3600)).padStart(2, '0');
            const minutes = String(Math.floor((this.durationSeconds % 3600) / 60)).padStart(2, '0');
            const seconds = String(this.durationSeconds % 60).padStart(2, '0');

            if (this.timerElement) {
                this.timerElement.innerText = `${hours}:${minutes}:${seconds}`;
            }

            this.updateLiveMetrics();
        }, 1000);
    }

    startGpsTracking() {
        if (!("geolocation" in navigator)) {
            if (this.gpsStatusElement) {
                this.gpsStatusElement.innerText = "GPS NOT SUPPORTED";
                this.gpsStatusElement.className = "text-xs font-bold text-red-400 uppercase font-mono";
            }
            return;
        }

        const options = {
            enableHighAccuracy: true,
            timeout: 10000,
            maximumAge: 0
        };

        this.watchId = navigator.geolocation.watchPosition(
            (pos) => this.handleGpsSuccess(pos),
            (err) => this.handleGpsError(err),
            options
        );
    }

    handleGpsSuccess(position) {
        const coords = position.coords;
        const lat = coords.latitude;
        const lng = coords.longitude;

        if (this.gpsStatusElement) {
            const acc = Math.round(coords.accuracy);
            if (acc <= 15) {
                this.gpsStatusElement.innerText = `GPS AKURAT (${acc}M)`;
                this.gpsStatusElement.className = "text-xs font-bold text-emerald-400 uppercase font-mono";
            } else if (acc <= 35) {
                this.gpsStatusElement.innerText = `GPS SEDANG (${acc}M)`;
                this.gpsStatusElement.className = "text-xs font-bold text-amber-400 uppercase font-mono";
            } else {
                this.gpsStatusElement.innerText = `GPS LEMAH (${acc}M)`;
                this.gpsStatusElement.className = "text-xs font-bold text-red-400 uppercase font-mono";
            }
        }

        if (coords.accuracy > 40.0) return;

        const currentPos = {
            latitude: lat,
            longitude: lng,
            altitude: coords.altitude || 0,
            speed: coords.speed || 0,
            accuracy: coords.accuracy,
            timestamp: new Date().toISOString()
        };

        this.updateMapPosition(lat, lng);

        if (this.lastPosition) {
            const deltaDist = this.calculateHaversineDistance(
                this.lastPosition.latitude, this.lastPosition.longitude,
                lat, lng
            );

            const minMoveThreshold = coords.accuracy <= 15 ? 1.2 : 2.0;

            if (deltaDist >= minMoveThreshold) {
                this.totalDistanceMeters += deltaDist;
                this.lastPosition = currentPos;
                this.unsyncedPoints.push(currentPos);

                this.pathCoordinates.push([lat, lng]);
                if (this.routePolyline) {
                    this.routePolyline.setLatLngs(this.pathCoordinates);
                }
            }
        } else {
            this.lastPosition = currentPos;
            this.unsyncedPoints.push(currentPos);
            
            this.pathCoordinates.push([lat, lng]);
            if (this.routePolyline) {
                this.routePolyline.setLatLngs(this.pathCoordinates);
            }
        }

        this.updateLiveMetrics();
    }

    updateMapPosition(lat, lng) {
        if (!this.map) return;

        const currentLatLng = [lat, lng];

        if (!this.userMarker) {
            const customIcon = L.divIcon({
                className: 'user-location-marker',
                iconSize: [16, 16],
                iconAnchor: [8, 8]
            });

            this.userMarker = L.marker(currentLatLng, { icon: customIcon }).addTo(this.map);
            this.map.setView(currentLatLng, 17);
        } else {
            this.userMarker.setLatLng(currentLatLng);
            this.map.panTo(currentLatLng);
        }
    }

    calculateHaversineDistance(lat1, lon1, lat2, lon2) {
        const R = 6371000;
        const dLat = (lat2 - lat1) * Math.PI / 180;
        const dLon = (lon2 - lon1) * Math.PI / 180;
        
        const a = Math.sin(dLat / 2) * Math.sin(dLat / 2) +
                  Math.cos(lat1 * Math.PI / 180) * Math.cos(lat2 * Math.PI / 180) *
                  Math.sin(dLon / 2) * Math.sin(dLon / 2);
                  
        const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a));
        return R * c;
    }

    updateLiveMetrics() {
        const kmDisplay = (this.totalDistanceMeters / 1000).toFixed(2).replace('.', ',');
        if (this.distanceElement) {
            this.distanceElement.innerText = kmDisplay;
        }

        if (this.sportType === 'cycling') {
            const speedKmh = this.durationSeconds > 0 ? ((this.totalDistanceMeters / 1000) / (this.durationSeconds / 3600)).toFixed(1) : '0.0';
            if (this.metricElement) this.metricElement.innerText = speedKmh;
        } else {
            if (this.totalDistanceMeters > 0 && this.durationSeconds > 0) {
                const paceSecPerKm = this.durationSeconds / (this.totalDistanceMeters / 1000);
                const pMin = String(Math.floor(paceSecPerKm / 60)).padStart(2, '0');
                const pSec = String(Math.floor(paceSecPerKm % 60)).padStart(2, '0');
                if (this.metricElement) this.metricElement.innerText = `${pMin}:${pSec}`;
            } else {
                if (this.metricElement) this.metricElement.innerText = "00:00";
            }
        }
    }

    startSyncLoop() {
        this.syncInterval = setInterval(() => {
            this.flushUnsyncedPoints();
        }, 6000);
    }

    async flushUnsyncedPoints() {
        if (this.isSyncing) return;
        
        const pointsToSend = [...this.unsyncedPoints];
        this.unsyncedPoints = [];
        this.isSyncing = true;

        const payload = {
            points: pointsToSend,
            total_distance_meters: this.totalDistanceMeters,
            duration_seconds: this.durationSeconds
        };

        try {
            const response = await fetch(this.syncUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': this.csrfToken
                },
                body: JSON.stringify(payload)
            });

            if (response.ok) {
                const data = await response.json();
                if (data.status === 'success' && data.calories_burned !== undefined) {
                    if (this.caloriesElement) {
                        this.caloriesElement.innerText = `${data.calories_burned} KCAL`;
                    }
                }
            } else {
                this.unsyncedPoints = [...pointsToSend, ...this.unsyncedPoints];
            }
        } catch (err) {
            this.unsyncedPoints = [...pointsToSend, ...this.unsyncedPoints];
            console.error("Tracking Sync Error:", err);
        } finally {
            this.isSyncing = false;
        }
    }

    stopTracking() {
        if (this.timerInterval) clearInterval(this.timerInterval);
        if (this.syncInterval) clearInterval(this.syncInterval);
        if (this.watchId !== null && "geolocation" in navigator) {
            navigator.geolocation.clearWatch(this.watchId);
        }
    }

    handleGpsError(error) {
        if (this.gpsStatusElement) {
            this.gpsStatusElement.innerText = "GPS OFF / DISCONNECTED";
            this.gpsStatusElement.className = "text-xs font-bold text-red-400 uppercase font-mono";
        }
    }
}

// Inisialisasi Engine Selebrasi Global
window.WoyukCelebration = new WoyukCelebrationEngine();