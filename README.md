osgb-tools
==========

Ordnance Survey (OS) National Grid for Great Britain (GB) reference conversion tools.

Usage
-----

To create a square, pass it a National Grid Reference (NGR) string:

    $square = new Academe\OsgbTools\Square($ngr_string);

A square is a square area on the OSGB National Grid which is identified by its South-West corner. The size of the
square, i.e. the accuracy of the location, is determined by the number of digits and letters. A two-letter
and five-digit NGR is a square of 1m, the smallest unit supported by OSGB.

The NGR string can take any form, so long as the letters come first, then the easting then the northing.
Examples for OSGB (these are all the same location):

    NT 0456 1230 (to 10m)
    NT04561230
    NT 04560 12300 (to 1m)
    N 30456 11230
    0304560,0612300 (to 1m)
    304560, 612300 (to 1m)

The letters and the digits are both optional:

    NT 00000 00000 (to 1m)
    NT (to 100km)
    N (to 500km)
    0,0 (square 'S' to 1m)
    0000000,0000000 (square 'S' to 1m)
    401250,1234350 (to 1m - note 7 digit northing to reach Orkneys)
    
Note: The leading zeroes are important when letters are used, as the most significant digit represents the
same square corner regardless of how many digits follow it. When no letters are used, the number-only
values are handled as metres from the false origin. A more detailed explanation follows.

Although the treatment of the digits can follow the
pattern from two letters, through one letter down to no letters, it does introduce some ambiguity.
When letters are involved, the digits supplied are assumed to start with the most significant digit. This
allows 1, 10, 100, 1000 etc. to all reference the same point (the SW corner of a square) though with varying
accuracies (different sizes squares). However, numeric-only easting/northings are stated to be always
measured in metres and describe the distance from a point at square SV. Thus 1, 10, 100 etc. will be very
different eastings or northing values in a numeric-only reference. For this reason, the library will be treating
numeric-only references differently to letter/digit references. Numeric-only is always a point of 1m squared.

Note that a single letter identifies a 500km square, so "N 712 834" will extend beyond that square and
come out as "J 212 334" (shifted North and East by one 500km box). This is done in preference to
raising an exception; "N 712 834" would not normally be used ("N 000 000" to "N 499 499" would be normal),
but still represents a real location. However, this library will not gererate out-of-range digits, so if
you pass in "N 712 834" you will always get "J 212 334" (or equivalent) out on the J square.

Any non-alphanumeric characters used in the NGR will be ignored. That includes spaces and commas.

The accuracy (size of the box in metres) is returned by:

    $square->getSize();
    
Parts of the formatted reference can be extracted separately:

    $letters = $square->getLetters();
    $easting = $square->getEasting();
    $northing = $square->getNorthing();
    echo "OSGB reference = " . trim("$letters $easting $northing");
    
Or it can be formatted in one go:

    echo (string)$square; // e.g. 'SE 0123034300'
    echo $square->setNumberOfLetters(1)->format(); // e.g. 'S 401230434300'
    echo $square->format('%l %e %n', 1, 3); // e.g. 'S 401 434' with 1 letter and 3 digits
    echo $square->format('%x,%y'); // e.g. '401250,1234350' numeric-only easting/northing metres from false origin

By default, the format of the parts will be the same as passed in. This can be overridden:

    $square->setNumberOfLetters($number_letters); // 0, 1 or 2
    $square->setNumberOfDigits($number_digits); // 0 to 7

As you change the number of letters, the number of digits will change automatically to try to
retain the same accuracy, i.e. to represent the same square size. You can still change the
number of digits after that to get more or less accuracy.

Not all letter combinations cover land that the OS maps. Only 500km squares H, N, O, S and T
are used by the OS. The squares do extend beyond that in theory, but the accuracy drifts out quickly.
Inside these 500km squares, only a selection of 100km squares are mapped by the OS. Again, 100km
squares outside of this range are still supported by this library, but they have no meaning on
OS maps.

Just as an aside, 500km square O covers just a tiny corner of Yorkshire, with the majority
of that square being in the North Sea. For this reason, the only 100km square prefixed O is
OV.

To check if the current reference lies within a valid 100km square mapped by the OS, use this
method:

    if ($square->isInBound()) echo "Yes, this place is on a printed map";

Example Coordinate Conversion
-----------------------------

This bit is work-in-progress, and is very much subject to change. However, it does demonstrate the process.

* `Academe\OsgbTools\Convert::osGridToLatLong()` will return a `Academe\OsgbTools\Coordinate`
* `Academe\OsgbTools\Convert::latLongToOsGrid()` will return a `Academe\OsgbTools\Square`

We are going to convert SE0123034300 to Lat/Long. First create the square:

    // Spaces in the reference are optional - they are shown here for clarity.
    $square = new Academe\OsgbTools\Square('SE 01230 34300');
    
