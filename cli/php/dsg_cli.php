<?php

// 	This program is copyright (c) 1993 - 2006 Jos Kunst, the Jos Kunst heirs. 
// 	This program is free software. You can redistribute it and/or modify it 
// 	under the terms of version 2 of the GNU General Public License, which you 
// 	should have received with it. 
// 	This program is distributed in the hope that it will be useful, but 
// 	without any warranty, expressed or implied. 

/**
 * Contains the main loop of the DSG program
 *
 * Dissonance Grading program by Jos Kunst (1936-1996), intended 
 * as a tool for musical composition and used as such by himself. 
 * 
 * Original version written in Pascal (1993). Ported to PHP 
 * (Command Line Interface version) by Jan Pieter Kunst (2006).
 *
 * Usage: <kbd>$ php [-f] dsg_cli.php</kbd>
 *
 * Notes:
 *
 * The Pascal control structure
 *
 *   <code>repeat ... until condition</code>
 *
 * is represented by 
 *
 *    <code>while(1) { ... if (condition) break;}</code>
 *
 * throughout.
 *
 * The Pascal data type 'set' is represented by PHP arrays,
 * see {@link set_emulation.inc.php}.
 *
 *
 * @package DSG
 * @subpackage cli
 * @version 2.0.1 (version number of original Pascal program)
 * @author Jos Kunst
 * @author Ported to PHP by Jan Pieter Kunst
 */

require('dsg_config.inc.php');

/**
 * DSG library
 */
require('DSG.class.php');

/**
 * Functions for overall program logic, user input and program output
 */
require('input_output.inc.php');


/**
 * Used to save original chord provided by the user
 *
 * @global string $last_fed_in_chord
 */
$last_fed_in_chord = '';

introduction();
$newchord = TRUE;
$fh = decidewhethertoprint();
while(1) {
	work($newchord, $fh);
	echo 'Do you want to leave the program? (Y/N)' . "\n";
  echo '--> ';
	$input = trim(fgets(STDIN));
	if ($input == 'n' || $input == 'N') {
		echo 'Go back to the CHORD you fed in the last time? (Y/N)' . "\n";
    echo '--> ';
		$input = trim(fgets(STDIN));
		if ($input != 'n' && $input != 'N') {
			$newchord = FALSE;
		} else {
			$newchord = TRUE;
		}
	} else {
		if ($fh !== FALSE) {
			fclose($fh);
		}
		break;
	}
}

