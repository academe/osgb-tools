<?php

namespace Academe\OsgbTools;

// Alias the Square in case we want to override it.
use Academe\OsgbTools\Square as OsgbSquare;

class Convert
{
    // The true origin, in degrees.
    // National Grid true origin is 49°N,2°W

    const TRUE_ORIGIN_LATITUDE = 49;
    const TRUE_ORIGIN_LONGITUDE = -2;

    // Northing and Easting of true origin, metres.

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

    // The following from http://www.movable-type.co.uk/scripts/latlong-gridref.html
    // and ported from JavaScript.

    /**
     * Calculate the meridian arc.
     */

    public static function meridianArc($n, $phi0, $phi, $F0, $b)
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
     * Convert (OSGB36/Airy) latitude/longitude to Ordnance Survey grid reference easting/northing coordinate.
     * The OSGB Lat/Long uses the Airy 1830 ellipsoid using the OSGB36 datum. The lat/long coordinate
     * must be converted to Airy before converting to an OS grid reference.
     * This not WGS84, as used by GPS globally.
     *
     * @param float latitude OSGB36 latitude
     * @param float longitude OSGB36 longitude
     * @return array OS Grid Reference easting/northing, pair of integers, from sqaure NV
     */

    public static function latLongToOsGrid($latitude, $longitude)
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

        $M = static::meridianArc($n, $phi0, $phi, $F0, $b);

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

        return array((int)round($E), (int)round($N));
    }


    /**
     * Convert Ordnance Survey grid reference easting/northing coordinate to (OSGB36) latitude/longitude
     *
     * @param {OsGridRef} easting/northing to be converted to latitude/longitude
     * @return {LatLon} latitude/longitude (in OSGB36) of supplied grid reference
     *
     * @todo The return data needs to make it clear that the lat/long is in the OSGB datum.
     * Accept input as separate easting/northing, easting/northing array, or a SquareInterface class.
     */

    public static function osGridToLatLong($easting_or_square, $northing = null)
    {
        // Check what has been passed in.
        if ( ! isset($northing) && is_array($easting_or_square)) {
            // A single array.
            list($easting, $northing) = $easting_or_square;
        } elseif($easting_or_square instanceof SquareInterfac) {
            // Square class passed in.
            // TODO: how do we set "centre_of_square"?
            list($easting, $northing) = $easting_or_square->getEastingNorthing();
        } elseif ( ! isset($northing) && is_string($easting_or_square)) {
            // NGR string.
            $square = new OsgbSquare($easting_or_square);
            list($easting, $northing) = $square->getEastingNorthing();
        } elseif (is_numeric($easting_or_square) && is_numeric($northing)) {
            // A pair of numeric easting/northing values.
            $easting = $easting_or_square;
        } else {
            // Not recognised format.
            throw new \InvalidArgumentException(
                sprintf('Unexpected values passed in; need easting+northing, array(easting,northing), Square or NGR string; got %s and %s', gettype($easting_or_square), gettype($northing))
            );
        }

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

            $M = static::meridianArc($n, $phi0, $phi, $F0, $b);

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

        // TODO: we want to return a simple object here for storing Lat/Long, then we can make
        // better use of interfaces.
        return array(rad2deg($phi), rad2deg($lambda));
    }
}

