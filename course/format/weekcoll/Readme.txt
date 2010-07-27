$Id: Readme.txt,v 1.1.4.7 2010/04/10 18:33:37 gb2048 Exp $

Introduction
------------

Week based course format with an individual 'toggle' for each week except 0.  The current week is always shown.

Documented on http://docs.moodle.org/en/Collapsed_Weeks_course_format

Installation
------------
1. Copy 'weekcoll' to /course/formats/
2. If using a Unix based system, chmod 755 on config.php - I have not tested this but have been told that it needs to be done.
3. If desired, edit the colours of the weeks_collapsed.css - which contains instructions on how to have per theme colours.
4. To change the arrow graphic you need to replace arrow_up.png and arrow_down.png.  Reuse the graphics
   if you want.  Created in Paint.Net.

Upgrade Instructions
--------------------
1. Put Moodle in Maintenance Mode so that there are no users using it bar you as the adminstrator.
2. In /course/formats/ move old 'weekcoll' directory to a backup folder outside of Moodle.
3. Follow installation instructions above.
4. Put Moodle out of Maintenance Mode.

References
----------
.Net Magazine Issue 186 - Article on Collapsed Tables by Craig Grannell -
 http://www.netmag.co.uk/zine/latest-issue/issue-186

Craig Grannell - http://www.snubcommunications.com/

Accordion Format - Initiated the thought - http://moodle.org/mod/forum/discuss.php?d=44773 & 
                                           http://www.moodleman.net/archives/47

Paint.Net - http://www.getpaint.net/

JavaScript: The Definitive Guide - David Flanagan - O'Reilly - ISBN: 978-0-596-10199-2

Moodle Tracker - http://tracker.moodle.org/

Version Information
-------------------
14th September 2009 - Version 1 - Moodle Tracker CONTRIB-1562
  Based upon version 1.3.2 of topcoll.
  Please see the documentation on http://docs.moodle.org/en/Collapsed_Topics_course_format
  
23rd January 2010 - Version 1.1 - Moodle Tracker CONTRIB-1756
  1. Put instructions in the CSS file 'weeks_collapsed.css' on how you can define theme based toggle colours.
  2. Redesigned the arrow to be more 'modern'.  
  
16th February 2010 - Version 1.2 - Moodle Tracker CONTRIB-1825
  1. Removed the capability to 'Show week x' unless editing as confusing to users.
  2. Removed redundant 'aToggle' as existing $course->numsections already contained the correct figure
     and counting toggles that are displayed causes an issue when in 'Show week x' mode as the toggle
	 number does not match the display number for the specific element.
  3. Removed redundant calls to 'get_context_instance(CONTEXT_COURSE, $course->id)' as result already
     stored in $context variable towards the top - so use in more places.

5th April 2010 - Version 1.2.1 - Moodle Tracker CONTRIB-1952 & CONTRIB-1954
  1. CONTRIB-1952 - Having an apostrophy in the site shortname causes the format to fail.
  2. CONTRIB-1954 - Reloading of the toggles by using JavaScript DOM events not working for the function reload_toggles,
     but instead the function was being called at the end of the page regardless of the readiness state of the DOM.  	 

9th April 2010 - Version 1.2.2 - Moodle Tracker CONTRIB-1973
  1. Tidied up format.php, made the fetching of week and toggle names more efficient and sorted a missing echo statement.
  2. Tidied up this file.
  
Thanks
------
I would like to thank Anthony Borrow - arborrow@jesuits.net & anthony@moodle.org - for his invaluable input.

For the Peristence upgrade I would like to thank all those who contributed to the developer forum -
http://moodle.org/mod/forum/discuss.php?d=124264 - Frank Ralf, Matt Gibson, Howard Miller and Tim Hunt.  And
indeed all those who have worked on the developer documentation - http://docs.moodle.org/en/Javascript_FAQ.

Michael de Raadt for CONTRIB-1945 & 1946 which sparked fixes in CONTRIB-1952 & CONTRIB-1954

Desired Enhancements
--------------------

1. Smoother animated toggle action.
2. Peristence beyond the session.
3. Moving 'window' date range functionality where the course shows only the weeks within a number of weeks before and
   after the current.

G J Barnard - BSc(Hons)(Sndw), MBCS, CEng, CITP, PGCE - 10th April 2010