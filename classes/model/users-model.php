<?php
/**
 * OrgHub_UsersModel
 * 
 * The users model for the Organization Hub plugin.
 * 
 * @package    orghub
 * @subpackage classes
 * @author     Crystal Barton <cbarto11@uncc.edu>
 */

if( !class_exists('OrgHub_UsersModel') ):
class OrgHub_UsersModel
{

	private static $instance = null;	// The only instance of this class.
	private $model = null;				// The "parent" model for Organization Hub.
	
	// Names of tables used by the model without prefix.
	private static $user_table 			= 'orghub_user';
	private static $type_table 			= 'orghub_type';
	private static $category_table 		= 'orghub_category';
	private static $connections_table 	= 'orghub_connections';


	
	/**
	 * Private Constructor.  Needed for a Singleton class.
	 * Creates an OrgHub_UsersModel object.
	 */
	private function __construct()
	{
		global $wpdb;
		self::$user_table        = $wpdb->base_prefix.self::$user_table;
		self::$type_table        = $wpdb->base_prefix.self::$type_table;
		self::$category_table    = $wpdb->base_prefix.self::$category_table;
		self::$connections_table = $wpdb->base_prefix.self::$connections_table;
		
		$this->model = OrgHub_Model::get_instance();
	}



	/**
	 * Get the only instance of this class.
	 * @return  OrgHub_UsersModel  A singleton instance of the users model class.
	 */
	public static function get_instance()
	{
		if( self::$instance	=== null )
		{
			self::$instance = new OrgHub_UsersModel();
		}
		return self::$instance;
	}



//========================================================================================
//================================================================== Database tables =====


	/**
	 * Create the required database tables.
	 */
	public function create_tables()
	{
		global $wpdb;
		
        $db_charset_collate = '';
        if( !empty($wpdb->charset) )
			$db_charset_collate = "DEFAULT CHARACTER SET $wpdb->charset";
        if( !empty($wpdb->collate) )
			$db_charset_collate .= " COLLATE $wpdb->collate";
		
		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
		
		$sql = "CREATE TABLE ".self::$user_table." (
				  id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
				  username varchar(16) NOT NULL UNIQUE,
				  first_name text NOT NULL DEFAULT '',
				  last_name text NOT NULL DEFAULT '',
				  email text NOT NULL DEFAULT '',
				  description text NOT NULL DEFAULT '',
				  site_domain text NOT NULL DEFAULT '',
				  site_path text NOT NULL DEFAULT '',
				  status varchar(16) NOT NULL,
				  warning text DEFAULT NULL,
				  error text DEFAULT NULL,
				  wp_user_id bigint(20) DEFAULT NULL,
				  wp_user_warning text DEFAULT NULL,
				  wp_user_error text DEFAULT NULL,
				  profile_blog_id bigint(20) DEFAULT NULL,
				  profile_blog_warning text DEFAULT NULL,
				  profile_blog_error text DEFAULT NULL,
				  PRIMARY KEY  (id)
				) ENGINE=InnoDB $db_charset_collate;";
		
        dbDelta($sql);
		
		$sql = "CREATE TABLE ".self::$type_table." (
				  id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
				  user_id bigint(20) NOT NULL,
				  type text NOT NULL DEFAULT '',
				  PRIMARY KEY  (id)
				) ENGINE=InnoDB $db_charset_collate;";
		
        dbDelta($sql);
		
		$sql = "CREATE TABLE ".self::$category_table." (
				  id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
				  user_id bigint(20) NOT NULL,
				  category text NOT NULL DEFAULT '',
				  PRIMARY KEY  (id)
				) ENGINE=InnoDB $db_charset_collate;";
		
        dbDelta($sql);
		
		$sql = "CREATE TABLE ".self::$connections_table." (
				  id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
				  user_id bigint(20) NOT NULL,
				  site text NOT NULL DEFAULT '',
				  required tinyint(1) NOT NULL DEFAULT 0,
				  post_id bigint(20) DEFAULT NULL,
				  connections_warning text DEFAULT NULL,
				  connections_error text DEFAULT NULL,
				  PRIMARY KEY  (id)
				) ENGINE=InnoDB $db_charset_collate;";
		
        dbDelta($sql);
	}
	
	
	/**
	 * Drop the required database tables.
	 */
	public function delete_tables()
	{
		global $wpdb;
		$wpdb->query( 'DROP TABLE '.self::$connections_table.';' );
		$wpdb->query( 'DROP TABLE '.self::$category_table.';' );
		$wpdb->query( 'DROP TABLE '.self::$type_table.';' );
		$wpdb->query( 'DROP TABLE '.self::$user_table.';' );
	}


	/**
	 * Clear the required database tables.
	 */
	public function clear_tables()
	{
		global $wpdb;
		$wpdb->query( 'DELETE FROM '.self::$connections_table.';' );
		$wpdb->query( 'DELETE FROM '.self::$category_table.';' );
		$wpdb->query( 'DELETE FROM '.self::$type_table.';' );
		$wpdb->query( 'DELETE FROM '.self::$user_table.';' );
	}



