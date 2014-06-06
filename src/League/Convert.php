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

        return parent::latLongToOsGrid($latitude_or_point, $longitude);
    }
}

