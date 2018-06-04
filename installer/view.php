<?php
/**
 * view.php
 */

defined('WITYCMS_VERSION') or die('Access denied');

/**
 * View sends all Installer's stuff to the client.
 *
 * @package Installer
 * @author Julien Blatecky <julien.blatecky@creatiwity.net>
 * @version 0.6.2-04-06-2018
 */
class View {
	/**
	 * @var array Stores the response which will be sent in json to the client
	 */
	private $response = array();

	public function error($level, $id, $head_message, $message) {
		$this->push_message('error', $level, $id, $head_message, $message);
	}

	public function warning($level, $id, $head_message, $message) {
		$this->push_message('warning', $level, $id, $head_message, $message);
	}

	public function success($level, $id, $head_message, $message) {
		$this->push_message('success', $level, $id, $head_message, $message);
	}

	public function info($level, $id, $head_message, $message) {
		$this->push_message('info', $level, $id, $head_message, $message);
	}

	private function push_message($state, $level, $id, $head_message, $message) {
		if(empty($this->response) || !is_array($this->response)) {
			$this->response = array();
		}

		if(empty($this->response[$level]) || !is_array($this->response[$level])) {
			$this->response[$level] = array();
		}

		if(empty($this->response[$level][$id]) || !is_array($this->response[$level][$id])) {
			$this->response[$level][$id] = array();
		}

		$this->response[$level][$id][$state][] = array('head_message' => $head_message, 'message' => $message);
	}

	public function push_content($id, $data) {
		if(empty($this->response) || !is_array($this->response)) {
			$this->response = array();
		}

		if(empty($this->response['content']) || !is_array($this->response['content'])) {
			$this->response['content'] = array();
		}

		$this->response['content'][$id] = $data;
	}

	public function render() {
		if(file_exists("installer".DS."view".DS."base.html")) {
			$content = file_get_contents("installer".DS."view".DS."base.html");
			echo $content;
		}
	}

	public function respond() {
		$final_response = array_map('self::prepare_array', $this->response);
		$final_response = html_entity_decode(json_encode($final_response));

		echo $final_response;
	}

	private function prepare_array($val = '') {
		if(is_numeric($val)) {
			$val = strval($val);
		}

		if(is_string($val)) {
			$val = htmlentities($val);
		}

		return $val;
	}
}

?>
