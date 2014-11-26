<?php



/**
 * The main model class for the Origanization Hub plugin.  This is a singleton class.
 */
class OrganizationHub_Model
{
	const ORGANIZATION_HUB_OPTIONS = 'organization-hub-options';
	const LOG_PATH = ORGANIZATION_HUB_PLUGIN_PATH;
	
	private static $instance = null;
	private static $users_table = 'organization_hub_users';

	
	/**
	 * Private Constructor.  Needed for a Singleton class.
	 */
	private function __construct()
	{
		global $wpdb;
		self::$users_table = $wpdb->base_prefix . self::$users_table;
	}
	

	/**
	 * Get the only instance of this class.
	 */
	public static function get_instance()
	{
		if( self::$instance	== null )
			self::$instance = new OrganizationHub_Model();
		return self::$instance;
	}


//========================================================================================
//========================================================================= Log file =====


	/**
	 * 
	 */
	public function clear_log()
	{
		file_put_contents( self::LOG_PATH.'/log.txt', '' );
	}
	

	/**
	 * 
	 */
	public function write_to_log( $username, $text, $newline = true )
	{
		if( $newline ) $text .= "\n";
		$text = str_pad( $username, 8, ' ', STR_PAD_RIGHT ).' : '.$text;
		file_put_contents( self::LOG_PATH.'/log.txt', $text, FILE_APPEND );
	}	


//========================================================================================
//===================================================================== Site options =====


	/**
	 *
	 */
	public function get_option( $name, $default = false )
	{
		$options = get_site_option( self::ORGANIZATION_HUB_OPTIONS, array() );
		
		if( isset($options[$name]) ) return $options[$name];
		return false;
	}


	/**
	 *
	 */
	public function update_options( $options, $merge = false )
	{
		if( $merge === true )
			$options = array_merge( get_site_option(ORGANIZATION_HUB_OPTIONS, array()), $options );
			
		update_site_option( self::ORGANIZATION_HUB_OPTIONS, $options );
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
	 * Create the Users database table.
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

		$sql = "CREATE TABLE ".self::$users_table." (
				  id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
				  username varchar(16) NOT NULL UNIQUE,
				  first_name varchar(255) NOT NULL DEFAULT '',
				  last_name varchar(255) NOT NULL DEFAULT '',
				  email varchar(255) NOT NULL DEFAULT '',
				  type varchar(16) NOT NULL,
				  description varchar(255) NOT NULL DEFAULT '',
				  category varchar(255) NOT NULL DEFAULT '',
				  domain varchar(255) NOT NULL DEFAULT '',
				  status varchar(16) NOT NULL,
				  wp_user_id bigint(20) DEFAULT NULL,
				  connections_post_id bigint(20) DEFAULT NULL,
				  profile_site_id bigint(20) DEFAULT NULL,
				  exceptions text,
				  PRIMARY KEY  (id)
				) ENGINE=InnoDB $db_charset_collate;";

        dbDelta($sql);
	}
	
	
	/**
	 * Drop the Users database table.
	 */
	public function delete_users_table()
	{
		global $wpdb;
		$wpdb->query( "DROP TABLE ".self::$users_table.";" );
	}


	/**
	 * Clear the Users database table.
	 */
	public function clear_users_table()
	{
		global $wpdb;
		$wpdb->query( "DELETE FROM ".self::$users_table );
	}
	