Then extract the Easting and Northing:

    list($Easting, $Northing) = $square->getEastNorth();
    // Easting = 401230, Northing = 434300
    
This gives us the 7-digit numeric only values. The Convert class has a method to convert this to Lat/Long
values. Note that this will be in the Airy Datum (also known as OSGB Lat/Long) and a further ellipsoid
conversion will be necessary if comparing to GPS coordinates.

    $convert = new Academe\OsgbTools\Convert;
    $lat_long = $convert->osGridToLatLong($Easting, $Northing);
    var_dump($lat_long);
    // object(Academe\OsgbTools\Coordinate)#8 (2) { ["latitude":protected]=> float(53.805230674459) ["longitude":protected]=> float(-1.9810171150917) }
    
The same conversion can also be seen here: http://www.nearby.org.uk/coord.cgi?p=SE0123034300&f=full

Another tool to check: http://www.russ-hore.co.uk/osgb/ (paste in the OSGB reference "SE 01230 34300"). I suspect all the tools are suffering from rounding errors in different ways, or are maybe using different ellipsoids. They need
investigating to find *the right one*.

The conversion can be done in one step now:

    // Conert an OSGB NGR to lat/long object (\Academe\OsgbTools\Coordinate).
    $lat_long = Academe\OsgbTools\Convert::osGridToLatLong('SE 0120 3430');
    //
    // A Square or array can also be passed in, as well as the examples further back:
    $lat_log = Academe\OsgbTools\Convert::osGridToLatLong(array($easting, $northing));
    $lat_log = Academe\OsgbTools\Convert::osGridToLatLong($square);

Converting the opposite way can also be done. The Lat/Long values must be in the Airy datum, so convert
it from whatever datum is used if it is not.

    $convert = new Academe\OsgbTools\Convert;
    $square = $convert->latLongToOsGrid($latitude, $longitude);
    echo $square;
    // e.g. SE 01230 34300
    //
    // or
    $square = Academe\OsgbTools\Convert::latLongToOsGrid($latitude, $longitude);
    //
    $latitude = 53.804781271911;
    $longitude = -1.9813210410013;
    $lat_long = new Academe\OsgbTools\Coordinate(array($latitude, $longitude));
    $square = Academe\OsgbTools\Convert::latLongToOsGrid($lat_long);

Some terms: Geodetic (also known as geographic) coordinates consist of geodetic latitude, geodetic longitude,
and geodetic height. 
Geocentric coordinates are Cartesian coordinates (X, Y, Z) that define the position of a point with respect
to the center of mass of the earth.

Again to note: this will change, and is just a quick library put in to demonstrate (getting more stable though).

TODO
----

* Currently this supports the GB national grid only. The Irish grid is similar, and we should be
  able to extend the OSGB to cover the Irish grid too. The Irish grid uses just one 500km square,
  and that square is not listed in the references. Instead, just one letter is used to denote the
  100km square in the 5x5 grid of 100km squares.
* Conversions to other Datums (namely WGS84) including conversion to the appropriate ellipsoid.
  Some refactoring may be needed to keep the various concerns separated. It does not look like there
  are any composer dependencies we can pull in to *just* handle the conversions, so whether we
  create a separate library (based on other tried-and-tested libraries) or incorporate the code into
  this library, I'm not sure. Probably the latter, then we can fork it off later. The key here
  though is to keep the NGR (the square/location references) and the conversions to and from other
  coordinate systems, separate. This will aid testing and understanding.
  No other composer library I've found does this.

Useful Links
------------

* http://en.wikipedia.org/wiki/Ordnance_Survey_National_Grid  
  Wikipedia overview of the OS national grid

* http://www.movable-type.co.uk/scripts/latlong-convert-coords.html  
  JavaScript datum conversions (WGS84, GRS80, Airy1830, AiryModified, Intl1924, Bessel1841)

* http://www.movable-type.co.uk/scripts/latlong-gridref.html  
  JavaScript conversions (Lat/Long and OS Grid Reference). Good background explanations. Interesting that it
  provides the centres of OSGB squares when parsing grid references and not the SW corner. This will give
  very different results when using squares other then 1m (two letters and five digits) then my assumption.
  Some investigation is needed to see how everyone else handles this.

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

* http://www.georeference.org/doc/the_earth_as_an_ellipsoid.htm  
  This page has a great visualisation of what a datum is, and how ellipsoids relate to each
  other. Without going into any of the maths, it simply shows you what it looks like, and
  that gives a deeper understanding of what the maths needs to achieve. This library does not
  have any datum conversions built in yet, but it does need it - the Airy datum on its own
  is a but of an island when every system under the sun uses WGS84 as its reference datum.
  Using this library to locate points on the map will disagree with Google Maps, for example,
  by hundreds of metres in many places without the conversion.

Ideally, the OSGB (Northing/Easting + grids) would convert directly from/to WGS84 and not Airy.

A class to handle OSGB coordinates all all their variations, will be necessary.

