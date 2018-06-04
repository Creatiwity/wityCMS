<?php
/**
 * User Application - Admin Model
 */

defined('WITYCMS_VERSION') or die('Access denied');

/**
 * Include Front Model for inheritance
 */
include_once APPS_DIR.'user'.DS.'front'.DS.'model.php';

/**
 * UserAdminModel is the Admin Model of the User Application.
 *
 * @package Apps\User\Admin
 * @author Johan Dufau <johan.dufau@creatiwity.net>
 * @version 0.6.2-04-06-2018
 */
class UserAdminModel extends UserModel {
	public function __construct() {
		parent::__construct();
	}

	/**
	 * Creates a user in the database.
	 *
	 * This is an admin version able to change the field `users.access`.
	 *
	 * @param array $data
	 * @return bool Request status
	 */
	public function createUser(array $data) {
		$prep = $this->db->prepare('
			INSERT INTO users(nickname, password, confirm, email, firstname, lastname, country, groupe, access, valid, ip)
			VALUES (:nickname, :password, :confirm, :email, :firstname, :lastname, :country, :groupe, :access, :valid, :ip)
		');
		$prep->bindParam(':nickname', $data['nickname']);
		$prep->bindParam(':password', $data['password']);
		$confirm = isset($data['confirm']) ? $data['confirm'] : '';
		$prep->bindParam(':confirm', $confirm);
		$prep->bindParam(':email', $data['email']);
		$firstname = isset($data['firstname']) ? $data['firstname'] : '';
		$prep->bindParam(':firstname', $firstname);
		$lastname = isset($data['lastname']) ? $data['lastname'] : '';
		$prep->bindParam(':lastname', $lastname);
		$country = isset($data['country']) ? $data['country'] : '';
		$prep->bindParam(':country', $country);
		$groupe = isset($data['groupe']) ? $data['groupe'] : '';
		$prep->bindParam(':groupe', $groupe);
		$prep->bindParam(':access', $data['access']);
		$valid = isset($data['valid']) ? $data['valid'] : 1;
		$prep->bindParam(':valid', $valid);
		$prep->bindParam(':ip', $_SERVER['REMOTE_ADDR']);

		if ($prep->execute()) {
			return $this->db->lastInsertId();
		} else {
			return false;
		}
	}

	/**
	 * Deletes a user.
	 *
	 * @param int $user_id
	 * @return bool Request status
	 */
	public function deleteUser($user_id) {
		$prep = $this->db->prepare('
			DELETE FROM users WHERE id = :id
		');
		$prep->bindParam(':id', $user_id, PDO::PARAM_INT);

		return $prep->execute();
	}

	/**
	 * Retrieves details of a group.
	 *
	 * @param int $group_id
	 * @return array Data of the group
	 */
	public function getGroup($group_id) {
		$prep = $this->db->prepare('
			SELECT id, name, access
			FROM users_groups
			WHERE id = :id
		');
		$prep->bindParam(':id', $group_id, PDO::PARAM_INT);
		$prep->execute();

		return $prep->fetch(PDO::FETCH_ASSOC);
	}

	/**
	 * Retrieves the list of user groups.
	 *
	 * @param string $order Name of the ordering column
	 * @param string $sens  Order: "ASC" or "DESC"
	 * @return array Set of groups
	 */
	public function getGroupsList($order = 'name', $sens = 'ASC') {
		if (strtoupper($sens) != 'ASC') {
			$sens = 'DESC';
		}

		$prep = $this->db->prepare('
			SELECT id, name, access
			FROM users_groups
			ORDER BY '.$order.' '.$sens
		);
		$prep->execute();

		return $prep->fetchAll(PDO::FETCH_ASSOC);
	}

	/**
	 * Retrieves the list of user groups with users_count row.
	 *
	 * @param string $order Name of the ordering column
	 * @param string $asc   Ascendent or descendent?
	 * @return array Set of groups
	 */
	public function getGroupsListWithCount($order = 'name', $asc = true) {
		$prep = $this->db->prepare('
			SELECT users_groups.id, name, users_groups.access, COUNT(*) AS users_count, groupe
			FROM users
			RIGHT JOIN users_groups
			ON groupe = users_groups.id
			GROUP BY users_groups.id
			ORDER BY '.$order.' '.($asc ? 'ASC' : 'DESC')
		);
		$prep->execute();

		$data = array();
		while ($row = $prep->fetch(PDO::FETCH_ASSOC)) {
			if ($row['groupe'] == 0) {
				$row['users_count'] = 0;
			}
			unset($row['groupe']);
			$data[] = $row;
		}

		return $data;
	}

	/**
	 * Creates a new group in the database.
	 *
	 * @param array $data array(nickname, access)
	 * @return bool Request status
	 */
	public function createGroup($data) {
		$prep = $this->db->prepare('
			INSERT INTO users_groups(name, access)
			VALUES (:name, :access)
		');
		$prep->bindParam(':name', $data['name']);
		$prep->bindParam(':access', $data['access']);

		if ($prep->execute()) {
			return $this->db->lastInsertId();
		} else {
			return false;
		}
	}

	/**
	 * Updates a group in the database.
	 *
	 * @param int   $group_id Group ID
	 * @param array $data     Columns to update
	 * @return bool Request status
	 */
	public function updateGroup($group_id, $data) {
		$prep = $this->db->prepare('
			UPDATE users_groups
			SET name = :name, access = :access
			WHERE id = :id
		');
		$prep->bindParam(':id', $group_id, PDO::PARAM_INT);
		$prep->bindParam(':name', $data['name']);
		$prep->bindParam(':access', $data['access']);

		return $prep->execute();
	}

	/**
	 * Deletes a group in the database.
	 *
	 * @param int $group_id Group ID
	 * @return bool Request status
	 */
	public function deleteGroup($group_id) {
		$prep = $this->db->prepare('
			DELETE FROM users_groups WHERE id = :id
		');
		$prep->bindParam(':id', $group_id, PDO::PARAM_INT);

		return $prep->execute();
	}

	/**
	 * Removes all users who belonged to an obsolete group.
	 *
	 * @param int $group_id Group ID
	 * @return bool Request status
	 */
	public function resetUsersInGroup($group_id) {
		$prep = $this->db->prepare('
			UPDATE users
			SET groupe = 0
			WHERE groupe = :id
		');
		$prep->bindParam(':id', $group_id, PDO::PARAM_INT);

		return $prep->execute();
	}

	/**
	 * Updates several users matching filters.
	 *
	 * @param array $data     Columns to update in users table
	 * @param array $filters  List of criteria to filter the request (nickname, email, firstname, lastname and groupe)
	 * @return bool Request status
	 */
	public function updateUsers(array $data, array $filters) {
		if (empty($data)) {
			return true;
		}

		// Add filters
		$cond = '';
		if (!empty($filters)) {
			$allowed = array('groupe', 'access');
			foreach ($filters as $name => $value) {
				if (in_array($name, $allowed)) {
					if (strpos($value, 'NOT:') === 0) {
						$cond .= $name." != ".$this->db->quote(substr($value, 4))." AND ";
					} else {
						$cond .= $name." = ".$this->db->quote($value)." AND ";
					}
				}
			}
		}

		if (empty($cond)) {
			return true;
		} else {
			$cond = substr($cond, 0, -5);
		}

		$string = '';
		foreach ($data as $key => $value) {
			$string .= $key.' = '.$this->db->quote($value).', ';
		}
		$string = substr($string, 0, -2);

		$prep = $this->db->prepare('
			UPDATE users
			SET '.$string.'
			WHERE '.$cond
		);

		return $prep->execute();
	}

	/**
	 * Transforms access data obtained from the admin form into an access string.
	 *
	 * @param array  $old_access      Previous access of the user
	 * @param array  $allowed_apps    List of allowed apps of current user
	 * @param string $new_type        New type for the user (none|all|custom)
	 * @param array  $new_access      New access for the user
	 * @return null|string Null if nothing to do. String to be updated
	 */
	public function treatAccessData($old_access, $allowed_apps, $new_type, $new_access) {
		if ($_SESSION['access'] == 'all') {
			return $this->stringifyAccess($new_type, $new_access);
		} else {
			if ($old_access == 'all') {
				return null;
			} else if ($old_access == '') {
				return $this->stringifyAccess($new_type, $new_access);
			} else {
				$new_type = 'custom';

				foreach ($allowed_apps as $app => $permissions) {
					foreach ($permissions as $permission) {
						// Remove this permission
						if (isset($old_access[$app]) && ($key = array_search($permission, $old_access[$app])) !== false) {
							unset($old_access[$app][$key]);
						}

						// Add this permission if asked
						if (isset($new_access[$app]) && in_array($permission, $new_access[$app])) {
							$old_access[$app][] = $permission;
						}
					}
				}

				return $this->stringifyAccess($new_type, $old_access);
			}
		}
	}

	/**
	 * Stringify acess.
	 *
	 * @param string $type    Type of the user (none|all|custom)
	 * @param array  $access  List of app and perms whose user have access
	 * @return string
	 */
	private function stringifyAccess($type, $access) {
		if ($type == 'all') {
			return 'all';
		} else if ($type == 'none' || empty($access)) {
			return '';
		} else {
			$access_string = '';
			foreach ($access as $app => $perms) {
				if (!empty($perms)) {
					$access_string .= $app.'['.implode('|', $perms).'],';
				}
			}

			return substr($access_string, 0, -1);
		}
	}

	/**
	 * Counts the users in the database having different access from their group's access.
	 *
	 * @param array $filters List of criteria to filter the request (nickname, email, firstname, lastname and groupe)
	 * @return array A list of information about the users found
	 */
	public function countUsersWithCustomAccess(array $filters = array()) {
		$cond = '';
		if (!empty($filters)) {
			$allowed = array('nickname', 'email', 'firstname', 'lastname');
			foreach ($filters as $name => $value) {
				if (in_array($name, $allowed)) {
					if (strpos($value, 'REGEXP:') === 0) {
						$cond .= $name." REGEXP ".$this->db->quote(substr($value, 7))." AND ";
					} else {
						if (strpos($value, '%') === false) {
							$value = '%'.$value.'%';
						}

						$cond .= $name." COLLATE UTF8_GENERAL_CI LIKE ".$this->db->quote($value)." AND ";
					}
				}
			}

			if (!empty($filters['groupe'])) {
				$cond .= 'groupe = '.intval($filters['groupe']).' AND ';
			}
		}

		$prep = $this->db->prepare('
			SELECT COUNT(*)
			FROM users
			LEFT JOIN users_groups
			ON groupe = users_groups.id
			WHERE '.$cond.'users.access != users_groups.access
		');
		$prep->execute();
		return intval($prep->fetchColumn());
	}

	/**
	 * Retrieves a list of users having different access from their group's access.
	 *
	 * @param array  $filters  List of criteria to filter the request (nickname, email, firstname, lastname and groupe)
	 * @return array A list of information about the users found
	 */
	public function getUsersWithCustomAccess(array $filters = array()) {
		$cond = '';
		if (!empty($filters)) {
			$allowed = array('nickname', 'email', 'firstname', 'lastname');
			foreach ($filters as $name => $value) {
				if (in_array($name, $allowed)) {
					if (strpos($value, 'REGEXP:') === 0) {
						$cond .= $name." REGEXP ".$this->db->quote(substr($value, 7))." AND ";
					} else {
						if (strpos($value, '%') === false) {
							$value = '%'.$value.'%';
						}

						$cond .= $name." COLLATE UTF8_GENERAL_CI LIKE ".$this->db->quote($value)." AND ";
					}
				}
			}

			if (!empty($filters['groupe'])) {
				$cond .= 'groupe = '.intval($filters['groupe']).' AND ';
			}
		}

		$prep = $this->db->prepare('
			SELECT users.id, nickname, email, firstname, lastname, users.access, groupe, name AS groupe_name, ip
			FROM users
			LEFT JOIN users_groups
			ON groupe = users_groups.id
			WHERE '.$cond.'users.access != users_groups.access
		');
		$prep->execute();

		return $prep->fetchAll(PDO::FETCH_ASSOC);
	}

	/**
	 * Defines a config in users_config table.
	 *
	 * @param string $name
	 * @param string $value
	 * @return Request status
	 */
	public function setConfig($name, $value) {
		$prep = $this->db->prepare('
			UPDATE users_config
			SET value = :value
			WHERE name = :name
		');
		$prep->bindParam(':name', $name);
		$prep->bindParam(':value', $value);

		return $prep->execute();
	}
}

?>
