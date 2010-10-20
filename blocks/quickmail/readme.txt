Quickmail v2.6 (designed for larger classes) README
Document created by Wen Hao Chuang (Email: whchuang@gmail.com)
Updated on June 26, 2009 (reverted back to v2.6 release)

This version of quickmail is mostly based on Michael Penny and Mark Nielsen's previous work - quickmail.
This particular version is designed for 1.9.x. For codes that would work with earlier version of Moodle, please contact with me.

Please file your bug report directly to whchuang@gmail.com. Thanks!

Please note, similar to the original quickmail, this block DOES require Javascript be ENABLED in your Web browser. Please check with your Web browser setting (in FireFox, go to Tools -> Options -> Content -> Enable JavaScript) to make sure this block would work correctly. Thanks! 

[Why this block?]:
    The original quickmail Graphics User Interface (GUI) does not
    work with larger classes. We hacked the GUI a little bit so that it
    becomes more user-friendly for larger classes."

Here are a list of files that were modified from the original quickmail block:

Added: selection.js (a javascript file that defines functions used by email.html) and /lang/help
Modified: email.html, email.php, config_instance.html, block_quickmail.php (minor modification)
Same: styles.php, \db\, and most part of \lang folder

Added/Removed features:
=======================
1. We removed the "Email history" feature but added a feature that instructors could use their
   external email client (e.g. Outlook, Eudora, thunderbird) for quickmail. The email receipients will be
   included in the BCC field, and the instructor's email will be in the To field. As we have many
   quickmail users we don't want to use our precious server space to keep all these logs (it can
   really add up if users use it very often with attachments, etc.)
2. We added a HELP button for this block, to enhance usability. This was based on our instructors'
   request as they were not quite sure what quickmail could do for them. We also added a help
   button to explain how they could use external email client to keep track of their quickmail
   history
3. We temporarily removed the "Group mode" settings, although the codes are still included in
   this release. You just have to remove the comments of the codes to enable this feature.
   However, we haven't been tested the Group mode in Moodle 1.8 yet. (Group codes are still not
   very stable in Moodle 1.8 yet).

How to install:
===============
1. Unzip the quickmailv2.zip into your moodle "blocks" folder, create a folder called quickmail
   and put all your files in that folder
2. Move the block_quickmail.php included in \lang\en to your language folder (e.g. \lang\en_utf8).
   If you are using other languages you probably need a language package for proper translation.
   By the way, you might have some luck to get one from the previous quickmail v1. See:
   http://moodle.org/mod/data/view.php?d=13&rid=92
3. Put the \lang\en\help\*.html into the \lang\en\help (or \lang\en_utf8\help for Moodle 1.6 or above) 
   in your moodle installation.
   These are the help button content of the quickmail block.
   You could modify the content based on your needs.

How to uninstall:
=================
1. Simply remove the block\quickmail folder and related quickmail database tables and that's it!

Notes:
======
1. Currently we disabled the group feature, as this is still under testing now. If you are interested to help out with testing or rewriting, please contact with me (Wen).
2. There are two files that has identical filename: block_quickmail.php. One should be put under\lang\en, the other under \blocks\quickmail. These two files should not be confused.
3. Unless you know how to tweak the codes, otherwise we don't recommend installing two versions of quickmails on the same moodle installation.
4. You could remove the 
5. If you created a language pack that you would like me to include for the next release please send me the files, thanks!
6. The implementation of the database part (table) is exactly the same with previous Quickmail v1.
7. If you would like to have different view for the list of "Potential Recipient(s)," you could
   hack around line#88 in email.html code. Thanks for Art Lader contribute this quick question.

We hope that you will enjoy using this block. Thanks!

The newest version could be downloaded from:
http://moodle.org/mod/data/view.php?d=13&rid=764

Wen