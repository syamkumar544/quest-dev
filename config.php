<?php  // Moodle configuration file

unset($CFG);
global $CFG;
$CFG = new stdClass();

$CFG->dbtype    = 'mysqli';
$CFG->dblibrary = 'native';
$CFG->dbhost    = '10.11.1.22';
$CFG->dbname    = 'questlms_dev';
$CFG->dbuser    = 'questlms_dev';
$CFG->dbpass    = 'QuEsT#LMS#dev0843';
$CFG->prefix    = 'mdl_';
$CFG->dboptions = array (
  'dbpersist' => 0,
  'dbsocket' => 0,
);

$CFG->wwwroot   = 'http://10.11.1.32:8107';
$CFG->dataroot  = '/var/www/md/quest_moodledata';
$CFG->admin     = 'admin';
/*
@error_reporting(E_ALL | E_STRICT); // NOT FOR PRODUCTION SERVERS!
 @ini_set('display_errors', '1');    // NOT FOR PRODUCTION SERVERS!
 $CFG->debug = (E_ALL | E_STRICT);   // === DEBUG_DEVELOPER - NOT FOR PRODUCTION SERVERS!
 $CFG->debugdisplay = 1;  
*/
$CFG->directorypermissions = 0777;

$CFG->passwordsaltmain = 'POmj+oii;Z1vRzyHZui,ykhdNn<;Zlja';

require_once(dirname(__FILE__) . '/lib/setup.php');

// There is no php closing tag in this file,
// it is intentional because it prevents trailing whitespace problems!

