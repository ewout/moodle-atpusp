<?PHP  //$Id: version.php,v 1.1.1.1 2008/07/16 10:11:38 cvswriter Exp $
// This file defines the current version of the
// backup/restore code that is being used.  This can be
// compared against the values stored in the 
// database (backup_version) to determine whether upgrades should
// be performed (see db/backup_*.php)

$file_manager_version = 2008112400;   // The current version is a date (YYYYMMDDXX)

$file_manager_release = "Version 2.1";  // User-friendly version number
?>