//========================================================================================
//================================================ Import / Updating database tables =====


	/**
	 * Verifies that all the required fields are present.
	 * @param  array  $args  An array of user values.
	 * @return  bool  True if the args are valid, otherwise False.
	 */
	private function check_args( &$args )
	{
		//
		// Verify that the required columns have been included.
		//
		$required_fields = array(
			'username', 'first_name', 'last_name', 'email', 'type', 'description', 'category',
		);
		
		foreach( $required_fields as $required_field )
		{
			if( !in_array($required_field, array_keys($args)) )
			{
				$this->model->last_error = 'Missing required field "'.$required_field.'".';
				return false;
			}
			elseif( !preg_replace("/[^a-z0-9]/i", "", $args[$required_field]) )
			{
				$this->model->last_error = 'Invalid field value for required field "'.$required_field.'".';
				return false;
			}
		}
		
		//
		// Verify that columns with multiple columns have valid values.
		//
		if( !$this->check_multiple_value_arg('type', $args['type'], false) ) return false;
		if( !$this->check_multiple_value_arg('category', $args['category'], false) ) return false;
		
		//
		// If not specified, populate site_domain column with its default value.
		// If site_domain is specified, then verify that the value is valid.
		//
		if( (!in_array('site_domain', array_keys($args))) )
		{
			$args['site_domain'] = '';
		}
		elseif( !$this->is_valid_site_domain_name($args['site_domain']) )
		{
			$this->model->last_error = 'Invalid field value for field "site_domain".';
			return false;
		}

		//
		// If not specified, populate site_domain column with its default value.
		// If site_domain is specified, then verify that the value is valid.
		//
		if( (!in_array('site_path', array_keys($args))) )
		{
			$args['site_path'] = '';
		}
		
		//
		// If not specified, populate connections_sites column with its default value.
		//
		if( (!in_array('connections_sites', array_keys($args))) || (!$args['connections_sites']) )
		{
			$args['connections_sites'] = '';
		}
		elseif( !$this->check_multiple_value_arg('connections_sites', $args['connections_sites'], false) )
		{
			$this->model->last_error = 'Invalid field value for field "connections_sites".';
			return false;
		}
		
		return true;
	}
	
	
	/**
	 * Parses and verifies each item in a column/field's comma-seperated value.
	 * @param  string  $field  The column/field name.
	 * @param  string  $value  The string value of the column/field.
	 * @param  bool    $at_least_one  True if the value should have at least one value.
	 * @return bool    True if the value is valid.
	 */
	private function check_multiple_value_arg( $field, &$value, $at_least_one = true )
	{
		$value = str_getcsv( $value, ",", '"', "\\" );
		if( ($at_least_one) && (count($value) === 0) )
		{
			$this->model->last_error = 'Invalid field value for field "'.$field.'".';
			return false;
		}
		else
		{
			foreach( $value as $v )
			{
				if( !preg_replace("/[^a-z0-9]/i", "", $v) )
				{
					$this->model->last_error = 'Invalid field value for multivalue field "'.$field.'".';
					return false;
				}
			}
		}

		return true;
	}
	
	
	/**
	 * Verifies that the domain value is a valid format.
	 * @param   string  $site_domain_name  The blog domain.
	 * @return  bool    True if the blog domain is a valid format.
	 */
	private function is_valid_site_domain_name( $site_domain_name )
	{
    	return (preg_match("/^([a-z\d](-*[a-z\d])*)(\.([a-z\d](-*[a-z\d])*))*$/i", $site_domain_name) //valid chars check
            && preg_match("/^.{1,253}$/", $site_domain_name) //overall length check
            && preg_match("/^[^\.]{1,63}(\.[^\.]{1,63})*$/", $site_domain_name)   ); //length of each label
	}
	
	
	/**
	 * Adds an OrgHub user to the database.
	 * @param   array     $args  An array of data about a user.
	 * @return  int|bool  The id of the inserted user or false on failure.
	 */
	public function add_user( &$args )
	{
		//
		// Check the args to determine that all needed values are present and valid.
		//
		if( !$this->check_args( $args ) ) return false;

		//
		// If user already exists, then update the user.
		//
		$db_user = $this->get_user_by_username( $args['username'] );
		if( $db_user )
		{
			$args['status'] = $db_user['status'];
			if( (!$db_user['status']) || ($db_user['status'] == 'inactive') ) $args['status'] = 'new';
			return $this->update_user( $db_user['id'], $args );
		}
		
		global $wpdb;
		
		//
		// Insert new user into Users table.
		//
		$result = $wpdb->insert(
			self::$user_table,
			array(
				'username'			=> $args['username'],
				'first_name'		=> $args['first_name'],
				'last_name'			=> $args['last_name'],
				'email'				=> $args['email'],
				'description'		=> $args['description'],
				'site_domain'		=> $args['site_domain'],
				'site_path'			=> $args['site_path'],
				'status'			=> 'new',
			),
			array( '%s', '%s', '%s', '%s', '%s', '%s', '%s' )
		);
		
		//
		// Check to make sure insertion was successful.
		//
		$user_id = $wpdb->insert_id;
		if( !$user_id )
		{
			$this->model->last_error = 'Unable to insert user.';
			return false;
		}
		
		//
		// Insert the user's types.
		//
		foreach( $args['type'] as $type )
		{
			$wpdb->insert(
				self::$type_table,
				array(
					'user_id'		=> $user_id,
					'type'			=> $type,
				),
				array( '%d', '%s' )
			);
		}
		
		//
		// Insert the user's categories.
		//
		foreach( $args['category'] as $category )
		{
			$wpdb->insert(
				self::$category_table,
				array(
					'user_id'		=> $user_id,
					'category'		=> $category,
				),
				array( '%d', '%s' )
			);
		}
		
		//
		// Insert the user's connections sites.
		//
		foreach( $args['connections_sites'] as $site )
		{
			$wpdb->insert(
				self::$connections_table,
				array(
					'user_id'		=> $user_id,
					'site'			=> $site,
					'required'		=> 1,
				),
				array( '%d', '%s', '%d' )
			);
		}
		
		return $user_id;
	}
	
	
	/**
	 * Updates an OrgHub user in the database.
	 * @param   int       $id    The user's id (not the WordPress user id).
	 * @param   array     $args  An array of data about a user.
	 * @return  int|bool  The id of the updated user or false on failure.
	 */
	public function update_user( $id, &$args )
	{
		global $wpdb;
		
		//
		// Update user in Users table.
		//
		$result = $wpdb->update(
			self::$user_table,
			array(
				'username'			=> $args['username'],
				'first_name'		=> $args['first_name'],
				'last_name'			=> $args['last_name'],
				'email'				=> $args['email'],
				'description'		=> $args['description'],
				'site_domain'		=> $args['site_domain'],
				'site_path'			=> $args['site_path'],
				'status'			=> $args['status'],
			),
			array( 'id' => intval( $id ) ),
			array( '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s' ),
			array( '%d' )
		);

		//
		// Check to make sure update was successful.
		//
		if( $result === false )
		{
			$this->model->last_error = 'Unable to update user.';
			return false;
		}
		
		//
		// Update the user's types.
		//
		$current_values = $wpdb->get_col( 
			$wpdb->prepare(
				"SELECT type FROM ".self::$type_table." WHERE user_id=%d",
				$id
			)
		);
		
		foreach( $current_values as $cv )
		{
			if( !in_array($cv, $args['type']) )
			{
				$wpdb->delete(
					self::$type_table,
					array(
						'user_id'		=> $id,
						'type'			=> $cv,
					),
					array( '%d', '%s' )
				);
			}
		}
		
		foreach( $args['type'] as $at )
		{
			if( !in_array($at, $current_values) )
			{
				$wpdb->insert(
					self::$type_table,
					array(
						'user_id'		=> $id,
						'type'			=> $at,
					),
					array( '%d', '%s' )
				);
			}
		}	
			
		//
		// Update the user's categories.
		//
		$current_values = $wpdb->get_col( 
			$wpdb->prepare(
				"SELECT category FROM ".self::$category_table." WHERE user_id=%d",
				$id
			)
		);
		
		foreach( $current_values as $cv )
		{
			if( !in_array($cv, $args['category']) )
			{
				$wpdb->delete(
					self::$category_table,
					array(
						'user_id'		=> $id,
						'category'		=> $cv,
					),
					array( '%d', '%s' )
				);
			}
		}
		
		foreach( $args['category'] as $ac )
		{
			if( !in_array($ac, $current_values) )
			{
				$wpdb->insert(
					self::$category_table,
					array(
						'user_id'		=> $id,
						'category'		=> $ac,
					),
					array( '%d', '%s' )
				);
			}
		}	
		
		//
		// Update the user's connections sites.
		//
		$current_values = $wpdb->get_results( 
			$wpdb->prepare(
				"SELECT site, required, post_id FROM ".self::$connections_table." WHERE user_id=%d",
				$id
			),
			ARRAY_A
		);
		
		foreach( $current_values as $cv )
		{
			if( !in_array($cv['site'], $args['connections_sites']) )
			{
				if( !$cs['post_id'] )
				{
					$wpdb->delete(
						self::$connections_table,
						array(
							'user_id'		=> $id,
							'site'			=> $cv['site'],
						),
						array( '%d', '%s' )
					);
				}
				elseif( $cv['required'] )
				{
					$wpdb->update(
						self::$connections_table,
						array(
							'required'		=> 0,
						),
						array(
							'user_id'		=> $id,
							'site'			=> $cv['site'],
						),
						array( '%d' ),
						array( '%d', '%s' )
					);
				}
			}
		}
		
		foreach( $args['connections_sites'] as $acs )
		{
			if( !$this->model->in_array_by_key($acs, 'site', $current_values) )
			{
				$wpdb->insert(
					self::$connections_table,
					array(
						'user_id'		=> $id,
						'site'			=> $acs,
						'required'		=> 1,
					),
					array( '%d', '%s', '%d' )
				);
			}
		}
		
		$this->update_user_data( $id );
		
		return $id;
	}
	

	/**
	 * Deletes an OrgHub user in the database.
	 * @param   int       $id    The user's id (not the WordPress user id).
	 * @return  bool      True if the user is deleted, otherwise false.
	 */
	public function delete_user( $id )
	{
		global $wpdb;
		
		$wpdb->delete(
			self::$connections_table,
			array( 'user_id' => intval($id) ),
			array( '%d' )
		);

		$wpdb->delete(
			self::$category_table,
			array( 'user_id' => intval($id) ),
			array( '%d' )
		);

		$wpdb->delete(
			self::$type_table,
			array( 'user_id' => intval($id) ),
			array( '%d' )
		);
		
		$wpdb->delete(
			self::$user_table,
			array( 'id' => intval($id) ),
			array( '%d' )
		);
		
		return true;
	}


	/**
	 * Set all non-active users to inactive.
	 * @param  array  $active_user_ids  An array of users that should remain active.
	 */
	public function set_inactive_users( &$active_user_ids )
	{
		global $wpdb;
		$active_user_ids = array_filter( $active_user_ids, 'intval' );
		return $wpdb->query( 
			"UPDATE ".self::$user_table." SET status = 'inactive' WHERE id NOT IN (".implode($active_user_ids, ",").")"
		);
	}
	
	
	
