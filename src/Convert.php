<?php

namespace Academe\OsgbTools;

class Convert
{
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

        $sinPHId2 = pow(sin($PHId),  2);
        $cosPHId  = pow(cos($PHId), -1);

        $tanPHId  = tan($PHId);
        $tanPHId2 = pow($tanPHId, 2);
        $tanPHId4 = pow($tanPHId, 4);
        $tanPHId6 = pow($tanPHId, 6);

        // Compute nu, rho and eta2 using value for PHId
        $nu = $af0 / (sqrt(1 - ($e2 * $sinPHId2)));
        $rho = ($nu * (1 - $e2)) / (1 - $e2 * $sinPHId2);
        $eta2 = ($nu / $rho) - 1;

        // Compute Longitude
        $X    = $cosPHId / $nu;
        $XI   = $cosPHId / (   6 * pow($nu, 3)) * (($nu / $rho)         +  2 * $tanPHId2);
        $XII  = $cosPHId / ( 120 * pow($nu, 5)) * (5  + 28 * $tanPHId2  + 24 * $tanPHId4);
        $XIIA = $cosPHId / (5040 * pow($nu, 7)) * (61 + 662 * $tanPHId2 + 1320 * $tanPHId4 + 720 * $tanPHId6);

        $VII  = $tanPHId / (  2 * $rho * $nu);
        $VIII = $tanPHId / ( 24 * $rho * pow($nu, 3)) * ( 5 +  3 * $tanPHId2 + $eta2 - 9 * $eta2 * $tanPHId2 );
        $IX   = $tanPHId / (720 * $rho * pow($nu, 5)) * (61 + 90 * $tanPHId2 + 45 * $tanPHId4 );

        $long = (180 / M_PI) * ($RadLAM0 + ($Et * $X) - pow($Et,3) * $XI + pow($Et,5) * $XII - pow($Et,7) * $XIIA);
        $lat  = (180 / M_PI) * ($PHId - (pow($Et,2) * $VII) + (pow($Et, 4) * $VIII) - (pow($Et, 6) * $IX));

