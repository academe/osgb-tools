osgb-tools
==========

Ordnance Survey (GB) grid reference conversion tools.

Usage
-----

To create a square, pass it a National Grid Reference (NGR) string:

    $square = new Academe\OsgbTools\Square($ngr_string);

A square is a square area on the OSGB NGR which is identified by its South-West corner. The size of the
square, i.e. the accuracy of the location, is determined by the number of digits and letters. A two-letter
and five-digit NGR is a square of 1m, the smallest unit supported by OSGB.

The NGT string can take any form, so long as the letters come first, then the easting then the northing.
Examples for OSGB (these are all the same location):

    NT 0456 1230 (to 10m)
    NT04561230
    NT 04560 12300 (to 1m)
    N 30456 11230
    030456,061230 (to 10m)
    0304560, 0612300 (to 1m)

The letters and the digits are both optional:

    NT 00000 00000 (to 1m)
    NT (to 100km)
    N (to 500km)
    0,0 (square 'S' to 1000km)
    0000000,0000000 (square 'S' to 1m)

Note that a single letter identifies a 500km square, so "N 712 834" will extend beyond that square and
come out as "J 212 334" (shifted North and East by one 500km box). This is done in preference to
raising an exception; "N 712 834" would not normally be used ("N 000 000" to "N 499 499" would be normal),
but still represents a real location.

Any non-alphanumeric characters used in the NGR will be ignored. That includes spaces and commas.

The accuracy (size of the box in metres) is returned by:

    $square->getSize();
    
A templated output formatter has not been written yet, but the parts can be extracted:

    $letters = $square->getLetters();
    $easting = $square->getEasting();
    $northing = $square->getNorthing();
    echo "OSGB reference = " . trim("$letters $easting $northing");

By default, the format of the parts will be the same as passed in. This can be changed:

    $square->setNumberOfLetters($number_letters); // 0, 1 or 2
    $square->setNumberOfDigits($number_digits); // 0 to 7

As you change the number of letters, the number of digits will change automatically to try to
retain the same accuracy, i.e. to represent the same square size. You can still change the
number of digits after that to get more or less accuracy.
    
    

Useful Links

* http://www.movable-type.co.uk/scripts/latlong-convert-coords.html  
  JavaScript datum conversions (WGS84, GRS80, Airy1830, AiryModified, Intl1924, Bessel1841)

* http://www.movable-type.co.uk/scripts/latlong-gridref.html  
  JavaScript conversions (Lat/Long and OS Grid Reference)

* http://www.jstott.me.uk/phpcoord/  
  Airy1830 (OSGB) <-> WGS84 conversions

* https://github.com/dvdoug/PHPCoord  
  Updated composer version PHPcoord

* http://bramp.net/blog/2008/06/04/os-easting-northing-to-lat-long/  
  Easting/Northing to Lat/Long (OSGB36 <-> Airy1830)  
  Convert between latitude/longitude, Universal Transverse Mercator (UTM)
  and Ordnance Survey (OSGB)

* https://github.com/thephpleague/geotools  
  Neat collection of tools.

* http://www.ordnancesurvey.co.uk/docs/support/guide-coordinate-systems-great-britain.pdf  
  6-digit references identify 100m grid squares;
  8 digits identify 10m grid squares, and 10 digits identify 1m squares.
  TG51401317 represents a 10m box with its (south-west) origin 51.40km across,
  13.17km up within the TG square.

Ideally, the OSGB (Northing/Easting + grids) would convert directly from/to WGS84 and not Airy.

A class to handle OSGB coordinates all all their variations, will be necessary.

