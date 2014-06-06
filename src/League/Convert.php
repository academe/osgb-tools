<?php

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
}