//========================================================================================
//================================================= Retrieve user data from database =====
	
	
	/**
	 * Retrieve a complete list of OrgHub users from the database after filtering.
	 * @param   array   $filter       An array of filter name and values.
	 * @param   array   $search       An array of search columns and phrases.
	 * @param   bool    $only_errors  True if filter out OrgHub users with errors.
	 * @param   string  $orderby      The column to orderby.
	 * @param   int     $offset       The offset of the users list.
	 * @param   int     $limit        The amount of users to retrieve.
	 * @return  array   An array of users given the filtering.
	 */
	public function get_users( $filter = array(), $search = array(), $only_errors = false, $orderby = null, $offset = 0, $limit = -1 )
	{
		global $wpdb;
		
		$groupby = self::$user_table.".id";
		//apl_print("SELECT * FROM ".self::$user_table.' '.$this->filter_sql($filter,$search,$only_errors,$groupby,$orderby,$offset,$limit));
		$users = $wpdb->get_results( "SELECT * FROM ".self::$user_table.' '.$this->filter_sql($filter,$search,$only_errors,$groupby,$orderby,$offset,$limit), ARRAY_A );
		//apl_print($users, '$users (get_users-results)');
		
		if( !is_array($users) ) return false;
		
		foreach( $users as &$user ) $this->prepare_db_user($user);
			
		//apl_print($users, '$users (get_users-end)');
		return $users;
	}


	/**
	 * The amount of OrgHub users from the database after filtering.
	 * @param   array   $filter       An array of filter name and values.
	 * @param   array   $search       An array of search columns and phrases.
	 * @param   bool    $only_errors  True if filter out OrgHub users with errors.
	 * @param   string  $orderby      The column to orderby.
	 * @param   int     $offset       The offset of the users list.
	 * @param   int     $limit        The amount of users to retrieve.
	 * @return  int     A count of users given the filtering.
	 */
	public function get_users_count( $filter = array(), $search = array(), $only_errors = false, $orderby = null, $offset = 0, $limit = -1 )
	{
		global $wpdb;
		
		$groupby = null;
		//apl_print("SELECT COUNT(DISTINCT ".self::$user_table.".id) FROM ".self::$user_table.' '.$this->filter_sql($filter,$search,$only_errors,$groupby,$orderby,$offset,$limit));
		return $wpdb->get_var( "SELECT COUNT(DISTINCT ".self::$user_table.".id) FROM ".self::$user_table.' '.$this->filter_sql($filter,$search,$only_errors,$groupby,$orderby,$offset,$limit) );
	}	


	/**
	 * Retrieve a OrgHub user by its id value.
	 * @param   int         $user_id  The OrgHub user's id (not the WordPress user id).
	 * @return  array|bool  The user's data or false on failure.
	 */
	public function get_user_by_id( $user_id )
	{
		global $wpdb;
		$user = $wpdb->get_row(
			$wpdb->prepare(
				'SELECT * FROM '.self::$user_table.' WHERE id = %d',
				$user_id
			),
			ARRAY_A
		);
		
		if( $this->prepare_db_user($user) ) return $user;
		return false;
	}
	
	
	/**
	 * Retrieve a OrgHub user by its username value.
	 * @param   string      $username  The OrgHub user's username.
	 * @return  array|bool  The user's data or false on failure.
	 */
	public function get_user_by_username( $username )
	{
		global $wpdb;
		$user = $wpdb->get_row(
			$wpdb->prepare(
				'SELECT * FROM '.self::$user_table.' WHERE username = %s',
				$username
			),
			ARRAY_A
		);
		
		if( $this->prepare_db_user($user) ) return $user;
		return false;
	}
	
	
	/**
	 * Creates the SQL needed to complete an SQL statement.
	 * @param   array   $filter       An array of filter name and values.
	 * @param   array   $search       An array of search columns and phrases.
	 * @param   bool    $only_errors  True if filter out OrgHub users with errors.
	 * @param   string  $orderby      The column to orderby.
	 * @param   int     $offset       The offset of the users list.
	 * @param   int     $limit        The amount of users to retrieve.
	 * @return  string  The constructed SQL needed to complete an SQL statement.
	 */
	private function filter_sql( $filter = array(), $search = array(), $only_errors = false, $groupby = null, $orderby = null, $offset = 0, $limit = -1 )
	{
		$where_string = '';
		if( is_array($filter) && count($filter) > 0 )
		{
			$keys = array_keys($filter);
			$where_string = 'WHERE ';
			
			for( $i = 0; $i < count($keys); $i++ )
			{
				$key = $keys[$i];
				$where_string .= ' ( ';
				
				if( $key == 'site' )
				{
					for( $j = 0; $j < count($filter[$key]); $j++ )
					{
						switch( $filter[$key][$j] )
						{
							case 'na-site':
								$where_string .= "site_path = '' ";
								break;
							
							case 'no-site':
								$where_string .= "( site_path != '' AND profile_blog_id IS NULL ) ";
								break;
								
							case 'has-site':
								$where_string .= "( site_path != '' AND profile_blog_id IS NOT NULL ) ";
								break;
						}
						if( $j < count($filter[$key])-1 ) $where_string .= ' OR ';
					}
				}
				else
				{
					for( $j = 0; $j < count($filter[$key]); $j++ )
					{
						$where_string .= $key." = '".$filter[$key][$j]."' ";
						if( $j < count($filter[$key])-1 ) $where_string .= ' OR ';
					}
				}
				$where_string .= ' ) ';
				
				if( $i < count($keys)-1 ) $where_string .= ' AND ';
			}
		}

		if( is_array($search) && count($search) > 0 )
		{
			$keys = array_keys($search);
			if( empty($where_string) ) $where_string = 'WHERE ';
			else $where_string .= ' AND ';
			
			$where_string .= ' ( ';
			for( $i = 0; $i < count($keys); $i++ )
			{
				$key = $keys[$i];
				$where_string .= ' ( ';
				for( $j = 0; $j < count($search[$key]); $j++ )
				{
					$where_string .= $key." LIKE '%".$search[$key][$j]."%' ";
					if( $j < count($search[$key])-1 ) $where_string .= ' OR ';
				}
				$where_string .= ' ) ';
				
				if( $i < count($keys)-1 ) $where_string .= ' OR ';
			}
			$where_string .= ' ) ';
		}
		
		if( $only_errors )
		{
			if( empty($where_string) ) $where_string = 'WHERE ';
			else $where_string .= ' AND ';

			$where_string .= " error IS NOT NULL AND error != '' AND ";
			$where_string .= " wp_user_error IS NOT NULL AND wp_user_id_error != '' AND ";
			$where_string .= " profile_blog_error IS NOT NULL AND profile_blog_error != '' AND ";
			$where_string .= " connections_error IS NOT NULL AND connections_error != '' ";
		}
				
		if( $orderby ) $orderby = 'ORDER BY '.$orderby; else $orderby = '';
		
		$limit = intval( $limit );
		$offset = intval( $offset );
		
		$limit_string = '';
		if( $limit > 0 )
		{
			if( $offset > 0 )
				$limit_string = "LIMIT $offset, $limit";
			else
				$limit_string = "LIMIT $limit";
		}
		
		$join = '';
		$join .= 'LEFT JOIN '.self::$type_table.' ON '.self::$user_table.'.id = '.self::$type_table.'.user_id ';
		$join .= 'LEFT JOIN '.self::$category_table.' ON '.self::$user_table.'.id = '.self::$category_table.'.user_id ';
		$join .= 'LEFT JOIN '.self::$connections_table.' ON '.self::$user_table.'.id = '.self::$connections_table.'.user_id ';

		if( !$groupby ) $groupby = ''; else $groupby = 'GROUP BY '.$groupby;
			
		return $join.' '.$where_string.' '.$groupby.' '.$orderby.' '.$limit_string;
	}
	

	/**
	 * Prepares the user's array with additional information from other database tables.
	 * @param   array  $user  The user's data retreived from the users database.
	 * @return  bool   True if the user was prepared correctly, otherwise False.
	 */
	private function prepare_db_user( &$user )
	{
 		if( !$user ) return false;
 		
 		global $wpdb;
 		
 		if( !empty($user['user_id']) ) $user['id'] = $user['user_id'];
 		
 		$user = array_map(
 			function( $v )
 			{
				return( is_null($v) ) ? '' : $v;
			},
			$user
		);
 		
 		$user['type'] = $wpdb->get_col(
 			$wpdb->prepare(
	 			'SELECT type FROM '.self::$type_table.' WHERE user_id=%d',
	 			$user['id']
	 		)
 		);
 		
 		$user['category'] = $wpdb->get_col(
 			$wpdb->prepare(
 				'SELECT category FROM '.self::$category_table.' WHERE user_id=%d',
 				$user['id']
 			)
 		);
 		
 		$user['connections_sites'] = $wpdb->get_results(
 			$wpdb->prepare(
 				'SELECT site, post_id, required FROM '.self::$connections_table.' WHERE user_id=%d',
 				$user['id']
 			),
 			ARRAY_A
 		);

 		return true;
	}



