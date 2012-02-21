<?php

/** 
This bootstraps the SilverStripe system so that phpunit can be run directly on SilverStripe tests

This is similar to the bootstrap.php that comes with recent versions of SilverStripe 2.4, but adds backwards compatibility
for older versions of SilverStripe (not yet tested how old)
*/

// Make sure display_errors is on
ini_set('display_errors', 1);

// Fake the script name and base
global $_SERVER;
if (!$_SERVER) $_SERVER = array();
$_SERVER['SCRIPT_FILENAME'] = getcwd() . DIRECTORY_SEPARATOR . 'sapphire' . DIRECTORY_SEPARATOR . 'cli-script.php';

// Prepare manifest autoloader
function silverstripe_test_autoload($className) {
	global $_CLASS_MANIFEST;
	$lClassName = strtolower($className);
	if(isset($_CLASS_MANIFEST[$lClassName])) include_once($_CLASS_MANIFEST[$lClassName]);
	else if(isset($_CLASS_MANIFEST[$className])) include_once($_CLASS_MANIFEST[$className]);
}
spl_autoload_register('silverstripe_test_autoload');

require_once(getcwd()."/sapphire/core/Core.php");

// Now set a fake REQUEST_URI
$_SERVER['REQUEST_URI'] = BASE_URL . '/dev/tests/all';

// Fake a session 
$_SESSION = null;

// Fake a current controller. Way harder than it should be
class FakeController extends Controller {
	
	function __construct() {
		parent::__construct();

		$session = new Session(isset($_SESSION) ? $_SESSION : null);
		$this->setSession($session);
		
		$this->pushCurrent();

		$this->request = new SS_HTTPRequest(
			(isset($_SERVER['X-HTTP-Method-Override'])) ? $_SERVER['X-HTTP-Method-Override'] : $_SERVER['REQUEST_METHOD'],
			'/'
		);

		$this->response = new SS_HTTPResponse();
		
		$this->init();
	}
}

global $_ALL_CLASSES;
$_ALL_CLASSES['parents']['FakeController'] = array_merge($_ALL_CLASSES['parents']['Controller'], array('Controller' => 'Controller'));

$controller = new FakeController();

// Connect to database
global $databaseConfig;

require_once(getcwd()."/sapphire/core/model/DB.php");
DB::connect($databaseConfig);

// Get test manifest
ManifestBuilder::load_test_manifest();


