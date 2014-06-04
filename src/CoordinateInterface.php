<?php

namespace Academe\OsgbTools;

/**
 * Interface for a Lat/Long coordinate.
 */

interface CoordinateInterface
{
    /**
     * Set the latitude.
     *
     * @param double $latitude
     */

    public function setLatitude($latitude);

    /**
     * Get the latitude.
     *
     * @return double
     */

    public function getLatitude();

    /**
     * Set the longitude.
     *
     * @param double $longitude
     */

    public function setLongitude($longitude);

    /**
     * Get the longitude.
     *
     * @return double
     */

    public function getLongitude();
}
