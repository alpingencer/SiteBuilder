<?php
/**************************************************
 *          The SiteBuilder PHP Framework         *
 *         Copyright (c) 2021 Alpin Gencer        *
 *      Refer to LICENSE.md for a full notice     *
 **************************************************/

/**
 * @param array $array1
 * @param array $array2
 *
 * @return array
 */
function array_merge_recursive_distinct(array $array1, array $array2): array {
	$merged = $array1;

	foreach($array2 as $key => $value) {
		if(is_array($value) && isset($merged[$key]) && is_array($merged[$key])) {
			$merged[$key] = array_merge_recursive_distinct($merged[$key], $value);
		} else {
			$merged[$key] = $value;
		}
	}

	return $merged;
}