//========================================================================================
//============================================================================ USERS =====
	
	
	/**
	 *
	 */
	public function add_user( &$args )
	{
		$db_user = $this->get_user_by_username( $args['username'] );
		if( $db_user )
		{
			if( $db_user['status'] == 'inactive' )
			{
				$args['status'] = 'new';
				return $this->update_user( $db_user['id'], $args );
			}

			return $db_user['id'];
		}

		global $wpdb;

		$result = $wpdb->insert(
			self::$users_table,
			array(
				'username'		=> $args['username'],
				'first_name'	=> $args['first_name'],
				'last_name'		=> $args['last_name'],
				'email'			=> $args['email'],
				'type'			=> $args['type'],
				'description'	=> $args['description'],
				'category'		=> $args['category'],
				'domain'		=> $args['domain'],
				'status'		=> 'new',
			),
			array( '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s' )
		);

		return $wpdb->insert_id;
	}
	
	
	/**
	 *
	 */
	public function set_inactive_users( &$active_user_ids )
	{
		global $wpdb;
		$active_user_ids = array_filter( $active_user_ids, 'intval' );
// 		orghub_print("UPDATE ".self::$users_table." SET status = 'inactive' WHERE id NOT IN (".implode($active_user_ids, ",").")");
		return $wpdb->query( 
			"UPDATE ".self::$users_table." SET status = 'inactive' WHERE id NOT IN (".implode($active_user_ids, ",").")"
		);
	}
	
	
	/**
	 *
	 */
	public function update_user( $id, &$args )
	{
		global $wpdb;

		$result = $wpdb->update(
			self::$users_table,
			array(
				'username'		=> $args['username'],
				'first_name'	=> $args['first_name'],
				'last_name'		=> $args['last_name'],
				'email'			=> $args['email'],
				'type'			=> $args['type'],
				'description'	=> $args['description'],
				'category'		=> $args['category'],
				'domain'		=> $args['domain'],
				'status'		=> $args['status'],
			),
			array( 'id' => intval( $id ) ),
			array( '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s' ),
			array( '%d' )
		);
		
		if( $result !== false ) return $id;
		return false;
	}


	/**
	 *
	 */
	public function delete_user( $id )
	{
		// TODO.
	}
	

	/**
	 *
	 */
	public function get_user_by_id( $user_id )
	{
		global $wpdb;
		return $wpdb->get_row(
			$wpdb->prepare(
				'SELECT * FROM '.self::$users_table.' WHERE id = %d',
				$user_id
			),
			ARRAY_A
		);
	}


	/**
	 *
	 */
	public function get_user_by_username( $username )
	{
		global $wpdb;
		return $wpdb->get_row(
			$wpdb->prepare(
				'SELECT * FROM '.self::$users_table.' WHERE username = %s',
				$username
			),
			ARRAY_A
		);
	}


	/**
	 *
	 */
	private function filter_sql( $filter = array(), $search = array(), $only_errors = false, $orderby = null, $offset = 0, $limit = -1 )
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
			
			$where_string .= " exceptions IS NOT NULL AND exceptions != '' ";
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
		
		return $where_string.' '.$orderby.' '.$limit_string;
	}
	
	
	/**
	 *
	 */
	public function get_users( $filter = array(), $search = array(), $only_errors = false, $orderby = null, $offset = 0, $limit = -1 )
	{
		global $wpdb;
		return $wpdb->get_results( "SELECT * FROM ".self::$users_table.' '.$this->filter_sql($filter,$search,$only_errors,$orderby,$offset,$limit), ARRAY_A );
	}


	/**
	 *
	 */
	public function get_users_count( $filter = array(), $search = array(), $only_errors = false, $orderby = null, $offset = 0, $limit = -1 )
	{
		global $wpdb;
		return $wpdb->get_var( "SELECT COUNT(*) FROM ".self::$users_table.' '.$this->filter_sql($filter,$search,$only_errors,$orderby,$offset,$limit) );
	}	

	
	/**
	 *
	 */
	public function set_user_status( $id, $status )
	{
		global $wpdb;
		
		$result = $wpdb->update(
			self::$users_table,
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
	public function update_connections_post_id( $id, $connections_post_id )
	{
		return $this->set_users_value( $id, 'connections_post_id', $connections_post_id );
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
					"UPDATE ".self::$users_table." SET $key = NULL WHERE id = %d",
					intval( $id )
				)
			);
		}

		return $wpdb->query( 
			$wpdb->prepare( 
				"UPDATE ".self::$users_table." SET $key = %d WHERE id = %d",
				intval( $value ),
				intval( $id )
			)
		);
	}
	

