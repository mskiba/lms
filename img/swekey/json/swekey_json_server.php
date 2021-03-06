<?php

/*
 * LMS version 1.11-git
 *
 *  (C) Copyright 2001-2013 LMS Developers
 *
 *  Please, see the doc/AUTHORS for more information about authors!
 *
 *  This program is free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License Version 2 as
 *  published by the Free Software Foundation.
 *
 *  This program is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  You should have received a copy of the GNU General Public License
 *  along with this program; if not, write to the Free Software
 *  Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA 02111-1307, 
 *  USA.
 *
 */

if (empty($_POST)) {
	echo "This is the swekey JSON server.<br />It should be called using a http POST request.";
	exit;
}

$CONFIG_FILE = '/etc/lms/lms.ini';

$CONFIG = (array) parse_ini_file($CONFIG_FILE, true);

// Check for configuration vars and set default values
$CONFIG['directories']['sys_dir'] = (!isset($CONFIG['directories']['sys_dir']) ? getcwd() : $CONFIG['directories']['sys_dir']);
$CONFIG['directories']['lib_dir'] = (!isset($CONFIG['directories']['lib_dir']) ? $CONFIG['directories']['sys_dir'] . '/lib' : $CONFIG['directories']['lib_dir']);
$CONFIG['directories']['modules_dir'] = (!isset($CONFIG['directories']['modules_dir']) ? $CONFIG['directories']['sys_dir'] . '/modules' : $CONFIG['directories']['modules_dir']);

define('SYS_DIR', $CONFIG['directories']['sys_dir']);
define('LIB_DIR', $CONFIG['directories']['lib_dir']);
define('MODULES_DIR', $CONFIG['directories']['modules_dir']);

// Init database
$_DBTYPE = $CONFIG['database']['type'];
$_DBHOST = $CONFIG['database']['host'];
$_DBUSER = $CONFIG['database']['user'];
$_DBPASS = $CONFIG['database']['password'];
$_DBNAME = $CONFIG['database']['database'];
$_DBDEBUG = (isset($CONFIG['database']['debug']) ? chkconfig($CONFIG['database']['debug']) : false);

require(LIB_DIR . '/LMSDB.php');

$DB = DBInit($_DBTYPE, $_DBHOST, $_DBUSER, $_DBPASS, $_DBNAME, $_DBDEBUG);

if (!$DB) {
	die();
}

// Include required files
require_once(LIB_DIR . '/language.php');
require_once(LIB_DIR . '/LMS.class.php');
require_once(LIB_DIR . '/Auth.class.php');
require_once(LIB_DIR . '/Session.class.php');

// Initialize Session, Auth and LMS classes
$SESSION = new Session($DB, $CONFIG['phpui']['timeout']);
$AUTH = new Auth($DB, $SESSION);
$LMS = new LMS($DB, $AUTH, $CONFIG);
$LMS->lang = $_language;

// Initialize Swekey class
require_once(LIB_DIR . '/swekey/lms_integration.php');

if (session_id() == '')
	session_start();

$JSON_SWEKEY = new LmsSwekeyIntegration($DB, $AUTH, $LMS);
$result = $JSON_SWEKEY->AjaxHandler($_POST);

echo json_encode($result);
exit;

?>
