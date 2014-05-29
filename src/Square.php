<?php

namespace Academe\OsgbTools;

/**
 * The model for an Ordnance Survey (OS) Great Britain (GB) National Grid Reference (NGR).
 * The Irish grid is handled slightly differently.
 *
 * The model represents the bottom-left corner (South-West) of a square in the OSGB NGR.
 * A square ranges in size from 1m to 500km, or even bigger without letters.
 *
 * The approach being taken is to make all values relative to square VV (far SW limit)
 * and convert to more appropriate offsets as needed for calculations and I/O. This may
 * be the right approach, or may be wrong, but we'll go this route and see what happens.
 *
 * This model does not perform conversions to other geographic coordinate systems; it is
 * just for bringing together the various different formats used in OSGB into one class
 * for eash of use.
 *
 * TODO: work out how the Irish grid can be implemented through shared code.
 * TODO: pull the validation rules together to avoid so much duplication.
 */

class Square
{
    /**
     * The national grid reference type (OSGB or Irish)
     */

    const NGR_TYPE = 'OSGB';

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
     * The maximum number of digits (for easting or nothing).
     * This is the maximum number with no letters. Each letter will
     * reduce this by one.
     */

    const MAX_DIGITS = 7;

    /**
     * The maximum number of letters in a coordinate.
     */

    const MAX_LETTERS = 2;

    /**
     * The number of metres East of square VV that the Western-most 500km square
     * of GB (square SV) is located.
     * 1000km
     */

    const ABS_FALSE_ORIGIN_EAST = 1000000;

    /**
     * The number of metres North of square VV that the Southern-most 500km square
     * of GB (square SV) is located.
     * 500km
     */

    const ABS_FALSE_ORIGIN_NORTH = 500000;

    /**
     * The letters used to name squares, in a 5x5 grid.
     * 'V' is the bottom left (South-West).
     * The letters are in groups of five, with letters in each group
     * listed West to East, and each group listed South to North.
     */

    const LETTERS = 'VWXYZQRSTULMNOPFGHJKABCDE';

    /**
     * Square sizes, in metres.
     */

    const KM500 = 500000;
    const KM100 = 100000;

    /**
     * The format placeholders.
     */

    const FORMAT_LETTERS = '%l';
    const FORMAT_EASTING = '%e';
    const FORMAT_NORTHING = '%n';

    const FORMAT_DEFAULT = '%l %e%n';

    /**
     * Valid squares, both 500km and 100km, used by the OS.
     * These cover just land in GB.
     * There is no reason in theory why sqaures outside these bounds cannot be
     * used, apart from being very inaccurate, so validation against these letter
     * combinations will be optional.
     */

    protected $valid_squares = array(
        'H' => array(
            'P' => 'HP',
            'T' => 'HY',
            'U' => 'HU',
            'W' => 'HW',
            'X' => 'HX',
            'Y' => 'HY',
            'Z' => 'HZ',
        ),
        'N' => array(
            'A' => 'NA',
            'B' => 'NB',
            'C' => 'NC',
            'D' => 'ND',
            'F' => 'NF',
            'G' => 'NG',
            'H' => 'NH',
            'J' => 'NJ',
            'K' => 'NK',
            'L' => 'NL',
            'M' => 'NM',
            'N' => 'NN',
            'O' => 'NO',
            'R' => 'NR',
            'S' => 'NS',
            'T' => 'NT',
            'U' => 'NU',
            'W' => 'NW',
            'X' => 'NX',
            'Y' => 'NY',
            'Z' => 'NZ',
        ),
        'O' => array(
            'V' => 'OV',
        ),
        'S' => array(
            'C' => 'SC',
            'D' => 'SD',
            'E' => 'SE',
            'H' => 'SH',
            'J' => 'SJ',
            'K' => 'SK',
            'M' => 'SM',
            'N' => 'SN',
            'O' => 'SO',
            'P' => 'SP',
            'R' => 'SR',
            'S' => 'SS',
            'T' => 'ST',
            'U' => 'SU',
            'V' => 'SV',
            'W' => 'SW',
            'X' => 'SX',
            'Y' => 'SY',
            'Z' => 'SZ',
        ),
        'T' => array(
            'A' => 'TA',
            'F' => 'TF',
            'G' => 'TG',
            'L' => 'TL',
            'M' => 'TM',
            'Q' => 'TQ',
            'R' => 'TR',
            'V' => 'TV',
        ),
    );

