<?php

namespace Academe\OsgbTools;

class Convert
{
    // The true origin, in degrees.
    // National Grid true origin is 49°N,2°W

    const TRUE_ORIGIN_LATITUDE = 49;
    const TRUE_ORIGIN_LONGITUDE = -2;

    // northing & easting of true origin, metres

    const TRUE_ORIGIN_EASTING = 400000;
    const TRUE_ORIGIN_NORTHING = -100000;

    // National Grid scale factor on central meridian

    const NAT_GRID_SCALE_MERIDIAN = 0.9996012717;

    // Airy 1830 major & minor semi-axes

    const AIRY_1830_MAJOR_SEMI_AXES = 6377563.396;
    const AIRY_1830_MINOR_SEMI_AXES = 6356256.909;

    // Accuracy for OSGB to Lat/Long conversion.
    // Value is 0.01mm

    const CONV_ACCURACY = 0.00001;

    // Converts OS Easting/Northing to Lat/Long
    // by bramp
    // Originally published at:
    // http://bramp.net/blog/2008/06/04/os-easting-northing-to-lat-long/
    //
    // I think this is the code we want to use, ported from JS:
    // http://www.movable-type.co.uk/scripts/latlong-gridref.html

    /*
     * Compute meridional arc.
     * Input: - 
     *  ellipsoid semi major axis multiplied by central meridian scale factor (bf0) in metres; 
     *  n (computed from a, b and f0); 
     *  lat of false origin (PHI0) 
     *  initial or final latitude of point (PHI) IN RADIANS.
     *
     * This method is now deprecated.
     */

    public function marc($bf0, $n, $PHI0, $PHI) {
        $n2 = pow($n, 2);
        $n3 = pow($n, 3);

        $ans  = ((1 + $n + ((5 / 4) * ($n2)) + ((5 / 4) * $n3)) * ($PHI - $PHI0));
        $ans -= (((3 * $n) + (3 * $n2) + ((21 / 8 ) * $n3)) * (sin($PHI - $PHI0)) * (cos($PHI + $PHI0)));
        $ans += ((((15 / 8 ) * $n2) + ((15 / 8 ) * $n3)) * (sin(2 * ($PHI - $PHI0))) * (cos(2 * ($PHI + $PHI0))));
        $ans -= (((35 / 24) * $n3) * (sin(3 * ($PHI - $PHI0))) * (cos(3 * ($PHI + $PHI0))));

        return $bf0 * $ans;
    }

    /*
     * Compute initial value for Latitude (PHI) IN RADIANS.
     * Input: - _
     * northing of point (North) and northing of false origin (n0) in meters; 
     * semi major axis multiplied by central meridian scale factor (af0) in meters; 
     * latitude of false origin (PHI0) IN RADIANS;
     * n (computed from a, b and f0) 
     * ellipsoid semi major axis multiplied by central meridian scale factor (bf0) in meters.
     *
     * This method is now deprecated.
     */

    public function initialLat($North, $n0, $afo, $PHI0, $n, $bfo) {
        // First PHI value (PHI1)
        $PHI1 = (($North - $n0) / $afo) + $PHI0;

        // Calculate M
        $M = $this->marc($bfo, $n, $PHI0, $PHI1);

        // Calculate new PHI value (PHI2)
        $PHI2 = (($North - $n0 - $M) / $afo) + $PHI1;

        // Iterate to get final value for InitialLat
        while ( abs($North - $n0 - $M) > 0.00001 ) {
            $PHI2 = (($North - $n0 - $M) / $afo) + $PHI1;
            $M = $this->marc($bfo, $n, $PHI0, $PHI2);
            $PHI1 = $PHI2;
        }

        return $PHI2;
    }

    /**
     * e.g.
     * $e = 349000;
     * $n = 461000;
     * 
     * print_r( $convert->E_N_to_Lat_Long($e, $n) );
     *
     * This method is now deprecated.
     */

