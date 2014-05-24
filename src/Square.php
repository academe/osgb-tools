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
 *
 * TODO: parse a coordinate string in any format.
 * TODO: output a coordinate string in any format.
 * TODO: parse format details so the output format can be defaulted to the input format. This effectively sets the square size.
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
     * The default number of letters to use in output formatting.
     */

    protected $number_of_letters = 2;

    /**
     * The default number of digits to use for the easting and northing in output formatting.
     */

    protected $number_of_digits = 5;

    /**
     * The number of metres East of square VV where the Western-most 500km square
     * of GB (square S) is located.
     * 1000km
     */

    const GB_ORIGIN_EAST = 1000000;

    /**
     * The number of metres North of square VV where the Southern-most 500km square
     * of GB (square S) is located.
     * 500km
     */

    const GB_ORIGIN_NORTH = 500000;

    /**
     * The letters used to name squares, in a 5x5 grid.
     * 'V' is the bottom left (South-West).
     */

    public static $letters = 'VWXYZQRSTULMNOPFGHJKABCDE';

    /**
     * Square sizes, in metres.
     */

    const KM500 = 500000;
    const KM100 = 100000;

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
        return floor(strpos(static::$letters, strtoupper($letter)) / 5);
    }

    /**
     * Convert one or two letters to number of metres East of square VV.
     * TODO: validate letters string.
     */

    public static function lettersToAbsEast($letters)
    {
        // If there are no letters, then default to square 'S'.
        if (empty($letters)) {
            $letters = 'S';
        }

        // Split the string into an array of single letters.
        $split = str_split($letters);

        // The first letter will aways be the 500km square.
        $east = static::KM500 * static::letterEastPosition($split[0]);

        // The optional second letter will identify the 100km square.
        if (isset($split[1])) {
            $east += static::KM100 * static::letterEastPosition($split[1]);
        }

        return $east;
    }

    /**
     * Convert one or two letters to number of metres North of square VV.
     * TODO: validate letters string.
     */

    public static function lettersToAbsNorth($letters)
    {
        // If there are no letters, then default to square 'S'.
        // Without letters, this is assumed to be the origin.
        if (empty($letters)) {
            $letters = 'S';
        }

        // Split the string into an array of single letters.
        $split = str_split($letters);

        // The first letter will aways be the 500km square.
        $north = static::KM500 * static::letterNorthPosition($split[0]);

        // The optional second letter will identify the 100km square.
        if (isset($split[1])) {
            $north += static::KM100 * static::letterNorthPosition($split[1]);
        }

        return $north;
    }

    /**
     * Convert a numeric string of N digits to a North or East offset value, in metres.
     * e.g. "NE 01230 14500" will be at 1230m East of the West edge of 100km sqaure "NE".
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
     * Alternatively, pass in the number of letters available in place of the box
     * size (0, 1 or 2).
     *
     * TODO: validation.
     * CHECKME: does truncating to the right make sense when the string is too long?
     */

    public static function digitsToDistance($digits, $box_size = 10)
    {
        switch ($box_size) {
            case 1000:
            case 0:
                $pad_size = 7;
                break;

            case 100:
            case 1:
                $pad_size = 6;
                break;

            default:
            case 10:
            case 2:
                $pad_size = 5;
                break;
        }

        // Pad the string out, or truncate if it started too long.
        $padded = substr(str_pad($digits, $pad_size, '0', STR_PAD_RIGHT), 0, $pad_size);

        // Now return as an integer number of metres.
        return (int)$padded;
    }

    /**
     * Return the absolute east offset for letters and a number string.
     * There can be zero, one or two letters.
     */

    public static function toAbsEast($letters, $digits)
    {
        $east = static::lettersToAbsEast($letters);

        $east += static::digitsToDistance($digits, strlen($letters));

        return $east;
    }

    /**
     * Return the absolute north offset for letters and a number string.
     * There can be zero, one or two letters.
     */

    public static function toAbsNorth($letters, $digits)
    {
        $north = static::lettersToAbsNorth($letters);

        $north += static::digitsToDistance($digits, strlen($letters));

        return $north;
    }

    /**
     * Convert an ABS East/North value pair into letters.
     * The number of letters is 0, 1 or 2.
     */

    public static function absToLetters($abs_east, $abs_north, $number_of_letters)
    {
        // TODO: if no letters are needed, then we are dealing with seven-digit numbers
        // based on the square SV origin. Make sure the ABS east and north values are
        // positive wrt square SV. This system is designed to avoid handling negative numbers
        // in all situations.

        $letters = array();

        if ($number_of_letters >= 1) {
            // Get the first letter (we have at least one).
            // Find the position on the 5x5 500km grid.
            $east_500_position = floor($abs_east / static::KM500);
            $north_500_position = floor($abs_north / static::KM500);

            $letters[] = static::$letters[($north_500_position * 5) + $east_500_position];
        }

        if ($number_of_letters >= 2) {
            // Get the second letter.
            // Find the position on the 5x5 100km grid, within the 500km grid.
            $east_100_position = floor(($abs_east - static::KM500 * $east_500_position) / static::KM100);
            $north_100_position = floor(($abs_north - static::KM500 * $north_500_position) / static::KM100);

            $letters[] = static::$letters[($north_100_position * 5) + $east_100_position];
        }

        return implode('', $letters);
    }

    /**
     * Convert an ABS East value into digits.
     * The number of letters we are using with the digits is 0, 1 or 2.
     * The number of digits is between 0 and 7, but the number of digits and
     * the number of letters combined must not be more than 7.
     * If the number of letters is zero, then the assumed letter origin will
     * be 500km square 'S'.
     */

    public static function absEastToDigits($abs_east, $number_of_letters, $number_of_digits)
    {
        switch ($number_of_letters) {
            case 0:
                // No letters, so an actual number of metres East square S.
                $offset = $abs_east - static::GB_ORIGIN_EAST;
                break;

            case 1:
                // One letter, so an offset within the 500km box.
                $offset = $abs_east % static::KM500;
                break;

            case 2:
                // Two letters, so an offset within a 100km box.
                $offset = $abs_east % static::KM100;
                break;
        }

        // Knock some digits off if it comes to greater than 7, when counting the letters too.
        if ($number_of_letters + $number_of_digits > 7) {
            $number_of_digits = 7 - $number_of_letters;
        }

        // Left-pad the number to 5, 6, or 7 digits, depending on the number of letters.
        $digits = str_pad((string)$offset, 7 - $number_of_letters, '0', STR_PAD_LEFT);

        // Now take only the required significant digits.
        return substr($digits, 0, $number_of_digits);
    }

    public static function absNorthToDigits($abs_north, $number_of_letters, $number_of_digits)
    {
        switch ($number_of_letters) {
            case 0:
                // No letters, so an actual number of metres East square S.
                $offset = $abs_north - static::GB_ORIGIN_NORTH;
                break;

            case 1:
                // One letter, so an offset within the 500km box.
                $offset = $abs_north % static::KM500;
                break;

            case 2:
                // Two letters, so an offset within a 100km box.
                $offset = $abs_north % static::KM100;
                break;
        }

        // Knock some digits off if it comes to greater than 7, when counting the letters too.
        if ($number_of_letters + $number_of_digits > 7) {
            $number_of_digits = 7 - $number_of_letters;
        }

        // Left-pad the number to 5, 6, or 7 digits, depending on the number of letters.
        $digits = str_pad((string)$offset, 7 - $number_of_letters, '0', STR_PAD_LEFT);

        // Now take only the required significant digits.
        return substr($digits, 0, $number_of_digits);
    }

    /**
     * Set the number of letters to be used by default for output.
     *
     * If we are changing the number of letters, then adjust the number of digits
     * to keep the same accuracy, i.e. the same box size.
     *
     * TODO: validation (0, 1 or 2)
     */

    public function setNumberOfLetters($number_of_letters)
    {
        // The number of letters we are increasing the current format by.
        $letter_increase = $number_of_letters - $this->number_of_letters;

        if ($letter_increase != 0) {
            // Decrease the current number of digits by the same amount.
            $new_number_of_digits = $this->getNumberOfDigits() - $letter_increase;

            // Set the new number of digits.
            // Overflow is handled in here.
            $this->setNumberOfDigits($new_number_of_digits);
        }

        $this->number_of_letters = $number_of_letters;

        return $this;
    }

    /**
     * Get the number of letters to be used by deafult for output.
     */

    public function getNumberOfLetters()
    {
        return $this->number_of_letters;
    }

    /**
     * Set the number of digits to be used by default for output.
     *
     * TODO: validation (0 to 7)
     */

    public function setNumberOfDigits($number_of_digits)
    {
        // Pull the valud into the allowed bounds.
        if ($number_of_digits > 7) $number_of_digits = 7;
        if ($number_of_digits < 0) $number_of_digits = 0;

        $this->number_of_digits = $number_of_digits;

        return $this;
    }

    /**
     * Get the number of digits to be used by default for output.
     */

    public function getNumberOfDigits()
    {
        return $this->number_of_digits;
    }

    /**
     * Set the value of the square.
     * The letters, easting and northinng strings, are stored as absolute
     * offsets from square VV, so all information about the original format
     * and consequently the square size it represents, is lost. What is
     * retained is the position to one metre.
     *
     * TODO: validation
     */

    public function set($letters, $easting, $northing)
    {
        // Set the default number of letters to be used for output formatting.
        $this->setNumberOfLetters(strlen($letters));

        $this->abs_easting = static::toAbsEast($letters, $easting);
        $this->abs_northing = static::toAbsNorth($letters, $northing);

        return $this;
    }

    /**
     * Get the letters for the current value.
     * The number of letters can be overwridden.
     */

    public function getLetters($number_of_letters = null)
    {
        if ( ! isset($number_of_letters)) {
            $number_of_letters = $this->getNumberOfLetters();
        }

        return $this->absToLetters($this->abs_easting, $this->abs_northing, $number_of_letters);
    }

    /**
     * Get the current easting.
     *
     * TODO: validation
     */

    public function getEasting($number_of_letters = null, $number_of_digits = null)
    {
        if ( ! isset($number_of_letters)) {
            $number_of_letters = $this->getNumberOfLetters();
        }

        if ( ! isset($number_of_digits)) {
            $number_of_digits = $this->getNumberOfDigits();
        }

        return $this->absEastToDigits($this->abs_easting, $number_of_letters, $number_of_digits);
    }

    /**
     * Get the current northinh.
     *
     * TODO: validation
     */

    public function getNorthing($number_of_letters = null, $number_of_digits = null)
    {
        if ( ! isset($number_of_letters)) {
            $number_of_letters = $this->getNumberOfLetters();
        }

        if ( ! isset($number_of_digits)) {
            $number_of_digits = $this->getNumberOfDigits();
        }

        return $this->absNorthToDigits($this->abs_northing, $number_of_letters, $number_of_digits);
    }
}

