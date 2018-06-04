<?php
/**
 * Newsletter Application - Admin Model
 */

defined('WITYCMS_VERSION') or die('Access denied');

/**
 * NewsletterAdminModel is the Admin Model of the Newsletter Application
 *
 * @package Apps\Newsletter\Admin
 * @author Thibault Vlacich <thibault@vlacich.fr>
 * @version 0.6.2-04-06-2018
 */
class NewsletterAdminModel {
	/**
	 * @var WDatabase instance
	 */
	protected $db;

	public function __construct() {
		$this->db = WSystem::getDB();

		// Declare tables
		$this->db->declareTable('newsletter');
	}

	/**
	 * Counts subscribers in the database
	 *
	 * @return int
	 */
	public function countSubscribers() {
		$prep = $this->db->prepare('
			SELECT COUNT(*)
			FROM newsletter
		');
		$prep->execute();

		return intval($prep->fetchColumn());
	}

	/**
	 * Retrieves the list of subscribers.
	 *
	 * @param int $from
	 * @param int $number
	 * @param string $order Ordering field name
	 * @param bool $asc true = ASC order / false = DESC order
	 * @return array
	 */
	public function getSubscribersList($from, $number, $order = 'created_date', $asc = false) {
		$prep = $this->db->prepare('
			SELECT id, email, created_date, created_by, modified_date
			FROM newsletter
			ORDER BY '.$order.' '.($asc ? 'ASC' : 'DESC').'
			LIMIT :start, :number
		');
		$prep->bindParam(':start', $from, PDO::PARAM_INT);
		$prep->bindParam(':number', $number, PDO::PARAM_INT);
		$prep->execute();

		$result = array();
		while ($data = $prep->fetch(PDO::FETCH_ASSOC)) {
			$result[] = $data;
		}

		return $result;
	}

	/**
	 * Retrieves all data linked to a subscribers
	 *
	 * @param int $sub_id
	 * @return array
	 */
	public function getSubscriber($sub_id) {
		$prep = $this->db->prepare('
			SELECT id, email, created_date, created_by, modified_date
			FROM newsletter
			WHERE id = :id
		');
		$prep->bindParam(':id', $sub_id, PDO::PARAM_INT);
		$prep->execute();

		return $prep->fetch(PDO::FETCH_ASSOC);
	}

	/**
	 * Deletes a page in the database
	 *
	 * @param int $page_id
	 * @return bool Success?
	 */
	public function deleteSubscriber($sub_id) {
		$prep = $this->db->prepare('
			DELETE FROM newsletter WHERE id = :id
		');
		$prep->bindParam(':id', $sub_id, PDO::PARAM_INT);

		return $prep->execute();
	}

	/**
	 * Checks that a given ID matches a ID in the database
	 *
	 * @param int $sub_id
	 * @return bool
	 */
	public function validExistingSubId($sub_id) {
		$sub_id = intval($sub_id);

		if ($sub_id <= 0) {
			return false;
		}

		$prep = $this->db->prepare('
			SELECT * FROM newsletter WHERE id = :id
		');
		$prep->bindParam(':id', $sub_id, PDO::PARAM_INT);
		$prep->execute();

		return $prep->rowCount() == 1;
	}

	/**
	 * Export the list of subscribers as a CSV file
	 */
	public function exportCSV() {
		$this->export_csv_send_headers("data_export_" . date("Y-m-d") . ".csv");

		$prep = $this->db->prepare('
			SELECT email
			FROM newsletter
		');
		$prep->execute();

		$listOfSubs = array();
		while ($data = $prep->fetch(PDO::FETCH_ASSOC)) {
			$listOfSubs[] = $data;
		}

		echo $this->array2csv($listOfSubs);

		die();
	}

	/**
	 * Generate a CSV file from an array
	 *
	 * @param array $array
	 * @return string
	 */
	protected function array2csv(array &$array)
	{
		if (count($array) == 0) {
			return null;
		}
		ob_start();
		$df = fopen("php://output", 'w');
		fputcsv($df, array_keys(reset($array)));
		foreach ($array as $row) {
			fputcsv($df, $row);
		}
		fclose($df);
		return ob_get_clean();
	}

	/**
	 * Generate headers for CSV file export
	 *
	 * @param string $filename
	 */
	protected function export_csv_send_headers($filename) {
		// disable caching
		$now = gmdate("D, d M Y H:i:s");
		header("Expires: Tue, 03 Jul 2001 06:00:00 GMT");
		header("Cache-Control: max-age=0, no-cache, must-revalidate, proxy-revalidate");
		header("Last-Modified: {$now} GMT");

		// force download
		header("Content-Type: application/force-download");
		header("Content-Type: application/octet-stream");
		header("Content-Type: application/download");

		// disposition / encoding on response body
		header("Content-Disposition: attachment;filename={$filename}");
		header("Content-Transfer-Encoding: binary");
	}
}

?>