//========================================================================================
//=============================================================== USERS - Exceptions =====


	/**
	 *
	 */
	public function add_user_exception( $id, $key, $exception )
	{
		$exceptions = $this->get_exceptions( $id );
		$exceptions[$key] = $exception;
		$this->set_exceptions( $id, $exceptions );
	}
	

	/**
	 *
	 */
	public function remove_user_exception( $id, $key )
	{
		$exceptions = $this->get_exceptions( $id );
		unset($exceptions[$key]);
		$this->set_exceptions( $id, $exceptions );
	}


	/**
	 *
	 */
	public function get_user_exception( $id, $key )
	{
		$exceptions = $this->get_exceptions( $id );
		if( isset($exceptions[$key]) )
			return $exceptions[$key];
		return false;
	}
	
	
	/**
	 *
	 */
	public function get_exceptions( $id )
	{
		global $wpdb;
		$exceptions = $wpdb->get_var( 
			$wpdb->prepare(
				"SELECT exceptions FROM ".self::$users_table." WHERE id = %d",
				intval($id)
			)
		);
		
		if( $exceptions && is_string($exceptions) )
			$exceptions = json_decode( $exceptions, true );
		else
			$exceptions = array();
		
		return $exceptions;
	}
	

	/**
	 *
	 */
	public function set_exceptions( $id, $exceptions )
	{
		if( count($exceptions) === 0 )
		{
			return $this->clear_user_exceptions( $id );
		}
		
		global $wpdb;	
		$wpdb->update(
			self::$users_table,
			array( 'exceptions' => json_encode($exceptions) ),
			array( 'id' => intval( $id ) ),
			array( '%s' ),
			array( '%d' )
		);
	}
	
	
	/**
	 * 
	 */
	public function clear_user_exceptions( $id )
	{
		global $wpdb;
		return $wpdb->query( 
			$wpdb->prepare( 
				"UPDATE ".self::$users_table." SET exceptions = NULL WHERE id = %d",
				intval( $id )
			)
		);
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
				$this->draft_connections_post( $db_user );
				$this->archive_site( $db_user );
				break;
			
			case 'new':
			case 'active':
				$db_user['wp_user_id'] = $this->create_username( $db_user );
				if( $db_user['wp_user_id'] )
				{
					$db_user['profile_site_id'] = $this->create_site( $db_user );
					$db_user['connections_post_id'] = $this->create_connections_post( $db_user );
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
				$this->remove_user_exception( $db_user['id'], 'username' );
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
				$this->add_user_exception( $db_user['id'], 'username', 'WPMU LDAP plugin not active.' );
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
						$this->add_user_exception( $db_user['id'], 'username', $result );
						return null;
					}
					
					$user = get_user_by( 'login', $db_user['username'] );
					
					if( $user ) $user_id = $user->ID;
					break;
				
				default:
					$this->write_to_log( $db_user['username'], 'Invalid create user type ("'.$create_user_type.'").' );
					$this->add_user_exception( $db_user['id'], 'username', 'Invalid create user type ("'.$create_user_type.'").' );
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
			$this->update_connections_post_id( $db_user['id'], null );
		
			if( $result !== false )
			{
				$this->remove_user_exception( $db_user['id'], 'username' );
				return $user_id;
			}
			else
			{
				$this->write_to_log( $db_user['username'], 'Unable to save user id ("'.$user_id.'") to database.' );
				$this->add_user_exception( $db_user['id'], 'username', 'Unable to save user id ("'.$user_id.'") to database.' );
				return null;
			}
		}

		$this->write_to_log( $db_user['username'], 'Unable to create user.' );
		$this->add_user_exception( $db_user['id'], 'username', 'Unable to create user.' );
		return null;
	}


	/**
	 *
	 */	
	public function create_site( $db_user, $path = false, $override_categories = false )
	{
		global $wpdb;
		
		if( is_numeric($db_user) )
		{
			$db_user = $this->get_user_by_id( $db_user );
			if( !$db_user ) return null;
		}
		
		if( !$override_categories )
		{
			$profile_site_categories = $this->get_option( 'profile-site-categories' );
			$profile_site_categories = array_filter( explode(',', $profile_site_categories), 'trim' );
		
			if( !in_array( $db_user['type'], $profile_site_categories ) ) return null;
		}
		
		if( !$db_user['wp_user_id'] )
		{
			$this->write_to_log( $db_user['username'], 'Wordpress username not set.' );
			$this->add_user_exception( $db_user['id'], 'site', 'Wordpress username not set.' );
			return null;
		}
		
		if( $db_user['profile_site_id'] )
		{
			$blog_details = get_blog_details( $db_user['profile_site_id'] );
			if( $blog_details !== false )
			{
				$this->update_blog_settings( $db_user['profile_site_id'], false, false, false, false );
				$this->remove_user_exception( $db_user['id'], 'site' );
				return $db_user['profile_site_id'];
			}
			else
			{
				$this->write_to_log( $db_user['username'], 'Profile site id set but cannot be found.' );
				$this->add_user_exception( $db_user['id'], 'site', 'Profile site id set but cannot be found.' );
				return null;
			}
		}
		
		if( !$path )
		{
			$path_creation_type = $this->get_option( 'path-creation-type', 'username-slug' );
			switch( $path_creation_type )
			{
				case 'full-name-slug':
					$path = sanitize_title( $db_user['first_name'].' '.$db_user['last_name'] );
					break;
				
				case 'username-slug':
					$path = $db_user['username'];
					break;

				default:
					$this->write_to_log( $db_user['username'], 'Invalid path creation type ("'.$path_creation_type.'").' );
					$this->add_user_exception( $db_user['id'], 'site', 'Invalid path creation type ("'.$path_creation_type.'").' );
					return null;
					break;
			}
		}
		
		// check if site already exists.
		$blog_id = $this->get_blog_by_path( $path );
		
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
						$this->write_to_log( $db_user['username'], 'Profile site already exists, but user does not exist.' );
						$this->add_user_exception( $db_user['id'], 'site', 'Profile site already exists, but user does not exist.' );
						return null;
					}
				}
				else
				{
					$result = $this->update_profile_site_id( $db_user['id'], $blog_id );
					$this->remove_user_exception( $db_user['id'], 'site' );
					return $blog_id;					
				}
				
				restore_current_blog();
			}
			else
			{
				$this->write_to_log( $db_user['username'], 'Unable to retreive blog details.' );
				$this->add_user_exception( $db_user['id'], 'site', 'Unable to retreive blog details.' );
				return null;
			}
			
			$this->update_blog_settings( $blog_id, false, false, false, false );
		}
		else
		{
			$blog_id = wpmu_create_blog( $db_user['domain'], '/'.$path, $db_user['first_name'].' '.$db_user['last_name'], $db_user['wp_user_id'] );
			if( is_wp_error($blog_id))
			{
				$this->write_to_log( $db_user['username'], $blog_id->get_error_message() );
				$this->write_to_log( '', 'Site: '.$db_user['domain'].'/'.$path );
				$this->add_user_exception( $db_user['id'], 'site', $blog_id->get_error_message() );
				return null;
			}
		}
		
		if( $blog_id )
		{
			$result = $this->update_profile_site_id( $db_user['id'], $blog_id );
		
			if( $result !== false )
			{
				$this->remove_user_exception( $db_user['id'], 'site' );
				return $blog_id;
			}
			else
			{
				$this->write_to_log( $db_user['username'], 'Unable to save profile site id ("'.$blog_id.'") to database.' );
				$this->add_user_exception( $db_user['id'], 'site', 'Unable to save profile site id ("'.$blog_id.'") to database.' );
				return null;
			}
		}
		
		$this->write_to_log( $db_user['username'], 'Unable to create profile site.' );
		$this->add_user_exception( $db_user['id'], 'site', 'Unable to create profile site.' );
		return null;
	}
	

	/**
	 *
	 */
	public function create_connections_post( $db_user, $override_categories = false )
	{
		global $wpdb;
		
		if( is_numeric($db_user) )
		{
			$db_user = $this->get_user_by_id( $db_user );
			if( !$db_user ) return null;
		}

		if( !$override_categories )
		{
			$connections_site_categories = $this->get_option( 'connections-site-categories' );
			$connections_site_categories = array_filter( explode(',', $connections_site_categories), 'trim' );
			
			if( !in_array( $db_user['type'], $connections_site_categories ) ) return null;
		}
		
		if( !$db_user['wp_user_id'] )
		{
			$this->write_to_log( $db_user['username'], 'Wordpress username not set.' );
			$this->add_user_exception( $db_user['id'], 'connections', 'Wordpress username not set.' );
			return null;
		}
		
		$connections_blog_id = $this->check_connections_site();
		
		if( !$connections_blog_id )
		{
			$this->write_to_log( $db_user['username'], 'Connections site does not exist or does not have Connections Hub plugin activated.' );
			$this->add_user_exception( $db_user['id'], 'connections', 'Connections site does not exist or does not have Connections Hub plugin activated.' );
			return null;
		}
		
		switch_to_blog( $connections_blog_id );
				
		if( $db_user['connections_post_id'] )
		{
			$post = get_post( $db_user['connections_post_id'], ARRAY_A );
			
			if( $post )
			{
				$connection_post = array(
					'ID'           => $db_user['connections_post_id'],
					'post_title'   => $db_user['first_name'].' '.$db_user['last_name'],
					'post_name'    => sanitize_title( $db_user['first_name'].' '.$db_user['last_name'] ),
					'post_author'  => $db_user['wp_user_id'],
					'tax_input'    => array( 'connection-group' => $db_user['category'] ),
				);
				wp_update_post( $connections_post );

				update_post_meta( $db_user['connections_post_id'], 'sort-title', $db_user['last_name'].', '.$db_user['first_name'] );
				update_post_meta( $db_user['connections_post_id'], 'site-type', 'wp' );
		
				$blog_details = get_blog_details( $db_user['profile_site_id'] );
				if( $blog_details )
					update_post_meta( $db_user['connections_post_id'], 'url', $blog_details->siteurl );
				else
					update_post_meta( $db_user['connections_post_id'], 'url', 'n/a' );
				
				wp_reset_query();

				$this->update_connections_post_id( $db_user['id'], $db_user['connections_post_id'] );
				$this->remove_user_exception( $db_user['id'], 'connections' );
				return $db_user['connections_post_id'];
			}
			else
			{
				$db_user['connections_post_id'] = null;
			}
		}
		
		$profile_site_categories = $this->get_option( 'profile-site-categories' );
		$profile_site_categories = array_filter( explode(',', $profile_site_categories), 'trim' );
		
		$connections_post_type = 'manual';
		
		if( !in_array( $db_user['type'], $profile_site_categories ) )
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
				$this->add_user_exception( $db_user['id'], 'connections', 'Unable to update Connections Post ("'.$connections_post['ID'].'")' );
			}
			else
			{
				$this->write_to_log( $db_user['username'], 'Unable to insert Connection Post.' );
				$this->add_user_exception( $db_user['id'], 'connections', 'Unable to insert Connection Post.' );
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

		restore_current_blog();
		
		if( $connections_post_id )
		{
			$result = $this->update_connections_post_id( $db_user['id'], $connections_post_id );
		
			if( $result !== false )
			{
				$this->remove_user_exception( $db_user['id'], 'connections' );
				return $connections_post_id;
			}
			else
			{
				$this->write_to_log( $db_user['username'], 'Unable to save connections post id ("'.$connections_post_id.'") to database.' );
				$this->add_user_exception( $db_user['id'], 'connections', 'Unable to save connections post id ("'.$connections_post_id.'") to database.' );
				return null;
			}
		}
		
		$this->write_to_log( $db_user['username'], 'Unable to create Connections post.' );
		$this->add_user_exception( $db_user['id'], 'connections', 'Unable to create Connections Post.' );
		return null;
	}
	
	
	/**
	 *
	 */
	public function draft_connections_post( $db_user )
	{
		global $wpdb;
		
		if( is_numeric($db_user) )
		{
			$db_user = $this->get_user_by_id( $db_user );
			if( !$db_user ) return null;
		}
		
		if( !$db_user['wp_user_id'] ) return null;
		if( !$db_user['connections_post_id'] ) return null;
		
		$connections_blog_id = $this->check_connections_site();
		
		if( !$connections_blog_id )
		{
			$this->write_to_log( $db_user['username'], 'Connections site does not exist or does not have Connections Hub plugin activated.' );
			$this->add_user_exception( $db_user['id'], 'connections', 'Connections site does not exist or does not have Connections Hub plugin activated.' );
			return null;
		}
		
		switch_to_blog( $connections_blog_id );
		
		$post = get_post( $db_user['connections_post_id'], ARRAY_A );
		
		if( $post )
		{
			$connections_post = array(
				'ID'           => $db_user['connections_post_id'],
				'post_status'  => 'draft',
			);
			wp_update_post( $connections_post );
		}

		restore_current_blog();
		
		$this->remove_user_exception( $db_user['id'], 'connections' );
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
		
		$this->remove_user_exception( $db_user['id'], 'site' );
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
		
		$this->remove_user_exception( $db_user['id'], 'site' );
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
	public function check_connections_site()
	{
		$connections_site_slug = $this->get_option( 'connections-site-slug', 'connection' );
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
	public function get_all_status_types()
	{
		global $wpdb;
		return $wpdb->get_col( "SELECT DISTINCT status FROM ".self::$users_table );		
	}
	

	/**
	 *
	 */
	public function get_all_user_types()
	{
		global $wpdb;
		return $wpdb->get_col( "SELECT DISTINCT type FROM ".self::$users_table );		
	}
	

	/**
	 *
	 */
	public function get_all_category_types()
	{
		global $wpdb;
		return $wpdb->get_col( "SELECT DISTINCT category FROM ".self::$users_table );		
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
		
		return get_blog_details( intval($id) );
	}
	
	
	/**
	 *
	 */
	public function get_connections_post( $id )
	{
		$connections_blog_id = $this->check_connections_site();
		if( !$connections_blog_id ) return false;
		
		switch_to_blog( $connections_blog_id );
				
		$post = get_post( intval($id), ARRAY_A );
		
		restore_current_blog();
		
		return $post;
	}
	
	
	public function get_connections_post_edit_link( $id )
	{
		$connections_blog_id = $this->check_connections_site();
		if( !$connections_blog_id ) return false;
		
		switch_to_blog( $connections_blog_id );
				
// 		$link = get_edit_post_link( intval($id) );
		$link = admin_url().'post.php?post='.$id.'&action=edit';
		
		restore_current_blog();
		
		return $link;
	}









}

