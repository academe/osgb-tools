osgb-tools
==========

Ordnance Survey (GB) grid reference conversion tools.

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