//========================================================================================
//========================================================= Get/Set from/to database =====
	

	/**
	 * Gets a column in the OrgHub users table.
	 * @param   int     $user_id  The OrgHub user's id (not WordPress user id).
	 * @param   string  $column   The column name.
	 * @return  bool    The requested value or false on failure.
	 */
	public function get_user_column( $user_id, $column )
	{
		global $wpdb;
		return $wpdb->get_var( 
			$wpdb->prepare( 
				"SELECT $column FROM ".self::$user_table." WHERE id=%d",
				intval( $user_id )
			)
		);
	}

	
	/**
	 * Sets a column in the OrgHub users table to a value.
	 * @param   int     $user_id  The OrgHub user's id (not WordPress user id).
	 * @param   string  $column   The column name.
	 * @param   strint  $value    The value to set column to.
	 * @return  bool    True if update was successful, otherwise false.
	 */
	public function set_user_column( $user_id, $column, $value )
	{
		global $wpdb;
		
		$type = '%s';
		if( is_int($value) ) $type = '%d';
		
		if( $value === null )
		{
			$return = $wpdb->query( 
				$wpdb->prepare( 
					"UPDATE ".self::$user_table." SET $column = NULL WHERE id = %d",
					intval( $user_id )
				)
			);
		}
		else
		{
			$return = $wpdb->query( 
				$wpdb->prepare( 
					"UPDATE ".self::$user_table." SET $column = $type WHERE id = %d",
					$value,
					intval( $user_id )
				)
			);
		}
		
		if( $return ) return true;
		return false;
	}


	/**
	 * Gets a column in the OrgHub connections sites table.
	 * @param   int     $user_id  The OrgHub user's id (not WordPress user id).
	 * @param   string  $site     The connections site.
	 * @param   string  $column   The column name.
	 * @return  bool    The requested value or false on failure.
	 */
	public function get_connections_column( $user_id, $site, $column )
	{
		global $wpdb;
		return $wpdb->get_var( 
			$wpdb->prepare( 
				"SELECT $column FROM ".self::$connections_table." WHERE user_id=%d AND site=%s",
				intval( $user_id ),
				$site
			)
		);
	}		


	/**
	 * Sets a column in the OrgHub connections sites table to a value.
	 * @param   int     $user_id  The OrgHub user's id (not WordPress user id).
	 * @param   string  $site     The connections site.
	 * @param   string  $column   The column name.
	 * @param   strint  $value    The value to set column to.
	 * @return  bool    True if update was successful, otherwise false.
	 */
	public function set_connections_column( $user_id, $site, $column, $value )
	{
		global $wpdb;
		
		$type = '%s';
		if( is_int($value) ) $type = '%d';
		
		if( $value === null )
		{
			$return = $wpdb->query( 
				$wpdb->prepare( 
					"UPDATE ".self::$connections_table." SET $column=NULL WHERE user_id=%d AND site=%s",
					intval( $user_id ),
					$site
				)
			);
		}
		else
		{
			$return = $wpdb->query( 
				$wpdb->prepare( 
					"UPDATE ".self::$connections_table." SET $column=$type WHERE user_id=%d AND site=%s",
					$value,
					intval( $user_id ),
					$site
				)
			);
		}
		
		if( $return ) return true;
		return false;
	}	
	


