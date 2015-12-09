# Phing Buildtools for the SilverStripe Project

**DEPRECATED These tools have been replaced by the [cow release tool](https://github.com/silverstripe/cow)**

## Overview

The goal of this project is to aid SilverStripe developers in automating various aspects of their build
through [phing](http://phing.info). The tools are designed to run as a standalone package, pulling the
modules to be packaged from Packagist.

It is primarily geared towards core developers preparing a SilverStripe release, 
but can also be a starting point for custom build setups.

Notable features:

 * Creating and uploading release archives
 * Multi-module tagging and checkout of tags and branches
 * Multi-module changelogs with automatic sorting by "commit tags"

The phing targets are generally compatible with SilverStripe 2.4 and newer.

## Installation

Install buildtools into a local directory:

	composer create-project silverstripe/buildtools

## Usage

Run `vendor/bin/phing -l` to see a full list of available targets,
and `vendor/bin/phing help` for in-depth help.

The most important command is `vendor/bin/phing release`. It roughly takes the following steps:

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
