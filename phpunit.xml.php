<?php 

$phpsupportdir = $argv[1];
$phpunitver = $argv[2];

$bootstrap = file_exists('sapphire/core/model/DB.php') ? 'bootstrap.php' : 'bootstrap3.php';

$excludes = array_merge(array('silverstripe-cache', 'sapphire/dev'), array_slice($argv, 3));

$xml = simplexml_load_string(
'<'.'?xml version="1.0" encoding="UTF-8" ?'.">
<phpunit backupGlobals='false'
        backupStaticAttributes='false'
        bootstrap='{$phpsupportdir}/{$bootstrap}'
        colors='false'
        convertErrorsToExceptions='true'
        convertNoticesToExceptions='true'
        convertWarningsToExceptions='true'
        processIsolation='false'
        stopOnError='false'
        stopOnFailure='false'
        stopOnIncomplete='false'
        stopOnSkipped='false'
        syntaxCheck='false'
        verbose='true'
        strict='true'>

	<testsuites></testsuites>

	<filter>
		<whitelist>
			<exclude></exclude>
		</whitelist>
	</filter>

	<listeners>
		<listener class='TeamCityListener' file='{$phpsupportdir}/TeamCityListener.php' />
		<listener class='SilverStripeListener' file='{$phpsupportdir}/SilverStripeListener.php' />
	</listeners>

</phpunit>
");

/**
 * Is the given path excluded from the test suite?
 * @param string $path - The path to test
 * @return bool - True if the path should be excluded
 */
function excluded($path) {
	global $excludes;
	
	if (strpos($path, '.') === 0) return true;
	
	foreach ($excludes as $exclude) {
		if (strpos($path, $exclude) !== false) return true;
	}
	return false;
}

/**
 * Given some path, find any test php files in that path (recursively)
 * @param string $path - The path to check in
 * @param SimpleXMLElement $node - The testsuites node to add elements to
 * @return null
 */
function buildtests($path, $node) {
	$dir = dir($path);

	$tests = array();
	$subdirs = array();

	// First step through all entries of this path, checking for subdirectories, tests, excluded files and _manifest_exclude
	while ($entry = $dir->read()) {
		$sub = $path . '/' . $entry;
		
		if ($entry == '_manifest_exclude') return;
		if (excluded($entry) || excluded($sub)) continue;
		
		if (is_dir($sub)) $subdirs[] = $sub;
		if (preg_match('/.*Test\.php/', $entry)) $tests[] = $sub;
	}

	// If we got some tests, add a node
	if ($tests) {
		$suite = strtr($path, '/', '.');

		$testsuite = $node->addChild('testsuite');
		$testsuite->addAttribute('name', $suite);

		foreach ($tests as $test) $testsuite->addChild('file', $test);
	}
	
	foreach ($subdirs as $subdir) buildtests($subdir, $node);
}

/**
 * Given some path, find any files that need to be excluded from code coverage (recursively)
 * @param string $path - The path to check in
 * @param SimpleXMLElement $node - The exclude node to add elements to
 * @return null
 */
function buildexcludes($path, $node) {
	$dir = dir($path);

	$subdirs = array();

	while ($entry = $dir->read()) {
		if (strpos($entry, '.') === 0) continue;

		$sub = $path . '/' . $entry;

		if ($entry == '_manifest_exclude') {
			$exclude = $node->addChild('directory', $path);
			$exclude->addAttribute('suffix', '.php');
			return;
		}

		if (excluded($entry) || excluded($sub) || $entry == 'tests' || $entry == '_config.php' || $entry == 'cli-script.php' || preg_match('/main.php$/', $entry) || $entry == 'bootstrap.php') {
			if (is_dir($sub)) {
				$exclude = $node->addChild('directory', $sub);
				$exclude->addAttribute('suffix', '.php');
			}
			else {
				$exclude = $node->addChild('file', $sub);
			}
		}

		else if (is_dir($sub)) $subdirs[] = $sub;
	}

	foreach ($subdirs as $subdir) buildexcludes($subdir, $node);
}

/**
 * Build a list of the "roots" (directories immediately below the top level of the project)
 */

$root = dir(getcwd());
$roots = array();

while ($entry = $root->read()) {
	if (is_dir($entry) && !excluded($entry)) $roots[] = $entry;
}

/**
 * For each root, add the root's testsuites, add root to the whitelist if it's a module, then build the excludes
 */

foreach ($roots as $root) {
	buildtests($root, $xml->testsuites);

	if (file_exists($root.'/_config.php')) {
		$dir = $xml->filter->whitelist->addChild('directory', $root);
		$dir->addAttribute('suffix', '.php');

		buildexcludes($root, $xml->filter->whitelist->exclude);
	}
}

/**
 * And done
 */

$dom = new DOMDocument('1.0');
$dom->preserveWhiteSpace = false;
$dom->formatOutput = true;
$dom->loadXML($xml->asXML());
echo $dom->saveXML();