//========================================================================================
//============================================================= Actions / Processing =====
	
	
	/**
	 * Process an OrgHub user.
	 * @param   int|array  $db_user  The user id or info array of an OrgHub user.
	 * @return  bool       True if process was completed, otherwise false.
	 */
	public function process_user( $db_user )
	{
		//
		// If $db_user is an id, then retrieve the user from the database.
		//
		if( is_numeric($db_user) )
		{
			if( !($db_user = $this->get_user_by_id( $db_user )) ) return false;
		}
		
		//
		// Process the user.
		//
		switch( $db_user['status'] )
		{
			case 'inactive':
				$this->process_all_connections_posts( $db_user );
				$this->archive_site( $db_user );
				break;
			
			case 'new':
			case 'active':
				$db_user['wp_user_id'] = $this->create_wp_user( $db_user );
				if( $db_user['wp_user_id'] )
				{
					$this->create_profile_blog( $db_user );
					$this->process_all_connections_posts( $db_user );
				}
				break;
			
			default:
				return false;
				break;
		}
		
		return true;
	}


	/**
	 * Create a WordPress user for an OrgHub user.
	 * @param   int|array  $db_user  The user id or info array of an OrgHub user.
	 * @return  int|bool   The wp_user_id of tne new user or false on failure.
	 */
	public function create_wp_user( $db_user )
	{
		global $wpdb;
		
		//
		// If $db_user is an id, then retrieve the user from the database.
		//
		if( is_numeric($db_user) )
		{
			if( !($db_user = $this->get_user_by_id( $db_user )) ) return false;
		}
		
		//
		// If wp_user_id is already set, then test that the id is valid.
		//
		if( $db_user['wp_user_id'] )
		{
			$user = $this->get_wp_user( $db_user['wp_user_id'] );
			if( $user !== false )
			{
				$this->update_user_data( $db_user, $user );
				
				$this->set_user_column( $db_user['id'], 'wp_user_error', null );
				$this->set_user_column( $db_user['id'], 'status', 'active' );
				return $db_user['wp_user_id'];
			}
		}
		
		//
		// Check if the current username is already in use.
		//
		$user = get_user_by( 'login', $db_user['username'] );
		$user_id = false;
		
		if( $user !== false )
		{
			// set the user's id.
			$user_id = $user->ID;
			$this->update_user_data( $db_user, $user );
		}
		else
		{
			// Determine how the user should be created.
			$create_user_type = $this->model->get_option( 'create-user-type', 'local' );
			
			if( ($create_user_type == 'wpmu-ldap') && (!$this->is_ldap_plugin_active()) )
			{
				$this->model->write_to_log( $db_user['username'], 'WPMU LDAP plugin not active.' );
				$this->set_user_column( $db_user['id'], 'wp_user_error', 'WPMU LDAP plugin not active.' );
				return null;
			}
			
			// Create the user.
			switch( $create_user_type )
			{
				case 'local':
					$random_password = wp_generate_password( 8, false );
					wp_create_user( $db_user['username'], $random_password, $db_user['email'] );

					$user = get_user_by( 'login', $db_user['username'] );
					if( $user )
					{
						$user_id = $user->ID;
						$this->update_user_data( $db_user, $user );
					}
					break;
				
				case 'wpmu-ldap':
					$result = wpmuLdapSearchUser(
						array(
							'username' => $db_user['username'],
							'new_role' => 'subscriber',
							'createUser' => true
						)
					);

					if( is_wp_error($result) )
					{
						$this->model->write_to_log( $db_user['username'], $result );
						$this->set_user_column( $db_user['id'], 'wp_user_error', $result );
						return null;
					}
					
					$user = get_user_by( 'login', $db_user['username'] );
					if( $user )
					{
						$user_id = $user->ID;
						$this->update_user_data( $db_user, $user );
					}
					break;
				
				default:
					$this->model->write_to_log( $db_user['username'], 'Invalid create user type ("'.$create_user_type.'").' );
					$this->set_user_column( $db_user['id'], 'wp_user_error', 'Invalid create user type ("'.$create_user_type.'").' );
					return null;
					break;
			}
		}
		
		//
		// If user was found or created, then update the users data.
		//
		if( $user_id )
		{
			$result = $this->set_user_column( $db_user['id'], 'wp_user_id', $user_id );
			$this->set_user_column( $db_user['id'], 'profile_blog_id', null );
			foreach( $db_user['connections_sites'] as $cs )
			{
				$this->set_connections_column( $db_user['id'], $cs['site'], 'post_id', null );
			}
		
			if( $result !== false )
			{
				$this->set_user_column( $db_user['id'], 'wp_user_error', null );
				$this->set_user_column( $db_user['id'], 'status', 'active' );
				return $user_id;
			}
			else
			{
				$this->model->write_to_log( $db_user['username'], 'Unable to save user id ("'.$user_id.'") to database.' );
				$this->set_user_column( $db_user['id'], 'wp_user_error', 'Unable to save user id ("'.$user_id.'") to database.' );
				return null;
			}
		}
		
		//
		// No user was created.
		//
		$this->model->write_to_log( $db_user['username'], 'Unable to create user.' );
		$this->set_user_column( $db_user['id'], 'wp_user_error', 'Unable to create user.' );
		return null;
	}
	
	
	public function update_user_data( $db_user, $user = null )
	{
		//
		// If $db_user is an id, then retrieve the user from the database.
		//
		if( is_numeric($db_user) )
		{
			if( !($db_user = $this->get_user_by_id( $db_user )) ) return false;
		}
		
		//
		// Verify that the user is valid.
		//
		if( $user === null && $db_user['wp_user_id'] )
		{
			$user = $this->get_wp_user( $db_user['wp_user_id'] );
		}
		
		if( !$user || !($user instanceof WP_User) )
		{
			$this->set_user_column( $db_user['id'], 'wp_user_error', 'Unable to update user data.' );
			return false;
		}
		
		//
		// Update user information.
		//
		
		//
		// TODO: update profile site id and connections post id to urls.
		//
		
//		update_usermeta( $user->ID, 'description', $db_user['description'] );
//		update_usermeta( $user->ID, 'category', implode( ', ', $db_user['category'] ) );
//		update_usermeta( $user->ID, 'type', implode( ', ', $db_user['type'] ) );
//		update_usermeta( $user->ID, 'website', $db_user['profile_blog_id'] );
		
		foreach( $db_user['connections_sites'] as $cs )
		{
//			update_usermeta( $user->ID, 'connections_post_url-'.$cs['site'], $cs['post_id'] );
		}
	}


	/**
	 * Create the OrgHub user's profile blog.
	 * @param   int|array  $db_user       The user id or info array of an OrgHub user.
	 * @return  int|bool   The profile blog id on success, otherwise false.
	 */	
	public function create_profile_blog( $db_user )
	{
		global $wpdb;
		
		//
		// If $db_user is an id, then retrieve the user from the database.
		//
		if( is_numeric($db_user) )
		{
			if( !($db_user = $this->get_user_by_id( $db_user )) ) return false;
		}
		
		//
		// The user's username needs to be setup before creating a profile blog.
		//
		if( !$db_user['wp_user_id'] )
		{
			$this->model->write_to_log( $db_user['username'], 'Wordpress username not set.' );
			$this->set_user_column( $db_user['id'], 'profile_blog_error', 'Wordpress username not set.' );
			return false;
		}
		
		//
		// If a profile blog is already set, then verify that it exists and that the 
		// options are setup correctly.
		//
		if( $db_user['profile_blog_id'] )
		{
			$blog_details = get_blog_details( $db_user['profile_blog_id'] );
			if( $blog_details !== false )
			{
				$this->update_blog_settings( $db_user['profile_blog_id'], false, false, false, false );
				$this->set_user_column( $db_user['id'], 'profile_blog_error', null );
				return $db_user['profile_blog_id'];
			}
			else
			{
				$this->model->write_to_log( $db_user['username'], 'Profile blog id set but cannot be found.' );
				$this->set_user_column( $db_user['id'], 'profile_blog_error', 'Profile blog id set but cannot be found.' );
				return false;
			}
		}
		
		if( !$db_user['site_path'] ) return false;
		if( !$db_user['site_domain'] )
		{
			$url_parts = parse_url( get_site_url(1) );
			$db_user['site_domain'] = $url_parts['host'];
		}
		
		//
		// Check if blog already exists.
		//
		$blog_id = $this->get_blog_by_path( $db_user['site_path'] );
		
		if( $blog_id )
		{
			// Verify the user is administrator of the blog and update blog options.
			add_user_to_blog( $blog_id, $db_user['wp_user_id'], 'administrator' );
		
			switch_to_blog( $blog_id );
			update_option( 'admin_email', $admin_email );
			restore_current_blog();

			$this->update_blog_settings( $blog_id, false, false, false, false );
		}
		else
		{
			// Create the blog.
			$blog_id = wpmu_create_blog( $db_user['site_domain'], '/'.$db_user['site_path'], $db_user['first_name'].' '.$db_user['last_name'], $db_user['wp_user_id'] );
			if( is_wp_error($blog_id))
			{
				$this->model->write_to_log( $db_user['username'], $blog_id->get_error_message() );
				$this->model->write_to_log( '', 'Blog: '.$db_user['site_domain'].'/'.$db_user['site_path'] );
				$this->set_user_column( $db_user['id'], 'profile_blog_error', $blog_id->get_error_message() );
				return false;
			}
			elseif( $blog_id )
			{
				switch_to_blog( $blog_id );
				update_option( 'blogdescription', $db_user['description'] );
				restore_current_blog();
			}
		}
		
		//
		// If blog was found or created, then update the users data.
		//
		if( $blog_id )
		{
			$result = $this->set_user_column( $db_user['id'], 'profile_blog_id', $blog_id );
			$this->update_user_data( $db_user );
		
			if( $result !== false )
			{
				$this->set_user_column( $db_user['id'], 'profile_blog_error', null );
				return $blog_id;
			}
			else
			{
				$this->model->write_to_log( $db_user['username'], 'Unable to save profile blog id ("'.$blog_id.'") to database.' );
				$this->set_user_column( $db_user['id'], 'profile_blog_error', 'Unable to save profile blog id ("'.$blog_id.'") to database.' );
				return false;
			}
		}
		
		//
		// No blog was created.
		//
		$this->model->write_to_log( $db_user['username'], 'Unable to create profile blog.' );
		$this->set_user_column( $db_user['id'], 'profile_blog_error', 'Unable to create profile blog.' );
		return false;
	}


	/**
	 * Arhive the user's profile blog.
	 * @param   int|array  $db_user       The user id or info array of an OrgHub user.
	 * @return  int|bool   The profile blog id on success, otherwise false.
	 */
	public function archive_profile_blog( $db_user )
	{
		//
		// If $db_user is an id, then retrieve the user from the database.
		//
		if( is_numeric($db_user) )
		{
			if( !($db_user = $this->get_user_by_id( $db_user )) ) return false;
		}
		
		//
		// If profile blog has not been set, then cannot archive blog.
		//
		if( !$db_user['profile_blog_id'] ) return false;
		
		//
		// Verify that the blog exists, then update the blog settings.
		//
		$blog_details = get_blog_details( $db_user['profile_blog_id'] );
		if( $blog_details !== false )
		{
			$this->update_blog_settings( 
				$db_user['profile_blog_id'], 
				true, 
				false, 
				false, 
				false 
			);
		}
		
		//
		// Clear the errors.
		//
		$this->set_user_column( $db_user['id'], 'profile_blog_error', null );
		return $db_user['profile_blog_id'];
	}
	
	
	/**
	 * Publish the user's profile blog.
	 * @param   int|array  $db_user       The user id or info array of an OrgHub user.
	 * @return  int|bool   The profile blog id on success, otherwise false.
	 */
	public function publish_profile_blog( $db_user )
	{
		//
		// If $db_user is an id, then retrieve the user from the database.
		//
		if( is_numeric($db_user) )
		{
			if( !($db_user = $this->get_user_by_id( $db_user )) ) return false;
		}
		
		//
		// If profile blog has not been set, then cannot publish blog.
		//
		if( !$db_user['profile_blog_id'] ) return false;
		
		//
		// Verify that the blog exists, then update the blog settings.
		//
		$blog_details = get_blog_details( $db_user['profile_blog_id'] );
		if( $blog_details !== false )
		{
			$this->update_blog_settings( 
				$db_user['profile_blog_id'], 
				false,
				false, 
				false, 
				false 
			);
		}
		
		//
		// Clear the errors.
		//
		$this->set_user_column( $db_user['id'], 'profile_blog_error', null );
		return $db_user['profile_blog_id'];
	}
	
	
	/**
	 * Update a blog's settings in the blogs table.
	 * @param   int   $blog_id   The blog's id.
	 * @param   bool  $archived  True if site should be archived, otherwise false.
	 * @param   bool  $mature    True if site should be mature, otherwise false.
	 * @param   bool  $spam      True if site should be spam, otherwise false.
	 * @param   bool  $deleted   True if site should be deleted, otherwise false.
	 * @return  bool  True if the update was successful, otherwise false.
	 */
	private function update_blog_settings( $blog_id, $archived, $mature, $spam, $deleted )
	{
		global $wpdb;
		$result = $wpdb->update(
			$wpdb->blogs,
			array( 
				'archived' => intval($archived), 
				'mature' => intval($mature),
				'spam' => intval($spam),
				'deleted' => intval($deleted),
			),
			array( 'blog_id' => intval($blog_id) ),
			array( '%d', '%d', '%d', '%d' ),
			array( '%d' )
		);
		
		if( $result ) return true;
		return false;
	}
		

	/**
	 * Process all of the Connections Sites' posts for an OrgHub user.
	 * @param   int|array  $db_user       The user id or info array of an OrgHub user.
	 * @return  int|bool   True on success, otherwise false.
	 */
	public function process_all_connections_posts( $db_user )
	{
		global $wpdb;
		
		//
		// If $db_user is an id, then retrieve the user from the database.
		//
		if( is_numeric($db_user) )
		{
			if( !($db_user = $this->get_user_by_id( $db_user )) ) return false;
		}
		
		//
		// Process each Connections Site's post.
		//
		foreach( $db_user['connections_sites'] as $cs )
		{
			$this->process_connections_post( $db_user, $cs );
		}
		
		return true;
	}

	
	/**
	 * Process a single Connections Site's post for an OrgHub user.
	 * @param   int|array     $db_user           The user id or info array of an OrgHub user.
	 * @param   string|array  $connections_info  The connections site name or info array.
	 * @return  bool          True if the processing occured successfully, otherwise false.
	 */
	private function process_connections_post( $db_user, $connections_info )
	{
		//
		// If $db_user is an id, then retrieve the user from the database.
		//
		if( is_numeric($db_user) )
		{
			if( !($db_user = $this->get_user_by_id( $db_user )) ) return false;
		}

		//
		// If $db_user is an id, then retrieve the user from the database.
		//
		if( is_string($connections_info) )
		{
			if( !($connections_info = $this->get_connections_info( $db_user['id'], $connections_info )) ) return false;
		}

		//
		// The user's username needs to be setup before processing a Connections post.
		//
		if( !$db_user['wp_user_id'] )
		{
			$this->model->write_to_log( $db_user['username'], 'Wordpress username not set.' );
			$this->set_connections_column( $db_user['id'], $connections_info['site'], 'connections_error', 'Wordpress username not set.' );
			return null;
		}
		
		//
		// Verify that the Connections site is a valid Connections site.
		//
		$connections_blog_id = $this->is_connections_site( $connections_info['site'] );
		
		if( !$connections_blog_id )
		{
			$this->model->write_to_log( $db_user['username'], 'Connections site does not exist or does not have Connections Hub plugin activated.' );
			$this->set_connections_column( $db_user['id'], $connections_info['site'], 'connections_error', 'Connections site does not exist or does not have Connections Hub plugin activated.' );
			return null;
		}
		
		switch_to_blog( $connections_blog_id );
				
		//
		// If the post already exists, then update the data, otherwise clear post_id.
		//
		if( $connections_info['post_id'] )
		{
			$post = get_post( $connections_info['post_id'], ARRAY_A );
			
			if( $post )
			{
				// only publish posts for new or active users.
				$post_status = 'publish';
				if( $db_user['status'] == 'inactive' ) $post_status = 'draft';
				
				// update the post's data.
				$connections_post = array(
					'ID'           => $connections_info['post_id'],
					'post_title'   => $db_user['first_name'].' '.$db_user['last_name'],
					'post_name'    => sanitize_title( $db_user['first_name'].' '.$db_user['last_name'] ),
					'post_author'  => $db_user['wp_user_id'],
					'post_status'  => $post_status,
//					'tax_input'    => array( 'connection-group' => $db_user['category'] ),
				);
				wp_update_post( $connections_post );

				update_post_meta( $connections_info['post_id'], 'sort-title', $db_user['last_name'].', '.$db_user['first_name'] );
				update_post_meta( $connections_info['post_id'], 'site-type', 'wp' );
				
				$blog_details = get_blog_details( $db_user['profile_blog_id'] );
				if( $blog_details )
					update_post_meta( $connections_info['post_id'], 'url', $blog_details->siteurl );
				else
					update_post_meta( $connections_info['post_id'], 'url', 'n/a' );
				
				wp_reset_query();

				$this->set_connections_column( $db_user['id'], $connections_info['site'], 'post_id', $connections_info['post_id'] );
				$this->set_connections_column( $db_user['id'], $connections_info['site'], 'connections_error', null );
				$connections_post_id = $connections_info['post_id'];
			}
			else
			{
				// clear out the post id since post does not exist.
				$connections_info['post_id'] = null;
			}
		}
		
		restore_current_blog();

		//
		// Inactive users will not have new Connections Posts created.
		//
		if( $db_user['status'] == 'inactive' ) return false;		
		
		return $this->create_connections_post( $db_user, $connections_info );
	}
	

	/**
	 * Create all of the Connections Sites' posts for an OrgHub user.
	 * @param   int|array  $db_user       The user id or info array of an OrgHub user.
	 * @return  int|bool   True on success, otherwise false.
	 */
	public function create_all_connections_posts( $db_user )
	{
		global $wpdb;
		
		//
		// If $db_user is an id, then retrieve the user from the database.
		//
		if( is_numeric($db_user) )
		{
			if( !($db_user = $this->get_user_by_id( $db_user )) ) return false;
		}
		
		//
		// Process each Connections Site's post.
		//
		foreach( $db_user['connections_sites'] as $cs )
		{
			$this->create_connections_post( $db_user, $cs );
		}
		
		return true;
	}

		
	/**
	 * Creates a Connections Post.
	 * @param   int|array     $db_user           The user id or info array of an OrgHub user.
	 * @param   string|array  $connections_info  The connections site name or info array.
	 * @return  int|bool      The connections post id on success, otherwise false.
	 */
	public function create_connections_post( $db_user, $connections_info )
	{
		//
		// If $db_user is an id, then retrieve the user from the database.
		//
		if( is_numeric($db_user) )
		{
			if( !($db_user = $this->get_user_by_id( $db_user )) ) return false;
		}

		//
		// If $db_user is an id, then retrieve the user from the database.
		//
		if( is_string($connections_info) )
		{
			if( !($connections_info = $this->get_connections_info( $db_user['id'], $connections_info )) ) return false;
		}

		$connections_post_id = false;
		
		//
		// Verify that the Connections site is a valid Connections site.
		//
		$connections_blog_id = $this->is_connections_site( $connections_info['site'] );
		
		if( !$connections_blog_id )
		{
			$this->model->write_to_log( $db_user['username'], 'Connections site does not exist or does not have Connections Hub plugin activated.' );
			$this->set_connections_column( $db_user['id'], $connections_info['site'], 'connections_error', 'Connections site does not exist or does not have Connections Hub plugin activated.' );
			return null;
		}
		
		switch_to_blog( $connections_blog_id );
		
		//
		// If connections post id is not set and user is not inactive, then create post.
		//
		if( !$connections_info['post_id'] )
		{
			// determine was tyep of conneciton to make (synch or manual).
			$connections_post_type = 'manual';		
			if( ($db_user['profile_blog_id']) && (get_blog_details($db_user['profile_blog_id']) !== false) )
			{
				$connections_post_type = 'synch';
			}
			
			// Connections Post settings.
			$connections_post = array(
				'post_title'   => $db_user['first_name'].' '.$db_user['last_name'],
				'post_name'    => sanitize_title( $db_user['first_name'].' '.$db_user['last_name'] ),
				'post_type'    => 'connection',
				'post_status'  => 'publish',
				'post_author'  => $db_user['wp_user_id'],
				'tax_input'    => array( 'connection-group' => $db_user['category'] ),
			);
			
			// determine if the Connections Post already exists.
			$wpquery = new WP_Query(
				array(
					'post_type'  => 'connection',
					'meta_key'   => 'username',
					'meta_value' => $db_user['username'],
					'posts_per_page' => -1,
				)
			);

			if( $wpquery->have_posts() )
			{
				// update the post.
				$wpquery->the_post();
				$post = get_post();
				$connections_post['ID'] = $post->ID;
				$connections_post_id = wp_update_post( $connections_post );
			}
			else
			{
				// insert the post.
				$connections_post_id = wp_insert_post( $connections_post, true );
			}
		
			wp_reset_query();
			
			// determine if the post was created successfully.
			if( !$connections_post_id )
			{
				if( isset($connections_post['ID']) )
				{
					$this->model->write_to_log( $db_user['username'], 'Unable to update Connections Post ("'.$connections_post['ID'].'")' );
					$this->set_connections_column( $db_user['id'], $connections_info['site'], 'connections_error', 'Unable to update Connections Post ("'.$connections_post['ID'].'")' );
				}
				else
				{
					$this->model->write_to_log( $db_user['username'], 'Unable to insert Connection Post.' );
					$this->set_connections_column( $db_user['id'], $connections_info['site'], 'connections_error', 'Unable to insert Connection Post.' );
				}
			}
			else
			{
				// update Connections post options.
				update_post_meta( $connections_post_id, 'sort-title', $db_user['last_name'].', '.$db_user['first_name'] );
				update_post_meta( $connections_post_id, 'username', $db_user['username'] );
				update_post_meta( $connections_post_id, 'site-type', 'wp' );
				update_post_meta( $connections_post_id, 'entry-method', $connections_post_type );
		
				$blog_details = get_blog_details( $db_user['profile_blog_id'] );
				if( $blog_details )
					update_post_meta( $connections_post_id, 'url', $blog_details->siteurl );
				else
					update_post_meta( $connections_post_id, 'url', 'n/a' );
			
				$this->update_user_data( $db_user );
			}
		}
		
		restore_current_blog();

		//
		// Connections post was created or updated successfully.
		//
		if( $connections_post_id )
		{
			$result = $this->set_connections_column( $db_user['id'], $connections_info['site'], 'post_id', $connections_post_id );
		
			if( $result !== false )
			{
				$this->set_connections_column( $db_user['id'], $connections_info['site'], 'connections_error', null );
				return $connections_post_id;
			}
			else
			{
				$this->model->write_to_log( $db_user['username'], 'Unable to save connections post id ("'.$connections_post_id.'") to database.' );
				$this->set_connections_column( $db_user['id'], $connections_info['site'], 'connections_error', 'Unable to save connections post id ("'.$connections_post_id.'") to database.' );
				return null;
			}
		}
		
		//
		// Connections post was not created successfully.
		//
		$this->model->write_to_log( $db_user['username'], 'Unable to create Connections post.' );
		$this->set_connections_column( $db_user['id'], $connections_info['site'], 'connections_error', 'Unable to create Connections Post.' );
		return null;
	}
	

	/**
	 * Draft all of the Connections Sites' posts for an OrgHub user.
	 * @param   int|array  $db_user       The user id or info array of an OrgHub user.
	 * @return  int|bool   True on success, otherwise false.
	 */
	public function draft_all_connections_posts( $db_user )
	{
		global $wpdb;
		
		//
		// If $db_user is an id, then retrieve the user from the database.
		//
		if( is_numeric($db_user) )
		{
			if( !($db_user = $this->get_user_by_id( $db_user )) ) return false;
		}
		
		//
		// Process each Connections Site's post.
		//
		foreach( $db_user['connections_sites'] as $cs )
		{
			$this->draft_connections_post( $db_user, $cs );
		}
		
		return true;
	}
	
		
	/**
	 * Draft a Connections Post.
	 * @param   int|array     $db_user           The user id or info array of an OrgHub user.
	 * @param   string|array  $connections_info  The connections site name or info array.
	 * @return  int|bool      The connections post id on success, otherwise false.
	 */
	public function draft_connections_post( $db_user, $connections_info )
	{
		global $wpdb;
		
		//
		// If $db_user is an id, then retrieve the user from the database.
		//
		if( is_numeric($db_user) )
		{
			if( !($db_user = $this->get_user_by_id( $db_user )) ) return false;
		}

		//
		// If $db_user is an id, then retrieve the user from the database.
		//
		if( is_string($connections_info) )
		{
			if( !($connections_info = $this->get_connections_info( $db_user['id'], $connections_info )) ) return false;
		}
		
		//
		// Cannot draft post that doesn't exist.
		//
		if( !$connections_info['post_id'] ) return false;
		
		//
		// Detemrine that site is valid Connections site.
		//
		$connections_blog_id = $this->is_connections_site( $connections_site );
		if( !$connections_blog_id )
		{
			$this->model->write_to_log( $db_user['username'], 'Connections site does not exist or does not have Connections Hub plugin activated.' );
			$this->set_connections_column( $db_user['id'], $connections_info['site'], 'connections_error', 'Connections site does not exist or does not have Connections Hub plugin activated.' );
			return false;
		}
		
		switch_to_blog( $connections_blog_id );
		
		//
		// Draft the post, if it exists.
		//
		$post = get_post( $connections_info['post_id'], ARRAY_A );
		if( $post )
		{
			$connections_post = array(
				'ID'           => $connections_info['post_id'],
				'post_status'  => 'draft',
			);
			wp_update_post( $connections_post );
		}

		restore_current_blog();
		
		//
		// Clear errors.
		//
		$this->set_connections_column( $db_user['id'], $connections_info['site'], 'connections_error', null );
		return $connections_post_id;
	}	
	
	
	/**
	 * Publish a Connections Post.
	 * @param   int|array     $db_user           The user id or info array of an OrgHub user.
	 * @param   string|array  $connections_info  The connections site name or info array.
	 * @return  int|bool      The connections post id on success, otherwise false.
	 */
	public function publish_connections_post( $db_user, $connections_info )
	{
		global $wpdb;
		
		//
		// If $db_user is an id, then retrieve the user from the database.
		//
		if( is_numeric($db_user) )
		{
			if( !($db_user = $this->get_user_by_id( $db_user )) ) return false;
		}

		//
		// If $db_user is an id, then retrieve the user from the database.
		//
		if( is_string($connections_info) )
		{
			if( !($connections_info = $this->get_connections_info( $db_user['id'], $connections_info )) ) return false;
		}

		//
		// Cannot publish post that doesn't exist.
		//
		if( !$connections_info['post_id'] ) return false;
		
		//
		// Detemrine that site is valid Connections site.
		//
		$connections_blog_id = $this->is_connections_site( $connections_site );
		if( !$connections_blog_id )
		{
			$this->model->write_to_log( $db_user['username'], 'Connections site does not exist or does not have Connections Hub plugin activated.' );
			$this->set_connections_column( $db_user['id'], $connections_info['site'], 'connections_error', 'Connections site does not exist or does not have Connections Hub plugin activated.' );
			return false;
		}
		
		switch_to_blog( $connections_blog_id );
		
		//
		// Publish the post, if it exists.
		//
		$post = get_post( $connections_info['post_id'], ARRAY_A );
		if( $post )
		{
			$connections_post = array(
				'ID'           => $connections_info['post_id'],
				'post_status'  => 'publish',
			);
			wp_update_post( $connections_post );
		}

		restore_current_blog();
		
		//
		// Clear errors.
		//
		$this->set_connections_column( $db_user['id'], $connections_info['site'], 'connections_error', null );
		return $connections_post_id;
	}	


	/**
	 * Determines if the Connections Site is a valid Connecitons site.
	 * @param   string  $connections_site_slug  The name/slug of the connections site.
	 * @return  bool    True if the site is valid Connections site, otherwise false.
	 */
	public function is_connections_site( $connections_site_slug )
	{
		$blog_id = $this->get_blog_by_path( $connections_site_slug );
		
		if( !$blog_id ) { return false; }
		
		switch_to_blog( $blog_id );
		
		if( !is_plugin_active('connections-hub/main.php') ) { $blog_id = false; }

		restore_current_blog();
		
		return $blog_id;
	}

	
	/**
	 * Get the information related to a Connections site for an OrgHub user.
	 * @param   int     $user_id           The user id of an OrgHub user.
	 * @param   string  $connections_site  The connections site name.
	 * @return  array   An array of a Connections site.
	 */
	public function get_connections_info( $user_id, $connections_site )
	{
		global $wpdb;
		
		return $wpdb->get_row(
			$wpdb->prepare(
				'SELECT site, post_id, required FROM '.self::$connections_table.' WHERE user_id=%d AND site=%s',
 				$user_id,
 				$connections_site
			),
			ARRAY_A
		);
	}
	


