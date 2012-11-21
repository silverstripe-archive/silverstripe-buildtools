<?php

/**
 * Scans a folder and its subfolders and returns all the git repositories contained within
 * @author jseide
 *
 */
class FindRepositoriesTask extends Task {

	private $targetDir = null;
	private $includeTarget = true;


	public function setTargetDir($targetDir) {
		$this->targetDir = $targetDir;
	}

	public function setIncludeTarget($includeTarget) {
		$this->includeTarget = $includeTarget;
	}

	/**
	 * Recursively lists a folder and includes only those directories that have the filter parameter as a sub-item
	 */
	protected function recursiveListDirFilter($dir, &$result, $filter = '.git') {
		$dir = realpath($dir);

		// open this directory
		if ($handle = opendir($dir)) {

			// get each git entry
			while (false !== ($file = readdir($handle))) {
				if ($file == "." || $file == "..") continue;
				if ($file == '.git' && is_dir($file))  {
					if (
						// valid git repo?
						file_exists($dir.'/'.$file.'/HEAD') 
						// ... and contains a _config.php (SS module) or index.php (weak indicator for installer)
						&& (file_exists($dir.'/_config.php') || file_exists($dir.'/index.php'))
					) {
						$result[] = $dir;
					}
				} else {
					$path = $dir.'/'.$file;
					if (is_dir($path)) {
						$this->recursiveListDirFilter($path, $result, $filter);
					}
				}
			}
		}

		// close directory
		closedir($handle);

		return $result;
	}

	public function main() {
		if (!is_dir($this->targetDir)) {
			throw new BuildException("Invalid target directory: $this->targetDir");
		}

		$gitDirs = array();
		$this->recursiveListDirFilter($this->targetDir, $gitDirs, '.git');

		$gitDirsOutput = array();
		if (!$this->includeTarget) { //don't include the target dir
			foreach($gitDirs as $dir) {
				if ($dir != $this->targetDir && $dir != realpath($this->targetDir)) {
					$gitDirsOutput[] = $dir;
				}
			}
		} else {
			$gitDirsOutput = $gitDirs;
		}

		$this->project->setProperty('GitReposList',implode(',',$gitDirsOutput));
	}
}

?>