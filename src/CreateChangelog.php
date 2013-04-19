<?php
include_once dirname(__FILE__) . '/SilverStripeBuildTask.php';

/**
 * Returns combined changelogs for specified git repositories.
 *
 * @author jseide
 *
 */
class CreateChangelog extends SilverStripeBuildTask {

	protected $definitions = null;
	protected $baseDir = null;
	protected $sort = 'type';
	protected $filter = null;
	
	/**
	 * Order of the array keys determines order of the lists.
	 */
	public $types = array(
		'API Changes' => array('/^(APICHANGE|API-CHANGE|API CHANGE|API)\s?:?/i'),
		'Features and Enhancements' => array('/^(ENHANCEMENT|ENHNACEMENT|FEATURE|NEW)\s?:?/i'),
		'Bugfixes' => array('/^(BUGFIX|BUGFUX|BUG|FIX)\s?:?/','/^(BUG FIX)\s?:?/'),
		// 'Other' => array('/^(MINOR)\s?:?/i')
	);
	
	public $commitUrls = array(
		'.' => 'https://github.com/silverstripe/silverstripe-installer/commit/%s',
		'framework' => 'https://github.com/silverstripe/sapphire/commit/%s',
		'cms' => 'https://github.com/silverstripe/silverstripe-cms/commit/%s',
	);
	
	public $ignoreRules = array(
		'/^Merge/',
		'/^Blocked revisions/',
		'/^Initialized merge tracking /',
		'/^Created (branches|tags)/',
		'/^NOTFORMERGE/',
		'/^\s*$/'
	);

	public $paths = array(
		'.',
		'framework',
		'cms'
	);

	public $fromCommit;

	public $toCommit;

	public function setDefinitions($definitions) {
		$this->definitions  = $definitions;
	}

	public function setBaseDir($base) {
		$this->baseDir = realpath($base);
	}

	public function setSort($sort) {
		$this->sort = $sort;
	}

	public function setFilter($filter) {
		$this->filter = $filter;
	}

	public function setPaths($paths) {
		$this->paths = is_array($paths) ? $paths : explode(',', $paths);
	}

	public function setFromCommit($commit) {
		$this->fromCommit = $commit;
	}

	public function setToCommit($commit) {
		$this->toCommit = $commit;
	}

	protected function gitLog($path, $from = null, $to = null) {
		//set the from -> to range, depending on which os these have been set
		if ($from && $to) $range = " $from..$to";
		elseif ($from) $range = " $from..HEAD";
		else $range = "";

		$this->log(sprintf('Changing to directory "%s"', $path), Project::MSG_INFO);

		chdir("$this->baseDir/$path");  //switch to the module's path

		// Internal serialization format, ideally this would be JSON but we can't escape characters in git logs.
		$log = $this->exec("git log --pretty=tformat:\"message:%s|||author:%aN|||abbrevhash:%h|||hash:%H|||date:%ad|||timestamp:%at\" --date=short {$range}", true);

		chdir($this->baseDir);  //switch the working directory back

		return $log;
	}
		