//========================================================================================
//================================================================= Connections Data =====


	/**
	 * Gets the Connections Post.
	 * @param   int           $id                The Connections Post's id.
	 * @param   string        $connections_site  The Connections Site's name/slug.
	 * @return  WP_Post|bool  The post on success, otherwise false.
	 */
	public function get_connections_post( $id, $connections_site )
	{
		$connections_blog_id = $this->is_connections_site( $connections_site );
		if( !$connections_blog_id ) return false;
		
		switch_to_blog( $connections_blog_id );
		$post = get_post( intval($id), ARRAY_A );
		restore_current_blog();
		
		if( $post ) return $post;
		return false;
	}
	
	
	/**
	 * Gets the Connections Post's edit link.
	 * @param   int     $id                The Connections Post's id.
	 * @param   string  $connections_site  The Connections Site's name/slug.
	 * @return  string  The url to the post's edit page.
	 */
	public function get_connections_post_edit_link( $id, $connections_site )
	{
		$connections_blog_id = $this->is_connections_site( $connections_site );
		if( !$connections_blog_id ) return false;
		
		switch_to_blog( $connections_blog_id );
		$link = admin_url().'post.php?post='.$id.'&action=edit';
		restore_current_blog();
		
		return $link;
	}



//========================================================================================
//=========================================================================== Export =====
	
	
	/**
	 * Exports a list of users to a CSV.
	 * @param   array   $filter       An array of filter name and values.
	 * @param   array   $search       An array of search columns and phrases.
	 * @param   bool    $only_errors  True if filter out OrgHub users with errors.
	 * @param   string  $orderby      The column to orderby.
	 */
	public function csv_export( $filter = array(), $search = array(), $only_errors = false, $orderby = null )
	{
		$users = $this->get_users( $filter, $search, $only_errors, $orderby );
		
		$headers = array(
			'username',
			'category',
			'first_name',
			'last_name',
			'description',
			'email',
			'site_domain',
			'site_path',
			'connections_sites',
			'type',
		);

		foreach( $users as &$user )
		{
			$u = $user;
			$user = array(
				$u['username'], // username
				$u['category'], // category
				$u['first_name'], // first name
				$u['last_name'], // last name
				$u['description'], // description
				$u['email'], // email
				$u['site_domain'], // blog domain
				$u['site_path'], // blog path
				array_map( function($cs) { return $cs['site']; }, $u['connections_sites'] ), // connections sites
				$u['type'], // type
			);
		}
		
		OrgHub_CsvHandler::export( 'users', $headers, $users );
		exit;
	}
	
	
	
