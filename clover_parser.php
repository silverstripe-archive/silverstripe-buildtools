<?php
/**
 * PHPUnit Clover code coverage parser for TeamCity integration.
 *
 * @author Grzegorz Drozd
 * @license GPL
 * @date 26.02.11
 * @package build
 * @subpackage phpunit
 *
 * Copyright (C) 2011  Grzegorz Drozd
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

if (empty($_SERVER['argv'][1])) {
    die('Missing argument.');
}

if (!is_file($_SERVER['argv'][1])) {
    die('Invalid argument - argument should be a file.');
}

if (!is_readable($_SERVER['argv'][1])) {
    die('Invalid argument - Unable to read file.');
}

$file = $_SERVER['argv'][1];

$xml = new XMLReader();
$xml->open($file, 'UTF-8', LIBXML_NOBLANKS);

// <coverage>
// <project>
// <file>        skiping other nodes
while ($xml->name != 'file'){
    $xml->read();
}

// <metrics> skiping other nodes
while ($xml->name != 'metrics'){
    $xml->next();
}

// xml pointer is in project level metrics tag
// read and print attributes
$metricsNode = $xml->expand();
$metrics = array();
foreach($metricsNode->attributes as $attr){
    $metrics[$attr->name] = (int) $attr->value;
}

// predefined fields - for automatic graphs
$metricAttributesToReportFieldsMap = array(
    // block level coverage
    'CodeCoverageAbsBCovered'   =>  'coveredelements',

    // line level coverage
    'CodeCoverageAbsLCovered'   => 'coveredstatements',

    // covered methods              covered methods
    'CodeCoverageAbsMCovered'   => 'coveredmethods',

    'CodeCoverageAbsLTotal'     => 'ncloc',
    'CodeCoverageAbsMTotal'     => 'methods',
    'CodeCoverageAbsCTotal'     => 'classes',
);

foreach ($metricAttributesToReportFieldsMap as $key => $metricsKey) {
    print "##teamcity[buildStatisticValue key='$key' value='".$metrics[$metricsKey]."']\n";
}

// % of default fields
print "##teamcity[buildStatisticValue key='CodeCoverageB' value='" . round($metrics['coveredelements']*100/$metrics['elements']) . "']\n";
print "##teamcity[buildStatisticValue key='CodeCoverageL' value='" . round($metrics['coveredstatements']*100/$metrics['statements']) . "']\n";
print "##teamcity[buildStatisticValue key='CodeCoverageM' value='" . round($metrics['coveredmethods']*100/$metrics['methods']) . "']\n";


// print all messages for custom graphs
foreach ($metrics as $key => $value) {
    print "##teamcity[buildStatisticValue key='". $key."' value='".$value."']\n";
}

// custom % metrics
print "##teamcity[buildStatisticValue key='ncloc_to_cloc' value='" . round($metrics['ncloc'] * 100 / $metrics['loc']) . "']\n";
print "##teamcity[buildStatisticValue key='files_to_classess' value='" . round($metrics['classes'] * 100 / $metrics['files']) . "']\n";
