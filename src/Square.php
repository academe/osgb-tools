<?php

namespace Academe\OsgbTools;

/**
 * The model for an Ordnace Survey (OS) Great Britain (GB) National Grid Reference (NGR).
 * The Irish grid is handled slightly differently.
 *
 * The model represents the bottom-left corner (South-West) of a square in the OSGB NGR.
 * A square ranges in size from 1m to 500km.
 */

class Square
{
    /**
     * The easting value.
     * Integer 0 to 9999999.
     * Represents the number of metres East from the Western-most edge
     * of square VV.
     */

    protected $abs_easting;

    /**
     * The northing value.
     * Integer 0 to 9999999.
     * Represents the number of metres North from the Southern-most edge
     * of a square VV.
     */

    protected $abs_northing;

    /**
     * The number of metres East of square VV where the Western-most 500km square
     * of GB (square S) is located.
     * 1000km
     * TODO: make this a constant?
     */

    protected $gb_origin_east = 1000000;

    /**
     * The number of metres North of square VV where the Southern-most 500km square
     * of GB (square S) is located.
     * 500km
     */

    protected $gb_origin_north = 500000;

    /**
     * The letters used to name squares, in a 5x5 grid.
     * 'A' is the top left (North-West).
     */

    public static $letters = 'ABCDEFGHJKLMNOPQRSTUVWXYZ';

    /**
     * Convert a letter to its Eastern zero-based postion in a 25x25 grid
     * TODO: validation check on letter.
     */

    public static function letterEastPosition($letter)
    {
        return (strpos(static::$letters, strtoupper($letter)) % 5);
    }

    /**
     * Convert a letter to its North zero-based postion in a 25x25 grid
     */

    public static function letterNorthPosition($letter)
    {
        return 4 - floor(strpos(static::$letters, strtoupper($letter)) / 5);
    }

    /**
     * Convert one or two letters to number of metres East of square VV.
     * TODO: validate letters string.
     */

    public static function lettersToAbsEast($letters)
    {
        $split = str_split($letters);

        // The first letter will aways be the 500km square.
        $east = 500000 * static::letterEastPosition($split[0]);

        // The optional second letter will identify the 100km square.
        if (isset($split[1])) {
            $east += 100000 * static::letterEastPosition($split[1]);
        }

        return $east;
    }

    /**
     * Convert one or two letters to number of metres North of square VV.
     * TODO: validate letters string.
     */

    public static function lettersToAbsNorth($letters)
    {
        $split = str_split($letters);

        // The first letter will aways be the 500km square.
        $north = 500000 * static::letterNorthPosition($split[0]);

        // The optional second letter will identify the 100km square.
        if (isset($split[1])) {
            $north += 100000 * static::letterNorthPosition($split[1]);
        }

        return $north;
    }
}

