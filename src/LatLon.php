<?php

/*******************************************************************
 * This script is incomplete, broken, and will probably be removed *
 *******************************************************************/

namespace Academe\OsgbTools;

class LatLon
{
    /**
     * Ellipsoid parameters; major axis (a), minor axis (b), and flattening (f) for each ellipsoid.
     * Note: if is inverted f (f = 1/if). This is until PHP 5.5 where constant expressions are allowed
     * in property definitions.
     */

    protected static $ellipsoid = array(
        'WGS84' =>          array('a' => 6378137,     'b' => 6356752.3142,   'if' => 298.2572235630),
        'GRS80' =>          array('a' => 6378137,     'b' => 6356752.314140, 'if' => 298.257222101),
        'Airy1830' =>       array('a' => 6377563.396, 'b' => 6356256.909,    'if' => 299.3249646),
        'AiryModified' =>   array('a' => 6377340.189, 'b' => 6356034.448,    'if' => 299.32496),
        'Intl1924' =>       array('a' => 6378388.000, 'b' => 6356911.946,    'if' => 297.0),
        'Bessel1841' =>     array('a' => 6377397.155, 'b' => 6356078.963,    'if' => 299.152815351),
    );

    /**
     * Datums; with associated *ellipsoid* and Helmert *transform* parameters to convert from WGS84
     * into given datum.
     */

    protected static $datums = array(
        'WGS84' => array(
            'ellipsoid' => 'WGS84',
            'transform' => array(
                'tx' => 0.0,    'ty' => 0.0,    'tz' => 0.0,    // m
                'rx' => 0.0,    'ry' => 0.0,    'rz' => 0.0,    // sec
                's' => 0.0,                                     // ppm
            ),
        ),
        'OSGB36' => array(
            // www.ordnancesurvey.co.uk/docs/support/guide-coordinate-systems-great-britain.pdf
            'ellipsoid' => 'Airy1830',
            'transform' => array(
                'tx' => -446.448,   'ty' => 125.157,    'tz' => -542.060,   // m
                'rx' => -0.1502,    'ry' => -0.2470,    'rz' => -0.8421,    // sec
                's' => 20.4894,                                             // ppm
            ),
        ),
        'ED50' => array(
            // og.decc.gov.uk/en/olgs/cms/pons_and_cop/pons/pon4/pon4.aspx
            'ellipsoid' => 'Intl1924',
            'transform' => array(
                'tx' => 89.5,   'ty' => 93.8,   'tz' => 123.1,  // m
                'rx' => 0.0,    'ry' => 0.0,    'rz' => 0.156,  // sec
                's' => -1.2,                                    // ppm
            ),
        ),
        'Irl1975' => array(
            // maps.osni.gov.uk/CMS_UserFiles/file/The_irish_grid.pdf
            'ellipsoid' => 'AiryModified',
            'transform' => array(
                'tx' => -482.530,   'ty' => 130.596,    'tz' => -564.557,   // m
                'rx' => -1.042,     'ry' => -0.214,     'rz' => -0.631,     // sec
                's' => -8.150,                                              // ppm
            ),
        ),
        'TokyoJapan' => array(
            // www.geocachingtoolbox.com?page=datumEllipsoidDetails
            'ellipsoid' => 'Bessel1841',
            'transform' => array(
                'tx' => 148,    'ty' => -507,   'tz' => 685,    // m
                'rx' => 0,      'ry' => 0,      'rz' => 0,      // sec
                's' => 0,                                       // ppm
            ),
        ),
    );

    // The properties of the point.

    protected $lat;
    protected $lon;
    protected $height;
    protected $datum;


    /** - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -  */
    /* Geodesy tools for an ellipsoidal earth model                       (c) Chris Veness 2005-2014  */
    /*                                                                                                */
    /* Includes methods for converting lat/lon coordinates bewteen different coordinate systems.      */
    /*   - www.movable-type.co.uk/scripts/latlong-convert-coords.html                                 */
    /*                                                                                                */
    /*  Usage: to eg convert WGS84 coordinate to OSGB coordinate:                                     */
    /*   - var wgs84 = new LatLonE(lat, lon, GeoParams.datum.WGS84);                                  */
    /*   - var osgb = wgs84.convertDatum(GeoParams.datum.OSGB36);                                     */
    /*                                                                                                */
    /*  q.v. Ordnance Survey 'A guide to coordinate systems in Great Britain' Section 6               */
    /*   - www.ordnancesurvey.co.uk/docs/support/guide-coordinate-systems-great-britain.pdf           */
    /*                                                                                                */
    /* - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -  */

    /**
     * Creates lat/lon (polar) point with latitude & longitude values and height above ellipsoid, on a
     * specified datum.
     *
     * @classdesc Library of geodesy functions for operations on an ellipsoidal earth model.
     * @requires GeoParams
     * @requires Vector3d
     *
     * @constructor
     * @param {number}          lat - Geodetic latitude in degrees.
     * @param {number}          lon - Longitude in degrees.
     * @param {GeoParams.datum} [datum=WGS84] - Datum this point is defined within.
     * @param {number}          [height=0] - Height above ellipsoid, in metres.
     */

    public function __construct($lat, $lon, $datum = null, $height = 0) {
        if ( ! isset($datum)) $datum = 'WGS84';

        $this->lat = Number(lat);
        $this->lon = Number(lon);
        $this->datum = datum;
        $this->height = Number(height);
    }

    /**
     * Converts ‘this’ lat/lon coordinate to new coordinate system.
     *
     * @param   {GeoParams.datum} toDatum - Datum this coordinate is to be converted to.
     * @returns {LatLonE} This point converted to new datum.
     */

    public function convertDatum($toDatum)
    {
        // Duplicate this?
        $oldLatLon = $this;

        if ($oldLatLon->datum == 'WGS84') {
            // Converting from WGS84
            $transform = $this->datums[$toDatum]['transform'];
        }

        if ($toDatum == 'WGS84') {
            // Converting to WGS84; use inverse transform (don't overwrite original!)
            $transform = array();

            foreach ($this->datums[]['transform'] as $key => $param) {
                $transform[$key] = -$oldLatLon->datums[$this->datum]['transform'][$param];
            }
        }
/*
        if (typeof transform == 'undefined') {
            // neither this.datum nor toDatum are WGS84: convert this to WGS84 first
            $oldLatLon = $this->convertDatum('WGS84');
            $transform = $toDatum.transform;
        }

        // convert polar to cartesian
        $cartesian = $oldLatLon.toCartesian();

        // apply transform
        $cartesian = $cartesian.applyTransform(transform);

        // convert cartesian to polar
        $newLatLon = $cartesian.toLatLon($toDatum);

        return $newLatLon;
*/
    }
}