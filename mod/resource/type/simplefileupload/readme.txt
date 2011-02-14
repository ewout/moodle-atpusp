********************
* SimpleFileUpload *
********************

Authors:    John Ennew, Steve Coppin; 

Contact: John Ennew, J.Ennew@kent.ac.uk
University of Kent
Canterbury
http://www.kent.ac.uk/

Description:
SimpleFileUpload provides a simpler mechanism for adding file resources to a Moodle Course and link them on the course page than the standard Moodle mechanism. The Name field is auto filled based on the filename. There is a JS mechanism to quickly add another file to the same section without clicking through the Moodle forms again.

Installation:
1. Copy simplefileupload directory to [moodle]/mod/resource/type/

2. Add the following ...
$string['resourcetypesimplefileupload'] = 'Simple File Upload';

.. to your resource.php language file. e.g. [moodle]/lang/en_utf8/resource.php

Tested working with 1.9.7, 1.9.8, 1.9.9 and 1.9.10