//========================================================================================
//==================================================================== Filter Values =====
	
	
	/**
	 * Gets all status values in the OrgHub Users table.
	 * @return  array  An array of statuses.
	 */
	public function get_all_status_values()
	{
		global $wpdb;
		return $wpdb->get_col( "SELECT DISTINCT status FROM ".self::$user_table );
	}
	

	/**
	 * Gets all type values in the OrgHub Type table.
	 * @return  array  An array of types.
	 */
	public function get_all_type_values()
	{
		global $wpdb;
		return $wpdb->get_col( "SELECT DISTINCT type FROM ".self::$type_table );
	}
	

	/**
	 * Gets all category values in the OrgHub Category table.
	 * @return  array  An array of categories.
	 */
	public function get_all_category_values()
	{
		global $wpdb;
		return $wpdb->get_col( "SELECT DISTINCT category FROM ".self::$category_table );
	}


	/**
	 * Gets all domain values in the OrgHub Users table.
	 * @return  array  An array of domains.
	 */
	public function get_all_site_domain_values()
	{
		global $wpdb;
		return $wpdb->get_col( "SELECT DISTINCT site_domain FROM ".self::$user_table );
	}


	/**
	 * Gets all domain values in the OrgHub Connections table.
	 * @return  array  An array of connections sites.
	 */
	public function get_all_connections_sites_values()
	{
		global $wpdb;
		return $wpdb->get_col( "SELECT DISTINCT site FROM ".self::$connections_table );
	}
	

	