    public function E_N_to_Lat_Long($East, $North) {
        $a  = 6377563.396; // Semi-major axis, a
        $b  = 6356256.910; //Semi-minor axis, b
        $e0 = 400000.000; //True origin Easting, E0	
        $n0 = -100000.000; //True origin Northing, N0	
        $f0 = 0.999601271700; //Central Meridan Scale, F0

        $PHI0 = 49.0; // True origin latitude, j0
        $LAM0 = -2.0; // True origin longitude, l0

        // Convert angle measures to radians
        $RadPHI0 = $PHI0 * (M_PI / 180);
        $RadLAM0 = $LAM0 * (M_PI / 180);

        // Compute af0, bf0, e squared (e2), n and Et
        $af0 = $a * $f0;
        $bf0 = $b * $f0;
        $e2 = ($af0*$af0 - $bf0*$bf0 ) / ($af0*$af0);
        $n = ($af0 - $bf0) / ($af0 + $bf0);
        $Et = $East - $e0;

        // Compute initial value for latitude (PHI) in radians
        $PHId = $this->initialLat($North, $n0, $af0, $RadPHI0, $n, $bf0);

        $sin_phid2 = pow(sin($PHId),  2);
        $cos_phid  = pow(cos($PHId), -1);

        $tan_phid  = tan($PHId);
        $tan_phid2 = pow($tan_phid, 2);
        $tan_phid4 = pow($tan_phid, 4);
        $tan_phid6 = pow($tan_phid, 6);

        // Compute nu, rho and eta2 using value for PHId
        $nu = $af0 / (sqrt(1 - ($e2 * $sin_phid2)));
        $rho = ($nu * (1 - $e2)) / (1 - $e2 * $sin_phid2);
        $eta2 = ($nu / $rho) - 1;

        // Compute Longitude
        $X    = $cos_phid / $nu;
        $XI   = $cos_phid / (   6 * pow($nu, 3)) * (($nu / $rho)         +  2 * $tan_phid2);
        $XII  = $cos_phid / ( 120 * pow($nu, 5)) * (5  + 28 * $tan_phid2  + 24 * $tan_phid4);
        $XIIA = $cos_phid / (5040 * pow($nu, 7)) * (61 + 662 * $tan_phid2 + 1320 * $tan_phid4 + 720 * $tan_phid6);

        $VII  = $tan_phid / (  2 * $rho * $nu);
        $VIII = $tan_phid / ( 24 * $rho * pow($nu, 3)) * ( 5 +  3 * $tan_phid2 + $eta2 - 9 * $eta2 * $tan_phid2 );
        $IX   = $tan_phid / (720 * $rho * pow($nu, 5)) * (61 + 90 * $tan_phid2 + 45 * $tan_phid4 );

        $long = (180 / M_PI) * ($RadLAM0 + ($Et * $X) - pow($Et,3) * $XI + pow($Et,5) * $XII - pow($Et,7) * $XIIA);
        $lat  = (180 / M_PI) * ($PHId - (pow($Et,2) * $VII) + (pow($Et, 4) * $VIII) - (pow($Et, 6) * $IX));

        return array($lat, $long);
    }

    // The following from http://www.movable-type.co.uk/scripts/latlong-gridref.html and
    // ported from JavaScript.

    /**
     * Calculate the meridian arc.
     */

    public function meridianArc($n, $phi0, $phi, $F0, $b)
    {
        $n2 = pow($n, 2);
        $n3 = pow($n, 3);

        $Ma = (1 + $n + (5/4) * $n2 + (5/4) * $n3) * ($phi - $phi0);
        $Mb = (3 * $n + 3 * $n2 + (21/8) * $n3) * sin($phi - $phi0) * cos($phi + $phi0);
        $Mc = ((15/8) * $n2 + (15/8) * $n3) * sin(2 * ($phi - $phi0)) * cos(2 * ($phi + $phi0));
        $Md = (35/24) * $n3 * sin(3 * ($phi - $phi0)) * cos(3 * ($phi + $phi0));

        return $b * $F0 * ($Ma - $Mb + $Mc - $Md);
    }

