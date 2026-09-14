<?php
// Buat API key di Google Cloud Console, lalu aktifkan Maps JavaScript API dan Places API.
// Sebaiknya batasi key ini berdasarkan HTTP referrer/domain aplikasi.
$google_maps_api_key = getenv('GOOGLE_MAPS_API_KEY') ?: 'AIzaSyC6GigN2fiV6rhoYodO-5cOz7a_Y46jnWA';
