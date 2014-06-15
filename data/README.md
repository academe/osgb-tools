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
set of datum shiting parameters that can be defined to get *closer* than not doing the
shift at all? For example, the countries of the UK all use their own reference origin, but the
same ellipsoid. There is a mean origin that can be used across all UK countries for conversion
from Airy 1830.

Data Columns
------------

These are columns in datum.csv

* datum name - the US name for the datum
* region of use - notes on which regions the datum covers
* ellipsoid code - the ISO/IEC 18026 code for the ellipsoid
* dx - transform delta in metres
* dy - transform delta in metres
* dz - transform delta in metres
* ex - error estimate in metres
* ey - error estimate in metres
* ez - error estimate in metres
* sat - number of salettite measurement stations

File is comma-separated, non-quoted (no commas in any data), ASCII.
The first row contain column headings, in lower-case, with a space between words.

The transform delta values are the number of metres that must be added to a local cartesian
ordinate to shift to the WGS-84 datum.

Ellipsoids
==========

The following digram shows what the ellipsoidal parameters represent.
Ity helps to put the raw figures into context:
http://www.colorado.edu/geography/gcraft/notes/datum/gif/ellipse.gif

Most of the datums and ellipsoids have come from here:
http://www.colorado.edu/geography/gcraft/notes/datum/elist.html

Data Columns
------------

These are columns in ellipsoid.csv

* code - the ISO/IEC 18026 code for the ellipsoid
* league code - the league/geotools code for the ellipsoid
* name - the human-readable name of the ellipsoid
* a - the semi-major access (metres)
* invf - 1/flattening

ARINC Datum Codes
=================

In an attempt to gather standard codes for referencing datums, the list of datums and codes
from the ARINC 424-18 (from 2007) specification has been collected. These are codes used for international
air travel. It is probably the only internationally accepted codes that exist, since air travel
is the only activity that needs to work with varying land-based geodfesic datums. It makes
sense to adopt these codes rather than make up our own.

Also worth looking at are EPSH codes - http://spatialreference.org/ref/epsg/ - though I think they may be
more related to map projections in various zones, but may be useful as an unambiguous authoritive reference
for datums, elliposoids etc.

