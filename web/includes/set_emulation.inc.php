<?php

// 	This program is copyright (c) 1993 - 2006 Jos Kunst, the Jos Kunst heirs. 
// 	This program is free software. You can redistribute it and/or modify it 
// 	under the terms of version 2 of the GNU General Public License, which you 
// 	should have received with it. 
// 	This program is distributed in the hope that it will be useful, but 
// 	without any warranty, expressed or implied. 

/**
 * Utility functions to emulate Pascal data type 'set' with PHP arrays
 *
 * @package DSG
 */

/**
 * Checks if array1 is a subset of array2
 *
 * @param array $array1 possible subset of array2
 * @param array $array2 which possibly contains array1 as a subset
 * @return bool TRUE if array1 is a subset of array2, FALSE otherwise
 */
function array_subset(array $array1, array $array2): bool
{

	$intersection = array_values(array_intersect($array1, $array2));
	if ($intersection == $array1)
		return TRUE;
	else
		return FALSE;
}

/**
 * Removes a single element, or a subset, from a set
 *
 * Based on a user contribution from doug at NOSPAM dot thrutch dot co dot uk 06-Jan-2006 01:22
 * on http://www.php.net/array_diff
 * @param mixed $element single element or array (subset), to be removed
 * @param array $array set from which the first parameter is to be removed
 * @return array set minus the first parameter
 */
function array_removefromset($element, array $array): array
{

	if (is_array($element)) {
		$array = array_values(array_diff($array, $element));	
	} else {
		$array = array_values(array_diff($array, array($element)));
	}
	// sets are officially unordered, but notes must be ordered
	// from low to high for the program to work correctly
	sort($array);
	
	return $array;
}

/**
 * Add a single element, or a set, to a set
 *
 * @param mixed $element single element or array (set), to be added
 * @param array $array set to which the first parameter is to be added
 * @return array set plus the first parameter
 */
function array_addtoset($element, array $array): array
{
	
	if(is_array($element)) {
		$array = array_merge($array, $element);
	} else {
		$array[] = $element;
	}
	$array = array_values(array_unique($array));
	// sets are officially unordered, but notes must be ordered
	// from low to high for the program to work correctly
	sort($array);
	
	return $array;
}

