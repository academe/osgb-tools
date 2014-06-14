<?php

/**
 * A lat/long coordinate, in any specified datum/ellipsoid.
 */

namespace Academe\OsgbTools\League;

use \Academe\OsgbTools;

class Coordinate extends \League\Geotools\Coordinate\Coordinate implements OsgbTools\CoordinateInterface
{
    /**
    * The height of the coordinate above the ellipsoid.
    *
    * @var double
    */
    protected $height;

    /**
     * Add height to the constructor.
     */

    public function __construct($coordinates, \League\Geotools\Coordinate\Ellipsoid $ellipsoid = null, $height = 0)
    {
        $this->setHeight($height);
        return parent::__construct($coordinates, $ellipsoid);
    }

    /**
    * {@inheritDoc}
    */

    public function setHeight($height)
    {
        $this->height = $height;
    }

    /**
    * This is the height above the geod, and typically ranges from -100m to +70m.
    * It is due to the variation of the Earth from a perfect ellipsoid due to gravitational
    * effects.
    * A 10x10 degree map of heights are published here:
    * http://www.colorado.edu/geography/gcraft/notes/datum/geoid84.html
    * and the height of any point in between can be calculated using a linear interpolation
    * from the nearest four points.
    * A method to calculate the height, for WGS84 at least, would be good.
    */

    public function getHeight()
    {
        return $this->height;
    }

    /**
    * Convert polar to cartesian.
    * Cartesian coordinates (XYZ) define three dimensional positions with respect to
    * the center of mass of the reference ellipsoid.
    */

    public function toCartesian()
    {
        $phi = deg2rad($this->getLatitude());
        $lambda = deg2rad($this->getLongitude());

        $H = $this->getHeight();
        $a = $this->getEllipsoid()->getA();
        $b = $this->getEllipsoid()->getB();

        $sin_phi = sin($phi);
        $cos_phi = cos($phi);
        $sin_lambda = sin($lambda);
        $cos_lambda = cos($lambda);

        $eSq = (pow($a, 2) - pow($b, 2)) / (pow($a, 2));
        $nu = $a / sqrt(1 - $eSq * $sin_phi * $sin_phi);

        $x = ($nu + $H) * $cos_phi * $cos_lambda;
        $y = ($nu + $H) * $cos_phi * $sin_lambda;
        $z = ((1 - $eSq) * $nu + $H) * $sin_phi;

        // TODO: return a cartesian coordinate object, not an array.
        // The object will provide the means for converting back again.
        $point = array($x, $y, $z);

        return $point;
    }
}
