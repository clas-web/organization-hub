<?php
/**
 * OrgHub_Model
 * 
 * 
 * 
 * @package    orghub
 * @subpackage classes
 * @author     Crystal Barton <cbarto11@uncc.edu>
 */

if( !class_exists('OrgHub_Model') ):
class OrgHub_Model
{
	
	private static $instance = null;
	private static $user_table 			= 'orghub_user';
	private static $type_table 			= 'orghub_type';
	private static $category_table 		= 'orghub_category';
	private static $connections_table 	= 'orghub_connections';
	
	private static $site_table			= 'orghub_site';
	
	public $last_error = null;

	
	/**
	 * Private Constructor.  Needed for a Singleton class.
	 * Creates an OrgHub_Model object.
	 */
	private function __construct()
	{
		global $wpdb;
		self::$user_table        = $wpdb->base_prefix.self::$user_table;
		self::$type_table        = $wpdb->base_prefix.self::$type_table;
		self::$category_table    = $wpdb->base_prefix.self::$category_table;
		self::$connections_table = $wpdb->base_prefix.self::$connections_table;
		self::$site_table        = $wpdb->base_prefix.self::$site_table;
	}
	

	/**
	 * Get the only instance of this class.
	 * @return  OrgHub_Model  A singleton instance of the model class.
	 */
	public static function get_instance()
	{
		if( self::$instance	=== null )
			self::$instance = new OrgHub_Model();
		return self::$instance;
	}


//========================================================================================
//========================================================================= Log file =====


	/**
	 * Clear the log.
	 */
	public function clear_log()
	{
		file_put_contents( ORGANIZATION_HUB_LOG_FILE );
	}
	

	/**
	 * Write the username followed by a log line.
	 *
	 * @param  string  $username  The user's username.
	 * @param  string  $text      The line of text to insert into the log.
	 * @param  bool    $newline   True if a new line character should be inserted after
	 *                              the line, otherwise False.
	 */
	public function write_to_log( $username, $text, $newline = true )
	{
		if( $newline ) $text .= "\n";
		$text = str_pad( $username, 8, ' ', STR_PAD_RIGHT ).' : '.$text;
		file_put_contents( ORGANIZATION_HUB_LOG_FILE, $text, FILE_APPEND );
	}	


//========================================================================================
//===================================================================== Site options =====


	/**
	 * Get an Organization Hub option.
	 *
	 * @param  string       $name     The name of the option.
	 * @param  bool|string  $default  The default value for the option used if the option
	 *                                doesn't currently exist.
	 *
	 * @return bool|string  The value of the option, if it exists, otherwise the default.
	 */
	public function get_option( $name, $default = false )
	{
		$options = get_site_option( ORGANIZATION_HUB_OPTIONS, array() );
		
		if( isset($options[$name]) ) return $options[$name];
		return $default;
	}


	/**
	 * Updates the current value(s) of the Organization Hub options.
	 *
	 * @param  array  $options  The new values.
	 * @param  bool   $merge    True if the new values should be merged into the existing
	 *                            options, otherwise the options are overwrited.
	 */
	public function update_options( $options, $merge = false )
	{
		if( $merge === true )
			$options = array_merge( get_site_option(ORGANIZATION_HUB_OPTIONS, array()), $options );
			
		update_site_option( ORGANIZATION_HUB_OPTIONS, $options );
	}
	
	
	public function update_option( $key, $value )
	{
		$options = array_merge( get_site_option(ORGANIZATION_HUB_OPTIONS, array()), array( $key => $value ) );
		update_site_option( ORGANIZATION_HUB_OPTIONS, $options );
	}


//========================================================================================
//================================================================== Database tables =====


	/**
	 * Create the required database tables.
	 */
	public function create_tables()
	{
		$this->create_users_table();
	}
	
	
	/**
	 * Drop the required database tables.
	 */
	public function delete_tables()
	{
		$this->delete_users_table();
	}


	/**
	 * Clear the required database tables.
	 */
	public function clear_tables()
	{
		$this->clear_users_table();
	}
	
	
//========================================================================================
//============================================================= Users database table =====


	/**
	 * Create the Users database table and all associated tables.
	 */
	public function create_users_table()
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
				  wp_user_id_warning text DEFAULT NULL,
				  wp_user_id_error text DEFAULT NULL,
				  profile_site_id bigint(20) DEFAULT NULL,
				  profile_site_id_warning text DEFAULT NULL,
				  profile_site_id_error text DEFAULT NULL,
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
				  post_id_warning text DEFAULT NULL,
				  post_id_error text DEFAULT NULL,
				  PRIMARY KEY  (id)
				) ENGINE=InnoDB $db_charset_collate;";
		
        dbDelta($sql);

		$sql = "CREATE TABLE ".self::$site_table." (
				  id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
				  blog_id bigint(20) unsigned NOT NULL UNIQUE,
				  url text NOT NULL DEFAULT '',
				  title text NOT NULL DEFAULT '',
				  num_posts bigint(20) NOT NULL DEFAULT 0,
				  num_pages bigint(20) NOT NULL DEFAULT 0,
				  num_comments bigint(20) NOT NULL DEFAULT 0,
				  last_post_url text NOT NULL DEFAULT '',
				  last_post_date datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
				  last_post_status text NOT NULL DEFAULT '',
				  last_comment_url text NOT NULL DEFAULT '',
				  last_comment_date datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
				  admin_email text NOT NULL DEFAULT '',
				  status text NOT NULL DEFAULT '',
				  PRIMARY KEY  (id)
				) ENGINE=InnoDB $db_charset_collate;";
		
        dbDelta($sql);
	}
	
	
	/**
	 * Drop the Users database table and all associated tables.
	 */
	public function delete_users_table()
	{
		global $wpdb;
		$wpdb->query( 'DROP TABLE '.self::$connections_table.';' );
		$wpdb->query( 'DROP TABLE '.self::$category_table.';' );
		$wpdb->query( 'DROP TABLE '.self::$type_table.';' );
		$wpdb->query( 'DROP TABLE '.self::$user_table.';' );
		$wpdb->query( 'DROP TABLE '.self::$site_table.';' );
	}


	/**
	 * Clear the Users database table and all associated tables.
	 */
	public function clear_users_table()
	{
		global $wpdb;
		$wpdb->query( 'DELETE FROM '.self::$connections_table.';' );
		$wpdb->query( 'DELETE FROM '.self::$category_table.';' );
		$wpdb->query( 'DELETE FROM '.self::$type_table.';' );
		$wpdb->query( 'DELETE FROM '.self::$user_table.';' );
		$wpdb->query( 'DELETE FROM '.self::$site_table.';' );
	}
	