//========================================================================================
//=================================================================== Util Functions =====
	
	
	/**
	 * Determines if the WPMU Ldap plugin is active.
	 * @return  bool  True if the plugin is active, otherwise false.
	 */
	public function is_ldap_plugin_active()
	{
		return is_plugin_active_for_network('wpmuldap/ldap_auth.php');
	}
	

	/**
	 * Retrieves the blog_id of a blog using it's path.
	 * @param   string  $path  The path of the blog.
	 * @return  The blog id on success, otherwise false.
	 */
	public function get_blog_by_path( $path )
	{
		global $wpdb;
		
		$base_path = $wpdb->get_var(
			"SELECT path FROM $wpdb->site WHERE id = 1"
		);
		
		if( !$base_path ) $base_path = '/';

		return $wpdb->get_var( 
			$wpdb->prepare(
				"SELECT blog_id FROM $wpdb->blogs WHERE path = %s",
				$base_path.$path.'/'
			)
		);
	}
	
	
	/**
	 * Get a user by it's WordPress user id.
	 * @param   int           $id  The WordPress user id.
	 * @return  WP_User|bool  A WP_User object on success, otherwise false.
	 */
	public function get_wp_user( $id )
	{
		return get_user_by( 'id', intval($id) );
	}
	
	
	/**
	 * Get a blog's information.
	 * @param   int         $id  The blog id.
	 * @return  array|bool  The blog's information on success, otherwise false.
	 */
	public function get_profile_blog( $id )
	{
		global $wpdb;
		$blog_info = $wpdb->get_row(
			$wpdb->prepare(
				'SELECT * FROM '.$wpdb->blogs.' WHERE blog_id = %d',
				$id
			),
			ARRAY_A
		);
		
		if( !$blog_info ) return false;
		
		$blog_details = get_blog_details( intval($id) );
		
		if( !$blog_details ) return false;
		
		$blog_info['siteurl'] = $blog_details->siteurl;
		$blog_info['blogname'] = $blog_details->blogname;
		
		return $blog_info;
	}
	
	
} // class OrgHub_UsersModel
endif; // if( !class_exists('OrgHub_UsersModel') ):