	/** Sort by the first two letters of the commit string.
	 *  Put any commits without BUGFIX, ENHANCEMENT, etc. at the end of the list
	 */
	function sortByType($commits) {
		$groupedByType = array();
		
		// sort by timestamp
		usort($commits, function($a, $b) {
			if($a['timestamp'] == $b['timestamp']) return 0;
			else return ($a['timestamp'] > $b['timestamp']) ? -1 : 1;
		});

		foreach($commits as $k => $commit) {
			// TODO
			// skip ignored revisions
			// if(in_array($commit['changeset'], $this->ignorerevisions)) continue;
			
			// Remove email addresses
			$commit['message'] = preg_replace('/(<?[A-Z0-9._%+-]+@[A-Z0-9.-]+\.[A-Z]{2,4}>?)/mi', '', $commit['message']);
			
			// Condense git-style "From:" messages (remove preceding newline)
			if(preg_match('/^From\:/mi', $commit['message'])) {
				$commit['message'] = preg_replace('/\n\n^(From\:)/mi', ' $1', $commit['message']);
			}
			
			$matched = false;
			foreach($this->types as $name => $rules) {
				if(!isset($groupedByType[$name])) $groupedByType[$name] = array();
				foreach($rules as $rule) {
					if(!$matched && preg_match($rule, $commit['message'], $matches)) {
						// @todo The fallback rule on other can't be replaced, as it doesn't match a full prefix
						$commit['message'] = trim(preg_replace($rule, '', $commit['message']));
						$groupedByType[$name][] = $commit;
						$matched = true;
					}
				}
			}

			// Don't show others, changelog gets too long
			// if(!$matched) {
			// 	if(!isset($groupedByType['Other'])) $groupedByType['Other'] = array();
			// 	$groupedByType['Other'][] = $commit;
			// }
			
		}
		
		// // remove all categories which should be ignored
		// if($this->categoryIgnore) foreach($this->categoryIgnore as $categoryIgnore) {
		// 	if(isset($groupedByType[$categoryIgnore])) unset($groupedByType[$categoryIgnore]);
		// }

		return $groupedByType;
	}
	
	function commitToArray($commit) {
		$arr = array();
		$parts = explode('|||', $commit);
		foreach($parts as $part) {
			preg_match('/([^:]*)\:(.*)/', $part, $matches);
			$arr[$matches[1]] = $matches[2];
		}
		
		return $arr;
	}

	static function isupper($i) {
		return (strtoupper($i) === $i);
	}
	static function islower($i) {
		return (strtolower($i) === $i);
	}

	public function main() {
		error_reporting(E_ALL);
		
		chdir($this->baseDir);  //change current working directory

		//run git log
		$log = array();
		foreach($this->paths as $path) {
			$logForPath = array();
			$logForPath = explode("\n", $this->gitLog($path, $this->fromCommit, $this->toCommit));
			foreach($logForPath as $commit) {
				if(!$commit) continue;
				$commitArr = $this->commitToArray($commit);
				$commitArr['path'] = $path;
				// Avoid duplicates by keying on hash
				$log[$commitArr['hash']] = $commitArr;
			}
		}

		// Remove ignored commits
		foreach($log as $k => $commit) {
			$ignore = false;
			foreach($this->ignoreRules as $ignoreRule) {
				if(preg_match($ignoreRule, $commit['message'])) {
					unset($log[$k]);
					continue;
				}
			}
		}

		//sort the output (based on params), grouping
		if ($this->sort == 'type') {
			$groupedLog = $this->sortByType($log);
		} else {
			//leave as sorted by default order
			$groupedLog = array('All' => $log);
		}

		//filter out commits we don't want
		// if ($this->filter) {
		// 	foreach($groupedLog as $key => $item) {
		// 		if (preg_match($this->filter, $item)) unset($groupedLog[$key]);
		// 	}
		// }

		//convert to string
		//and generate markdown (add list to beginning of each item)
		$output = "\n";
		foreach($groupedLog as $groupName => $commits) {
			if(!$commits) continue;
			
			$output .= "\n### $groupName\n\n";
			
			foreach($commits as $commit) {
				if(isset($this->commitUrls[$commit['path']])) {
					$hash = sprintf('[%s](%s)', 
						$commit['abbrevhash'],
						sprintf($this->commitUrls[$commit['path']], $commit['abbrevhash'])
					);
				} else {
					$hash = sprintf('[%s]', $commit['abbrevhash']);
				}
				$commitStr = sprintf('%s %s %s (%s)',
					$commit['date'],
					$hash,
					// Avoid rendering HTML in markdown
					str_replace(array('<', '>'), array('&lt;', '&gt;'), $commit['message']),
					$commit['author']
				);
				// $commitStr = sprintf($this->exec("git log --pretty=tformat:\"%s\" --date=short {$hash}^..{$hash}", true), $this->gitLogFormat);
				$output .= " * $commitStr\n";
			}
		}

		$this->project->setProperty('changelogOutput',$output);
	}
}

?>
