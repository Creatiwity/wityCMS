<?php
/**
 * Media Application - Front Controller
 */

defined('IN_WITY') or die('Access denied');

/**
 * MediaController is the Front Controller of the Media Application
 *
 * @package Apps/Media/Front
 * @author Johan Dufau <johan.dufau@creatiwity.net>
 * @author Julien Blatecky <julien.blatecky@creatiwity.net>
 * @version 0.4.0-02-12-2013
 */
class MediaController extends WController {

	/**
	 * Returns a list of available resources corresponding to the filter's parameters
	 *
	 * @param array $params Input parameters
	 * @return array An array of available resources
	 */
	protected function listing(array $params) {
		// Check parameters
		// Get resources
		// Filter it by key (perms)
		// Returns the list
	}

	/**
	 * Stores a file sent with an Ajax request and returns the corresponding id.
	 *
	 * @param array $params Input parameters
	 * @return array Unique identifier of the stored resource
	 */
	protected function upload(array $params) {
		$data = WRequest::get(array('media_file'), null, 'FILES');

		if (in_array(null, $data)) {
			return WNote::error('no_file_sent', WLang::_('no_file_sent'));
		}

		// Test if folders exist and create them
		if (($c = $this->checkFolders()) != true) {
			return $c;
		}

		// Save file
		$h_upload = WHelper::load('upload', array($data['media_file'], WConfig::get('config.lang')));

		$h_upload->file_safe_name = true;

		// Generate file hash (sha1) and file hashID (to identify this file sha1(uniqid.hash.rand))
		$file_hash = sha1_file($h_upload->file_src_pathname);

		if ($file_hash == false) {
			return WNote::error('file_hash_error', 'file_upload_error');
		}

		$fileID = $this->model->generateFileID($file_hash);

		$h_upload->file_name_body_add = '.'.$fileID;

		$h_upload->process(UPLOAD_DIR.'media'.DS.'private'.DS);
		$h_upload->clean();

		if (!$h_upload->processed) {
			return WNote::error('file_upload_error', 'file_upload_error: '.$h_upload->error);
		}

		$dst_filename_wthID = str_replace($fileID.'.', '', $h_upload->file_dst_name);

		$data = array(
			'fileID'    => $fileID,
			'hash'      => $file_hash,
			'filename'  => substr($h_upload->file_dst_name_body, 0, strrpos($h_upload->file_dst_name_body, '.')),
			'mime'      => $h_upload->file_src_mime,
			'extension' => $h_upload->file_dst_name_ext,
			'state'     => 'ONLINE',
			'link'      => WRoute::getBase().'/o/media/'.$dst_filename_wthID.'?f='.$fileID.'&h='.$file_hash
		);

		$media_params = $this->model->createNewMedia($data);

		if ($media_params == false) {
			return WNote::error('unable_to_store_file_data', 'unable_to_store_file_data');
		}

		// Disallow access to anyone but the uploader
		// Add a UID
		// Store in DB
		// Return JSON identifier and link
		return $data;
	}

	/**
	 * Returns true if upload folders are correctly created and secured, otherwise returns WNote.
	 *
	 * @return boolean|WNote
	 */
	private function checkFolders() {
		if (!file_exists(UPLOAD_DIR.'media') || !is_dir(UPLOAD_DIR.'media')) {
			if (!mkdir(UPLOAD_DIR.'media')) {
				return WNote::error('unable_to_mkdir_media', WLang::_('server_configuration_error'));
			}
		}

		if (!file_exists(UPLOAD_DIR.'media'.DS.'public') || !is_dir(UPLOAD_DIR.'media'.DS.'public')) {
			if (!mkdir(UPLOAD_DIR.'media'.DS.'public')) {
				return WNote::error('unable_to_mkdir_public', WLang::_('server_configuration_error'));
			}
		}

		if (!file_exists(UPLOAD_DIR.'media'.DS.'private') || !is_dir(UPLOAD_DIR.'media'.DS.'private')) {
			if (!mkdir(UPLOAD_DIR.'media'.DS.'private')) {
				return WNote::error('unable_to_mkdir_private', WLang::_('server_configuration_error'));
			}

			$htaccess_file = @fopen(UPLOAD_DIR.'media'.DS.'private'.DS.'.htaccess', "w+");

			if ($htaccess_file === false) {
				return WNote::error('unable_to_fopen_htaccess', WLang::_('server_configuration_error'));
			}

			if (fwrite($htaccess_file, "deny from all") === false) {
				return WNote::error('unable_to_fwrite_htaccess', WLang::_('server_configuration_error'));
			}

			if (fclose($htaccess_file) === false) {
				return WNote::error('unable_to_fclose_htaccess', WLang::_('server_configuration_error'));
			}
		}

		return true;
	}

	/**
	 * Returns the logic needed to call a media upload in a view.
	 *
	 * @param array $params Input parameters
	 * @return array Nothing
	 */
	protected function upload_button(array $params) {
		// Do nothing here
	}

