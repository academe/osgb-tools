<?php

namespace Academe\OsgbTools;

/**
 * The model for an Ordnace Survey (OS) Great Britain (GB) National Grid Reference (NGR).
 * The Irish grid is handled slightly differently.
 *
 * The model represents the bottom-left corner (South-West) of a square in the OSGB NGR.
 * A square ranges in size from 1m to 500km.
 *
 * The approach being taken is to make all values relative to square VV (far SW limit)
 * and convert to more appropriate offsets as needed for calculations and I/O. This may
 * be the right approach, or may be wrong, but we'll go this route and see what happens.
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

    /**
     * Convert a numeric string of N digits to a North or East value, in metres.
     *
     * The digits will represent an offset in a box 10km, 100km
     * or 1000km. The size of the box will depend on how many letters are used with the
     * representation of the position.
     * Leading zeroes are significant, and the digits are right-padded with zeroes to fill
     * one of the three box sizes, then converted to an integer, in metres.
     * The box size is in km, and can be 10, 100 or 1000.
     * The default is a 10km box, with up to 5 digits identifying a 1m location, with the
     * most sigificant digit (which may be a zero) identifying a 10km box.
     *
     * TODO: validation.
     */

    public static function digitsToDistance($digits, $box_size = 10)
    {
        switch ($box_size) {
            case 1000:
            $pad_size = 7;
            break;

            case 100:
            $pad_size = 6;
            break;

            default:
            $pad_size = 5;
            break;
        }

        // Pad the string out.
        $padded = str_pad($digits, $pad_size, '0', STR_PAD_RIGHT);

        // Now return as an integer number of metres.
        return (int)$padded;
    }
}

