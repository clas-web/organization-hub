<?php
/**
 * The main model for the Organization Hub plugin.
 * 
 * @package    organization-hub
 * @subpackage classes/model
 * @author     Crystal Barton <atrus1701@gmail.com>
 */
if( !class_exists('OrgHub_Model') ):
class OrgHub_Model
{
	/**
	 * The only instance of the current model.
	 * @var  OrgHub_Model
	 */	
	private static $instance = null;
	
	/**
	 * The Sites model.
	 * @var  OrgHub_SitesModel
	 */	
	public $site = null;

	/**
	 * The Users Model.
	 * @var  OrgHub_UsersModel
	 */	
	public $user = null;

	/**
	 * The Upload model.
	 * @var  OrgHub_UploadModel
	 */	
	public $upload = null;
	
	/**
	 * The last error saved by the model.
	 * @var  string
	 */	
	public $last_error = null;
		
	
	/**
	 * Private Constructor.  Needed for a Singleton class.
	 */
	protected function __construct() { }
	
	
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
	 * @param  string  $text  The line of text to insert into the log.
	 * @param  bool  $newline  True if a new line character should be inserted after the line, otherwise False.
	 */
	public function write_to_log( $username = '', $text = '', $newline = true )
	{
		$text = print_r( $text, true );
		if( $newline ) $text .= "\n";
		$text = str_pad( $username, 8, ' ', STR_PAD_RIGHT ).' : '.$text;
		file_put_contents( ORGANIZATION_HUB_LOG_FILE, $text, FILE_APPEND );
	}	



//========================================================================================
//========================================================================== Options =====
	
	
	/**
	 * Gets the complete array of Organization Hub options.
	 * @return array  The Organization Hub option array.
	 */
	public function get_options()
	{
		if( is_network_admin() )
			return get_site_option( ORGANIZATION_HUB_OPTIONS, array() );
		
		return get_option( ORGANIZATION_HUB_OPTIONS, array() );
	}
	
	
	/**
	 * Get an Organization Hub option.
	 * @param  string  $name  The name of the option.
	 * @param  bool|string  $default  The default value for the option used if the option 
	 *                                doesn't currently exist.
	 * @return  bool|string  The value of the option, if it exists, otherwise the default.
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
	 * @param  bool  $merge  True if the new values should be merged into the existing
	 *                       options, otherwise the options are overwrited.
	 */
	public function update_options( $options, $merge = false )
	{
		if( $merge === true )
			$options = array_merge( $this->get_options(), $options );
			if( is_network_admin() ){
				update_site_option( ORGANIZATION_HUB_OPTIONS, $options );
			}
			else{
				update_option( ORGANIZATION_HUB_OPTIONS, $options );
			}
		}
	
	
	/**
	 * Updates the current value(s) of the Organization Hub options.
	 * @param  string  $key  The key name of the option.
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
	 * @param  mixed  $value  The value to use in the comparison.
	 * @param  string  $key  The key to use in the comparison.
	 * @param  array  $array  The array to use in the comparison.
	 * @param  bool  $strict  Use a strict comparison (eg. case-sensitive string).
	 * @return  bool  True if a match, otherwise false.
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
	 * @param  array  $columns  An associative array with key being table name and values being column names.
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
	 * Creates a user.
	 * @param  string  $username  The user's login name.
	 * @param  string  $password  The user's password.
	 * @param  string  $email  The user's email.
	 * @return  WP_User|null  The user object on success, otherwise null.
	 */
	public function create_user( $username, $password, $email, $firstname, $lastname )
	{
		$user = null;
		

		// Determine how the user should be created.
		$create_user_type = $this->get_option( 'create-user-type', 'local' );
		// Create the user.
		switch( $create_user_type )
		{
			case 'local':
				if (!isset($password)) 	$newPassword = wp_generate_password( 8, false );
				
				// Check to see if email is empty
				if ( empty($email) )
					return new WP_Error('newusercreate_emailempty', sprintf(__('<strong>ERROR</strong>: <strong>%s</strong> does not have an email address in the uploaded file.  All WordPress accounts must have a unique email address.'),$username));
				// Check to see if email already exists
				if ( email_exists($email) )
					return new WP_Error('newusercreate_emailconflict', sprintf(__('<strong>ERROR</strong>: <strong>%s</strong> (%s) is already associated with another account.  All accounts (including the admin account) must have unique email addresses.'),$email,$username));
				$user_id = wpmu_create_user( $username, $newPassword, $email );
				if ( $user_id === false ) {
					$this->last_error = $result->get_error_message();
					return null;
				}
				
				//For use with shibboleth login. Not neccessary for general use.
				update_usermeta($user_id, 'shibboleth_account', true);

				//Set Public Display Name
				if (!empty($firstname) && !empty($lastname)){
					update_usermeta($user_id, 'first_name', $firstname);
					update_usermeta($user_id, 'last_name', $lastname);
					$display_name = $firstname.' '.$lastname;
					if (!empty($display_name)) {
						$args = array(
							'ID' => $user_id,
							'nickname' => $firstname,
							'display_name' => $display_name
						);
						wp_update_user( $args );
					}
				}

				//This is for plugin events
				do_action('wpmu_activate_user', $user_id, $newPassword, false);
	
				// Add user as subscriber to blog #1
				add_user_to_blog( '1', $userid, get_site_option( 'default_user_role', 'subscriber' ) );
				update_usermeta($userid, "primary_blog", 1);
				break;
			
			default:
				$user = apply_filters(
					"orghub_create_user-$create_user_type",
					get_user_by( 'login', $username ),
					$username,
					$password,
					$email
				);
				
				if( is_numeric($user) || is_a($user, 'WP_User') )
				{
					break;
				}
				
				if( is_wp_error($user) )
				{
					$this->last_error = $user->get_error_message();
					return null;
				}
				
				if( is_string($user) )
				{
					$this->last_error = $user;
					return null;
				}
				break;
		}
		
		$user = get_user_by( 'login', $username );
		return $user;
	}
	
	
	/**
	 * Gets the complete site url and alters the domain and variables.
	 * @param  string  $domain  The domain of the site. If domain is empty then the default domain is used.
	 * @param  string  $path  The path of the site.
	 * @param  bool  $add_to_path  Prepend the default site's path to the path.
	 * @return  string  The complete path from the domain and path.
	 */
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
	 * Get the blog id from the slug (supports multi-domain sites).
	 * @param  String  $slug  The blog slug.
	 * @return  int|NULL  The blog id or NULL if the blog is not found.
	 */
	public function get_blog_id( $slug )
	{
		global $wpdb;
		$slug = ''.$slug;

		$blog_id = wp_cache_get( 'get_id_from_blogname_' . $slug, 'blog-details' );
		if( $blog_id ) return $blog_id;

		if( $slug === '' )
		{
			$blog_id = 1;
		}
		elseif( is_subdomain_install() )
		{
			$blog_id = $wpdb->get_var(
				$wpdb->prepare(
					"SELECT blog_id FROM {$wpdb->blogs} WHERE `domain` LIKE %s",
					$slug.'.%'
				)
			);
		}
		else
		{
			$blog_id = $wpdb->get_var(
				$wpdb->prepare(
					"SELECT blog_id FROM {$wpdb->blogs} WHERE `path` LIKE %s",
					'%/'.$slug.'/'
				)
			);
		}

		wp_cache_set( 'get_id_from_blogname_' . $slug, $blog_id, 'blog-details' );
		
		return $blog_id;
	}
	
} // class OrgHub_Model
endif; // if( !class_exists('OrgHub_Model') ):