        return array($lat, $long);
    }

    // The following from http://www.movable-type.co.uk/scripts/latlong-gridref.html and
    // ported from JavaScript.

    /**
     * Convert (OSGB36) latitude/longitude to Ordnance Survey grid reference easting/northing coordinate
     *
     * @param {LatLon} point: OSGB36 latitude/longitude
     * @return {OsGridRef} OS Grid Reference easting/northing
     */

    public function latLongToOsGrid ($lat, $lon)
    {
        /*
        if (point.datum != CoordTransform.datum.OSGB36) throw new Error('Can only convert OSGB36 point to OsGrid');
        var phi = point.lat.toRad();
        var lambda = point.lon.toRad();

        var a = 6377563.396, b = 6356256.909;      // Airy 1830 major & minor semi-axes
        var F0 = 0.9996012717;                     // NatGrid scale factor on central meridian
        var phi0 = (49).toRad(), lambda0 = (-2).toRad();  // NatGrid true origin is 49°N,2°W
        var N0 = -100000, E0 = 400000;             // northing & easting of true origin, metres
        var e2 = 1 - (b*b)/(a*a);                  // eccentricity squared
        var n = (a-b)/(a+b), n2 = n*n, n3 = n*n*n; // n, n², n³

        var cosphi = Math.cos(phi), sinphi = Math.sin(phi);
        var nu = a*F0/Math.sqrt(1-e2*sinphi*sinphi);            // nu = transverse radius of curvature
        var rho = a*F0*(1-e2)/Math.pow(1-e2*sinphi*sinphi, 1.5); // rho = meridional radius of curvature
        var eta2 = nu/rho-1;                                    // eta = ?

        var Ma = (1 + n + (5/4)*n2 + (5/4)*n3) * (phi-phi0);
        var Mb = (3*n + 3*n*n + (21/8)*n3) * Math.sin(phi-phi0) * Math.cos(phi+phi0);
        var Mc = ((15/8)*n2 + (15/8)*n3) * Math.sin(2*(phi-phi0)) * Math.cos(2*(phi+phi0));
        var Md = (35/24)*n3 * Math.sin(3*(phi-phi0)) * Math.cos(3*(phi+phi0));
        var M = b * F0 * (Ma - Mb + Mc - Md);              // meridional arc

        var cos3phi = cosphi*cosphi*cosphi;
        var cos5phi = cos3phi*cosphi*cosphi;
        var tan2phi = Math.tan(phi)*Math.tan(phi);
        var tan4phi = tan2phi*tan2phi;

        var I = M + N0;
        var II = (nu/2)*sinphi*cosphi;
        var III = (nu/24)*sinphi*cos3phi*(5-tan2phi+9*eta2);
        var IIIA = (nu/720)*sinphi*cos5phi*(61-58*tan2phi+tan4phi);
        var IV = nu*cosphi;
        var V = (nu/6)*cos3phi*(nu/rho-tan2phi);
        var VI = (nu/120) * cos5phi * (5 - 18*tan2phi + tan4phi + 14*eta2 - 58*tan2phi*eta2);

        var Δlambda = lambda-lambda0;
        var Δlambda2 = Δlambda*Δlambda, Δlambda3 = Δlambda2*Δlambda, Δlambda4 = Δlambda3*Δlambda, Δlambda5 = Δlambda4*Δlambda, Δlambda6 = Δlambda5*Δlambda;

        var N = I + II*Δlambda2 + III*Δlambda4 + IIIA*Δlambda6;
        var E = E0 + IV*Δlambda + V*Δlambda3 + VI*Δlambda5;

        return new OsGridRef(E, N);
        */
    }


    /**
     * Convert Ordnance Survey grid reference easting/northing coordinate to (OSGB36) latitude/longitude
     *
     * @param {OsGridRef} easting/northing to be converted to latitude/longitude
     * @return {LatLon} latitude/longitude (in OSGB36) of supplied grid reference
     */

    public function osGridToLatLong ($easting, $northing)
    {
        $E = $easting;
        $N = $northing;

        // Airy 1830 major & minor semi-axes
        $a = 6377563.396;
        $b = 6356256.909;

        // NatGrid scale factor on central meridian
        $F0 = 0.9996012717;

        // NatGrid true origin
        $phi0 = 49*pi()/180;
        $lambda0 = -2*pi()/180;

        // northing & easting of true origin, metres
        $N0 = -100000;
        $E0 = 400000;

        // eccentricity squared
        $e2 = 1 - ($b*$b)/($a*$a);

        // n, n², n³
        $n = ($a-$b)/($a+$b);
        $n2 = $n*$n;
        $n3 = $n*$n*$n;

        $phi = $phi0;
        $M = 0;

        do {
            $phi = ($N-$N0-$M)/($a*$F0) + $phi;

            $Ma = (1 + $n + (5/4)*$n2 + (5/4)*$n3) * ($phi-$phi0);
            $Mb = (3*$n + 3*$n*$n + (21/8)*$n3) * sin($phi-$phi0) * cos($phi+$phi0);
            $Mc = ((15/8)*$n2 + (15/8)*$n3) * sin(2*($phi-$phi0)) * cos(2*($phi+$phi0));
            $Md = (35/24)*$n3 * sin(3*($phi-$phi0)) * cos(3*($phi+$phi0));

            // meridional arc
            $M = $b * $F0 * ($Ma - $Mb + $Mc - $Md);

            // loop until < 0.01mm
        } while ($N-$N0-$M >= 0.00001);

        $cosphi = cos($phi);
        $sinphi = sin($phi);

        // nu = transverse radius of curvature
        $nu = $a*$F0/sqrt(1-$e2*$sinphi*$sinphi);

        // rho = meridional radius of curvature
        $rho = $a*$F0*(1-$e2)/pow(1-$e2*$sinphi*$sinphi, 1.5);

        // eta = ?
        $eta2 = $nu/$rho-1;

        $tanphi = tan($phi);
        $tan2phi = $tanphi*$tanphi;
        $tan4phi = $tan2phi*$tan2phi;
        $tan6phi = $tan4phi*$tan2phi;
        $secphi = 1/$cosphi;
        $nu3 = $nu*$nu*$nu;
        $nu5 = $nu3*$nu*$nu;
        $nu7 = $nu5*$nu*$nu;
        $VII = $tanphi/(2*$rho*$nu);
        $VIII = $tanphi/(24*$rho*$nu3)*(5+3*$tan2phi+$eta2-9*$tan2phi*$eta2);
        $IX = $tanphi/(720*$rho*$nu5)*(61+90*$tan2phi+45*$tan4phi);
        $X = $secphi/$nu;
        $XI = $secphi/(6*$nu3)*($nu/$rho+2*$tan2phi);
        $XII = $secphi/(120*$nu5)*(5+28*$tan2phi+24*$tan4phi);
        $XIIA = $secphi/(5040*$nu7)*(61+662*$tan2phi+1320*$tan4phi+720*$tan6phi);

        $dE = ($E-$E0);
        $dE2 = $dE*$dE;
        $dE3 = $dE2*$dE;
        $dE4 = $dE2*$dE2;
        $dE5 = $dE3*$dE2;
        $dE6 = $dE4*$dE2;
        $dE7 = $dE5*$dE2;
        $phi = $phi - $VII*$dE2 + $VIII*$dE4 - $IX*$dE6;
        $lambda = $lambda0 + $X*$dE - $XI*$dE3 + $XII*$dE5 - $XIIA*$dE7;

        return array(rad2deg($phi), rad2deg($lambda));
    }
}

