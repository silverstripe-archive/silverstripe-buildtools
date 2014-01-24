# Phing Buildtools for the SilverStripe Project

## Overview

The goal of this project is to aid SilverStripe developers in automating various aspects of their build
through [phing](http://phing.info). The tools are designed to run within a SilverStripe project,
in the `buildtools/` subfolder.

It is primarily geared towards core developers preparing a SilverStripe release, 
but can also be a starting point for custom build setups.

Notable features:

 * Creating and uploading release archives
 * Multi-module tagging and checkout of tags and branches
 * Multi-module changelogs with automatic sorting by "commit tags"

The phing targets are generally compatible with SilverStripe 2.4 and newer.

## Installation

First of all, get started with phing:

	composer config --global repositories.pear pear http://pear.php.net
	composer global require phing/phing:2.4.*
	composer global require pear-pear.php.net/Pear:*
	composer global require pear-pear.php.net/Archive_Tar:*
	composer global require pear-pear.php.net/VersionControl_Git:*

Ensure the global composer binaries are set in your `$PATH`:

	echo -e 'export PATH=$PATH:~/.composer/vendor/bin' >> ~/.bash_profile

Then install the project via [composer](http://getcomposer.org). 

	cd my-silverstripe-webroot/
	composer require silverstripe/buildtools:*

In case you're not using the tools from a [silverstripe-installer](https://github.com/silverstripe/silverstripe-installer) based webroot, you might need to add a `build.xml` into the webroot to auto-import `buildtools/build.xml`:

```xml
<?xml version="1.0" encoding="UTF-8"?>
<project name="my-project" default="help" phingVersion="2.4.5">
	<import file="buildtools/build.xml" optional="true" />
	<property name="basedir" value="." override="true" />
	<target name="help">
		<phingcall target="buildtools.help" />
	</target>
</project>
```

## Usage

Run `phing -l` to see a full list of available targets,
and `phing help` for in-depth help.

The most important command is `phing release`. It roughly takes the following steps:

 * Checks out the base release branch (e.g. `3.1`) for core modules and the installer
 * Ensures no local changes are present
 * Writes a combined changelog from core modules, and pushes the committed Markdown file
 * Tags core modules and pushes those tags
 * Temporarily overwrites the `composer.json` version constraints with the new tag,
   and generates a `composer.lock` file by running `composer update`
 * Pushes the `composer.lock` file, tags the release, and removes it again (it should just exist in the tag)
 * Creates archives (separately for cms+framework and standalone framework)
 * Uploads archives to `silverstripe.org`
 * Checks out the base release branch again

**Caution:** The task uses a lot of `--force` in its git commands, for example
overwriting existing tags. It is your responsibility to ensure tag overwrites
should indeed take place. While you can generally abort and restart the release task,
it is recommended to start with a fresh composer project each time you run it.

## License ##

	Copyright (c) 2007-2014, SilverStripe Limited - www.silverstripe.com
	All rights reserved.

	Redistribution and use in source and binary forms, with or without modification, are permitted provided that the following conditions are met:

	    * Redistributions of source code must retain the above copyright notice, this list of conditions and the following disclaimer.
	    * Redistributions in binary form must reproduce the above copyright notice, this list of conditions and the following disclaimer in the 
	      documentation and/or other materials provided with the distribution.
	    * Neither the name of SilverStripe nor the names of its contributors may be used to endorse or promote products derived from this software 
	      without specific prior written permission.

	THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE 
	IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT OWNER OR CONTRIBUTORS BE 
	LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE 
	GOODS OR SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, 
	STRICT LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY 
	OF SUCH DAMAGE.