//========================================================================================
//============================================================================ USERS =====
	
	
	/**
	 * Verifies that all the required fields are present.
	 *
	 * @param  array  $args  An array of user values.
	 * 
	 * @return  bool  True if the args are valid, otherwise False.
	 */
	private function check_user_args( &$args )
	{
		//orghub_print($args, '$args (check_user_args-start)');
		
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
				$this->last_error = 'Missing required field "'.$required_field.'".';
				return false;
			}
			elseif( !preg_replace("/[^a-z0-9]/i", "", $args[$required_field]) )
			{
				$this->last_error = 'Invalid field value for required field "'.$required_field.'".';
				return false;
			}
		}
		
		//
		// Verify that columns with multiple columns have valid values.
		//
		if( !$this->check_multiple_value_arg('type', $args['type']) ) return false;
		if( !$this->check_multiple_value_arg('category', $args['category']) ) return false;
		
		//
		// If not specified, populate site_domain column with its default value.
		// If site_domain is specified, then verify that the value is valid.
		//
		if( (!in_array('site_domain', array_keys($args))) || (!$args['site_domain']) )
		{
			$args['site_domain'] = get_site_url( 1 );
		}
		elseif( !$this->is_valid_site_domain_name($args['site_domain']) )
		{
			$this->last_error = 'Invalid field value for field "site_domain".';
			return false;
		}
		
		//
		// If not specified, populate connections_sites column with its default value.
		//
		if( (!in_array('connections_sites', array_keys($args))) || (!$args['connections_sites']) )
		{
			$args['connections_sites'] = '';
		}
		elseif( !$this->check_multiple_value_arg('connections_sites', $args['connections_sites']) )
		{
			return false;
		}
		
		//orghub_print($args, '$args (check_user_args-end)');
		return true;
	}
	
	
	/**
	 * 
	 */
	private function check_multiple_value_arg( $field, &$value )
	{
		$value = str_getcsv( $value, ",", '"', "\\" );
		if( count($value) === 0 )
		{
			$this->last_error = 'Invalid field value for field "'.$field.'".';
			return false;
		}
		else
		{
			foreach( $value as $v )
			{
				if( !preg_replace("/[^a-z0-9]/i", "", $v) )
				{
					$this->last_error = 'Invalid field value for multivalue field "'.$field.'".';
					return false;
				}
			}
		}

		return true;
	}
	
	
	/**
	 * 
	 */
	private function is_valid_site_domain_name($site_domain_name)
	{
    	return (preg_match("/^([a-z\d](-*[a-z\d])*)(\.([a-z\d](-*[a-z\d])*))*$/i", $site_domain_name) //valid chars check
            && preg_match("/^.{1,253}$/", $site_domain_name) //overall length check
            && preg_match("/^[^\.]{1,63}(\.[^\.]{1,63})*$/", $site_domain_name)   ); //length of each label
	}
	
	
	/**
	 * 
	 */
	private function prepare_db_user( &$user )
	{
 		if( !$user ) return false;
 		
 		global $wpdb;
 		
 		if( !empty($user['user_id']) ) $user['id'] = $user['user_id'];
 		
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
	
	
	/**
	 *
	 */
	public function add_user( &$args )
	{
		if( !$this->check_user_args( $args ) ) return false;

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
			$this->last_error = 'Unable to insert user.';
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
	 * Set all non-active users to inactive.
	 */
	public function set_inactive_users( &$active_user_ids )
	{
		global $wpdb;
		$active_user_ids = array_filter( $active_user_ids, 'intval' );
		return $wpdb->query( 
			"UPDATE ".self::$user_table." SET status = 'inactive' WHERE id NOT IN (".implode($active_user_ids, ",").")"
		);
	}
	
	
	/**
	 *
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
			$this->last_error = 'Unable to update user.';
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
			if( !$this->in_array_field($acs, 'site', $current_values) )
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
	
		
		return $id;
	}
	
	
	/**
	 * 
	 */
	private function in_array_field($needle, $needle_field, $haystack, $strict = false) { 
		if( $strict )
		{
			foreach( $haystack as $item )
			{
				if( isset($item[$needle_field]) && $item[$needle_field] === $needle ) return true;
			}
		}
		else
		{
			foreach( $haystack as $item )
			{
				if( isset($item[$needle_field]) && $item[$needle_field] == $needle ) return true; 
			}
		}
		
		return false; 
	}
	
	
	/**
	 *
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
	 * 
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
		
		//orghub_print( $user, '$user (get_user_by_id-row)' );
		if( $this->prepare_db_user($user) ) 
		{
			//orghub_print( $user, '$user (get_user_by_id-end)' );
			return $user;
		}
		return false;
	}
	
	
	/**
	 * 
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
	 * 
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
				for( $j = 0; $j < count($filter[$key]); $j++ )
				{
					$where_string .= $key." = '".$filter[$key][$j]."' ";
					if( $j < count($filter[$key])-1 ) $where_string .= ' OR ';
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
			$where_string .= " wp_user_id_error IS NOT NULL AND wp_user_id_error != '' AND ";
			$where_string .= " profile_site_id_error IS NOT NULL AND profile_site_id_error != '' AND ";
			$where_string .= " post_id_error IS NOT NULL AND post_id_error != '' ";
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
	 *
	 */
	public function get_users( $filter = array(), $search = array(), $only_errors = false, $orderby = null, $offset = 0, $limit = -1 )
	{
		global $wpdb;
		
		$groupby = self::$user_table.".id";
		//orghub_print("SELECT * FROM ".self::$user_table.' '.$this->filter_sql($filter,$search,$only_errors,$groupby,$orderby,$offset,$limit));
		$users = $wpdb->get_results( "SELECT * FROM ".self::$user_table.' '.$this->filter_sql($filter,$search,$only_errors,$groupby,$orderby,$offset,$limit), ARRAY_A );
		//orghub_print($users, '$users (get_users-results)');
		
		if( !is_array($users) ) return false;
		
		foreach( $users as &$user ) $this->prepare_db_user($user);
			
		//orghub_print($users, '$users (get_users-end)');
		return $users;
	}


	/**
	 *
	 */
	public function get_users_count( $filter = array(), $search = array(), $only_errors = false, $orderby = null, $offset = 0, $limit = -1 )
	{
		global $wpdb;
		
		$groupby = null;
		//orghub_print("SELECT COUNT(DISTINCT ".self::$user_table.".id) FROM ".self::$user_table.' '.$this->filter_sql($filter,$search,$only_errors,$groupby,$orderby,$offset,$limit));
		return $wpdb->get_var( "SELECT COUNT(DISTINCT ".self::$user_table.".id) FROM ".self::$user_table.' '.$this->filter_sql($filter,$search,$only_errors,$groupby,$orderby,$offset,$limit) );
	}	

	
	/**
	 * 
	 */
	public function set_user_status( $id, $status )
	{
		global $wpdb;
		
		$result = $wpdb->update(
			self::$user_table,
			array( 'status' => $status ),
			array( 'id' => intval( $id ) ),
			array( '%s' ),
			array( '%d' )
		);
	
		if( $result !== false )
		{
			return $status;
		}
		else
		{
			$this->write_to_log( $db_user['username'], 'Unable to set user status in database: '.$id.' => '.$status );
			return false;
		}
	}	


//========================================================================================
//============================================================= USERS - Foreign Keys =====


	/**
	 *
	 */
	public function update_wp_user_id( $id, $wp_user_id )
	{
		return $this->set_users_value( $id, 'wp_user_id', $wp_user_id );
	}


	/**
	 *
	 */
	public function update_profile_site_id( $id, $profile_site_id )
	{
		return $this->set_users_value( $id, 'profile_site_id', $profile_site_id );
	}


	/**
	 *
	 */
	public function update_connections_post_id( $id, $connections_site, $connections_post_id )
	{
		global $wpdb;
		
		$current_value = $wpdb->get_var(
			$wpdb->prepare(
				'SELECT id FROM '.self::$connections_table.' WHERE user_id=%d AND site=%s',
				intval($id),
				$connections_site
			)
		);
		
		if( $current_value )
		{
			if( $connections_post_id === null )
			{
				return $wpdb->query( 
					$wpdb->prepare( 
						"UPDATE ".self::$connections_table." SET post_id=NULL WHERE user_id=%d AND site=%s",
						intval( $id ),
						$connections_site
					)
				);
			}

			return $wpdb->query( 
				$wpdb->prepare( 
					"UPDATE ".self::$connections_table." SET post_id=%d WHERE user_id=%d AND site=%s",
					intval( $connections_post_id ),
					intval( $id ),
					$connections_site
				)
			);
		}
		else
		{
			if( $connections_post_id === null )
			{
				return $wpdb->insert(
					self::$connections_table,
					array(
						'user_id'		=> $id,
						'site'			=> $connections_site,
						'required'		=> 0,
					),
					array( '%d', '%s', '%d' )
				);
			}
			
			return $wpdb->insert(
				self::$connections_table,
				array(
					'user_id'			=> $id,
					'site'				=> $connections_site,
					'post_id'			=> $connections_post_id,
					'required'			=> 0,
				),
				array( '%d', '%s', '%d', '%d' )
			);
		}
		
		return false;
	}
	
	
	/**
	 *
	 */
	private function set_users_value( $id, $key, $value )
	{
		global $wpdb;
		
		if( $value === null )
		{
			return $wpdb->query( 
				$wpdb->prepare( 
					"UPDATE ".self::$user_table." SET $key = NULL WHERE id = %d",
					intval( $id )
				)
			);
		}

		return $wpdb->query( 
			$wpdb->prepare( 
				"UPDATE ".self::$user_table." SET $key = %d WHERE id = %d",
				intval( $value ),
				intval( $id )
			)
		);
	}
	

//========================================================================================
//====================================================== USERS - Errors and Warnings =====


	public function set_user_value( $id, $key, $value, $value_type='%s' )
	{
		global $wpdb;
		
		if( $value = null )
		{
			return $wpdb->query( 
				$wpdb->prepare( 
					"UPDATE ".self::$user_table." SET $key=NULL WHERE id=%d",
					intval( $id )
				)
			);
		}
		
		return $wpdb->query( 
			$wpdb->prepare( 
				"UPDATE ".self::$user_table." SET $key=$value_type WHERE id=%d",
				intval( $value ),
				intval( $id )
			)
		);
	}

	public function get_user_value( $id, $key )
	{
		global $wpdb;
		return $wpdb->get_var( 
			$wpdb->prepare( 
				"SELECT $key FROM ".self::$user_table." WHERE id=%d",
				intval( $id )
			)
		);
	}
	
	public function set_user_connections_value( $id, $connections_site, $key, $value, $value_type = '%s' )
	{
		global $wpdb;
		
		if( $value === null )
		{
			return $wpdb->query( 
				$wpdb->prepare( 
					"UPDATE ".self::$connections_table." SET $key=NULL WHERE user_id=%d AND site=%s",
					intval( $id ),
					$connections_site
				)
			);
		}
		
		return $wpdb->query( 
			$wpdb->prepare( 
				"UPDATE ".self::$connections_table." SET $key=%s WHERE user_id=%d AND site=%s",
				$value,
				intval( $id ),
				$connections_site
			)
		);
		
	}

	public function get_user_connections_value( $id, $connections_site, $key )
	{
		global $wpdb;
		return $wpdb->get_var( 
			$wpdb->prepare( 
				"SELECT $key FROM ".self::$connections_table." WHERE user_id=%d AND site=%s",
				intval( $id ),
				$connections_site
			)
		);
	}		
	
	public function set_user_error( $id, $error )
	{
		$this->set_user_value( $id, 'error', $error );
	}

	public function get_user_error( $id )
	{
		return $this->get_user_value( $id, 'error' );
	}
	
	public function set_user_warning( $id, $warning )
	{
		$this->set_user_value( $id, 'warning', $warning );
	}

	public function get_user_warning( $id )
	{
		return $this->get_user_value( $id, 'warning' );
	}

	public function set_wp_user_error( $id, $error )
	{
		$this->set_user_value( $id, 'wp_user_id_error', $error );
	}

	public function get_wp_user_error( $id )
	{
		return $this->get_user_value( $id, 'wp_user_id_error' );
	}
	
	public function set_wp_user_warning( $id, $warning )
	{
		$this->set_user_value( $id, 'wp_user_id_warning', $warning );
	}

	public function get_wp_user_warning( $id )
	{
		return $this->get_user_value( $id, 'wp_user_id_warning' );
	}

	public function set_profile_site_error( $id, $error )
	{
		$this->set_user_value( $id, 'profile_site_id_error', $error );
	}

	public function get_profile_site_error( $id )
	{
		return $this->get_user_value( $id, 'profile_site_id_error' );
	}
	
	public function set_profile_site_warning( $id, $warning )
	{
		$this->set_user_value( $id, 'profile_site_id_warning', $warning );
	}
	
	public function get_profile_site_warning( $id )
	{
		return $this->get_user_value( $id, 'profile_site_id_warning' );
	}

	public function set_connections_error( $id, $connections_site, $error )
	{
		$this->set_user_connections_value( $id, $connections_site, 'post_id_error', $error );
	}
	
	public function get_connections_error( $id, $connections_site )
	{
		return $this->get_user_connections_value( $id, $connections_site, 'post_id_error' );
	}

	public function set_connections_warning( $id, $connections_site, $warning )
	{
		$this->set_user_connections_value( $id, $connections_site, 'post_id_warning', $error );
	}
	
	public function get_connections_warning( $id, $connections_site )
	{
		return $this->get_user_connections_value( $id, $connections_site, 'post_id_warning' );
	}



//========================================================================================
//=============================================================== USERS - Processing =====


	/**
	 *
	 */
	public function process_user( $db_user )
	{
		if( is_numeric($db_user) )
		{
			$db_user = $this->get_user_by_id( $db_user );
			if( !$db_user ) return null;
		}

		switch( $db_user['status'] )
		{
			case 'inactive':
				$this->process_connections_posts( $db_user );
				$this->archive_site( $db_user );
				break;
			
			case 'new':
			case 'active':
				$db_user['wp_user_id'] = $this->create_username( $db_user );
				if( $db_user['wp_user_id'] )
				{
					$db_user['profile_site_id'] = $this->create_site( $db_user );
					$db_user['connections_post_id'] = $this->process_connections_posts( $db_user );
				}
				$this->set_user_status( $db_user['id'], 'active' );
				break;
		}
	}


	/**
	 *
	 */
	public function create_username( $db_user )
	{
		global $wpdb;
		
		if( is_numeric($db_user) )
		{
			$db_user = $this->get_user_by_id( $db_user );
			if( !$db_user ) return null;
		}
		
		$user_id = false;
		
		if( $db_user['wp_user_id'] )
		{
			$user = $this->get_wp_user( $db_user['wp_user_id'] );
			if( $user !== false )
			{
				$this->set_wp_user_error( $db_user['id'], null );
				return $db_user['wp_user_id'];
			}
		}
		
		$user = get_user_by( 'login', $db_user['username'] );
		
		if( $user === false )
		{
			$create_user_type = $this->get_option( 'create-user-type', 'local' );
			
			if( ($create_user_type == 'wpmu-ldap') && (!$this->is_ldap_plugin_active()) )
			{
				$this->write_to_log( $db_user['username'], 'WPMU LDAP plugin not active.' );
				$this->set_wp_user_error( $db_user['id'], 'WPMU LDAP plugin not active.' );
				return null;
			}
			
			switch( $create_user_type )
			{
				case 'local':
					$random_password = wp_generate_password( 8, false );
					$user_id = wp_create_user( $db_user['username'], $random_password, $db_user['email'] );
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
						$this->write_to_log( $db_user['username'], $result );
						$this->set_wp_user_error( $db_user['id'], $result );
						return null;
					}
					
					$user = get_user_by( 'login', $db_user['username'] );
					
					if( $user ) $user_id = $user->ID;
					break;
				
				default:
					$this->write_to_log( $db_user['username'], 'Invalid create user type ("'.$create_user_type.'").' );
					$this->set_wp_user_error( $db_user['id'], 'Invalid create user type ("'.$create_user_type.'").' );
					return null;
					break;
			}
		}
		else
		{
			$user_id = $user->ID;
		}
		
		if( $user_id )
		{
			$result = $this->update_wp_user_id( $db_user['id'], $user_id );
			$this->update_profile_site_id( $db_user['id'], null );
			foreach( $db_user['connections_sites'] as $cs )
			{
				$this->update_connections_post_id( $db_user['id'], $cs['site'], null );
			}
		
			if( $result !== false )
			{
				$this->set_wp_user_error( $db_user['id'], null );
				return $user_id;
			}
			else
			{
				$this->write_to_log( $db_user['username'], 'Unable to save user id ("'.$user_id.'") to database.' );
				$this->set_wp_user_error( $db_user['id'], 'Unable to save user id ("'.$user_id.'") to database.' );
				return null;
			}
		}

		$this->write_to_log( $db_user['username'], 'Unable to create user.' );
		$this->set_wp_user_error( $db_user['id'], 'Unable to create user.' );
		return null;
	}


	/**
	 *
	 */	
	public function create_site( $db_user, $force_create = false )
	{
		global $wpdb;
		
		//
		// If only a number is passed in, then retreive the user's database information.
		//
		if( is_numeric($db_user) )
		{
			$db_user = $this->get_user_by_id( $db_user );
			if( !$db_user ) return null;
		}
		
		//
		// The user's username needs to be setup before creating a profile site.
		//
		if( !$db_user['wp_user_id'] )
		{
			$this->write_to_log( $db_user['username'], 'Wordpress username not set.' );
			$this->set_profile_site_error( $db_user['id'], 'Wordpress username not set.' );
			return null;
		}
		
		//
		// If a profile site is already set, then verify that it exists and that the 
		// options are setup correctly (not archived).
		//
		if( $db_user['profile_site_id'] )
		{
			$blog_details = get_blog_details( $db_user['profile_site_id'] );
			if( $blog_details !== false )
			{
				$this->update_blog_settings( $db_user['profile_site_id'], false, false, false, false );
				$this->set_profile_site_error( $db_user['id'], null );
				return $db_user['profile_site_id'];
			}
			else
			{
				$this->write_to_log( $db_user['username'], 'Profile site id set but cannot be found.' );
				$this->set_profile_site_error( $db_user['id'], 'Profile site id set but cannot be found.' );
				return null;
			}
		}

		// check if site already exists.
		$blog_id = $this->get_blog_by_path( $db_user['site_path'] );
		
		if( $blog_id )
		{
			$blog_details = get_blog_details( $blog_id );
			if( $blog_details )
			{
				switch_to_blog( $blog_id );
				
				$user = get_user_by( 'email', get_bloginfo('admin_email') );
				if( (!$user) || ($user->slug !== $db_user['username']) )
				{
					$user = null;
					$blog_users = get_users( array( 'blog_id' => $blog_id ) );
					foreach( $blog_users as $bu )
					{
						if( $bu->id == $db_user['wp_user_id'] )
						{
							$user = $bu;
							break;
						}
					}
					
					if( $user )
					{
						if( !in_array('administrator', $user->roles) )
							$user->set_role( 'administrator' );
						
						update_option( 'admin_email', $db_user['email'] );
					}
					else
					{
						$this->write_to_log( $db_user['username'], 'Profile site ("'.$db_user['site_path'].'") already exists, but '.$db_user['username'].' user does not exist on the site.' );
						$this->set_profile_site_error( $db_user['id'], 'Profile site ("<a href="'.$blog_details->siteurl.'/wp-admin/users.php" target="_blank">'.$db_user['site_path'].'</a>") already exists, but '.$db_user['username'].' user does not exist on the site.' );
						return null;
					}
				}
				else
				{
					$result = $this->update_profile_site_id( $db_user['id'], $blog_id );
					$this->set_profile_site_error( $db_user['id'], null );
					return $blog_id;					
				}
				
				restore_current_blog();
			}
			else
			{
				$this->write_to_log( $db_user['username'], 'Unable to retreive blog details.' );
				$this->set_profile_site_error( $db_user['id'], 'Unable to retreive blog details.' );
				return null;
			}
			
			$this->update_blog_settings( $blog_id, false, false, false, false );
		}
		else
		{
			$blog_id = wpmu_create_blog( $db_user['site_domain'], '/'.$db_user['site_path'], $db_user['first_name'].' '.$db_user['last_name'], $db_user['wp_user_id'] );
			if( is_wp_error($blog_id))
			{
				$this->write_to_log( $db_user['username'], $blog_id->get_error_message() );
				$this->write_to_log( '', 'Site: '.$db_user['site_domain'].'/'.$db_user['site_path'] );
				$this->set_profile_site_error( $db_user['id'], $blog_id->get_error_message() );
				return null;
			}
		}
		
		if( $blog_id )
		{
			$result = $this->update_profile_site_id( $db_user['id'], $blog_id );
		
			if( $result !== false )
			{
				$this->set_profile_site_error( $db_user['id'], null );
				return $blog_id;
			}
			else
			{
				$this->write_to_log( $db_user['username'], 'Unable to save profile site id ("'.$blog_id.'") to database.' );
				$this->set_profile_site_error( $db_user['id'], 'Unable to save profile site id ("'.$blog_id.'") to database.' );
				return null;
			}
		}
		
		$this->write_to_log( $db_user['username'], 'Unable to create profile site.' );
		$this->set_profile_site_error( $db_user['id'], 'Unable to create profile site.' );
		return null;
	}
	

	/**
	 *
	 */
	public function process_connections_posts( $db_user, $override_type_restriction = false )
	{
		global $wpdb;
		
		if( is_numeric($db_user) )
		{
			$db_user = $this->get_user_by_id( $db_user );
			if( !$db_user ) return null;
		}
		
		foreach( $db_user['connections_sites'] as $cs )
		{
			$this->process_connections_post( $db_user, $cs, $override_type_restriction );
		}
	}
	
	
	public function create_connections_posts( $db_user, $force_create = false )
	{
		global $wpdb;
		
		if( is_numeric($db_user) )
		{
			$db_user = $this->get_user_by_id( $db_user );
			if( !$db_user ) return null;
		}
		
		if( !$db_user['wp_user_id'] )
		{
			$this->write_to_log( $db_user['username'], 'Wordpress username not set.' );
			$this->set_connections_error( $db_user['id'], $connections_info['site'], 'Wordpress username not set.' );
			return null;
		}
		
		foreach( $db_user['connections_sites'] as $cs )
		{
			$this->create_connections_post( $db_user, $cs['site'], $force_create );
		}
	}
	
	public function create_connections_post( $db_user, $connections_site, $force_create = false )
	{
		global $wpdb;
		
		if( is_numeric($db_user) )
		{
			$db_user = $this->get_user_by_id( $db_user );
			if( !$db_user ) return null;
		}
		
		if( !$db_user['wp_user_id'] )
		{
			$this->write_to_log( $db_user['username'], 'Wordpress username not set.' );
			$this->set_connections_error( $db_user['id'], $connections_info['site'], 'Wordpress username not set.' );
			return null;
		}
		
		$connections_blog_id = $this->is_connections_site( $connections_site );

		if( !$connections_blog_id )
		{
			$this->write_to_log( $db_user['username'], 'Connections site does not exist or does not have Connections Hub plugin activated.' );
			$this->set_connections_error( $db_user['id'], $connections_site, 'Connections site does not exist or does not have Connections Hub plugin activated.' );
			return null;
		}
	
		$connections_info = null;
		foreach( $db_user['connections_sites'] as $cs )
		{
			if( $cs['site'] == $connections_site )
			{
				$connections_info = $cs;
				break;
			}
		}
		
		//orghub_print($connections_info, 'connections_info');
		
		if( !$connections_info ) return null;

		if( !$force_create && !$connections_info['required'] )
		{
			$this->set_connections_warning( $db_user['id'], $connections_site, 'Connections site post is not required.' );
			return null;
		}
		
		switch_to_blog( $connections_blog_id );
		
		if( $connections_info['post_id'] )
		{
			$post = get_post( $connections_info['post_id'], ARRAY_A );
			
			if( $post )
			{
				
				$connections_post = array(
					'ID'           => $connections_info['post_id'],
					'post_title'   => $db_user['first_name'].' '.$db_user['last_name'],
					'post_name'    => sanitize_title( $db_user['first_name'].' '.$db_user['last_name'] ),
					'post_author'  => $db_user['wp_user_id'],
					'post_status'  => 'publish',
					'tax_input'    => array( 'connection-group' => $db_user['category'] ),
				);
				wp_update_post( $connections_post );

				update_post_meta( $connections_info['post_id'], 'sort-title', $db_user['last_name'].', '.$db_user['first_name'] );
				update_post_meta( $connections_info['post_id'], 'site-type', 'wp' );
		
				$blog_details = get_blog_details( $db_user['profile_site_id'] );
				if( $blog_details )
					update_post_meta( $connections_info['post_id'], 'url', $blog_details->siteurl );
				else
					update_post_meta( $connections_info['post_id'], 'url', 'n/a' );
				
				wp_reset_query();

				$this->update_connections_post_id( $db_user['id'], $connections_info['site'], $connections_info['post_id'] );
				$this->set_connections_error( $db_user['id'], $connections_info['site'], null );
				$connections_post_id = $connections_info['post_id'];
			}
			else
			{
				$connections_info['post_id'] = null;
			}
		}
		elseif( $db_user['status'] != 'inactive' || $force_create )
		{
			$connections_post_type = 'manual';
		
			if( ($db_user['profile_site_id']) && (get_blog_details($db_user['profile_site_id']) !== false) )
			{
				$connections_post_type = 'synch';
			}
		
			$connections_post = array(
				'post_title'   => $db_user['first_name'].' '.$db_user['last_name'],
				'post_name'    => sanitize_title( $db_user['first_name'].' '.$db_user['last_name'] ),
				'post_type'    => 'connection',
				'post_status'  => 'publish',
				'post_author'  => $db_user['wp_user_id'],
				'tax_input'    => array( 'connection-group' => $db_user['category'] ),
			);
		
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
				$updating_post = true;
				$wpquery->the_post();
				$post = get_post();
				$connections_post['ID'] = $post->ID;
				$connections_post_id = wp_update_post( $connections_post );
			}
			else
			{
				$connections_post_id = wp_insert_post( $connections_post, true );
			}
		
			wp_reset_query();

			if( !$connections_post_id )
			{
				if( isset($connections_post['ID']) )
				{
					$this->write_to_log( $db_user['username'], 'Unable to update Connections Post ("'.$connections_post['ID'].'")' );
					$this->set_connections_error( $db_user['id'], $connections_info['site'], 'Unable to update Connections Post ("'.$connections_post['ID'].'")' );
				}
				else
				{
					$this->write_to_log( $db_user['username'], 'Unable to insert Connection Post.' );
					$this->set_connections_error( $db_user['id'], $connections_info['site'], 'Unable to insert Connection Post.' );
				}

				return null;
			}

			update_post_meta( $connections_post_id, 'sort-title', $db_user['last_name'].', '.$db_user['first_name'] );
			update_post_meta( $connections_post_id, 'username', $db_user['username'] );
			update_post_meta( $connections_post_id, 'site-type', 'wp' );
			update_post_meta( $connections_post_id, 'entry-method', $connections_post_type );
		
			$blog_details = get_blog_details( $db_user['profile_site_id'] );
			if( $blog_details )
				update_post_meta( $connections_post_id, 'url', $blog_details->siteurl );
			else
				update_post_meta( $connections_post_id, 'url', 'n/a' );
		}

		restore_current_blog();
		
		if( $db_user['status'] == 'inactive' && !$force_create ) return null;

		if( $connections_post_id )
		{
			$result = $this->update_connections_post_id( $db_user['id'], $connections_info['site'], $connections_post_id );
		
			if( $result !== false )
			{
				$this->set_connections_error( $db_user['id'], $connections_info['site'], null );
				return $connections_post_id;
			}
			else
			{
				$this->write_to_log( $db_user['username'], 'Unable to save connections post id ("'.$connections_post_id.'") to database.' );
				$this->set_connections_error( $db_user['id'], $connections_info['site'], 'Unable to save connections post id ("'.$connections_post_id.'") to database.' );
				return null;
			}
		}
		
		$this->write_to_log( $db_user['username'], 'Unable to create Connections post.' );
		$this->set_connections_error( $db_user['id'], $connections_info['site'], 'Unable to create Connections Post.' );
		return null;
	}
	
	private function process_connections_post( $db_user, $connections_info, $process_connections_post = false )
	{

// 		if( !$override_type_restriction )
// 		{
// 			$connections_site_types = $this->get_option( 'connections-site-types' );
// 			$connections_site_types = array_filter( explode(',', $connections_site_types), 'trim' );
// 			
// 			$is_correct_connections_site_type = false;
// 			foreach( $db_user['type'] as $user_type )
// 			{
// 				if( !in_array( $user_type, $connections_site_types ) )
// 					$is_correct_connections_site_type = true;
// 			}
// 			if( !$is_correct_connections_site_type ) return null;
// 		}
		
		if( !$db_user['wp_user_id'] )
		{
			$this->write_to_log( $db_user['username'], 'Wordpress username not set.' );
			$this->set_connections_error( $db_user['id'], $connections_info['site'], 'Wordpress username not set.' );
			return null;
		}
		
		$connections_blog_id = $this->is_connections_site( $connections_info['site'] );
		
		if( !$connections_blog_id )
		{
			$this->write_to_log( $db_user['username'], 'Connections site does not exist or does not have Connections Hub plugin activated.' );
			$this->set_connections_error( $db_user['id'], $connections_info['site'], 'Connections site does not exist or does not have Connections Hub plugin activated.' );
			return null;
		}
		
		switch_to_blog( $connections_blog_id );
				
		if( $connections_info['post_id'] )
		{
			$post = get_post( $connections_info['post_id'], ARRAY_A );
			
			if( $post )
			{
				$post_status = 'publish';
				if( $db_user['status'] == 'inactive' ) $post_status = 'draft';
				
				$connections_post = array(
					'ID'           => $connections_info['post_id'],
					'post_title'   => $db_user['first_name'].' '.$db_user['last_name'],
					'post_name'    => sanitize_title( $db_user['first_name'].' '.$db_user['last_name'] ),
					'post_author'  => $db_user['wp_user_id'],
					'post_status'  => $post_status,
					'tax_input'    => array( 'connection-group' => $db_user['category'] ),
				);
				wp_update_post( $connections_post );

				update_post_meta( $connections_info['post_id'], 'sort-title', $db_user['last_name'].', '.$db_user['first_name'] );
				update_post_meta( $connections_info['post_id'], 'site-type', 'wp' );
		
				$blog_details = get_blog_details( $db_user['profile_site_id'] );
				if( $blog_details )
					update_post_meta( $connections_info['post_id'], 'url', $blog_details->siteurl );
				else
					update_post_meta( $connections_info['post_id'], 'url', 'n/a' );
				
				wp_reset_query();

				$this->update_connections_post_id( $db_user['id'], $connections_info['site'], $connections_info['post_id'] );
				$this->set_connections_error( $db_user['id'], $connections_info['site'], null );
				$connections_post_id = $connections_info['post_id'];
			}
			else
			{
				$connections_info['post_id'] = null;
			}
		}
		elseif( $db_user['status'] != 'inactive' )
		{
			$profile_site_types = $this->get_option( 'profile-site-types' );
			$profile_site_types = array_filter( explode(',', $profile_site_types), 'trim' );

			$is_correct_profile_site_type = false;
			foreach( $db_user['type'] as $user_type )
			{
				if( !in_array( $user_type, $profile_site_types ) )
					$is_correct_profile_site_type = true;
			}
		
			$connections_post_type = 'manual';
		
			if( !$is_correct_profile_site_type )
			{
				if( ($db_user['profile_site_id']) && (get_blog_details($db_user['profile_site_id']) !== false) )
				{
					$connections_post_type = 'synch';
				}
			}
			else
			{
				$connections_post_type = 'synch';
			}
		
			$connections_post = array(
				'post_title'   => $db_user['first_name'].' '.$db_user['last_name'],
				'post_name'    => sanitize_title( $db_user['first_name'].' '.$db_user['last_name'] ),
				'post_type'    => 'connection',
				'post_status'  => 'publish',
				'post_author'  => $db_user['wp_user_id'],
				'tax_input'    => array( 'connection-group' => $db_user['category'] ),
			);
		
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
				$updating_post = true;
				$wpquery->the_post();
				$post = get_post();
				$connections_post['ID'] = $post->ID;
				$connections_post_id = wp_update_post( $connections_post );
			}
			else
			{
				$connections_post_id = wp_insert_post( $connections_post, true );
			}
		
			wp_reset_query();

			if( !$connections_post_id )
			{
				if( isset($connections_post['ID']) )
				{
					$this->write_to_log( $db_user['username'], 'Unable to update Connections Post ("'.$connections_post['ID'].'")' );
					$this->set_connections_error( $db_user['id'], $connections_info['site'], 'Unable to update Connections Post ("'.$connections_post['ID'].'")' );
				}
				else
				{
					$this->write_to_log( $db_user['username'], 'Unable to insert Connection Post.' );
					$this->set_connections_error( $db_user['id'], $connections_info['site'], 'Unable to insert Connection Post.' );
				}

				return null;
			}

			update_post_meta( $connections_post_id, 'sort-title', $db_user['last_name'].', '.$db_user['first_name'] );
			update_post_meta( $connections_post_id, 'username', $db_user['username'] );
			update_post_meta( $connections_post_id, 'site-type', 'wp' );
			update_post_meta( $connections_post_id, 'entry-method', $connections_post_type );
		
			$blog_details = get_blog_details( $db_user['profile_site_id'] );
			if( $blog_details )
				update_post_meta( $connections_post_id, 'url', $blog_details->siteurl );
			else
				update_post_meta( $connections_post_id, 'url', 'n/a' );
		}
		
		restore_current_blog();
		
		if( $db_user['status'] == 'inactive' ) return null;
		
		if( $connections_post_id )
		{
			$result = $this->update_connections_post_id( $db_user['id'], $connections_info['site'], $connections_post_id );
		
			if( $result !== false )
			{
				$this->set_connections_error( $db_user['id'], $connections_info['site'], null );
				return $connections_post_id;
			}
			else
			{
				$this->write_to_log( $db_user['username'], 'Unable to save connections post id ("'.$connections_post_id.'") to database.' );
				$this->set_connections_error( $db_user['id'], $connections_info['site'], 'Unable to save connections post id ("'.$connections_post_id.'") to database.' );
				return null;
			}
		}
		
		$this->write_to_log( $db_user['username'], 'Unable to create Connections post.' );
		$this->set_connections_error( $db_user['id'], $connections_info['site'], 'Unable to create Connections Post.' );
		return null;
	}
	
	
	/**
	 *
	 */
	public function draft_connections_post( $db_user, $connections_site )
	{
		global $wpdb;
		
		if( is_numeric($db_user) )
		{
			$db_user = $this->get_user_by_id( $db_user );
			if( !$db_user ) return null;
		}
		
		if( !$db_user['wp_user_id'] ) return null;

		$connections_info = null;
		foreach( $db_user['connections_sites'] as $cs )
		{
			if( $cs['site'] == $connections_site )
			{
				$connections_info = $cs;
				break;
			}
		}
		
		if( !$connections_info ) return null;
		if( !$connections_info['post_id'] ) return null;
		
		$connections_blog_id = $this->is_connections_site( $connections_site );
		
		if( !$connections_blog_id )
		{
			$this->write_to_log( $db_user['username'], 'Connections site does not exist or does not have Connections Hub plugin activated.' );
			$this->set_connections_error( $db_user['id'], $connections_info['site'], 'Connections site does not exist or does not have Connections Hub plugin activated.' );
			return null;
		}
		
		switch_to_blog( $connections_blog_id );
		
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
		
		$this->set_connections_error( $db_user['id'], $connections_info['site'], null );
		return $connections_post_id;
	}	
	
	
	public function publish_connections_post( $db_user, $connections_site )
	{
		global $wpdb;
		
		if( is_numeric($db_user) )
		{
			$db_user = $this->get_user_by_id( $db_user );
			if( !$db_user ) return null;
		}
		
		if( !$db_user['wp_user_id'] ) return null;

		$connections_info = null;
		foreach( $db_user['connections_sites'] as $cs )
		{
			if( $cs['site'] == $connections_site )
			{
				$connections_info = $cs;
				break;
			}
		}
		
		if( !$connections_info ) return null;
		if( !$connections_info['post_id'] ) return null;
		
		$connections_blog_id = $this->is_connections_site( $connections_site );
		
		if( !$connections_blog_id )
		{
			$this->write_to_log( $db_user['username'], 'Connections site does not exist or does not have Connections Hub plugin activated.' );
			$this->set_connections_error( $db_user['id'], $connections_info['site'], 'Connections site does not exist or does not have Connections Hub plugin activated.' );
			return null;
		}
		
		switch_to_blog( $connections_blog_id );
		
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
		
		$this->set_connections_error( $db_user['id'], $connections_info['site'], null );
		return $connections_post_id;
	}	


	/**
	 *
	 */
	public function archive_site( $db_user )
	{
		if( is_numeric($db_user) )
		{
			$db_user = $this->get_user_by_id( $db_user );
			if( !$db_user ) return null;
		}
		
		if( !$db_user['wp_user_id'] ) return null;
		if( !$db_user['profile_site_id'] ) return null;
		
		$blog_details = get_blog_details( $db_user['profile_site_id'] );
		
		if( $blog_details !== false )
		{
			$this->update_blog_settings( 
				$db_user['profile_site_id'], 
				true, 
				false, 
				false, 
				false 
			);
		}
		
		$this->set_profile_site_error( $db_user['id'], null );
		return $db_user['profile_site_id'];
	}
	
	
	/**
	 *
	 */
	public function publish_site( $db_user )
	{
		if( is_numeric($db_user) )
		{
			$db_user = $this->get_user_by_id( $db_user );
			if( !$db_user ) return null;
		}
		
		if( !$db_user['wp_user_id'] ) return null;
		if( !$db_user['profile_site_id'] ) return null;
		
		$blog_details = get_blog_details( $db_user['profile_site_id'] );
		
		if( $blog_details !== false )
		{
			$this->update_blog_settings( 
				$db_user['profile_site_id'], 
				false,
				false, 
				false, 
				false 
			);
		}
		
		$this->set_profile_site_error( $db_user['id'], null );
		return $db_user['profile_site_id'];
	}

	
	
	/**
	 *
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
	}
	
	
	/**
	 *
	 */
	private function update_connections_post_settings( $post_id, $draft )
	{
		$status = ( $draft ? 'draft' : 'publish' );
		
		wp_update_post( 
			array(
				'ID'          => $post_id,
				'post_status' => $status,
			)
		);
	}
	
	









































	
	
	





	/**
	 *
	 */
	public function is_connections_site( $connections_site_slug )
	{
		$blog_id = $this->get_blog_by_path( $connections_site_slug );
		
		if( !$blog_id )
			return false;
		
		switch_to_blog( $blog_id );
		
		if( !is_plugin_active('connections-hub/main.php') )
			$blog_id = false;

		restore_current_blog();
		
		return $blog_id;
	}
	
	


	/**
	 *
	 */
	public function get_all_status_values()
	{
		global $wpdb;
		return $wpdb->get_col( "SELECT DISTINCT status FROM ".self::$user_table );
	}
	

	/**
	 *
	 */
	public function get_all_type_values()
	{
		global $wpdb;
		return $wpdb->get_col( "SELECT DISTINCT type FROM ".self::$type_table );
	}
	

	/**
	 *
	 */
	public function get_all_category_values()
	{
		global $wpdb;
		return $wpdb->get_col( "SELECT DISTINCT category FROM ".self::$category_table );
	}


	/**
	 *
	 */
	public function get_all_site_domain_values()
	{
		global $wpdb;
		return $wpdb->get_col( "SELECT DISTINCT site_domain FROM ".self::$user_table );
	}


	/**
	 *
	 */
	public function get_all_connections_sites_values()
	{
		global $wpdb;
		return $wpdb->get_col( "SELECT DISTINCT site FROM ".self::$connections_table );
	}
	

	/**
	 *
	 */
	public function is_ldap_plugin_active()
	{
		return is_plugin_active_for_network('wpmuldap/ldap_auth.php');
	}
	

	/**
	 *
	 */
	public function get_blog_by_path( $path )
	{
		global $wpdb;
		return $wpdb->get_var( 
			$wpdb->prepare(
				"SELECT blog_id FROM $wpdb->blogs WHERE path = %s",
				'/'.$path.'/'
			)
		);
	}
	
	

	
	
	/**
	 *
	 */
	public function get_wp_user( $id )
	{
		return get_user_by( 'id', intval($id) );
	}
	
	
	/**
	 *
	 */
	public function get_profile_site( $id )
	{
		global $wpdb;
		$blog_info = $wpdb->get_row(
			$wpdb->prepare(
				'SELECT * FROM '.$wpdb->blogs.' WHERE blog_id = %d',
				$id
			),
			ARRAY_A
		);
		
		$blog_details = get_blog_details( intval($id) );
		
		$blog_info['siteurl'] = $blog_details->siteurl;
		$blog_info['blogname'] = $blog_details->blogname;
		
		return $blog_info;
	}
	
	
	/**
	 *
	 */
	public function get_connections_post( $id, $connections_site )
	{
		$connections_blog_id = $this->is_connections_site( $connections_site );
		if( !$connections_blog_id ) return false;
		
		switch_to_blog( $connections_blog_id );
				
		$post = get_post( intval($id), ARRAY_A );
		
		restore_current_blog();
		
		return $post;
	}
	
	
	public function get_connections_post_edit_link( $id, $connections_site )
	{
		$connections_blog_id = $this->is_connections_site( $connections_site );
		if( !$connections_blog_id ) return false;
		
		switch_to_blog( $connections_blog_id );
				
// 		$link = get_edit_post_link( intval($id) );
		$link = admin_url().'post.php?post='.$id.'&action=edit';
		
		restore_current_blog();
		
		return $link;
	}


	public function get_csv_export( $filter = array(), $search = array(), $only_errors = false, $orderby = null )
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
				$u['site_domain'], // site domain
				$u['site_path'], // site path
				array_map( function($cs) { return $cs['site']; }, $u['connections_sites'] ), // connections sites
				$u['type'], // type
			);
		}
		
		OrgHub_CsvHandler::export( 'users', $headers, $users );
		exit;
	}
	
	
	public function get_site_csv_export( $filter = array(), $search = array(), $orderby = null )
	{
		global $wpdb;
		$users = $this->get_sites( $filter, $search, $orderby );


		$headers = array(
			'blog_id',
			'url',
			'title',
			'num_posts',
			'num_pages',
			'num_comments',
			'last_post_url',
			'last_post_date', 
			'last_post_status',
			'last_comment_url',
			'last_comment_date',
			'admin_email',
			'admin_username',
			'admin_name',
		);

		foreach( $users as &$user )
		{
			$u = $user;
			$user = array(
				$u['blog_id'], // blog_id
				$u['url'], // url
				$u['title'], // title
				$u['num_posts'], // num_posts
				$u['num_pages'], // num_pages
				$u['num_comments'], // num_comments
				$u['last_post_url'], // last_post_url
				$u['last_post_date'], // last_post_date
				$u['last_post_status'], // last_post_status
				$u['last_comment_url'], // last_comment_url
				$u['last_comment_date'], // last_comment_date
				$u['admin_email'], // admin_email
				$u['user_login'], // admin_username
				$u['display_name'], // admin_name
			);
		}
		
		OrgHub_CsvHandler::export( 'sites', $headers, $users );
		exit;
	}
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	//====================================================================================
	// SITES
	//
	
	public function get_sites( $filter = array(), $search = array(), $orderby = array(), $offset = 0, $limit = -1 )
	{
		global $wpdb;
		
		$list = array();
		$list[self::$site_table] = array(
			'id', 'blog_id', 'url', 'title', 'num_posts', 'num_pages', 'num_comments',
			'last_post_url', 'last_post_date', 'last_post_status',
			'last_comment_url', 'last_comment_date', 'admin_email',
		);
		$list[$wpdb->users] = array(
			'display_name','user_login'
		);
		
		$list = $this->get_column_list( $list );
		$filter = $this->get_sites_filter($filter, $search, $orderby, $offset, $limit);
		
// 		apl_print( 'SELECT '.$list.' FROM '.self::$site_table.' '.$filter );
		return $wpdb->get_results( 'SELECT '.$list.' FROM '.self::$site_table.' '.$filter, ARRAY_A );
	}
	
	public function get_sites_filter( $filter, $search, $orderby, $offset = 0, $limit = -1 )
	{
		$where_string = '';
		if( is_array($filter) && count($filter) > 0 )
		{
			if( $filter['filter_by_time'] !== false )
			{
				if( empty($where_string) ) $where_string = 'WHERE ';
				else $where_string .= ' AND ';
				
				$time_frame = new DateTime('today -'.$filter['time']);
				switch( $filter['time_compare'] )
				{
					case 'greater':
						$where_string .= "last_post_date < '".$time_frame->format('Y-m-d H:i:s')."'";
						break;
					
					case 'less':
						$where_string .= "last_post_date > '".$time_frame->format('Y-m-d H:i:s')."'";
						break;
					
					default:
						break;
				}
			}
			
			if( $filter['filter_by_posts'] !== false )
			{
				if( empty($where_string) ) $where_string = 'WHERE ';
				else $where_string .= ' AND ';
				
				$posts_count = intval($filter['posts']);
				switch( $filter['posts_compare'] )
				{
					case 'greater':
						$where_string .= 'num_posts > '.$posts_count;
						break;
					
					case 'less':
						$where_string .= 'num_posts < '.$posts_count;
						break;
					
					default:
						break;
				}
			}
			
			if( $filter['filter_by_pages'] !== false )
			{
				if( empty($where_string) ) $where_string = 'WHERE ';
				else $where_string .= ' AND ';
				
				$posts_count = intval($filter['pages']);
				switch( $filter['pages_compare'] )
				{
					case 'greater':
						$where_string .= 'num_pages > '.$posts_count;
						break;
					
					case 'less':
						$where_string .= 'num_pages < '.$posts_count;
						break;
					
					default:
						break;
				}
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
		$join .= 'LEFT JOIN wp_users ON wp_users.user_email = '.self::$site_table.'.admin_email ';

		return $join.' '.$where_string.' GROUP BY blog_id '.$orderby.' '.$limit_string;
	}
	
	
	private function get_column_list( $columns )
	{
		$list = '';
		$i = 0;
		foreach( $columns as $table => $names )
		{
			if( count($names) === 0 ) continue;
			if( $i > 0 ) $list .= ',';
			$list .= $table.'.'.implode( ','.$table.'.', $names );
			$i++;
		}
		
		if( $list === '' ) $list = '*';
		return $list;
	}
	
	
	
	public function get_sites_count( $filter, $search, $orderby )
	{
		global $wpdb;
// 		apl_print("SELECT COUNT(DISTINCT ".self::$site_table.".id) FROM ".self::$site_table.' '.$this->get_sites_filter($filter, $search, $orderby));
		return $wpdb->get_var( "SELECT COUNT(DISTINCT ".self::$site_table.".id) FROM ".self::$site_table.' '.$this->get_sites_filter($filter, $search, $orderby) );
	}
	
	
	public function clear_sites()
	{
		global $wpdb;
		$wpdb->query( 'DELETE FROM '.self::$site_table.';' );
	}
	
	
	public function refresh_sites()
	{
		$sites = wp_get_sites( array( 'limit' => 10000 ) );
		
		foreach( $sites as &$site )
		{
			switch_to_blog( $site['blog_id'] );

			$site['url'] = get_bloginfo( 'url' );
			$site['title'] = get_bloginfo( 'name' );
			
			$posts_count = wp_count_posts();
			$site['num_posts'] = $posts_count->publish;
			
			$pages_count = wp_count_posts('page');
			$site['num_pages'] = $pages_count->publish;
			
			$comments = wp_count_comments();
			$site['num_comments'] = $comments->total_comments;

			$recent_post = wp_get_recent_posts( array('numberposts' => 1) );
			if( !empty($recent_post) && count($recent_post) > 0 )
			{
				$site['last_post_url'] = get_permalink( $recent_post[0]['ID'] );
				$site['last_post_date'] = $recent_post[0]['post_modified'];
				$site['last_post_status'] = $recent_post[0]['post_status'];
			}
			else
			{
				$site['last_post_url'] = '';
				$site['last_post_date'] = '0000-00-00 00:00:00';
				$sits['last_post_status'] = '';
			}
			
			$recent_comment = get_comments( array('number' => 1) );
			if( !empty($recent_comment) && count($recent_comment) > 0 )
			{
				$site['last_comment_url'] = get_permalink( $recent_comment[0]->comment_ID );
				$site['last_comment_date'] = $recent_comment[0]->comment_date;
			}
			else
			{
				$site['last_comment_url'] = '';
				$site['last_comment_date'] = '0000-00-00 00:00:00';
			}
			
			$site['admin_email'] = get_bloginfo( 'admin_email' );
			
			$site['status'] = 'TO DO';

			restore_current_blog();
		}
		
		foreach( $sites as &$site )
		{
			$this->add_site( $site );
		}
		
		$this->update_option( 'sites-refresh-time', date('Y-m-d H:i:s') );
	}
	
	
	public function get_site_by_blog_id( $blog_id )
	{
		global $wpdb;
		
		$list = array();
		$list[self::$site_table] = array(
			'id', 'blog_id', 'url', 'title', 'num_posts', 'num_pages', 'num_comments',
			'last_post_url', 'last_post_date', 'last_post_status',
			'last_comment_url', 'last_comment_date', 'admin_email',
		);
		$list[$wpdb->users] = array(
			'display_name','user_login'
		);
		
		$list = $this->get_column_list( $list );

		$site = $wpdb->get_row(
			$wpdb->prepare(
				'SELECT '.$list.' FROM '.self::$site_table.' LEFT JOIN wp_users ON wp_users.user_email = '.self::$site_table.'.admin_email WHERE blog_id = %d',
				$blog_id
			),
			ARRAY_A
		);
		
		return $site;
	}
	
	
	public function add_site( &$args )
	{
		//if( !$this->check_user_args( $args ) ) return false;

		$db_site = $this->get_site_by_blog_id( $args['blog_id'] );
		if( $db_site )
		{
			return $this->update_site( $db_site['id'], $args );
		}
		
		global $wpdb;
		
		//
		// Insert new user into Users table.
		//
		$result = $wpdb->insert(
			self::$site_table,
			array(
				'blog_id'			=> $args['blog_id'],
				'url'				=> $args['url'],
				'title'				=> $args['title'],
				'num_posts'			=> $args['num_posts'],
				'num_pages'			=> $args['num_pages'],
				'num_comments'		=> $args['num_comments'],
				'last_post_url'		=> $args['last_post_url'],
				'last_post_date'	=> $args['last_post_date'],
				'last_post_status'	=> $args['last_post_status'],
				'last_comment_url'	=> $args['last_comment_url'],
				'last_comment_date'	=> $args['last_comment_date'],
				'admin_email'		=> $args['admin_email'],
				'status'			=> $args['status'],
			),
			array( '%d', '%s', '%s', '%d', '%d', '%d', '%s', '%s', '%s', '%s', '%s', '%s', '%s' )
		);
		
		//
		// Check to make sure insertion was successful.
		//
		$site_id = $wpdb->insert_id;
		if( !$site_id )
		{
			$this->last_error = 'Unable to insert site.';
			return false;
		}

		return $site_id;
	}
	
	public function update_site( $id, &$args )
	{
		global $wpdb;
		
		//
		// Update user in Users table.
		//
		$result = $wpdb->update(
			self::$site_table,
			array(
				'blog_id'			=> $args['blog_id'],
				'url'				=> $args['url'],
				'title'				=> $args['title'],
				'num_posts'			=> $args['num_posts'],
				'num_pages'			=> $args['num_pages'],
				'num_comments'		=> $args['num_comments'],
				'last_post_url'		=> $args['last_post_url'],
				'last_post_date'	=> $args['last_post_date'],
				'last_post_status'	=> $args['last_post_status'],
				'last_comment_url'	=> $args['last_comment_url'],
				'last_comment_date'	=> $args['last_comment_date'],
				'admin_email'		=> $args['admin_email'],
				'status'			=> $args['status'],
			),
			array( 'id' => intval( $id ) ),
			array( '%d', '%s', '%s', '%d', '%d', '%d', '%s', '%s', '%s', '%s', '%s', '%s', '%s' ),
			array( '%d' )
		);

		//
		// Check to make sure update was successful.
		//
		if( $result === false )
		{
			$this->last_error = 'Unable to update site.';
			return false;
		}
		
		return $id;
	}
	
	
	public function get_blog_ids()
	{
		global $wpdb;
		return $wpdb->get_col( 'SELECT blog_id FROM '.$wpdb->blogs );
	}
	
	
	public function refresh_site( $blog_id )
	{
		global $wpdb;
		
		$site = $wpdb->get_row( 'SELECT * FROM '.$wpdb->blogs.' WHERE blog_id = '.intval($blog_id), ARRAY_A );
		if( !$site ) return $site;
		
		switch_to_blog( $blog_id );

		$site['url'] = get_bloginfo( 'url' );
		$site['title'] = get_bloginfo( 'name' );
		
		$posts_count = wp_count_posts();
		$site['num_posts'] = $posts_count->publish;
		
		$pages_count = wp_count_posts('page');
		$site['num_pages'] = $pages_count->publish;
		
		$comments = wp_count_comments();
		$site['num_comments'] = $comments->total_comments;
		
		$args = array(
			'public'   => true,
			'_builtin' => false
		);
		
		$post_types = array( 'post', 'page' );
		$post_types = array_merge( $post_types, get_post_types($args, 'names', 'and') );
		
		$recent_post = $wpdb->get_row( 'SELECT * FROM '.$wpdb->posts." WHERE post_type IN ('".implode("','", $post_types)."') ORDER BY post_modified_gmt LIMIT 1" );
		if( !$recent_post )
		{
			$site['last_post_url'] = '';
			$site['last_post_date'] = '0000-00-00 00:00:00';
			$site['last_post_status'] = '';
		}
		else
		{
			$site['last_post_url'] = get_permalink( $recent_post->ID );
			$site['last_post_date'] = $recent_post->post_modified;
			$site['last_post_status'] = $recent_post->post_status;
		}
		
		$recent_comment = get_comments( array('number' => 1) );
		if( !$recent_comment || count($recent_comment) === 0 )
		{
			$site['last_comment_url'] = '';
			$site['last_comment_date'] = '0000-00-00 00:00:00';
		}
		else
		{
			$site['last_comment_url'] = get_permalink( $recent_comment[0]->comment_ID );
			$site['last_comment_date'] = $recent_comment[0]->comment_date;
		}
		
		$site['admin_email'] = get_bloginfo( 'admin_email' );
		
		$site['status'] = 'TO DO';

		restore_current_blog();

		$this->add_site( $site );
		
		return $this->get_site_by_blog_id( $blog_id );
	}




	function delete_blog( $blog_id )
	{
		do_action( 'deactivate_blog', $blog_id );
		update_blog_status( $blog_id, 'deleted', '1' );
	}



	function archive_blog( $blog_id )
	{
		update_blog_status( $blog_id, 'archived', '1' );
	}


	function change_theme( $blog_id, $theme )
	{
		switch_to_blog( $blog_id );

		switch_theme( $theme );
	
		restore_current_blog();
	}



	function change_site_admin( $blog_id, $admin_user_id, $admin_email )
	{
		$blog_id = intval($blog_id);
		$admin_user_id = intval($admin_user_id);
		
		add_user_to_blog( $blog_id, $admin_user_id, 'administrator' );
		
		switch_to_blog( $blog_id );

		update_option( 'admin_email', $admin_email );

		restore_current_blog();
		
		$this->refresh_site( $blog_id );
	}
	
	
	
	function update_site_column( $blog_id, $column_name, $value, $type = '%s' )
	{
		global $wpdb;
		
		//
		// Update user in Users table.
		//
		$result = $wpdb->update(
			self::$site_table,
			array(
				$column_name		=> $value,
			),
			array( 'blog_id' => intval( $blog_id ) ),
			array( $type ),
			array( '%d' )
		);

		//
		// Check to make sure update was successful.
		//
		if( $result === false )
		{
			$this->last_error = 'Unable to update site.';
			return false;
		}
		
		return $blog_id;
	}

}
endif;

