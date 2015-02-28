<?php
/**
 * OrgHub_Model
 * 
 * The main model for the Organization Hub plugin.
 * 
 * @package    orghub
 * @subpackage classes
 * @author     Crystal Barton <cbarto11@uncc.edu>
 */

if( !class_exists('OrgHub_Model') ):
class OrgHub_Model
{
	
	private static $instance = null;	// The only instance of this class.
	
	public $site = null;				// The site model.
	public $user = null;				// The user model.
	public $upload = null;				// The upload model.
	
	public $last_error = null;			// The error logged by a model.
	
	
	
	/**
	 * Private Constructor.  Needed for a Singleton class.
	 * Creates an OrgHub_Model object.
	 */
	protected function __construct()
	{
	}
	
	
	/**
	 * Sets up the "children" models used by this model.
	 */
	protected function setup_models()
	{
		$this->site = OrgHub_SitesModel::get_instance();
		$this->user = OrgHub_UsersModel::get_instance();
		$this->upload = OrgHub_UploadModel::get_instance();
	}
	

	/**
	 * Get the only instance of this class.
	 * @return  OrgHub_Model  A singleton instance of the model class.
	 */
	public static function get_instance()
	{
		if( self::$instance	=== null )
		{
			self::$instance = new OrgHub_Model();
			self::$instance->setup_models();
		}
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
//========================================================================== Options =====
	
	
	/**
	 * 
	 */
	public function get_options()
	{
		if( is_network_admin() )
			return get_site_option( ORGANIZATION_HUB_OPTIONS, array() );
		
		return get_option( ORGANIZATION_HUB_OPTIONS, array() );
	}
	
	
	/**
	 * Get an Organization Hub option.
	 * @param  string       $name     The name of the option.
	 * @param  bool|string  $default  The default value for the option used if the option
	 *                                doesn't currently exist.
	 * @return bool|string  The value of the option, if it exists, otherwise the default.
	 */
	public function get_option( $name, $default = false )
	{
		$options = $this->get_options();
		
		if( isset($options[$name]) ) return $options[$name];
		return $default;
	}


	/**
	 * Updates the current value(s) of the Organization Hub options.
	 * @param  array  $options  The new values.
	 * @param  bool   $merge    True if the new values should be merged into the existing
	 *                            options, otherwise the options are overwrited.
	 */
	public function update_options( $options, $merge = false )
	{
		if( $merge === true )
			$options = array_merge( $this->get_options(), $options );
		
		if( is_network_admin() )
			update_site_option( ORGANIZATION_HUB_OPTIONS, $options );
		else
			update_option( ORGANIZATION_HUB_OPTIONS, $options );
	}
	
	
	/**
	 * Updates the current value(s) of the Organization Hub options.
	 * @param  string  $key    The key name of the option.
	 * @param  string  $value  The string value of the option.
	 */
	public function update_option( $key, $value )
	{
		$this->update_options( array( $key => $value ), true );
	}



//========================================================================================
//================================================================== Database tables =====


	/**
	 * Create the required database tables.
	 */
	public function create_tables()
	{
		$this->user->create_tables();
		$this->site->create_tables();
		$this->upload->create_tables();
	}
	
	
	/**
	 * Drop the required database tables.
	 */
	public function delete_tables()
	{
		$this->user->delete_tables();
		$this->site->delete_tables();
		$this->upload->delete_tables();
	}


	/**
	 * Clear the required database tables.
	 */
	public function clear_tables()
	{
		$this->user->clear_tables();
		$this->site->clear_tables();
		$this->upload->clear_tables();
	}



//========================================================================================
//================================================================ Utility functions =====


	/**
	 * Determines if a key/value pair is present in an array.
	 * @param   mixed   $value   The value to use in the comparison.
	 * @param   string  $key     The key to use in the comparison.
	 * @param   array   $array   The array to use in the comparison.
	 * @param   bool    $strict  Use a strict comparison (eg. case-sensitive string).
	 * @return  bool    True if a match, otherwise false.
	 */
	public function in_array_by_key( $value, $key, $array, $strict = false )
	{ 
		if( $strict )
		{
			foreach( $array as $item )
			{
				if( isset($item[$key]) && $item[$key] === $value ) return true;
			}
		}
		else
		{
			foreach( $array as $item )
			{
				if( isset($item[$key]) && $item[$key] == $value ) return true; 
			}
		}
		
		return false; 
	}
	
	
	/**
	 * Creates a list of column names.
	 * @param   array   $columns  An associative array with key being table name and
	 *                            values being column names.
	 * @return  string  The generated SQL of column names.
	 */
	public function get_column_list( $columns )
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
	
	
	/**
	 * 
	 */
	public function create_user( $username, $password, $email )
	{
		$user = null;
		
		// Determine how the user should be created.
		$create_user_type = $this->get_option( 'create-user-type', 'local' );
		
		if( ($create_user_type == 'wpmu-ldap') && (!$this->is_ldap_plugin_active()) )
		{
			$this->last_error = 'WPMU LDAP plugin not active.';
			return null;
		}
		
		// Create the user.
		switch( $create_user_type )
		{
			case 'local':
				if( empty($password) )
					$password = wp_generate_password( 8, false );
				
				$result = wp_create_user( $username, $password, $email );
				
				if( is_wp_error($result) )
				{
					$this->last_error = $result->get_error_message();
					return null;
				}
				
				$user = get_user_by( 'login', $username );
				break;
			
			case 'wpmu-ldap':
				$result = wpmuLdapSearchUser(
					array(
						'username' => $username,
						'new_role' => 'subscriber',
						'createUser' => true
					)
				);

				if( is_wp_error($result) )
				{
					$this->last_error = $result;
					return null;
				}
				
				$user = get_user_by( 'login', $username );
				break;
			
			default:
				do_action( 'orghub_create_user-'.$create_user_type, $username, $password, $email );
				$user = get_user_by( 'login', $username );
				break;
		}
		
		return $user;
	}
	
	
	public function get_site_url( &$domain, &$path, $add_to_path = true )
	{
		$url_parts = parse_url( get_site_url(1) );
		
		if( !$domain )
		{
			$domain = $url_parts['host'];
		}
		
		if( $add_to_path )
		{
			if( array_key_exists('path', $url_parts) && $url_parts['path'] !== '/' )
				$path = $url_parts['path'].'/'.$path;
		}
		
		if( (strlen($path) > 0) && ($path[0] === '/') ) $path = substr( $path, 1 );
		
		return $domain.$path;
	}


	/**
	 * Determines if the WPMU Ldap plugin is active.
	 * @return  bool  True if the plugin is active, otherwise false.
	 */
	public function is_ldap_plugin_active()
	{
		return is_plugin_active_for_network('wpmuldap/ldap_auth.php');
	}
		
		
} // class OrgHub_Model
endif; // if( !class_exists('OrgHub_Model') ):

