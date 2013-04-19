# Phing Buildtools for the SilverStripe Project

## Overview

The goal of this project is to aid SilverStripe developers in automating various aspects of their build
through [phing](http://phing.info).

It is primarily geared towards core developers preparing a SilverStripe release, 
but can also be a starting point for custom build setups.

Notable features:

 * Creating and uploading release archives
 * Multi-module tagging and checkout of tags and branches
 * Multi-module changelogs with automatic sorting by "commit tags"
 * Translation workflow around [getlocalization.com](http://getlocalization.com/) (see [docs](http://doc.silverstripe.org/framework/en/trunk/misc/translation-process)) (SilverStripe 3.x only)

The phing targets are generally compatible with SilverStripe 2.4 and newer.

## Installation

First of all, get started with phing:

	pear channel-discover pear.phing.info
	pear install phing/phing
	pear install pear/VersionControl_Git-0.4.4

Then install the project via [composer](http://getcomposer.org).

	composer require silverstripe/buildtools


## Usage

Run `phing -l` to see a full list of available targets,
and `phing help` for in-depth help.

## License ##

	Copyright (c) 2007-2012, SilverStripe Limited - www.silverstripe.com
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