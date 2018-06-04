<?php
/**
 * WExport.php
 */

defined('WITYCMS_VERSION') or die('Access denied');

/**
 * WExport helps to export data.
 *
 * @package System\WCore
 * @author Johan Dufau <johan.dufau@creatiwity.net>
 * @author Thibault Vlacich <thibault@vlacich.fr>
 * @version 0.6.2-04-06-2018
 */
class WExport {
	public static function toCSVNamed($filename_prefix, array &$data) {
		$filename = $filename_prefix.'_' . date('Ymd-His') . '.csv';

		// Disable caching
		$now = gmdate('D, d M Y H:i:s');
		header('Expires: Tue, 03 Jul 2001 06:00:00 GMT');
		header('Cache-Control: max-age=0, no-cache, must-revalidate, proxy-revalidate');
		header('Last-Modified: '.$now.' GMT');

		// Disposition / encoding on response body
		header('Content-Disposition: attachment;filename='.$filename);
		header('Content-Transfer-Encoding: binary');
		header('Content-Encoding: UTF-8');
		header('Content-type: text/csv; charset=UTF-8');

		$output = fopen('php://output', 'w');

		if (!empty($data)) {
			fwrite($output, "\xEF\xBB\xBF"); // UTF-8 BOM

			// Columns name
			fputcsv($output, array_keys(reset($data)), ';');

			// Values
			foreach ($data as $line) {
				fputcsv($output, $line, ';');
			}
		}

		fclose($output);

		exit();
	}
}

?>
