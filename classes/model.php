<?php

/**
 * The main model class.
 */

class OrganizationHub_Model
{
	const ORGANIZATION_HUB_OPTIONS = 'organization-hub-options';
	const LOG_PATH = ORGANIZATION_HUB_PLUGIN_PATH;
	
	private static $instance = null;
	private static $table = 'organization_hub';


	private function __construct()
	{
		global $wpdb;
		self::$table = $wpdb->base_prefix . self::$table;
	}
	
	public static function get_instance()
	{
		if( self::$instance	== null )
			self::$instance = new OrganizationHub_Model();
		return self::$instance;
	}
	
	/**
	 * Create the required DB table
	 */
	public function create_table()
	{
		global $wpdb;
	
        $db_charset_collate = '';
        if( !empty($wpdb->charset) )
			$db_charset_collate = "DEFAULT CHARACTER SET $wpdb->charset";
        if( !empty($wpdb->collate) )
			$db_charset_collate .= " COLLATE $wpdb->collate";

		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );

		$sql = "CREATE TABLE ".self::$table." (
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
				  exception text,
				  PRIMARY KEY  (id)
				) ENGINE=InnoDB $db_charset_collate;";

        dbDelta($sql);
	}

	/**
	 * Drop the table
	 */
	public function delete_table() {
		global $wpdb;

		$wpdb->query( "DROP TABLE ".self::$table.";" );
	}


	public function count_items() {
		global $wpdb;

		$result = $wpdb->get_var( "SELECT COUNT(*) FROM ".self::$table );

		return absint( $result );
	}


	/**
	 * Add entry into the database
	 */
	public function insert( &$args )
	{
		global $wpdb;

		$wpdb->insert(
			self::$table,
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
			array( '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s' )
		);

		return $wpdb->insert_id;
	}
	
	public function update( $id, &$args )
	{
		global $wpdb;

		$result = $wpdb->update(
			self::$table,
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

	public function clear_table()
	{
		$wpdb->query( "DELETE FROM ".self::$table );
	}

	public function get_users( $filter = array(), $offset = 0, $limit = -1 )
	{
		global $wpdb;
		
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

		//orghub_print("SELECT * FROM ".self::$table." $where_string $limit_string");
		return $wpdb->get_results( "SELECT * FROM ".self::$table." $where_string $limit_string", ARRAY_A );
	}
	
	
	public function get_user( $username )
	{
		return $wpdb->get_row(
			$wpdb->prepare(
				'SELECT * FROM '.self::$table.' WHERE username = %s',
				$username
			),
			ARRAY_A
		);
	}
	
	
	public function clear_user_exceptions( $id )
	{
		global $wpdb;
		// set exception to null
	}
	
	public function add_to_user_exception( $id, $key, $exception )
	{
		global $wpdb;
		// get exception from db
		// deserialize
		// set new exception
		// $exceptions[$key] = $exception;
		
	}
	
	public function set_user_exceptions( $id, $exceptions )
	{
		global $wpdb;

		if( !is_array($exceptions) ) $exceptions = array( $exceptions );
		
		
	}
	
	
	public function add_user( &$args )
	{
		// TODO: check for all required args (use defaults?).
		
		$user_row = $this->get_user( $args['username'] );
		
		if( $user_row === false )
		{
			$args['status'] = 'new';
			$this->insert( $args );
		}
		else
		{
			if( $user_row['status'] == 'inactive' )
			{
				$args['status'] = 'new';
				$this->update( $args );
			}
		}
		
		// TODO: check for errors.
		return true;
	}
	
	
	public function process_user( $db_user )
	{
		switch( $db_user['status'] )
		{
			case 'inactive':
				$this->draft_connections_post( $db_user );
				$this->archive_site( $db_user );
				break;
			
			case 'new':
			case 'active':
				$db_user['wp_user_id'] = $this->create_user( $db_user );
				if( $db_user['wp_user_id'] )
				{
					$db_user['profile_site_id'] = $this->create_site( $db_user );
					$db_user['connections_post_id'] = $this->create_connection_post( $db_user );
				}
				$this->set_user_status( $db_user, 'active' );
				break;
		}
	}
	
	private function create_user( $db_user )
	{

		global $wpdb;
		$user_id = false;
		
		if( $db_user['wp_user_id'] )
		{
			$user = get_user_by( 'id', $db_user['wp_user_id'] );
			if( $user !== false ) return $db_user['wp_user_id'];
		}
		
		$user = get_user_by( 'login', $db_user['username'] );
		
		if( $user === false )
		{
			$create_user_type = $this->get_option( 'create-user-type', 'local' );
			
			if( ($create_user_type == 'wpmu-ldap') && (!$this->is_ldap_plugin_active()) )
			{
				$this->write_to_log( 'ERROR: WPMU LDAP plugin not active.', true );
				return null;
//				$this->write_to_log( 'ERROR: WPMU LDAP plugin not active. Reverting to local user creation.', true );
// 				$create_user_type = 'local';
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
						$this->write_to_log( $result, true );
						return null;
					}
					
					$user = get_user_by( 'login', $db_user['username'] );
					
					if( $user ) $user_id = $user->ID;
					break;
				
				default:
					$this->write_to_log( "ERROR: Invalid create user type: $create_user_type", true );
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
			$result = $wpdb->update(
				self::$table,
				array( 'wp_user_id' => intval( $user_id ) ),
				array( 'id' => intval( $db_user['id'] ) ),
				array( '%d' ),
				array( '%d' )
			);
		
			if( $result !== false )
			{
				return $user_id;
			}
			else
			{
				$this->write_to_log( "ERROR: Unable to save user id to DB: $user_id", true );
				return null;
			}
		}

		$this->write_to_log( "ERROR: Unable to create user.", true );
		return null;
	}
	
	private function create_site( $db_user )
	{
		global $wpdb;
		
		$profile_site_categories = $this->get_option( 'profile-site-categories' );
		$profile_site_categories = array_filter( explode(',', $profile_site_categories), 'trim' );
		
		if( !in_array( $db_user['type'], $profile_site_categories ) ) return null;
		if( !$db_user['wp_user_id'] ) return null;
		
		if( $db_user['profile_site_id'] )
		{
			$blog_details = get_blog_details( $db_user['profile_site_id'] );
			if( $blog_details !== false )
			{
				// TODO LATER: check blog settings.
				return $db_user['profile_site_id'];
			}
		}
		
		// TODO: determine path based on settings.
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
				$this->write_to_log( 'Invalid path creation type: '.$path_creation_type, true );
				return null;
				break;
		}
		
		// check if site already exists.
		$blog_id = $this->get_blog_by_path( $path );
		
		if( $blog_id )
		{
			$blog_details = get_blog_details( $blog_id );
			if( $blog_details )
			{
				$user = get_user_by( 'email', $blog_details['admin_email'] );
				if( (!$user) || ($user->slug !== $db_user['username']) )
				{
					$this->write_to_log( 'Profile site already exists with another admin.', true );
					$this->write_to_log( "Admin for $path is ".$user->slug.'.', true );
					return null;
				}
			}
			else
			{
				$this->write_to_log( 'Unable to retreive blog details.', true );
				return null;
			}
			
			// TODO LATER: check blog settings.
		}
		else
		{
			$blog_id = wpmu_create_blog( $db_user['domain'], $path, $db_user['first_name'].' '.$db_user['last_name'], $db_user['wp_user_id'] );
		}
		
		if( $blog_id )
		{
			$result = $wpdb->update(
				self::$table,
				array( 'profile_site_id' => intval( $blog_id ) ),
				array( 'id' => intval( $db_user['id'] ) ),
				array( '%d' ),
				array( '%d' )
			);
		
			if( $result !== false )
			{
				return $blog_id;
			}
			else
			{
				$this->write_to_log( "ERROR: Unable to save profile site id to DB: $blog_id", true );
				return null;
			}
		}
		
		$this->write_to_log( "ERROR: Unable to create profile site.", true );
		return false;
	}
	
	private function create_connection_post( $db_user )
	{
		global $wpdb;
		
		$connections_site_categories = $this->get_option( 'connections-site-categories' );
		$connections_site_categories = array_filter( explode(',', $connections_site_categories), 'trim' );
		
		if( !in_array( $db_user['type'], $connections_site_categories ) ) return null;
		if( !$db_user['wp_user_id'] ) return null;
		
		$connections_blog_id = $this->check_connections_site();
		
		if( !$connections_blog_id )
		{
			$this->write_to_log( 'Connections site does not exist or does not have Connections Hub plugin activated.', true );
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
			if( ($db_user['profile_site_id']) && (get_blog_details( $db_user['profile_site_id'] ) !== false) )
			{
				$connections_post_type = 'synch';
			}
		}
		else
		{
			$connections_post_type = 'synch';
		}
		
		$connection_post = array(
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
			$connections_post_id = wp_insert_post( $connections_post );
		}
		
		wp_reset_query();

		if( !$connections_post_id )
		{
			if( isset($connections_post['ID']) )
				$this->write_to_log( 'Unable to update Connections Post '.$connections_post['ID'].'.' );
			else
				$this->write_to_log( 'Unable to insert Connection Post.' );

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
			$result = $wpdb->update(
				self::$table,
				array( 'connections_post_id' => intval( $connections_post_id ) ),
				array( 'id' => intval( $db_user['id'] ) ),
				array( '%d' ),
				array( '%d' )
			);
		
			if( $result !== false )
			{
				return $connections_post_id;
			}
			else
			{
				$this->write_to_log( "ERROR: Unable to save connections post id to DB: $connections_post_id", true );
				return null;
			}
		}
		
		$this->write_to_log( "ERROR: Unable to create Connections post.", true );
		return null;
	}
	
	
	
	
	
	private function draft_connections_post( $db_user )
	{
		
	}
	
	private function archive_site( $db_user )
	{
		
	}
	
	
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
	
	
	public function get_option( $name, $default = false )
	{
		$options = get_site_option( self::ORGANIZATION_HUB_OPTIONS, array() );
		
		if( isset($options[$name]) ) return $options[$name];
		return false;
	}
	
	public function update_options( $options, $merge = false )
	{
		if( $merge === true )
			$options = array_merge( get_site_option(ORGANIZATION_HUB_OPTIONS, array()), $options );
			
		update_site_option( self::ORGANIZATION_HUB_OPTIONS, $options );
	}
	
	public function get_all_status_types()
	{
		global $wpdb;
		return $wpdb->get_col( "SELECT DISTINCT status FROM ".self::$table );		
	}
	
	public function get_all_user_types()
	{
		global $wpdb;
		return $wpdb->get_col( "SELECT DISTINCT type FROM ".self::$table );		
	}
	
	public function get_all_category_types()
	{
		global $wpdb;
		return $wpdb->get_col( "SELECT DISTINCT category FROM ".self::$table );		
	}
	
	public function is_ldap_plugin_active()
	{
		return is_plugin_active_for_network('wpmuldap/ldap_auth.php');
	}
	
	public function clear_log()
	{
		file_put_contents( self::LOG_PATH.'/log.txt', '' );
	}
	
	public function write_to_log( $text, $newline = false )
	{
		if( $newline ) $text .= "\n";
		file_put_contents( self::LOG_PATH.'/log.txt', $text, FILE_APPEND );
	}
	
	
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
	
	
	public function set_user_status( $db_user, $status )
	{
		global $wpdb;
		
		$result = $wpdb->update(
			self::$table,
			array( 'status' => $status ),
			array( 'id' => intval( $db_user['id'] ) ),
			array( '%s' ),
			array( '%d' )
		);
	
		if( $result !== false )
		{
			return $status;
		}
		else
		{
			$this->write_to_log( "ERROR: Unable to set user status in DB: ".$db_user['id']." => ".$status, true );
			return false;
		}
	}


}