	/**
	 * Edits the meta informations of a given resource.
	 *
	 * @param array $params New informations and id of the resource to edit
	 * @return array Nothing
	 */
	protected function metaedit(array $params) {
		// Check information
		// Update resource in DB
		// Update file_id.perm file with the new permission
	}

	/**
	 * Returns the sources which relates to a given resource.
	 *
	 * @param array $params Input parameters
	 * @return array Array of resources which relates to a given source
	 */
	protected function relatives(array $params) {
		// Find sources in database
		// Returns a way to retrieve a tiny view of these and informations
	}

	/**
	 * Returns the link to a given resource.
	 *
	 * @param array $params Input parameters
	 * @return array The link which will be used to access to a resource
	 */
	protected function link(array $params) {
		// If resource is public, returns the real link (not yet)
		// Otherwise, find the resource id (in the name file name.this_is_the_id_of_this_file.ext)
		// Check in .perm file if the user has access to this file (not yet)
		// Returns (name.ext?f=this_is_the_id_of_this_file&h='sha1')
		if (empty($params) || !is_array($params) || empty($params['fileID'])) {
			return WNote::error('missing_fileID_parameter', 'missing_fileID_parameter');
		}

		$data = $this->model->getMediaData($params['fileID']);

		if ($data == false) {
			return WNote::error('file_not_found', 'file_not_found');
		}

		$data['filename'] = str_replace($data['fileID'].'.', '', $data['filename']);

		return array(
			'fileID'    => $data['fileID'],
			'hash'      => $data['hash'],
			'filename'  => $data['filename'],
			'mime'      => $data['mime'],
			'extension' => $data['extension'],
			'state'     => $data['state'],
			'link'      => WRoute::getBase().'/o/media/'.$data['filename'].'.'.$data['extension'].'?f='.$data['fileID'].'&h='.$data['hash']
		);
	}

	/**
	 * Triggers download of the file with the given id.
	 *
	 * @param array $params Input parameters
	 * @return array Nothing
	 */
	protected function get(array $params) {
		// Retrieve fileID and hash in GET variables
		$data = WRequest::get(array('f', 'h'), null, 'GET');

		if (is_null($data['f'])) {
			$this->setHeader('Location', Wroute::getDir());
			return array();
		} else if (preg_match('/^[a-zA-Z0-9]+$/', $data['f']) != 1) {
			WNote::error('media_fileID_missing', 'media_fileID_missing', 'debug');
			$this->setHeader('Location', Wroute::getDir());
			return array();
		}

		$params['fileID'] = $data['f'];

		if (!empty($data['h']) && preg_match('/^[a-zA-Z0-9]+$/', $data['h']) == 1) {
			$params['hash'] = $data['h'];
		}

		// Remove all forbidden chars
		$params[0] = preg_replace('/[^a-zA-Z0-9_\.-]/', '', $params[0]);

		// Must be fresh start
		if (headers_sent()) {
			WNote::error('media_headers_sent', 'media_headers_sent', 'debug');
			$this->setHeader('Location', Wroute::getDir());
			return array();
		}

		// Required for some browsers that don't handle uncompress correctly with zipped files
		if (ini_get('zlib.output_compression')) {
			ini_set('zlib.output_compression', 'Off');
		}

		// Check if file exists on the filesystem, if user has the right to access it, if the sha1 hash should be checked with the db
		$filename = $this->model->getFile($params);

		if ($filename == false) {
			WNote::error('media_file_permissions_error', 'media_file_permissions_error', 'debug');
			$this->setHeader('Location', Wroute::getDir());
			return array();
		}

		if (!is_string($filename)) {
			WNote::error('media_unknown_error', 'media_unknown_error', 'debug');
			$this->setHeader('Location', Wroute::getDir());
			return array();
		}

		if ($filename == 'corrupted') {
			WNote::error('media_corrupted_file', 'media_corrupted_file', 'debug');
			$this->setHeader('Location', Wroute::getDir());
			return array();
		}

		// Using upload helper to determine the right MIME type
		$h_file = WHelper::load('upload', array($filename, WConfig::get('config.lang')));

		if (is_null($h_file->file_src_mime)) {
			WNote::error('media_mime_unknown', 'media_mime_unknown', 'debug');
			$this->setHeader('Location', Wroute::getDir());
			return array();
		}

		header("Content-Description: File Transfer");
		header("Pragma public");
		header("Expires: 0");
		header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
		header("Cache-Control: private", false);
		header("Content-Type: ".$h_file->file_src_mime);
		header("Content-Disposition: attachment; filename=\"".$h_upload->file_src_name."\";");
		header("Content-Transfer-Encoding: binary");
		header("Content-Length: ".$h_file->file_src_size);
		ob_clean();
		flush();
		readfile($filename);

		return array();
	}

}

?>
