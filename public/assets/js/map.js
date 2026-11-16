/**
 * Inicialización segura de mapa interactivo con Leaflet.js + OpenStreetMap
 * Cumple con Content-Security-Policy (script-src 'self') sin JS inline.
 */
document.addEventListener('DOMContentLoaded', function () {
    const mapElement = document.getElementById('mapa-veterinaria');
    if (!mapElement || typeof L === 'undefined') {
        return;
    }

    const lat = parseFloat(mapElement.getAttribute('data-lat') || '21.9235');
    const lng = parseFloat(mapElement.getAttribute('data-lng') || '-102.2642');
    const nombre = mapElement.getAttribute('data-title') || 'Veterinaria Madri';
    const direccion = mapElement.getAttribute('data-address') || '';

    const map = L.map('mapa-veterinaria', {
        scrollWheelZoom: false
    }).setView([lat, lng], 16);

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        maxZoom: 19,
        attribution: '&copy; <a href="https://www.openstreetmap.org/copyright" target="_blank" rel="noopener noreferrer">OpenStreetMap</a> contributors'
    }).addTo(map);

    // Marcador SVG en color institucional (#B91C1C) sin depender de PNGs externos
    const pinIcon = L.divIcon({
        className: 'custom-map-pin',
        html: '<svg xmlns="http://www.w3.org/2000/svg" width="36" height="36" viewBox="0 0 24 24" fill="#B91C1C" stroke="#FFFFFF" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path><circle cx="12" cy="10" r="3" fill="#FFFFFF"></circle></svg>',
        iconSize: [36, 36],
        iconAnchor: [18, 36],
        popupAnchor: [0, -34]
    });

    const marker = L.marker([lat, lng], { icon: pinIcon }).addTo(map);
    marker.bindPopup('<strong>' + nombre + '</strong><br>' + direccion).openPopup();
});