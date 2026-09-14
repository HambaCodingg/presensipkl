<?php
require_once __DIR__ . '/../../config/google_maps.php';
$pkl_latitude = isset($pkl_latitude) ? $pkl_latitude : '';
$pkl_longitude = isset($pkl_longitude) ? $pkl_longitude : '';
$pkl_radius_meter = isset($pkl_radius_meter) ? $pkl_radius_meter : 100;
?>
<div class="row">
    <div class="col-sm-12">
        <div class="form-group">
            <label>Lokasi Presensi PKL</label>
            <?php if ($google_maps_api_key === ''): ?>
                <div class="alert alert-warning">Google Maps belum dikonfigurasi. Isi variabel lingkungan <code>GOOGLE_MAPS_API_KEY</code> terlebih dahulu.</div>
            <?php endif; ?>
            <input type="text" id="pkl-map-search" class="form-control" placeholder="Cari nama atau alamat perusahaan di Google Maps" autocomplete="off">
            <small class="text-muted">Cari lokasi, lalu klik titik pada peta atau geser penanda untuk menentukan titik presensi.</small>
            <div id="pkl-location-map" style="height:330px;margin-top:10px;border:1px solid #ddd;border-radius:4px;"></div>
        </div>
    </div>
    <div class="col-sm-4"><div class="form-group"><label>Latitude</label><input type="number" step="any" class="form-control" name="pkl_latitude" id="pkl_latitude" value="<?php echo htmlspecialchars((string) $pkl_latitude, ENT_QUOTES, 'UTF-8'); ?>" required></div></div>
    <div class="col-sm-4"><div class="form-group"><label>Longitude</label><input type="number" step="any" class="form-control" name="pkl_longitude" id="pkl_longitude" value="<?php echo htmlspecialchars((string) $pkl_longitude, ENT_QUOTES, 'UTF-8'); ?>" required></div></div>
    <div class="col-sm-4"><div class="form-group"><label>Radius diizinkan (meter)</label><input type="number" min="25" max="5000" class="form-control" name="pkl_radius_meter" value="<?php echo (int) $pkl_radius_meter; ?>" required><small class="text-muted">Contoh: 100 meter.</small></div></div>
</div>
<?php if ($google_maps_api_key !== ''): ?>
<script>
function initPklLocationMap() {
    var latInput = document.getElementById('pkl_latitude');
    var lngInput = document.getElementById('pkl_longitude');
    var initial = {lat: parseFloat(latInput.value) || -6.597146, lng: parseFloat(lngInput.value) || 106.806039};
    var map = new google.maps.Map(document.getElementById('pkl-location-map'), {zoom: (latInput.value && lngInput.value) ? 16 : 11, center: initial});
    var marker = new google.maps.Marker({position: initial, map: map, draggable: true});
    function setLocation(location) {
        latInput.value = location.lat().toFixed(7);
        lngInput.value = location.lng().toFixed(7);
        marker.setPosition(location);
        map.panTo(location);
    }
    marker.addListener('dragend', function() { setLocation(marker.getPosition()); });
    map.addListener('click', function(event) { setLocation(event.latLng); });
    var autocomplete = new google.maps.places.Autocomplete(document.getElementById('pkl-map-search'));
    autocomplete.bindTo('bounds', map);
    autocomplete.addListener('place_changed', function() {
        var place = autocomplete.getPlace();
        if (!place.geometry) return;
        if (place.geometry.viewport) map.fitBounds(place.geometry.viewport); else { map.setCenter(place.geometry.location); map.setZoom(17); }
        setLocation(place.geometry.location);
    });
}
</script>
<script async defer src="https://maps.googleapis.com/maps/api/js?key=<?php echo rawurlencode($google_maps_api_key); ?>&libraries=places&callback=initPklLocationMap"></script>
<?php endif; ?>
