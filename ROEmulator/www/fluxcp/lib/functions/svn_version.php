<?php
/**
 * Get the SVN revision of a directory.
 *
 * @param string `entries' file name.
 * @return int Revision number
 */
function svn_version($entriesFile = null)
{
	if (!$entriesFile) {
		$entriesFile = FLUX_ROOT.'/.svn/entries';
	}
	
	if (file_exists($entriesFile) && is_readable($entriesFile)) {
		$fp  = fopen($entriesFile, 'r');
		$arr = explode("\n", fread($fp, 256));

		if (isset($arr[3]) && ctype_digit($rev=trim($arr[3]))) {
			fclose($fp);
			return (int)$rev;
		}
		else {
			return null;
		}
	}
}
?>