    /**
     * Convert a letter to its Eastern zero-based postion in a 25x25 grid
     * TODO: validation check on letter.
     */

    public static function letterEastPosition($letter)
    {
        return (strpos(static::LETTERS, strtoupper($letter)) % 5);
    }

    /**
     * Convert a letter to its North zero-based postion in a 25x25 grid
     */

    public static function letterNorthPosition($letter)
    {
        return floor(strpos(static::LETTERS, strtoupper($letter)) / 5);
    }

    /**
     * Convert one or two letters to number of metres East of square VV.
     * TODO: validate letters string.
     */

    public static function lettersToAbsEast($letters)
    {
        // If there are no letters, then default to square 'S'.
        // Without letters, this is assumed to be the origin.
        if (empty($letters)) {
            $letters = 'S';
        }

        // Split the string into an array of single letters.
        $split = str_split($letters);

        // The first letter will aways be the 500km square.
        $east = static::KM500 * static::letterEastPosition($split[0]);

        // The optional second letter will identify the 100km square.
        if (isset($split[1]) && static::MAX_LETTERS == 2) {
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
        if (isset($split[1]) && static::MAX_LETTERS == 2) {
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
     * CHECKME: does truncating to the right make sense when the string is too long?
     */

    public static function digitsToDistance($digits, $number_of_letters)
    {
        if ($number_of_letters > static::MAX_LETTERS) {
            $number_of_letters = static::MAX_LETTERS;
        }

        switch ($number_of_letters) {
            case 0:
                $pad_size = static::MAX_DIGITS;
                break;

            case 1:
                $pad_size = static::MAX_DIGITS - 1;
                break;

            case 2:
                $pad_size = static::MAX_DIGITS - min(2, static::MAX_LETTERS);
                break;

            default:
                // Invalid number of letters.
                throw new \UnexpectedValueException(
                    sprintf('Number of letters out of range; expected value 0 to %d; %d passed in', static::MAX_LETTERS, $number_of_letters)
                );
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
            // Find the offset on the 5x5 500km grid.
            $east_500_offset = floor($abs_east / static::KM500);
            $north_500_offset = floor($abs_north / static::KM500);

            $letters[] = substr(static::LETTERS, ($north_500_offset * 5) + $east_500_offset, 1);
        }

        if ($number_of_letters >= 2 && $number_of_letters <= static::MAX_LETTERS) {
            // Get the second letter (if the grid system supports it).

            // Find the offset on the 5x5 100km grid, within the 500km grid.
            $east_100_offset = floor(($abs_east - static::KM500 * $east_500_offset) / static::KM100);
            $north_100_offset = floor(($abs_north - static::KM500 * $north_500_offset) / static::KM100);

            $letters[] = substr(static::LETTERS, ($north_100_offset * 5) + $east_100_offset, 1);
        }

        return implode('', $letters);
    }

    /**
     * Convert an ABS East value into digits.
     * The number of letters we are using with the digits is 0, 1 or 2.
     * The number of digits is between 0 and MAX_DIGITS, but the number of digits and
     * the number of letters combined must not be more than MAX_DIGITS.
     * If the number of letters is zero, then the assumed letter origin will
     * be 500km square 'S'.
     */

    protected static function absToDigits($abs_distance, $number_of_letters, $number_of_digits, $origin)
    {
        if ( ! is_int($number_of_letters)) {
            throw new \InvalidArgumentException(
                sprintf('Number of letters must be an integer; %s passed in', gettype($number_of_letters))
            );
        }

        if ($number_of_letters < 0 || $number_of_letters > static::MAX_LETTERS) {
            throw new \UnexpectedValueException(
                sprintf('Number of letters out of range; expected value 0 to %d; %d passed in', static::MAX_LETTERS, $number_of_letters)
            );
        }

        if ( ! is_int($number_of_digits)) {
            throw new \InvalidArgumentException(
                sprintf('Number of digits must be an integer; %s passed in', gettype($number_of_digits))
            );
        }

        switch ($number_of_letters) {
            case 0:
                // No letters, so an actual number of metres East square S.
                $offset = $abs_distance - $origin;
                break;

            case 1:
                // One letter, so an offset within the 500km box (for OSGB, at least, not for the Irish grid).
                $offset = $abs_distance % static::KM500;
                break;

            case 2:
                // Two letters, so an offset within a 100km box.
                $offset = $abs_distance % static::KM100;
                break;
        }

        // Knock some digits off, if it comes to greater than MAX_DIGITS, when counting the letters too.
        if ($number_of_letters + $number_of_digits > static::MAX_DIGITS) {
            $number_of_digits = static::MAX_DIGITS - $number_of_letters;
        }

        // Left-pad the number to 5, 6, or 7 digits, depending on the number of letters.
        $digits = str_pad((string)$offset, static::MAX_DIGITS - $number_of_letters, '0', STR_PAD_LEFT);

        // Now take only the required significant digits.
        return substr($digits, 0, $number_of_digits);
    }

    public static function absEastToDigits($abs_east, $number_of_letters, $number_of_digits)
    {
        return static::absToDigits($abs_east, $number_of_letters, $number_of_digits, static::ABS_FALSE_ORIGIN_EAST);
    }

    public static function absNorthToDigits($abs_north, $number_of_letters, $number_of_digits)
    {
        return static::absToDigits($abs_north, $number_of_letters, $number_of_digits, static::ABS_FALSE_ORIGIN_NORTH);
    }

    /**
     * Set the number of letters to be used by default for output.
     *
     * If we are changing the number of letters, then adjust the number of digits
     * to keep the same accuracy, i.e. the same box size.
     */

    public function setNumberOfLetters($number_of_letters)
    {
        // Must be an integer.
        if ( ! is_int($number_of_letters)) {
            throw new \InvalidArgumentException(
                sprintf('Number of letters must be an integer; %s passed in', gettype($number_of_letters))
            );
        }

        // Pull to within the bounds.
        if ($number_of_letters < 0) $number_of_letters = 0;
        if ($number_of_letters > static::MAX_LETTERS) $number_of_letters = static::MAX_LETTERS;

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
     * Get the number of letters to be used by default for output.
     */

    public function getNumberOfLetters()
    {
        return $this->number_of_letters;
    }

    /**
     * Set the number of digits to be used by default for output.
     */

    public function setNumberOfDigits($number_of_digits)
    {
        // Must be an integer.
        if ( ! is_int($number_of_digits)) {
            throw new \InvalidArgumentException(
                sprintf('Number of digits must be an integer; %s passed in', gettype($number_of_digits))
            );
        }

        // Pull the values into the allowed bounds.
        if ($number_of_digits < 0) $number_of_digits = 0;
        if ($number_of_digits > static::MAX_DIGITS) $number_of_digits = static::MAX_DIGITS;

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
     * TODO: more validation
     */

    public function setParts($letters, $easting, $northing)
    {
        if (strlen($letters) < 0 || strlen($letters) > static::MAX_LETTERS) {
            throw new \UnexpectedValueException(
                sprintf('Number of letters out of range; expected number of letters 0 to %d; %d passed in', static::MAX_LETTERS, strlen($letters))
            );
        }

        // Set the default number of letters to be used for output formatting.
        $this->setNumberOfLetters(strlen($letters));

        // Set the number of digits to the length of the Easting or Northing.
        $this->setNumberOfDigits(max(strlen($easting), strlen($northing)));

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

        // Seven digit references (without letters) must only be used in the valid 500km square range,
        // since its origin is square L.

        if ($number_of_letters == 0 && ! $this->isInBound(true)) {
            throw new \UnexpectedValueException(
                sprintf('Numeric-only reference out of range; reference must lie in squares %s', implode(', ', array_keys($this->valid_squares)))
            );
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

        // Seven digit references (without letters) must only be used in the valid 500km square range,
        // since its origin is square L.

        if ($number_of_letters == 0 && ! $this->isInBound(true)) {
            throw new \UnexpectedValueException(
                sprintf('Numeric-only reference out of range; reference must lie in squares %s', implode(', ', array_keys(static::$valid_squares)))
            );
        }

        return $this->absNorthToDigits($this->abs_northing, $number_of_letters, $number_of_digits);
    }

    /**
     * Get the current square size, i.e. the accuracy of the geographic reference.
     * The square size will vary between 1m (two letters and five digits, OSGB) and
     * 500km (just a single OSGB letter).
     * A single digit, and no letter, in has a square size of 1000km.
     */

    public function getSize($number_of_letters = null, $number_of_digits = null)
    {
        if ( ! isset($number_of_letters)) {
            $number_of_letters = $this->getNumberOfLetters();
        }

        if ( ! isset($number_of_digits)) {
            $number_of_digits = $this->getNumberOfDigits();
        }

        $total_characters = $number_of_letters + $number_of_digits;

        if ($total_characters > static::MAX_DIGITS) {
            $total_characters = static::MAX_DIGITS;
        }

        // Each missing character from MAX_DIGITS takes the accuracy down by a factor
        // of ten. The last remaining letter has a square size of 500km, not 1000km.
        $missing_factor = static::MAX_DIGITS - $total_characters;

        // Are we down to one letter only?
        if ($total_characters == 1 && $number_of_letters == 1) {
            // A single letter has a aquare size of 500km.
            $final_multiplier = 5;

            // Knock the missing accuracy factor down by one, as this is what the
            // multiplier will replace.
            $missing_factor -= 1;
        } else {
            // Any other combination of letters and digits will be handled as a factor of ten.
            $final_multiplier = 1;
        }

        // The square size is ten to the power of the number of digits less than the
        // maximum, with an exception (final multiplier) for a single letter. Location
        // 'S' of OSGB will be the South West region of Great Britain.
        return pow(10, $missing_factor) * $final_multiplier;
    }

    /**
     * Set the value of the square from a single string.
     * Multiple formats are supported, but all take the order:
     *  [letters] [easting northing]
     * - All whitespace and separating characters are disregarded.
     * - Letters and easting/norhting are optional.
     * - Easting and northing must use the same number of digits.
     * - Letters are case-insenstive.
     * - Exceptions will be raised for invalid formats (e.g. invalid
     *   characters, letters in the wrong place, too many letters or
     *   digits, unbalanced easting/northing digit length).
     */

    public function set($ngr)
    {
        // ngr must be a string.
        if ( ! is_string($ngr)) {
            throw new \InvalidArgumentException(
                sprintf('National Grid Reference (NGR) must be a string; %s passed in', gettype($ngr))
            );
        }

        // Get letters upper-case.
        $ngr = strtoupper($ngr);

        // Remove any non-alphanumeric characters.
        $ngr = preg_replace('/[^A-Z0-9]/', '', $ngr);

        // Letters should be at the start only.
        $letters = '';
        $ngr_array = str_split($ngr);
        while(count($ngr_array) && strpos(static::LETTERS, $ngr_array[0]) !== false) {
            $letters .= array_shift($ngr_array);
        }
        $digits = implode($ngr_array);

        // Exception if the number of letters is greater than allowed.
        if (strlen($letters) > static::MAX_LETTERS) {
            throw new \UnexpectedValueException(
                sprintf('An NGR of type %s cannot have more then %d letters; %d passed in', static::NGR_TYPE, static::MAX_LETTERS, strlen($letters))
            );
        }

        // Exception if the digits string contains non-digits.
        if ( ! preg_match('/^[0-9]*$/', $digits)) {
            throw new \UnexpectedValueException(
                sprintf('Invalid (no-numeric) characters found in Easting or Northing digits')
            );
        }

        // Exception if the digits are not balanced, i.e. different length for Eastings and Northings.
        if (strlen($digits) % 2 != 0) {
            throw new \UnexpectedValueException(
                sprintf('Eastings and Northings must contain the same number of digits; a combined total of %d digits found', strlen($digits))
            );
        }

        $digit_length = strlen($digits) / 2;

        // Exception if too many digits.
        if (strlen($letters) + $digit_length > static::MAX_DIGITS) {
            throw new \UnexpectedValueException(
                sprintf('Too many digits; a maximum of %d digits for a Easting or Northing is allowed with %d letters; %d digits supplied', static::MAX_DIGITS - strlen($letters), strlen($letters), $digit_length)
            );
        }

        // Split the digits into eastings and northings.
        $eastings = substr($digits, 0, $digit_length);
        $northings = substr($digits, $digit_length);

        $this->setParts($letters, $eastings, $northings);

        $this->setNumberOfDigits($digit_length);

        return $this;
    }

    /**
     * Pass an optional NGR string in at instantiation.
     */

    public function __construct($ngr = '')
    {
        if ($ngr != '') {
            $this->set($ngr);
        }
    }

    /**
     * Determine whether the NGR is valid, within a 100km square bound covered by OSGB.
     * If $km500_square_only is true, then only the 500km squares are checked. This ensures
     * the reference is within the GB range, without going into too much detail.
     */

    public function isInBound($km500_square_only = false)
    {
        // Get the two letters for the current NGR.
        $letters = $this->getLetters(static::MAX_LETTERS);

        // If we don't have two letters, then we are certainly out of bounds.
        if (strlen($letters) != static::MAX_LETTERS) {
            return false;
        }

        // Split up into separate letters.
        $letters_parts = str_split($letters);

        if ( ! isset($this->valid_squares[$letters_parts[0]])) {
            // The first letter is out of bounds.
            return false;
        }

        if ( ! $km500_square_only && ! isset($this->valid_squares[$letters_parts[0]][$letters_parts[1]])) {
            // The second letter is out of bounds.
            return false;
        }

        // Not out of bounds, so must be valid.
        return true;
    }

    /**
     * Return a formatted string.
     * TODO: exception if trying to format with no letters outside of the GB false origin.
     */

    public function format($format = null, $number_of_letters = null, $number_of_digits = null)
    {
        if ( ! isset($format)) {
            $format = static::FORMAT_DEFAULT;
        }

        $formatted = strtr($format, array(
            static::FORMAT_LETTERS => $this->getLetters($number_of_letters),
            static::FORMAT_EASTING => $this->getEasting($number_of_letters, $number_of_digits),
            static::FORMAT_NORTHING => $this->getNorthing($number_of_letters, $number_of_digits),
        ));

        // Trim the result, as the numbers or digits are both optional.
        return trim($formatted);
    }

    /**
     * Default conversion to string.
     */

    public function __toString()
    {
        return $this->format();
    }

    /**
     * Get the easting and northing for conversion.
     * This is the numeric-only 7-digit version with the GB origin at square S.
     * CHECKME: we know what size square was passed into this object, so here should
     * we be returning the easting/northing of the centre of the square rather than
     * the SW corner? Need to find a definitive defintion of an OSGB *point* given an
     * OSGB reference that covers a square greater than 1m.
     *
     * I will default $centre_of_square to true for now, and this will return the
     * centre of the square.
     */

    public function getEastNorth($centre_of_square = true)
    {
        // Do we want to return the centre of the square?
        $offset = 0;
        if ($centre_of_square) {
            $square_size = $this->getSize();

            if ($square_size > 1) {
                $offset = $square_size / 2;
            }
        }

        return array(
            (int) $this->getEasting(0, static::MAX_DIGITS) + $offset,
            (int) $this->getNorthing(0, static::MAX_DIGITS) + $offset,
        );
    }
}