    /**
     * Convert (OSGB36) latitude/longitude to Ordnance Survey grid reference easting/northing coordinate
     *
     * @param {LatLon} point: OSGB36 latitude/longitude
     * @return {OsGridRef} OS Grid Reference easting/northing
     *
     * Note: it is unclear what this does. OSGB36 is not a lat/long grid reference, so I'm not sure what it
     * is claiming to convert. It does seem to correctly reverse the result of osGridToLatLong().
     * Maybe the "OSGB36 lat/long" actually is refering to the ellipsoide being Airy rather than a more
     * modern ellipsoid?
     */

    public function latLongToOsGrid($latitude, $longitude)
    {
        // Latitude and longitude are angles in degrees.
        // Convert them to radians.

        $phi = deg2rad($latitude);
        $lambda = deg2rad($longitude);

        // Airy 1830 major & minor semi-axes

        $a = static::AIRY_1830_MAJOR_SEMI_AXES;
        $b = static::AIRY_1830_MINOR_SEMI_AXES;

        // National Grid scale factor on central meridian

        $F0 = static::NAT_GRID_SCALE_MERIDIAN;

        // National Grid true origin is 49°N,2°W

        $phi0 = deg2rad(static::TRUE_ORIGIN_LATITUDE);
        $lambda0 = deg2rad(static::TRUE_ORIGIN_LONGITUDE);

        // Easting and northing of true origin, metres.

        $E0 = static::TRUE_ORIGIN_EASTING;
        $N0 = static::TRUE_ORIGIN_NORTHING;

        // eccentricity squared
        $e2 = 1 - ($b * $b) / ($a * $a);

        // n, n², n³
        $n = ($a - $b) / ($a + $b);
        $n2 = pow($n, 2);
        $n3 = pow($n, 3);

        $cos_phi = cos($phi);
        $sin_phi = sin($phi);

        // nu is the transverse radius of curvature.

        $nu = $a * $F0 / sqrt(1 - $e2 * pow($sin_phi, 2));

        // rho is the meridional radius of curvature.

        $rho = $a * $F0 * (1 - $e2) / pow(1 - $e2 * pow($sin_phi, 2), 1.5);

        // eta = ?

        $eta2 = $nu / $rho - 1;

        // The meridional arc.

        $M = $this->meridianArc($n, $phi0, $phi, $F0, $b);

        $cos_phi3 = pow($cos_phi, 3);
        $cos_phi5 = pow($cos_phi, 5);

        $tan_phi = tan($phi);
        $tan_phi2 = pow($tan_phi, 2);
        $tan_phi4 = pow($tan_phi, 4);

        $I      = $M + $N0;
        $II     = ($nu/2)   * $sin_phi * $cos_phi;
        $III    = ($nu/24)  * $sin_phi * $cos_phi3 * (5 - $tan_phi2 + 9 * $eta2);
        $IIIA   = ($nu/720) * $sin_phi * $cos_phi5 * (61 - 58 * $tan_phi2 + $tan_phi4);
        $IV     = $nu       * $cos_phi;
        $V      = ($nu/6)   * $cos_phi3 * ($nu / $rho - $tan_phi2);
        $VI     = ($nu/120) * $cos_phi5 * (5 - 18 * $tan_phi2 + $tan_phi4 + 14 * $eta2 - 58 * $tan_phi2 * $eta2);

        $delta_lambda = $lambda - $lambda0;

        $N = $I
            + $II * pow($delta_lambda, 2)
            + $III * pow($delta_lambda, 4)
            + $IIIA * pow($delta_lambda, 6);

        $E = $E0
            + $IV * $delta_lambda
            + $V * pow($delta_lambda, 3)
            + $VI * pow($delta_lambda, 5);

        return array(round($E), round($N));
    }


