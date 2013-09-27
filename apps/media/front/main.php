<?php
/**
 * Media Application - Front Controller - /apps/media/front/main.php
 */

defined('IN_WITY') or die('Access denied');

/**
 * MediaController is the Front Controller of the Media Application
 * 
 * @package Apps
 * @author Johan Dufau <johan.dufau@creatiwity.net>
 * @author Julien Blatecky <julien.blatecky@creatiwity.net>
 * @version 0.4-14-09-2013
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
	 * Stores a file sent with an Ajax request and returns the corresponding id
	 * 
	 * @param array $params Input parameters
	 * @return array Unique identifier of the stored resource
	 */
	protected function upload(array $params) {
		$datas = WRequest::get(array('media_file'), null, 'FILES');

		if(in_array(null, $datas)) {
			return WNote::error("no_file_sent", WLang::_("no_file_sent"));
		}

		// Test if folders exist and create them
		if (!file_exists(UPLOAD_DIR.'media') || !is_dir(UPLOAD_DIR.'media')) {
			if (!mkdir(UPLOAD_DIR.'media')) {
				return WNote::error("unable_to_mkdir_media", WLang::_("server_configuration_error"));
			}
		}

		if (!file_exists(UPLOAD_DIR.'media'.DS.'public') || !is_dir(UPLOAD_DIR.'media'.DS.'public')) {
			if (!mkdir(UPLOAD_DIR.'media'.DS.'public')) {
				return WNote::error("unable_to_mkdir_public", WLang::_("server_configuration_error"));
			}
		}

		if (!file_exists(UPLOAD_DIR.'media'.DS.'private') || !is_dir(UPLOAD_DIR.'media'.DS.'private')) {
			if (!mkdir(UPLOAD_DIR.'media'.DS.'private')) {
				return WNote::error("unable_to_mkdir_private", WLang::_("server_configuration_error"));
			}

			$htaccess_file = @fopen(UPLOAD_DIR.'media'.DS.'private'.DS.'.htaccess', "w+");

			if ($htaccess_file === false) {
				return WNote::error("unable_to_fopen_htaccess", WLang::_("server_configuration_error"));
			}

			if (fwrite($htaccess_file, "deny from all") === false) {
				return WNote::error("unable_to_fwrite_htaccess", WLang::_("server_configuration_error"));
			}

			if (fclose($htaccess_file) === false) {
				return WNote::error("unable_to_fclose_htaccess", WLang::_("server_configuration_error"));
			}	
		}

		// Save file
		/**
		 * $this->file_max_size
		 * $this->allowed
		 * $this->forbidden
		 * $this->image_max_width
		 * $this->image_min_width
		 * $this->image_max_height
		 * $this->image_min_height
		 * $this->image_max_ratio
		 * $this->image_min_ratio
		 * $this->image_max_pixels
		 * $this->image_min_pixels
		 * 
		 * $this->file_new_name_body
		 * $this->file_new_name_ext
		 * $this->file_name_body_add
		 * $this->file_name_body_pre
		 * 
		 * $this->file_safe_name
		 * $this->file_dst_pathname
		 */
		$h_upload = WHelper::load('upload', array($datas['media_file'], WConfig::get('config.lang')));

		$h_upload->file_safe_name = true;
		$h_upload->file_dst_pathname = UPLOAD_DIR.'media'.DS.'private';

		$media_params = $this->model->create_new_media($h_upload->file_src_name);

		// Disallow access to anyone but the uploader
		// Add a UID
		// Store in DB
		// Return JSON identifier and link
	}

	/**
	 * Returns the logic needed to call a media upload in a view
	 * 
	 * @param array $params Input parameters 
	 * @return array Nothing
	 */
	protected function upload_button(array $params) {
		// Do nothing here
	}

	/**
	 * Edits the meta informations of a given resource
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
	 * Returns the sources which relates to a given resource
	 * 
	 * @param array $params Input parameters
	 * @return array Array of resources which relates to a given source
	 */
	protected function relatives(array $params) {
		// Find sources in database
		// Returns a way to retrieve a tiny view of these and informations
	}

	/**
	 * Returns the link to a given resource
	 * 
	 * @param array $params Input parameters
	 * @return array The link which will be used to access to a resource
	 */
	protected function link(array $params) {
		// If resource is public, returns the real link
		// Otherwise, find the resource id (in the name file name.this_is_the_id_of_this_file.ext)
		// Returns (name.ext?=this_is_the_id_of_this_file)
	}
	
}

?>