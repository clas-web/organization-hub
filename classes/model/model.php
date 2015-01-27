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
	
	public $site = null;
	public $user = null;
	public $last_error = null;

	
	/**
	 * Private Constructor.  Needed for a Singleton class.
	 * Creates an OrgHub_Model object.
	 */
	private function __construct()
	{
	}
	
	private function setup_models()
	{
		$this->site = OrgHub_SitesModel::get_instance();
		$this->user = OrgHub_UsersModel::get_instance();
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
		$this->user->create_tables();
		$this->site->create_tables();
	}
	
	
	/**
	 * Drop the required database tables.
	 */
	public function delete_tables()
	{
		$this->user->delete_tables();
		$this->site->delete_tables();
	}


	/**
	 * Clear the required database tables.
	 */
	public function clear_tables()
	{
		$this->user->clear_tables();
		$this->site->clear_tables();
	}
	
}
endif;

