<?php

/**
 * 
 */

namespace Academe\OsgbTools\League;

use Academe\OsgbTools;
use League\Geotools\Coordinate\Ellipsoid;

class Convert extends OsgbTools\Convert
{
    /**
     * Return a new coordinate (Lat/Long) instance.
     * Default the ellipsoid to Airy.
     */

    public static function createCoordinate($latitude, $longitude)
    {
        return new OsgbTools\League\Coordinate(array($latitude, $longitude), Ellipsoid::createFromName(Ellipsoid::AIRY));
    }

    /**
     * @todo When converting from a League\Geotools\ coordinate make sure the ellipsoid is AIRY first. Maybe siliently convert.
     */

    public static function latLongToOsGrid($latitude_or_point, $longitude = null)
    {
        // TODO: validate (and possibly convert) the ellipsoide if given a \League\Geotools\Coordinate\CoordinateInterface
        // The assumption will need to be that the ellipsoid represents coordinate system and datum, which
        // would not be a safe assumption in a more generic library that could cover many more datums (there
        // are about 666 of them in use, 480 of them geodetic).

        return parent::latLongToOsGrid($latitude_or_point, $longitude);
    }

    /**
     * TODO: Do the Helmert transform on cartesian coordinates.
     * The transform will need the transform parameters that define how to
     * transform FROM WGS84. The inverse of these transform parameters will
     * transform TO WGS84.
     * The parameters should be stored against each datum, along with the
     * ellipsoid. For now we will mirror the datums and put the parameters
     * here. Any datums for which we do not have the transform parameters yet,
     * will raise an exception.
     * Some parameters:
     * https://www.google.com/fusiontables/DataSource?dsrcid=844833
     * http://sas2.elte.hu/tg/eesti_datums_egs9.htm
     * http://home.hiwaay.net/~taylorc/bookshelf/math-science/geodesy/datum/transform/high-accuracy/
     * http://www.uvm.edu/giv/resources/WGS84_NAD83.pdf
     * http://digaohm.semar.gob.mx/imagenes/hidrografia/S60_Ed3Eng.pdf <- very good, complete tables of parameters
     * http://www.colorado.edu/geography/gcraft/notes/datum/edlist.html <- big table here
     * The trouble is, all these figures are correct only to a given date.
     *
     * Useful to note: A datum defines an ellipsoid and the position of its centre. The ellisoids seldom
     * change over time, but the locations of the centres do, as measurements become more accurate.
     * e.g. WGS84 (G730) and WGS84 (G873) have different realisations of the centres.
     * The different datums tend to track different changes. WGS83 (G*) uses GPS to track the centre of mass
     * of the Earth. NAD84 tracks the movement of US plates, so will also change over time.
     * Some parameters even include rates of change, so you must add in the date when doing the transformation.
     * In practice, these time-base variations are not of practical importance.
     */

     /*
        Example transform parameters:
            WGS84: { // just present for reference
                transform: { tx:    0.0,    ty:    0.0,     tz:    0.0,    // m
                             rx:    0.0,    ry:    0.0,     rz:    0.0,    // sec
                              s:    0.0 }                                  // ppm
            },
            OSGB36: { // www.ordnancesurvey.co.uk/docs/support/guide-coordinate-systems-great-britain.pdf
                transform: { tx: -446.448,  ty:  125.157,   tz: -542.060,  // m
                             rx:   -0.1502, ry:   -0.2470,  rz:   -0.8421, // sec
                              s:   20.4894 }                               // ppm
            },
     */
}

