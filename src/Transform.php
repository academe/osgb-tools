<?php

/**
 * Transform between OSGB and WGS84 coordinates.
 */

namespace Academe\OsgbTools;

class Transform
{
    /**
     * Parameters for transforming from OSGB to WGS84.
     */

    $transform = array(
        // x, y and z-axis translation, in metres (m).
        'tx' => -446.448,
        'ty' =>  125.157,
        'tz' => -542.060,

        // x, y and z-axis rotation, in arc-seconds (s)
        'rx' => -0.1502,
        'ry' => -0.2470,
        'rz' => -0.8421,

        // Scale accuracy, in parts per million (ppm).
        's' => 20.4894,
    );

    /**
     * Transform from OSGB and WGS84 coordinates.
     * Returns a Coordinate object.
     */

    public function osgbToWgs84()
    {
        // The steps are:
        // 1. Convert to cartesian (xyz) using the OSGB (Airy) ellipsoid.
        // 2. Apply the translation and rotation parameters.
        // 3. Convert from cartesian back to polar (lat/long) using the WGS84 ellipsoid.
    }
}
