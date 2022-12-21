<?php

// 	This program is copyright (c) 1993 - 2006 Jos Kunst, the Jos Kunst heirs. 
// 	This program is free software. You can redistribute it and/or modify it 
// 	under the terms of version 2 of the GNU General Public License, which you 
// 	should have received with it. 
// 	This program is distributed in the hope that it will be useful, but 
// 	without any warranty, expressed or implied. 

/**
 * @package DSG
 */

/**
 * Directory from which DSG is running, from the point of view of the webserver. Absolute links are generated from this setting. Edit as needed.
 */
define('DSG_ROOTDIR', '/' . basename(dirname(__FILE__)) . '/');
/**
 * Include path, change to wherever you want to keep the classes and functions
 */
define('DSG_INCLUDE_PATH', realpath('./includes'));
ini_set('include_path', ini_get('include_path') . PATH_SEPARATOR . DSG_INCLUDE_PATH);
/**
 * Include path for third-party libraries, change to wherever you want to keep those
 */
const DSG_EXTERNAL_LIBS_INCLUDE_PATH = DSG_INCLUDE_PATH . DIRECTORY_SEPARATOR . 'extlib';
ini_set('include_path', ini_get('include_path') . PATH_SEPARATOR . DSG_EXTERNAL_LIBS_INCLUDE_PATH);
/**
 * Directory to save MIDI files, must be writable by webserver and within the document root
 */
define('DSG_TMPDIR', dirname(__FILE__) . DIRECTORY_SEPARATOR . 'tmp');
/**
 * Directory for MIDI files from the point of view of the webserver
 */
define('DSG_TMPDIR_DOCROOT', basename(DSG_TMPDIR));
/**
 * Set to TRUE if MIDI support is needed
 *
 * The Dissonance Grading package optionally uses Valentin Schmidts Midi Class,
 * which can be downloaded from {@link https://valentin.dasdeck.com/midi/},
 * and should be installed in DSG_EXTERNAL_LIBS_INCLUDE_PATH defined above.
 */
const DSG_MIDI_SUPPORT = true;
/**
 * Default stylesheet, based on joskunst.net style
 */
const DSG_CSS = 'dsg.css';

/**
 * Model part of the MVC setup for the Dissonance Grading package
 */
require('DSG.class.php');
/**
 * Controller part of the MVC setup for the Dissonance Grading package
 */
require('dsgController.class.php');
/**
 * View part of the MVC setup for the Dissonance Grading package
 */
require('dsgView.class.php');

