Datum
=====

Geodetic datums define the size and shape of the earth and the origin and orientation of the
coordinate systems used to map the earth.

Each datum defines an ellipsis, and a centre origin. The centres shift over time, party due to
plate tectonics, and party through new techniques for more accurate measurements. However, we
will not need an accuracy that high for general cartology, so these figures are close enough.

The transformation parameters cover local to WGS84 conversion. Add the offsets to the local xyz
coordinates, to convert to WGS84. Subtract them to convert from WGS84 to local.

The source of the transformation (aka shifting) parameters are:

* NIMA 8350.2 4 July 1977
* MADTRAN 1 October 1996

And are listed here:
http://www.colorado.edu/geography/gcraft/notes/datum/edlist.html

The table shows that each datum is further devided into regions, with means that cover
groups of those regions. When a datum is assighned to a coordinate, the regions of that
datum must be taken into account.

TODO: denormalise the datums table into master-detail datum+regional variation.

No datum is defined by more than one ellipsoid.

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

Q: For conversion, if we don't know the datum, but do know the ellipsoid, is there a mean
set of datum shitfing parameters that can be defined to get *closer* than not doing the
shift at all? For example, the countries of the UK all use their own reference origin, but the
same ellipsis. There is a mean originin that can be used across all UK countries for conversion
from Airy 1830.

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

* Indonesian 1974 a=6378388 b=6356911.946 invF=297
* Everest (India 1830) a=6377276.345 b=6356075.413 invf=300.8017
* Everest (Malay. & Sing 1948) a=6377304.063 b=6356103.039 invF=300.8017
* Everest (Pakistan) a=6377309.613 b=6356109.571 invF=300.8017
* Everest (Sabah & Sarawak) a=6377298.556 b=6356097.550 invF=300.8017
* International 1924 a=6378388 b=6356911.946 invF=297
* Krassovsky 1940 a=6378245 b=6356863.019 invF=298.3

