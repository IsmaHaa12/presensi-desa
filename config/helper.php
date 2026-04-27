<?php
// Fungsi menghitung jarak GPS dalam meter (Haversine)
function hitungJarak($lat1, $lon1, $lat2, $lon2)
{
    $earth_radius = 6371000;
    $dLat = deg2rad($lat2 - $lat1);
    $dLon = deg2rad($lon2 - $lon1);

    $a = sin($dLat / 2) * sin($dLat / 2) + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($dLon / 2) * sin($dLon / 2);
    $c = 2 * asin(sqrt($a));

    return round($earth_radius * $c);
}
