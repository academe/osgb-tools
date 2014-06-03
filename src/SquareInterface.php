<?php

namespace Academe\OsgbTools;

/**
 */

interface SquareInterface
{
    /**
     * Set the number of letters (0, 1 or 2) used for default output formatting.
     */

    function setNumberOfLetters($number_of_letters);

    /**
     * Get the number of letters to be used by default for output formatting.
     */

    function getNumberOfLetters();

    /**
     * Set the number of digits to be used by default for output formatting.
     */

    function setNumberOfDigits($number_of_digits);

    /**
     * Get the number of digits to be used by default for output formatting.
     */

    function getNumberOfDigits();

    /**
     * Get the current easting, formatted to the default settings, or formatting
     * overridden.
     */

    function getEasting($number_of_letters = null, $number_of_digits = null);

    /**
     * Get the current northing.
     */

    function getNorthing($number_of_letters = null, $number_of_digits = null);

    /**
     * Get the current square size in metres.
     */

    function getSize($number_of_letters = null, $number_of_digits = null);

    /**
     * Set the value of the square from a single National Grid Reference string.
     */

    function setNgr($ngr);

    /**
     * Return a formatted string.
     */

    function format($format = null, $number_of_letters = null, $number_of_digits = null);

    /**
     * Set the Easting and Northing in one go, as an numeric array.
     */

    function setEastingNorthing($easting_northing);

    /**
     * Get the easting and northing for conversion, as an array.
     */

    function getEastingNorthing($centre_of_square = true);
}