    /**
     * Convert Ordnance Survey grid reference easting/northing coordinate to (OSGB36) latitude/longitude
     *
     * @param {OsGridRef} easting/northing to be converted to latitude/longitude
     * @return {LatLon} latitude/longitude (in OSGB36) of supplied grid reference
     */

    public function osGridToLatLong($easting, $northing)
    {
        // Airy 1830 major & minor semi-axes

        $a = static::AIRY_1830_MAJOR_SEMI_AXES;
        $b = static::AIRY_1830_MINOR_SEMI_AXES;

        // National Grid scale factor on central meridian.

        $F0 = static::NAT_GRID_SCALE_MERIDIAN;

        // National Grid true origin.

        $phi0 = deg2rad(static::TRUE_ORIGIN_LATITUDE);
        $lambda0 = deg2rad(static::TRUE_ORIGIN_LONGITUDE);

        // Easting and northing of true origin, metres

        $E0 = static::TRUE_ORIGIN_EASTING;
        $N0 = static::TRUE_ORIGIN_NORTHING;

        // Eccentricity squared

        $e2 = 1 - ($b * $b) / ($a * $a);

        // n, n², n³

        $n = ($a - $b) / ($a + $b);
        $n2 = pow($n, 2);
        $n3 = pow($n, 3);

        $phi = $phi0;
        $M = 0;

        do {
            $phi = ($northing - $N0 - $M) / ($a * $F0) + $phi;

            // The meridional arc.

            $M = $this->meridianArc($n, $phi0, $phi, $F0, $b);

            // loop until < 0.01mm
        } while ($northing - $N0 - $M >= static::CONV_ACCURACY);

        $cos_phi = cos($phi);
        $sin_phi = sin($phi);

        // nu is the transverse radius of curvature.

        $nu = $a * $F0 / sqrt(1 - $e2 * pow($sin_phi, 2));

        // rho is the meridional radius of curvature.

        $rho = $a * $F0 * (1 - $e2) / pow(1 - $e2 * pow($sin_phi, 2), 1.5);

        // eta = ?

        $eta2 = $nu / $rho - 1;

        $tan_phi = tan($phi);
        $tan_phi2 = pow($tan_phi, 2);
        $tan_phi4 = pow($tan_phi, 4);
        $tan_phi6 = pow($tan_phi, 6);

        $sec_phi = 1 / $cos_phi;

        $nu3 = pow($nu, 3);
        $nu5 = pow($nu, 5);
        $nu7 = pow($nu, 7);

        $VII =  $tan_phi / (2 * $rho * $nu);
        $VIII = $tan_phi / (24 * $rho * $nu3) * (5 + 3 * $tan_phi2 + $eta2 - 9 * $tan_phi2 * $eta2);
        $IX =   $tan_phi / (720 * $rho * $nu5) * (61 + 90 * $tan_phi2 + 45 * $tan_phi4);
        $X =    $sec_phi / $nu;
        $XI =   $sec_phi / (6 * $nu3) * ($nu / $rho + 2 * $tan_phi2);
        $XII =  $sec_phi / (120 * $nu5) * (5 + 28 * $tan_phi2 + 24 * $tan_phi4);
        $XIIA = $sec_phi / (5040 * $nu7) * (61 + 662 * $tan_phi2 + 1320 * $tan_phi4 + 720 * $tan_phi6);

        $dE = ($easting - $E0);

        $phi = $phi
            - $VII * pow($dE, 2)
            + $VIII * pow($dE, 4)
            - $IX * pow($dE, 6);

        $lambda = $lambda0
            + $X * $dE
            - $XI * pow($dE, 3)
            + $XII * pow($dE, 5)
            - $XIIA * pow($dE, 7);

        return array(rad2deg($phi), rad2deg($lambda));
    }
}

