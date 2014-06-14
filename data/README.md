Datum
=====

Geodetic datums define the size and shape of the earth and the origin and orientation of the
coordinate systems used to map the earth.

Reference datums:

ED50 	European Datum 1950
SAD69 	South American Datum 1969
GRS80 	Geodetic Reference System 1980	http://en.wikipedia.org/wiki/GRS_80	http://www.gfy.ku.dk/~iag/HB2000/part4/grs80_corr.htm
NAD83 	North American Datum 1983	http://en.wikipedia.org/wiki/North_American_Datum#North_American_Datum_of_1983
WGS84 	World Geodetic System 1984	http://en.wikipedia.org/wiki/World_Geodetic_System
NAVD88 	N. American Vertical Datum 1988
ETRS89 	European Terrestrial Reference System 1989

For datum conversion parameters, see:
http://www.colorado.edu/geography/gcraft/notes/datum/edlist.html

Shifting between datums in cartesian (xyz) coordinates is simple:
http://www.colorado.edu/geography/gcraft/notes/datum/gif/geoconv.gif

Data Columns
------------

TODO: desacriptions

* Datum
* Ellipsoid Code
* Ellipsoid Name
* dX
* dY
* dZ
* Region of use
* eX
* eY
* eZ
* #S


Ellipsoids
==========

Values are:

* id - the unique code for the ellipsoid
* name - the human-readable name of the ellipsoid
* a - the semi-major access (metres)
* infF - 1/Flattening

The following digram shows what the ellipsoidal parameters represent:
http://www.colorado.edu/geography/gcraft/notes/datum/gif/ellipse.gif

A good reference ellipsoid list:

http://www.colorado.edu/geography/gcraft/notes/datum/elist.html

Missing ellipses from this data, or clarification needed on the names:

* Indonesian 1974
* Everest (India 1830)
* Everest (Malay. & Sing)
* Everest (Pakistan)
* Everest (Sabah Sarawak)
* International 1924
* Krassovsky 1940





