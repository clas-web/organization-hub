<?php
/**
 * The upload model for the Organization Hub plugin.
 * 
 * @package    organization-hub
 * @subpackage classes/model
 * @author     Crystal Barton <atrus1701@gmail.com>
 */
if( !class_exists('OrgHub_UploadModel') ):
class OrgHub_UploadModel
{
	/**
	 * The only instance of the current model.
	 * @var  OrgHub_UploadModel
	 */	
	private static $instance = null;

	/**
	 * The main model for the Organization Hub.
	 * @var  OrgHub_Model
	 */	
	private $model = null;
	
	/**
	 * The base name (without prefix) for the upload table.
	 * @var  string
	 */
	private static $upload_table = 'orghub_upload';
	
	
	/**
	 * Private Constructor.  Needed for a Singleton class.
	 */
	protected function __construct()
	{
		global $wpdb;
		self::$upload_table		= $wpdb->base_prefix.self::$upload_table;
		
		$this->model = OrgHub_Model::get_instance();
	}


	/**
	 * Get the only instance of this class.
	 * @return  OrgHub_UploadModel  A singleton instance of the sites model class.
	 */
	public static function get_instance()
	{
		if( self::$instance	=== null )
		{
			self::$instance = new OrgHub_UploadModel();
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

		$sql = "CREATE TABLE ".self::$upload_table." (
				  id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
				  blog_id bigint(20) unsigned NOT NULL,
				  data text NOT NULL DEFAULT '',
				  timestamp timestamp DEFAULT CURRENT_TIMESTAMP,
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
		$wpdb->query( 'DROP TABLE '.self::$upload_table.';' );
	}


	/**
	 * Clear the required database tables.
	 */
	public function clear_tables()
	{
		global $wpdb;
		$wpdb->query( 'DELETE FROM '.self::$upload_table.';' );
	}
	
	
//========================================================================================
//================================================ Import / Updating database tables =====
	
	
	/**
	 * Verifies that all the required fields are present.
	 * @param  array  $args  An array of user values.
	 * @return  bool  True if the args are valid, otherwise False.
	 */
	protected function check_args( &$args, $validate_for_action = false )
	{
		//
		// Verify that the required columns have been included.
		//
		$required_keys = array( 'type', 'action' );
		$required_values = array( 'type', 'action' );
		
		foreach( $required_keys as $key )
		{
			if( !isset($args[$key]) )
			{
				$this->model->last_error = 'The '.$key.' must be specified.';
				return false;
			}
			
			if( in_array($key, $required_values) && empty($args[$key]) )
			{
				$this->model->last_error = 'The '.$key.' must contain a value.';
				return false;
			}
		}
		
		if( !is_network_admin() && $args['type'] === 'site' )
		{
			$this->model->last_error = 'Unable to create/alter a site from a site.';
			return false;
		}
		
		$function = array(
			$this,
			'check_'.(str_replace('-', '_', $args['type'])).'_args',
		);
		
		if( is_callable($function) )
			return call_user_func_array( $function, array(&$args, $validate_for_action) );

		$this->model->last_error = 'Invalid type specified: "'.$args['type'].'"';
		return false;
	}
	
	
	/**
	 * Validates the arguments for an item based on required key, values, and other
	 * checks that checks for errors before writting to the database.
	 * @param  array  $args  The arguments.
	 * @param  array  $required_keys  The required keys that must exist in the args.
	 * @param  array  $required_values  The keys that must have non-empty values in the args.
	 * @param  array  $valid_keys  Others keys that are also valid in the args and should not be ignored 
	 *                             or should be created if they are missing.
	 * @param  array  $valid_regex_keys  Regular expression that need to be matched to determine there 
	 *                                   any other valid keys.
	 * @return  bool  True if all args where validated, otherwise false on error.
	 */
	private function validate_args_for_db( &$args, $required_keys, $required_values, $valid_keys, $valid_regex_keys )
	{
		$new_args = array(
			'action'	=> $args['action'],
			'type'		=> $args['type'],
		);
		
		foreach( array_keys($args) as $key )
		{
			if( in_array($key, $required_keys) || in_array($key, $valid_keys) )
			{
				$new_args[$key] = $args[$key];
				continue;
			}
			
			foreach( $valid_regex_keys as $regex )
			{
				if( preg_match("/^$regex$/", $key, $matches) ) 
					$new_args[$key] = $args[$key];
			}
		}
		
		foreach( $required_keys as $key )
		{
			if( !isset($new_args[$key]) )
			{
				$this->model->last_error = 'The '.$key.' must be specified.';
				return false;
			}
			
			if( in_array($key, $required_values) && empty($new_args[$key]) )
			{
				$this->model->last_error = 'The '.$key.' must contain a value.';
				return false;
			}
		}
		
		$args = $new_args;
		return true;
	}
	
	
	/**
	 * Validates the arguments for an item based on required key, values, and other
	 * checks that checks for errors before processing.
	 * @param  array  $args  The arguments.
	 * @param  array  $required_keys  The required keys that must exist in the args.
	 * @param  array  $required_values  The keys that must have non-empty values in the args.
	 * @param  array  $valid_keys  Others keys that are also valid in the args and should not be ignored or 
	 *                             should be created if they are missing.
	 * @param  array  $valid_regex_keys  Regular expression that need to be matched to determine there any
	 *                                   other valid keys.
	 * @return  bool  True if all args where validated, otherwise false on error.
	 */
	private function validate_args_for_action( &$args, $required_keys, $required_values, $valid_keys, $valid_regex_keys )
	{
		$result = $this->validate_args_for_db( 
			$args, 
			$required_keys, 
			$required_values, 
			$valid_keys, 
			$valid_regex_keys );
		if( !$result ) return false;

		foreach( $valid_keys as $key )
		{
			if( isset($args[$key]) ) continue;
			$args[$key] = '';
		}
		
		foreach( array_keys($args) as $key )
		{
			foreach( $valid_regex_keys as $regex )
			{
				$regex_key = '\(([a-zA-Z0-9]+)\)\\\-';
				if( !preg_match("/$regex_key/", $regex, $matches) ) continue;
				
				$args_key = $matches[1];
				if( !array_key_exists($args_key, $args) )
					$args[$args_key] = array();
					
				if( !preg_match("/^$regex$/", $key, $matches) ) continue;
				
				$args[$args_key][$matches[2]] = $args[$key];
				unset($args[$key]);
			}
		}
		
		// Taxonomies
		if( $args['type'] === 'post' )
		{
			if( array_key_exists('categories', $args) )
			{
				$args['taxonomy']['category'] = $args['categories'];
				unset($args['categories']);
			}
			if( array_key_exists('tags', $args) )
			{
				$args['taxonomy']['post_tag'] = $args['tags'];
				unset($args['tags']);
			}
		}
		
		$array_keys = array_keys($args);
		for( $i = 0; $i < count($array_keys); $i++ )
		{
			$key = $array_keys[$i];
			
			if( strpos( $key, '-' ) !== false )
			{
				$value = $args[$key];
				$args[str_replace('-','_',$key)] = $value;
				unset($args[$key]);
			}
		}
		
		return true;
	}
	
	
	/**
	 * Adds an OrgHub import data to the database.
	 * @param  array  $args  An array of data about a site.
	 * @return  int|bool  The id of the inserted site or false on failure.
	 */
	public function add_item( &$args )
	{
		if( !$this->check_args( $args ) ) return false;
		
		global $wpdb;
		
		
		// Insert new site into Upload table.
		$result = $wpdb->insert(
			self::$upload_table,
			array(
				'blog_id'		=> $this->get_current_blog_id(),
				'data'			=> json_encode($args),
			),
			array( '%s' )
		);
		

		// Check to make sure insertion was successful.
		$item_id = $wpdb->insert_id;
		if( !$item_id )
		{
			$this->model->last_error = 'Unable to insert item.';
			return false;
		}

		return $item_id;
	}
	
	
	/**
	 * Delete an OrgHub upload item from the database.
	 * @param  int  $id  The id of the item to delete.
	 * @return  bool  True if the item was deleted successfully, otherwise false.
	 */
	public function delete_item( $id )
	{
		global $wpdb;
		
		$wpdb->delete(
			self::$upload_table,
			array(
				'id'		=> $id,
			),
			array( '%d' )
		);
		
		return true;
	}
	
	
//========================================================================================
//=============================================== Retrieve upload data from database =====
	
	
	/**
	 * Gets an item based on the id.
	 * @param  int  $id  The id of the item
	 * @return  array|null  The item if found, otherwise null on failure or not found.
	 */
	public function get_item_by_id( $id )
	{
		global $wpdb;
		$item = $wpdb->get_row(
			$wpdb->prepare(
				"SELECT * FROM ".self::$upload_table." WHERE id = %d",
				$id
			),
			ARRAY_A
		);
		
		if( $item ) $item['data'] = json_decode( $item['data'], true );
		
		return $item;
	}
	
	
	/**
	 * Retrieve a complete list of OrgHub items from the database after filtering.
	 * @param  int  $blog_id  The blog id of the current site's batch. 
	 * @param  int  $offset  The offset of the users list.
	 * @param  int  $limit  The amount of users to retrieve.
	 * @return  array  An array of items given the filtering.
	 */
	public function get_items( $orderby = null, $offset = 0, $limit = -1 )
	{
		global $wpdb;
		
		$list = array();
		$list[self::$upload_table] = array(
			'id', 'data', 'timestamp'
		);
		
		$list = $this->model->get_column_list( $list );
		
		$blog_id = $this->get_current_blog_id();
		$filter = $this->filter_sql($blog_id, $orderby, $offset, $limit);
		
		$items = $wpdb->get_results( 'SELECT '.$list.' FROM '.self::$upload_table.' '.$filter, ARRAY_A );
		
		foreach( $items as &$item ) $item['data'] = json_decode( $item['data'], true );
		
		return $items;
	}
	
	
	/**
	 * The amount of OrgHub items from the database.
	 * @param  int  $blog_id  The blog id of the current site's batch. 
	 * @return  array  The amount of items.
	 */
	public function get_items_count()
	{
		global $wpdb;
		$blog_id = $this->get_current_blog_id();
		return $wpdb->get_var( "SELECT COUNT(DISTINCT ".self::$upload_table.".id) FROM ".self::$upload_table.' '.$this->filter_sql($blog_id) );
	}


	/**
	 * Creates the SQL needed to complete an SQL statement.
	 * @param  int  $blog_id  The blog id of the current site's batch. 
	 * @param  int  $offset  The offset of the users list.
	 * @param  int  $limit  The amount of users to retrieve.
	 * @return  string  The constructed SQL needed to complete an SQL statement.
	 */
	protected function filter_sql( $blog_id = 0, $orderby = null, $offset = 0, $limit = -1 )
	{
		global $wpdb;
		
		$where = 'WHERE blog_id = '.intval($blog_id);
		
		if( $orderby ) $orderby = 'ORDER BY '.$orderby; else $orderby = 'ORDER BY timestamp';
		
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

		return $where.' '.$orderby.' '.$limit_string;
	}
	
	
	/**
	 * Clear all batch items for particular blog.
	 */
	public function clear_blog_batch_items()
	{
		global $wpdb;
		$wpdb->query( 'DELETE FROM '.self::$upload_table.' WHERE blog_id = '.$this->get_current_blog_id() );
	}
	
	
	/**
	 * Gets the current blog's id.
	 * @return  int  The current blog's id.
	 */
	public function get_current_blog_id()
	{
		$blog_id = 0;
		if( !is_network_admin() ) $blog_id = get_current_blog_id();
		return $blog_id;
	}
	
	
//========================================================================================
//========================================================================== Actions =====
	
	
	/**
	 * Process all the items in the upload table.
	 * @return  bool  True if no errors occurred, otherwise false.
	 */
	public function process_items()
	{
		$items = $this->get_items();
		
		$errors = array();
		foreach( $items as &$item )
		{
			$result = $this->process_item( $item['id'], $item );
			
			if( !$result )
				$errors[] = $this->model->last_error;
		}
		
		if( !empty($errors) )
		{
			$this->model->last_error = $errors;
			return false;
		}
		
		return true;
	}

	
	/**
	 * Process a single item in the upload table.
	 * @param  int  $id  The id of the item to process.
	 * @param  array|null  $item  The item to process or null in order to get item from db.
	 * @return  bool  True if no errors occured, otherwise false.
	 */
	public function process_item( $id, &$item = null )
	{
		$this->model->last_error = '';
		
		if( ($item === null) && ($item = $this->get_item_by_id( $id )) === null )
		{
			$this->model->last_error = "Item # $id: Invalid item id.";
			return false;
		}
		
		$data =& $item['data'];
		$error = $this->get_item_description( $id, $data ).' error: ';
		
		if( !$this->check_args($data, true) )
		{
			$this->model->last_error = $error . $this->model->last_error;
			return false;
		}
	
		$type = str_replace( '-', '_', $data['type'] );
		$action = str_replace( '-', '_', $data['action'] );

		$function = array(
			$this,
			$action.'_'.$type,
		);
		
		$switch_to_blog = true;
		$site_required = true;
		
		if( $type === 'site' && $action === 'add' ) $switch_to_blog = false;
		if( $type === 'user' ) $site_required = false;
		
		if( is_callable($function) )
		{
			$this->delete_item( $item['id'] );
			
			if( ($switch_to_blog) && (!$this->switch_to_blog($data, $site_required)) )
			{
				$site = ( isset($data['site']) ? '"'.$data['site'].'"' : '[not specified]' );
				$this->model->last_error = $error . 'Unable to switch to site: "'.$site.'". ';
				return false;
			}
			
			$return = call_user_func_array( $function, array(&$data) );
			
			$this->restore_blog();
			
			if( !$return )
				$this->model->last_error = $error . $this->model->last_error;
			
			return $return;
		}
		
		$this->model->last_error = $error . 'Invalid type "'.$data['type'].' or action "'.$data['action'].'" specified.';
		return false;
	}
	
	
//========================================================================================
//======================================================================= TYPE: Post =====
	
	
	/**
	 * Verifies that all the required fields are present for "post" types.
	 * @param  array  $args  An array of user values.
	 * @return  bool  True if the args are valid, otherwise False.
	 */
	protected function check_post_args( &$args, $validate_for_action = false )
	{
		$required_keys = array();
		$required_values = array();
		$valid_keys = array();
		$valid_regex_keys = array();
		
		switch( $args['action'] )
		{
			case 'add':
				/*
				required fields:
					type, action, site, title
				all supported fields:
					type, action, site, title, excerpt, content, post-type, date, author, slug, 
					guid, status, password, categories, tags, taxonomy-{name}, meta-{name}
				*/
				$required_keys = array( 'site', 'title' );
				$required_values = array( 'site', 'title' );
				$valid_keys = array( 'excerpt', 'content', 'post-type', 'date', 'author', 'slug', 'guid', 'status', 'password', 'categories', 'tags' );
				$valid_regex_keys = array( '(taxonomy)\-([a-zA-Z0-9\-_]+)', '(meta)\-([a-zA-Z0-9\-_]+)' );
				break;
				
			case 'update':
				/*
				required fields:
					type, action, site, title
				all supported fields:
					type, action, site, title, excerpt, content, post-type, date, author, slug, 
					status, password, categories, tags, taxonomy-{name}, meta-{name}
				*/
				$required_keys = array( 'site', 'title' );
				$required_values = array( 'site', 'title' );
				$valid_keys = array( 'excerpt', 'content', 'post-type', 'date', 'author', 'slug', 'status', 'password', 'categories', 'tags' );
				$valid_regex_keys = array( '(taxonomy)\-([a-zA-Z0-9\-_]+)', '(meta)\-([a-zA-Z0-9\-_]+)' );
				break;
			
			case 'replace':
				/*
				required fields:
					type, action, site, title
				all supported fields:
					type, action, site, title, excerpt, content, post-type, date, author, slug, 
					guid, status, password, categories, tags, taxonomy-{name}, meta-{name}
				*/
				$required_keys = array( 'site', 'title' );
				$required_values = array( 'site', 'title' );
				$valid_keys = array( 'excerpt', 'content', 'post-type', 'date', 'author', 'slug', 'guid', 'status', 'password', 'categories', 'tags' );
				$valid_regex_keys = array( '(taxonomy)\-([a-zA-Z0-9\-_]+)', '(meta)\-([a-zA-Z0-9\-_]+)' );
				break;
			
			case 'prepend':
				/*
				required fields:
					type, action, site, title
				all supported fields:
					type, action, site, title, excerpt, content
				*/
				$required_keys = array( 'site', 'title' );
				$required_keys = array( 'site', 'title' );
				$valid_keys = array( 'excerpt', 'content', 'post-type' );
				break;
			
			case 'append':
				/*
				required fields:
					type, action, site, title
				all supported fields:
					type, action, site, title, excerpt, content
				*/
				$required_keys = array( 'site', 'title' );
				$required_keys = array( 'site', 'title' );
				$valid_keys = array( 'excerpt', 'content', 'post-type' );
				break;
			
			case 'delete':
				/*
				required fields:
					type, action, site, title
				all supported fields:
					type, action, site, title
				*/
				$required_keys = array( 'site', 'title' );
				$required_values = array( 'site', 'title' );
				$valid_keys = array( 'post-type' );
				break;
				
			case 'rename':
				/*
				required fields:
					type, action, site, title, new-title
				all supported fields:
					type, action, site, title, new-title
				*/
				$required_keys = array( 'site', 'title', 'new-title' );
				$required_values = array( 'site', 'title', 'new-title' );
				$valid_keys = array( 'post-type' );
				break;
				
			case 'grep':
				/*
				required fields:
					type, action, site, subject, regex, replace-text
				all supported fields:
					type, action, site, title, post-type, subject, regex, replace-text
				*/
				$required_keys = array( 'site', 'subject', 'regex', 'replace-text' );
				$required_values = array( 'site', 'subject', 'regex' );
				$valid_keys = array( 'title', 'post-type' );
				break;
				
			case 'add-taxonomy':
			case 'update-taxonomy':
			case 'delete-taxonomy':
				/*
				required fields:
					type, action, site, title
				all supported fields:
					type, action, site, title, categories, tags, taxonomy-{name}
				*/
				$required_keys = array( 'site', 'title' );
				$required_values = array( 'site', 'title' );
				$valid_keys = array( 'categories', 'tags', 'post-type' );
				$valid_keys_regex = array( '(taxonomy)\-([a-zA-Z0-9\-_]+)' );
				break;
			
			case 'add-meta':
				/*
				required fields:
					type, action, site, title, name, value
				all supported fields:
					type, action, site, title, name, value
				*/
				$required_keys = array( 'site', 'title', 'name', 'value' );
				$required_values = array( 'site', 'title', 'name' );
				$valid_keys = array( 'post-type' );
				break;
			
			case 'update-meta':
				/*
				required fields:
					type, action, site, title, name, value
				all supported fields:
					type, action, site, title, name, value
				*/
				$required_keys = array( 'site', 'title', 'name', 'value' );
				$required_values = array( 'site', 'title', 'name' );
				$valid_keys = array( 'post-type' );
				break;
			
			case 'replace-meta':
				/*
				required fields:
					type, action, site, title, name, value
				all supported fields:
					type, action, site, title, name, value
				*/
				$required_keys = array( 'site', 'title', 'name', 'value' );
				$required_values = array( 'site', 'title', 'name' );
				$valid_keys = array( 'post-type' );
				break;
			
			case 'delete-meta':
				/*
				required fields:
					type, action, site, title, name
				all supported fields:
					type, action, site, title, name
				*/
				$required_keys = array( 'site', 'title', 'name' );
				$required_values = array( 'site', 'title', 'name' );
				$valid_keys = array( 'post-type' );
				break;
			
			case 'copy-meta':
				/*
				required fields:
					type, action, site, title, name, new-name
				all supported fields:
					type, action, site, title, name, new-name
				*/
				$required_keys = array( 'site', 'title', 'name', 'new-name' );
				$required_values = array( 'site', 'title', 'name', 'new-name' );
				$valid_keys = array( 'post-type' );
				break;
			
			default:
				$this->model->last_error = 'Invalid action for type: "post" => "'.$args['action'].'".';
				return false;
				break;
		}
		
		if( !is_network_admin() )
		{
			$a = array( 'site' );
			$required_keys = array_diff( $required_keys, $a );
			$required_values = array_diff( $required_values, $a );
			$valid_keys = array_diff( $valid_keys, $a );
		}
		
		if( $validate_for_action )
		{
			return $this->validate_args_for_action( 
				$args, 
				$required_keys, 
				$required_values, 
				$valid_keys, 
				$valid_regex_keys );
		}
		
		return $this->validate_args_for_db( 
			$args, 
			$required_keys, 
			$required_values, 
			$valid_keys, 
			$valid_regex_keys );
	}
	
	
	/**
	 * Add a new post to a site.  If a post with the same title already exists, then the 
	 * post will not be created.
	 * @param  array  $item  The data for the batch item.
	 * @return  bool  True if the action was successful, otherwise false.
	 */
	private function add_post( &$item )
	{
		extract($item);
		
		$post = $this->get_post_by_title( $title, $post_type );
		if( $post ) 
		{
			$this->model->last_error = 'Post already exists "'.$title.'". ';
			return false;
		}
		
		$post_data = array(
			'post_content'	=> $this->get_string_value( $content, true ),
			'post_name'		=> $this->get_string_value( $slug, true ),
			'post_title'	=> $title,
			'post_status'	=> $this->get_post_status( $status ),
			'post_type'		=> $this->get_post_type( $post_type ),
			'post_author'	=> $this->get_author_id( $author ),
			'post_password'	=> $this->get_string_value( $password, true ),
			'guid'			=> $this->get_string_value( $guid, true ),
			'post_excerpt'	=> $this->get_string_value( $excerpt, true ),
			'post_date'		=> $this->parse_date( $date, true ),
			'tax_input'		=> $this->get_taxonomies( $taxonomy, true ),
		);
		
		$this->filter_post_data( $post_data );	
		$result = wp_insert_post( $post_data, true );
		
		if( is_wp_error($result) )
		{
			$this->model->last_error = 'Unable to insert post "'.$title.'". '.$result->get_error_message();
			return false;
		}
		
		$post_id = $result;
		foreach( $meta as $key => $value )
		{
			update_post_meta( $post_id, $key, $value );
		}
		
		return true;
	}
	
	
	/**
	 * Updates an existing post on a site.  If a post with a matching title does not 
	 * exist, then it will not be created.
	 * @param  array  $item  The data for the batch item.
	 * @return  bool  True if the action was successful, otherwise false.
	 */
	private function update_post( &$item )
	{
		extract($item);
		
		$post = $this->get_post_by_title( $title, $post_type );
		if( !$post )
		{
			$this->model->last_error = 'Post does not exist "'.$title.'". ';
			return false;
		}
		
		$post_data = array(
			'ID'			=> $post->ID,
			'post_content'	=> $this->get_string_value( $content, true ),
			'post_name'		=> $this->get_string_value( $slug, true ),
			'post_status'	=> $this->get_post_status( $status ),
			'post_type'		=> $this->get_post_type( $post_type ),
			'post_author'	=> $this->get_author_id( $author ),
			'post_password'	=> $this->get_string_value( $password, true ),
			'post_excerpt'	=> $this->get_string_value( $excerpt, true ),
			'post_date'		=> $this->parse_date( $date, true ),
			'tax_input'		=> $this->get_taxonomies( $taxonomy, true ),
		);
		
		$this->filter_post_data( $post_data );	
		$result = wp_update_post( $post_data, true );
		
		if( is_wp_error($result) )
		{
			$this->model->last_error = 'Unable to update post "'.$title.'". '.$result->get_error_message();
			return false;
		}
		
		$post_id = $result;
		foreach( $meta as $key => $value )
		{
			update_post_meta( $post_id, $key, $value );
		}
		
		return true;
	}
	
	
	/**
	 * Adds or updates a post on a site.  If a post with a matching title exists, then it 
	 * will be updated.  If a matching post is not found, then it will be created.
	 * @param  array  $item  The data for the batch item.
	 * @return  bool  True if the action was successful, otherwise false.
	 */
	private function replace_post( &$item )
	{
		extract($item);
		
		$post = $this->get_post_by_title( $title, $post_type );
		
		if( !$post ) return $this->add_post( $item );
		
		return $this->update_post( $item );
	}
	
	
	/**
	 * Prepend content to the start of the post’s excerpt or content.
	 * @param  array  $item  The data for the batch item.
	 * @return  bool  True if the action was successful, otherwise false.
	 */
	private function prepend_post( &$item )
	{
		extract($item);
		
		$post = $this->get_post_by_title( $title, $post_type );
		if( !$post ) 
		{
			$this->model->last_error = 'Post does not exist "'.$title.'". ';
			return false;
		}
		
		$post_data = array(
			'ID'			=> $post->ID,
			'post_excerpt'	=> $excerpt.$post->post_excerpt,
			'post_content'	=> $this->prepend_to_content( $post->post_content, $content ),
		);

		$this->filter_post_data( $post_data );
		$result = wp_update_post( $post_data, true );
		
		if( is_wp_error($result) )
		{
			$this->model->last_error = 'Unable to update post "'.$title.'". '.$result->get_error_message();
			return false;
		}
		
		return true;
	}
	
	
	/**
	 * Append content to the start of the post’s excerpt or content.
	 * @param  array  $item  The data for the batch item.
	 * @return  bool  True if the action was successful, otherwise false.
	 */
	private function append_post( &$item )
	{
		extract($item);
		
		$post = $this->get_post_by_title( $title, $post_type );
		if( !$post )
		{
			$this->model->last_error = 'Post does not exist "'.$title.'". ';
			return false;
		}
		
		$post_data = array(
			'ID'			=> $post->ID,
			'post_excerpt'	=> $post->post_excerpt.$excerpt,
			'post_content'	=> $this->append_to_content( $post->post_content, $content ),
		);
		
		$this->filter_post_data( $post_data );
		$result = wp_update_post( $post_data, true );
		
		if( is_wp_error($result) )
		{
			$this->model->last_error = 'Unable to update post "'.$title.'". '.$result->get_error_message();
			return false;
		}
		
		return true;
	}
	
	
	/**
	 * Deletes a post for a site.
	 * @param  array  $item  The data for the batch item.
	 * @return  bool  True if the action was successful, otherwise false.
	 */
	private function delete_post( &$item )
	{
		extract($item);
		
		$post = $this->get_post_by_title( $title, $post_type );
		if( !$post )
		{
			$this->model->last_error = 'Post does not exist "'.$title.'". ';
			return false;
		}
		
		$result = wp_delete_post( $post->ID, true );
		if( $result === false )
		{
			$this->model->last_error = 'Unable to delete post "'.$title.'". ';
			return false;
		}
		
		return true;
	}
	
	
	/**
	 * Renames a post by changing it’s title.
	 * @param  array  $item  The data for the batch item.
	 * @return  bool  True if the action was successful, otherwise false.
	 */
	private function rename_post( &$item )
	{
		extract($item);
		
		$post = $this->get_post_by_title( $title, $post_type );
		if( !$post )
		{
			$this->model->last_error = 'Post does not exist "'.$title.'". ';
			return false;
		}
		
		$post_data = array(
			'ID'			=> $post->ID,
			'post_title'	=> $new_title,
		);
		
		$this->filter_post_data( $post_data );
		$result = wp_update_post( $post_data, true );
		
		if( is_wp_error($result) )
		{
			$this->model->last_error = 'Unable to update post "'.$title.'". '.$result->get_error_message();
			return false;
		}
		
		return true;
	}
	
	
	/**
	 * Search through a field in a post using a regular expression.  If the title of the 
	 * post is not specified, then the grep will be performed on all posts in the site.
	 * @param  array  $item  The data for the batch item.
	 * @return  bool  True if the action was successful, otherwise false.
	 */
	private function grep_post( &$item )
	{
		extract($item);
		
		$posts = array();
		
		if( !empty($title) )
		{
			$post = $this->get_post_by_title( $title, $post_type );
			if( !$post )
			{
				$this->model->last_error = 'Post does not exist "'.$title.'". ';
				return false;
			}
			
			$posts[] = $post;
		}
		else
		{
			$posts = get_posts(
				array(
					'posts_per_page' => -1,
					'post_type' => $post_type,
					'post_status' => 'any',
				)
			);
		}
		
		$errors = array();
		foreach( $posts as $post )
		{
			$post_data = array( 'ID' => $post->ID );
			
			switch( $subject )
			{
				case 'excerpt':
					$excerpt = preg_replace( "/{$regex}/", $replace_text, $post->post_excerpt, -1, $count );
					if( $count === 0 ) continue;
					
					$post_data['post_excerpt'] = $excerpt;
					break;
					
				case 'content':
					$content = preg_replace( "/{$regex}/", $replace_text, $post->post_content, -1, $count );
					if( $count === 0 ) continue;

					$post_data['post_content'] = $content;
					break;
				
				default:
					continue; break;
			}
			
			$this->filter_post_data( $post_data );
			$result = wp_update_post( $post_data, true );
		
			if( is_wp_error($result) )
			{
				$errors[] = 'Unable to update post "'.$title.'". '.$result->get_error_message();
				continue;
			}
		}
		
		if( !empty($errors) )
		{
			$this->model->last_error = $this->format_errors_into_string( $errors );
			return false;
		}
		
		return true;
	}
	
	
	/**
	 * Adds to the post’s existing terms for the taxonomy by adding these taxonomy terms(s).
	 * @param  array  $item  The data for the batch item.
	 * @return  bool  True if the action was successful, otherwise false.
	 */
	private function add_taxonomy_post( &$item )
	{
		extract($item);
		
		$post = $this->get_post_by_title( $title, $post_type );
		if( !$post )
		{
			$this->model->last_error = 'Post does not exist "'.$title.'". ';
			return false;
		}
		
		$existing_taxonomies = array();
		$taxonomy_names = array_keys($taxonomy);
		foreach( $taxonomy_names as $taxname )
		{
			$taxterms = wp_get_object_terms( $post->ID, array($taxname) );
			$existing_taxonomies[$taxname] = $taxterms;
		}
		
		$new_taxonomies = $this->get_taxonomies( $taxonomy );
		foreach( $new_taxonomies as $taxname => $terms )
		{
			if( !array_key_exists($taxname, $existing_taxonomies) )
			{
				$existing_taxonomies[$taxname] = $terms;
				continue;
			}

			if( is_taxonomy_hierarchical($taxname) )
			{
				$existing_taxonomies_ids = array_map(
					function( $term )
					{
						return $term->term_id;
					},
					$existing_taxonomies[$taxname]
				);
				$existing_taxonomies[$taxname] = array_merge( $existing_taxonomies_ids, $new_taxonomies[$taxname] );
			}
			else
			{
				$existing_taxonomies_names = array_map(
					function( $term )
					{
						return $term->name;
					},
					$existing_taxonomies[$taxname]
				);
				$existing_taxonomies[$taxname] = array_merge( $existing_taxonomies_names, $new_taxonomies[$taxname] );
			}

			$existing_taxonomies[$taxname] = array_unique( $existing_taxonomies[$taxname] );
		}
		
		$post_data = array(
			'ID'			=> $post->ID,
			'tax_input'		=> $existing_taxonomies,
		);
		
		$this->filter_post_data( $post_data );
		$result = wp_update_post( $post_data, true );
		
		if( is_wp_error($result) )
		{
			$this->model->last_error = 'Unable to update post "'.$title.'". '.$result->get_error_message();
			return false;
		}
		
		return true;
	}
	
	
	/**
	 * Overwrites the post’s existing terms for the taxonomy and replaces with these 
	 * taxonomy term(s).
	 * @param  array  $item  The data for the batch item.
	 * @return  bool  True if the action was successful, otherwise false.
	 */
	private function update_taxonomy_post( &$item )
	{
		extract($item);
		
		$post = $this->get_post_by_title( $title, $post_type );
		if( !$post )
		{
			$this->model->last_error = 'Post does not exist "'.$title.'". ';
			return false;
		}
		
		$taxonomies = $this->get_taxonomies( $taxonomy, true );
		foreach( $taxonomies as $taxname => $terms )
		{
			wp_set_post_terms( $post->ID, $terms, $taxname ); 
		}
		
		return true;
	}
	
	
	/**
	 * Deletes the post’s terms for the taxonomy that matches these taxonomy term(s).
	 * @param  array  $item  The data for the batch item.
	 * @return  bool  True if the action was successful, otherwise false.
	 */
	private function delete_taxonomy_post( &$item )
	{
		extract($item);
		
		$post = $this->get_post_by_title( $title, $post_type );
		if( !$post )
		{
			$this->model->last_error = 'Post does not exist "'.$title.'". ';
			return false;
		}
		
		$existing_taxonomies = array();
		$taxonomy_names = array_keys($taxonomy);
		foreach( $taxonomy_names as $taxname )
		{
			$taxterms = wp_get_object_terms( $post->ID, array($taxname) );
			$existing_taxonomies[$taxname] = $taxterms;
		}
		
		$new_taxonomies = $this->get_taxonomies( $taxonomy );
		foreach( $new_taxonomies as $taxname => $terms )
		{
			if( !array_key_exists($taxname, $existing_taxonomies) )
			{
				$existing_taxonomies[$taxname] = $terms;
				continue;
			}

			if( is_taxonomy_hierarchical($taxname) )
			{
				$existing_taxonomies_ids = array_map(
					function( $term )
					{
						return $term->term_id;
					},
					$existing_taxonomies[$taxname]
				);
				$existing_taxonomies[$taxname] = array_diff( $existing_taxonomies_ids, $new_taxonomies[$taxname] );
			}
			else
			{
				$existing_taxonomies_names = array_map(
					function( $term )
					{
						return $term->name;
					},
					$existing_taxonomies[$taxname]
				);
				$existing_taxonomies[$taxname] = array_diff( $existing_taxonomies_names, $new_taxonomies[$taxname] );
			}
		}
		
		$post_data = array(
			'ID'			=> $post->ID,
			'tax_input'		=> $existing_taxonomies,
		);
		
		$this->filter_post_data( $post_data );
		$result = wp_update_post( $post_data, true );
		
		if( is_wp_error($result) )
		{
			$this->model->last_error = 'Unable to update post "'.$title.'". '.$result->get_error_message();
			return false;
		}
		
		return true;
	}


	/**
	 * Adds a custom field / metadata for the post.  If the meta field already exists, 
	 * then it will not be updated.
	 * @param  array  $item  The data for the batch item.
	 * @return  bool  True if the action was successful, otherwise false.
	 */
	private function add_meta_post( &$item )
	{
		extract($item);
		
		$post = $this->get_post_by_title( $title, $post_type );
		if( !$post )
		{
			$this->model->last_error = 'Post does not exist "'.$title.'". ';
			return false;
		}
		
		if( !add_post_meta($post->ID, $name, $value, true) )
		{
			$this->model->last_error = 'Unable to update meta data "'.$name.'" for post "'.$title.'". ';
			return false;
		}
		
		return true;
	}


	/**
	 * Updates an existing custom field / metadata for the post.  If the meta field does 
	 * not exist, then it will not be created.
	 * @param  array  $item  The data for the batch item.
	 * @return  bool  True if the action was successful, otherwise false.
	 */
	private function update_meta_post( &$item )
	{
		extract($item);
		
		$post = $this->get_post_by_title( $title, $post_type );
		if( !$post )
		{
			$this->model->last_error = 'Post does not exist "'.$title.'". ';
			return false;
		}
		
		if( !update_post_meta($post->ID, $name, $value, true) )
		{
			$this->model->last_error = 'Unable to update meta data "'.$name.'" for post "'.$title.'". ';
			return false;
		}
		
		return true;
	}
	
	
	/**
	 * Adds or updates an existing custom field / metadata for the post.  If the meta 
	 * field does not exist, then it will be created.  If the meta field exists, it will 
	 * be updated.
	 * @param  array  $item  The data for the batch item.
	 * @return  bool  True if the action was successful, otherwise false.
	 */
	private function replace_meta_post( &$item )
	{
		extract($item);
		
		$post = $this->get_post_by_title( $title, $post_type );
		if( !$post )
		{
			$this->model->last_error = 'Post does not exist "'.$title.'". ';
			return false;
		}
		
		if( !add_post_meta($post->ID, $name, $value, true) && !update_post_meta($post->ID, $name, $value, true) )
		{
			$this->model->last_error = 'Unable to add or update meta data "'.$name.'" for post "'.$title.'". ';
			return false;
		}
		
		return true;
	}
	
	
	/**
	 * Deletes a custom field / metadata for the post.
	 * @param  array  $item  The data for the batch item.
	 * @return  bool  True if the action was successful, otherwise false.
	 */
	private function delete_meta_post( &$item )
	{
		extract($item);
		
		$post = $this->get_post_by_title( $title, $post_type );
		if( !$post )
		{
			$this->model->last_error = 'Post does not exist "'.$title.'". ';
			return false;
		}
		
		if( !delete_post_meta($post->ID, $name) )
		{
			$this->model->last_error = 'Unable to delete meta data "'.$name.'" for post "'.$title.'". ';
			return false;
		}
		
		return true;
	}


	/**
	 * Creates a copy of the custom field / metadata with a new name.  If the field does 
	 * not exists, a copy will not be made.  If the new field already exists, it will be 
	 * overwritten.
	 * @param  array  $item  The data for the batch item.
	 * @return  bool  True if the action was successful, otherwise false.
	 */
	private function copy_meta_post( &$item )
	{
		extract($item);
		
		$post = $this->get_post_by_title( $title, $post_type );
		if( !$post )
		{
			$this->model->last_error = 'Post does not exist "'.$title.'". ';
			return false;
		}
		
		$value = get_post_meta( $post->ID, $name, false );
		if( $value === array() )
		{
			$this->model->last_error = 'Unable to get original meta data "'.$name.'" for post "'.$title.'". ';
			return false;
		}
		
		$value = $value[0];
		if( !add_post_meta($post->ID, $name, $value, true) && !update_post_meta($post->ID, $name, $value, true) )
		{
			$this->model->last_error = 'Unable to add or update meta data "'.$name.'" for post "'.$title.'". ';
			return false;
		}
		
		return true;
	}	
	
	
//========================================================================================
//======================================================================= TYPE: Page =====
	
	
	/**
	 * Verifies that all the required fields are present for "page" type.
	 * @param  array  $args  An array of user values.
	 * @return  bool  True if the args are valid, otherwise False.
	 */
	protected function check_page_args( &$args, $validate_for_action = false )
	{
		$required_keys = array();
		$required_values = array();
		$valid_keys = array();
		$valid_regex_keys = array();
		
		switch( $args['action'] )
		{
			case 'add':
				/*
				required fields:
					type, action, site, title
				all supported fields:
					type, action, site, title, content, date, author, slug, guid, parent, 
					status, order, password, meta-{name}
				*/
				$required_keys = array( 'site', 'title' );
				$required_values = array( 'site', 'title' );
				$valid_keys = array( 'excerpt', 'content', 'date', 'author', 'slug', 'guid', 'parent', 'status', 'order', 'password' );
				$valid_regex_keys = array( '(meta)\-([a-zA-Z0-9\-_]+)' );
				break;
				
			case 'update':
				/*
				required fields:
					type, action, site, title
				all supported fields:
					type, action, site, title, content, date, author, slug, parent, 
					status, order, password, meta-{name}
				*/
				$required_keys = array( 'site', 'title' );
				$required_values = array( 'site', 'title' );
				$valid_keys = array( 'excerpt', 'content', 'date', 'author', 'slug', 'parent', 'status', 'order', 'password' );
				$valid_regex_keys = array( '(meta)\-([a-zA-Z0-9\-_]+)' );
				break;
				
			case 'replace':
				/*
				required fields:
					type, action, site, title
				all supported fields:
					type, action, site, title, content, date, author, slug, guid, parent, 
					status, order, password, meta-{name}
				*/
				$required_keys = array( 'site', 'title' );
				$required_values = array( 'site', 'title' );
				$valid_keys = array( 'excerpt', 'content', 'date', 'author', 'slug', 'guid', 'parent', 'status', 'order', 'password' );
				$valid_regex_keys = array( '(meta)\-([a-zA-Z0-9\-_]+)' );
				break;
			
			case 'prepend':
				/*
				required fields:
					type, action, site, title
				all supported fields:
					type, action, site, title, content
				*/
				$required_keys = array( 'site', 'title' );
				$required_values = array( 'site', 'title' );
				$valid_keys = array( 'excerpt', 'content' );
				break;
				
			case 'append':
				/*
				required fields:
					type, action, site, title
				all supported fields:
					type, action, site, title, content
				*/
				$required_keys = array( 'site', 'title' );
				$required_values = array( 'site', 'title' );
				$valid_keys = array( 'excerpt', 'content' );
				break;
				
			case 'delete':
				/*
				required fields:
					type, action, site, title
				all supported fields:
					type, action, site, title
				*/
				$required_keys = array( 'site', 'title' );
				$required_values = array( 'site', 'title' );
				break;
				
			case 'rename':
				/*
				required fields:
					type, action, site, title, new-title
				all supported fields:
					type, action, site, title, new-title
				*/
				$required_keys = array( 'site', 'title', 'new-title' );
				$required_values = array( 'site', 'title', 'new-title' );
				break;
				
			case 'grep':
				/*
				required fields:
					type, action, site, subject, regex, replace-text
				all supported fields:
					type, action, site, title, subject, regex, replace-text
				*/
				$required_keys = array( 'site', 'subject', 'regex', 'replace-text' );
				$required_values = array( 'site', 'subject', 'regex' );
				$valid_keys = array( 'title' );
				break;
				
			case 'add-meta':
				/*
				required fields:
					type, action, site, title, name, value
				all supported fields:
					type, action, site, title, name, value
				*/
				$required_keys = array( 'site', 'title', 'name', 'value' );
				$required_values = array( 'site', 'title', 'name' );
				break;
			
			case 'update-meta':
				/*
				required fields:
					type, action, site, title, name, value
				all supported fields:
					type, action, site, title, name, value
				*/
				$required_keys = array( 'site', 'title', 'name', 'value' );
				$required_values = array( 'site', 'title', 'name' );
				break;
			
			case 'replace-meta':
				/*
				required fields:
					type, action, site, title, name, value
				all supported fields:
					type, action, site, title, name, value
				*/
				$required_keys = array( 'site', 'title', 'name', 'value' );
				$required_values = array( 'site', 'title', 'name' );
				break;
			
			case 'delete-meta':
				/*
				required fields:
					type, action, site, title, name
				all supported fields:
					type, action, site, title, name
				*/
				$required_keys = array( 'site', 'title', 'name' );
				$required_values = array( 'site', 'title', 'name' );
				break;

			case 'copy-meta':
				/*
				required fields:
					type, action, site, title, name, new-name
				all supported fields:
					type, action, site, title, name, new-name
				*/
				$required_keys = array( 'site', 'title', 'name', 'new-name' );
				$required_values = array( 'site', 'title', 'name', 'new-name' );
				break;
			
			default:
				$this->model->last_error = 'Invalid action for type: "page" => "'.$args['action'].'".';
				return false;
				break;
		}
		
		if( !is_network_admin() )
		{
			$a = array( 'site' );
			$required_keys = array_diff( $required_keys, $a );
			$required_values = array_diff( $required_values, $a );
			$valid_keys = array_diff( $valid_keys, $a );
		}
		
		if( $validate_for_action )
		{
			return $this->validate_args_for_action( 
				$args, 
				$required_keys, 
				$required_values, 
				$valid_keys, 
				$valid_regex_keys );
		}
		
		return $this->validate_args_for_db( 
			$args, 
			$required_keys, 
			$required_values, 
			$valid_keys, 
			$valid_regex_keys );
	}
	
	
	/**
	 * Adds a new page if an existing page with same title does not exist.
	 * @param  array  $item  The data for the batch item.
	 * @return  bool  True if the action was successful, otherwise false.
	 */
	private function add_page( &$item )
	{
		extract($item);

		$post = $this->get_post_by_title( $title, 'page' );
		if( $post )
		{
			$this->model->last_error = 'Page already exists "'.$title.'". ';
			return false;
		}
				
		$post_data = array(
			'post_content'	=> $this->get_string_value( $content, true ),
			'post_name'		=> $this->get_string_value( $slug, true ),
			'post_title'	=> $title,
			'post_status'	=> $this->get_post_status( $status ),
			'post_type'		=> 'page',
			'menu_order'	=> $this->get_int_value( $order, true ),
			'parent'		=> $this->get_post_by_title( $parent, 'page' ),
			'post_author'	=> $this->get_author_id( $author ),
			'post_password'	=> $this->get_string_value( $password, true ),
			'guid'			=> $this->get_string_value( $guid, true ),
			'post_excerpt'	=> $this->get_string_value( $excerpt, true ),
			'post_date'		=> $this->parse_date( $date, true ),
		);
		
		$this->filter_post_data( $post_data );
		$result = wp_insert_post( $post_data, true );
		
		if( is_wp_error($result) )
		{
			$this->model->last_error = 'Unable to insert post "'.$title.'". '.$result->get_error_message();
			return false;
		}
		
		$post_id = $result;
		foreach( $meta as $key => $value )
		{
			update_post_meta( $post_id, $key, $value );
		}
		
		return true;
	}
	
	
	/**
	 * Updates an existing page.
	 * @param  array  $item  The data for the batch item.
	 * @return  bool  True if the action was successful, otherwise false.
	 */
	private function update_page( &$item )
	{
		extract($item);
		
		$post = $this->get_post_by_title( $title, 'page' );
		if( !$post )
		{
			$this->model->last_error = 'Page does not exist "'.$title.'". ';
			return false;
		}
		
		$post_data = array(
			'ID'			=> $post->ID,
			'post_content'	=> $this->get_string_value( $content, true ),
			'post_name'		=> $this->get_string_value( $slug, true ),
			'post_status'	=> $this->get_post_status( $status ),
			'post_type'		=> 'page',
			'menu_order'	=> $this->get_int_value( $order ),
			'parent'		=> $this->get_post_by_title( $parent, 'page' ),
			'post_author'	=> $this->get_author_id( $author ),
			'post_password'	=> $this->get_string_value( $password, true ),
			'post_excerpt'	=> $this->get_string_value( $excerpt, true ),
			'post_date'		=> $this->parse_date( $date, true ),
		);
		
		$this->filter_post_data( $post_data );
		$result = wp_update_post( $post_data, true );
		
		if( is_wp_error($result) )
		{
			$this->model->last_error = 'Unable to update post "'.$title.'". '.$result->get_error_message();
			return false;
		}
		
		$post_id = $result;
		foreach( $meta as $key => $value )
		{
			update_post_meta( $post_id, $key, $value );
		}
		
		return true;
	}
	
	
	/**
	 * Adds a new page if one does not exist, otherwise the page is updated.
	 * @param  array  $item  The data for the batch item.
	 * @return  bool  True if the action was successful, otherwise false.
	 */
	private function replace_page( &$item )
	{
		extract($item);
		
		$post = $this->get_post_by_title( $title, 'page' );
		
		if( !$post ) return $this->add_page( $item );
		
		return $this->update_page( $item );
	}
	
	
	/**
	 * Prepend content to the start of the page’s excerpt or content.
	 * @param  array  $item  The data for the batch item.
	 * @return  bool  True if the action was successful, otherwise false.
	 */
	private function prepend_page( &$item )
	{
		extract($item);
		
		$post = $this->get_post_by_title( $title, 'page' );
		if( !$post )
		{
			$this->model->last_error = 'Page does not exist "'.$title.'". ';
			return false;
		}
		
		$post_data = array(
			'ID'			=> $post->ID,
			'post_excerpt'	=> $excerpt.$post->post_excerpt,
			'post_content'	=> $this->prepend_to_content( $post->post_content, $content ),
		);
		
		$this->filter_post_data( $post_data );
		$result = wp_update_post( $post_data, true );
		
		if( is_wp_error($result) )
		{
			$this->model->last_error = 'Unable to update post "'.$title.'". '.$result->get_error_message();
			return false;
		}
		
		return true;
	}
	
	
	/**
	 * Append content to the start of the page’s excerpt or content.
	 * @param  array  $item  The data for the batch item.
	 * @return  bool  True if the action was successful, otherwise false.
	 */
	private function append_page( &$item )
	{
		extract($item);
		
		$post = $this->get_post_by_title( $title, 'page' );
		if( !$post )
		{
			$this->model->last_error = 'Page does not exist "'.$title.'". ';
			return false;
		}
		
		$post_data = array(
			'ID'			=> $post->ID,
			'post_excerpt'	=> $post->post_excerpt.$excerpt,
			'post_content'	=> $this->append_to_content( $post->post_content, $content ),
		);
		
		$this->filter_post_data( $post_data );
		$result = wp_update_post( $post_data, true );
		
		if( is_wp_error($result) )
		{
			$this->model->last_error = 'Unable to update post "'.$title.'". '.$result->get_error_message();
			return false;
		}
		
		return true;
	}
	
	
	/**
	 * Delete an existing page.
	 * @param  array  $item  The data for the batch item.
	 * @return  bool  True if the action was successful, otherwise false.
	 */
	private function delete_page( &$item )
	{
		extract($item);
		
		$post = $this->get_post_by_title( $title, 'page' );
		if( !$post )
		{
			$this->model->last_error = 'Page does not exist "'.$title.'". ';
			return false;
		}
		
		$result = wp_delete_post( $post->ID, true );
		
		if( $result === false )
		{
			$this->model->last_error = 'Unable to delete post "'.$title.'". ';
			return false;
		}
		
		return true;
	}
	
	
	/**
	 * Rename an existing page to new title.
	 * @param  array  $item  The data for the batch item.
	 * @return  bool  True if the action was successful, otherwise false.
	 */
	private function rename_page( &$item )
	{
		extract($item);
		
		$post = $this->get_post_by_title( $title, 'page' );
		if( !$post )
		{
			$this->model->last_error = 'Page does not exist "'.$title.'". ';
			return false;
		}
		
		$post_data = array(
			'ID'			=> $post->ID,
			'post_title'	=> $new_title,
		);
		
		$this->filter_post_data( $post_data );
		$result = wp_update_post( $post_data, true );
		
		if( is_wp_error($result) )
		{
			$this->model->last_error = 'Unable to update post "'.$title.'". '.$result->get_error_message();
			return false;
		}
		
		return true;
	}
	
	
	/**
	 * Search a page or all pages on a site for matches that will be replaced with
	 * replacement text.
	 * @param  array  $item  The data for the batch item.
	 * @return  bool  True if the action was successful, otherwise false.
	 */
	private function grep_page( &$item )
	{
		extract($item);
		
		$posts = array();
		
		if( !empty($title) )
		{
			$post = $this->get_post_by_title( $title, 'page' );
			if( !$post )
			{
				$this->model->last_error = 'Page does not exist "'.$title.'". ';
				return false;
			}
			
			$posts[] = $post;
		}
		else
		{
			$posts = get_posts(
				array(
					'posts_per_page' => -1,
					'post_type' => 'page',
					'post_status' => 'any',
				)
			);
		}
		
		$errors = array();
		foreach( $posts as $post )
		{
			$post_data = array( 'ID' => $post->ID );
			
			switch( $subject )
			{
				case 'content':
					$content = preg_replace( "/{$regex}/", $replace_text, $post->post_content, -1, $count );
					if( $count === 0 ) continue;

					$post_data['post_content'] = $content;
					break;
				
				default:
					continue; break;
			}
			
			$this->filter_post_data( $post_data );
			$result = wp_update_post( $post_data, true );

			if( is_wp_error($result) )
			{
				$errors[] = 'Unable to update post "'.$title.'". '.$result->get_error_message();
				continue;
			}
		}
		
		if( !empty($errors) )
		{
			$this->model->last_error = $this->format_errors_into_string( $errors );
			return false;
		}
		
		return true;
	}
	
	
	/**
	 * Add meta data to existing page.
	 * @param  array  $item  The data for the batch item.
	 * @return  bool  True if the action was successful, otherwise false.
	 */
	private function add_meta_page( &$item )
	{
		extract($item);
		
		$post = $this->get_post_by_title( $title, 'page' );
		if( !$post )
		{
			$this->model->last_error = 'Page does not exist "'.$title.'". ';
			return false;
		}
		
		if( !add_post_meta($post->ID, $name, $value, true) )
		{
			$this->model->last_error = 'Unable to add meta data "'.$name.'" for post "'.$title.'". ';
			return false;
		}
		
		return true;
	}


	/**
	 * Update meta data for an existing page.
	 * @param  array  $item  The data for the batch item.
	 * @return  bool  True if the action was successful, otherwise false.
	 */
	private function update_meta_page( &$item )
	{
		extract($item);
		
		$post = $this->get_post_by_title( $title, 'page' );
		if( !$post )
		{
			$this->model->last_error = 'Page does not exist "'.$title.'". ';
			return false;
		}
		
		if( !update_post_meta($post->ID, $name, $value, true) )
		{
			$this->model->last_error = 'Unable to update meta data "'.$name.'" for post "'.$title.'". ';
			return false;
		}
		
		return true;
	}


	/**
	 * Adds or updates an existing custom field / metadata for the page.  If the meta 
	 * field does not exist, then it will be created.  If the meta field exists, it will 
	 * be updated.
	 * @param  array  $item  The data for the batch item.
	 * @return  bool  True if the action was successful, otherwise false.
	 */
	private function replace_meta_page( &$item )
	{
		extract($item);
		
		$post = $this->get_post_by_title( $title, 'page' );
		if( !$post )
		{
			$this->model->last_error = 'Page does not exist "'.$title.'". ';
			return false;
		}
		
		if( !add_post_meta($post->ID, $name, $value, true) && !update_post_meta($post->ID, $name, $value, true) )
		{
			$this->model->last_error = 'Unable to add or update meta data "'.$name.'" for post "'.$title.'". ';
			return false;
		}
		
		return true;
	}
	
	
	/**
	 * Delete meta data for an existing page.
	 * @param  array  $item  The data for the batch item.
	 * @return  bool  True if the action was successful, otherwise false.
	 */
	private function delete_meta_page( &$item )
	{
		extract($item);
		
		$post = $this->get_post_by_title( $title, 'page' );
		if( !$post )
		{
			$this->model->last_error = 'Page does not exist "'.$title.'". ';
			return false;
		}
		
		if( !delete_post_meta($post->ID, $name) )
		{
			$this->model->last_error = 'Unable to delete meta data "'.$name.'" for post "'.$title.'". ';
			return false;
		}
		
		return true;
	}
	
	
	/**
	 * Copy meta data to a new name for an existing page.
	 * @param  array  $item  The data for the batch item.
	 * @return  bool  True if the action was successful, otherwise false.
	 */
	private function copy_meta_page( &$item )
	{
		extract($item);
		
		$post = $this->get_post_by_title( $title, 'page' );
		if( !$post )
		{
			$this->model->last_error = 'Page does not exist "'.$title.'". ';
			return false;
		}
		
		$value = get_post_meta( $post->ID, $name, false );
		if( $value === array() )
		{
			$this->model->last_error = 'Unable to get original meta data "'.$name.'" for post "'.$title.'". ';
			return false;
		}
		
		$value = $value[0];
		if( !add_post_meta($post->ID, $name, $value, true) && !update_post_meta($post->ID, $name, $value, true) )
		{
			$this->model->last_error = 'Unable to add or update meta data "'.$name.'" for post "'.$title.'". ';
			return false;
		}
		
		return true;
	}


//========================================================================================
//======================================================================= TYPE: Link =====
	
	
	/**
	 * Verifies that all the required fields are present for "link" type.
	 * @param  array  $args  An array of values.
	 * @return  bool  True if the args are valid, otherwise False.
	 */
	protected function check_link_args( &$args, $validate_for_action = false )
	{
		$required_keys = array();
		$required_values = array();
		$valid_keys = array();
		$valid_regex_keys = array();
		
		switch( $args['action'] )
		{
			case 'add':
				/*
				required fields:
					type, action, site, name, url
				all supported fields:
					type, action, site, name, url, description, target, categories
				*/
				$required_keys = array( 'site', 'name', 'url' );
				$required_values = array( 'site', 'name', 'url' );
				$valid_keys = array( 'description', 'target', 'categories' );
				break;
			
			case 'update':
				/*
				required fields:
					type, action, site, name, url
				all supported fields:
					type, action, site, name, url, description, target, categories
				*/
				$required_keys = array( 'site', 'name', 'url' );
				$required_values = array( 'site', 'name', 'url' );
				$valid_keys = array( 'description', 'target', 'categories' );
				break;
			
			case 'replace':
				/*
				required fields:
					type, action, site, name, url
				all supported fields:
					type, action, site, name, url, description, target, categories
				*/
				$required_keys = array( 'site', 'name', 'url' );
				$required_values = array( 'site', 'name', 'url' );
				$valid_keys = array( 'description', 'target', 'categories' );
				break;
			
			case 'delete':
				/*
				required fields:
					type, action, site, name
				all supported fields:
					type, action, site, name
				*/
				$required_keys = array( 'site', 'name' );
				$required_values = array( 'site', 'name' );
				break;
			
			case 'rename':
				/*
				required fields:
					type, action, site, name, new-name
				all supported fields:
					type, action, site, name, new-name
				*/
				$required_keys = array( 'site', 'name', 'new-name' );
				$required_values = array( 'site', 'name', 'new-name' );
				break;
				
			case 'grep':
				/*
				required fields:
					type, action, site, subject, regex, replace-text
				all supported fields:
					type, action, site, name, subject, regex, replace-text
				*/
				$required_keys = array( 'site', 'subject', 'regex', 'replace-text' );
				$required_values = array( 'site', 'subject', 'regex' );
				$valid_keys = array( 'name' );
				break;
				
			default:
				$this->model->last_error = 'Invalid action for type: "link" => "'.$args['action'].'".';
				return false;
				break;
		}
		
		if( !is_network_admin() )
		{
			$a = array( 'site' );
			$required_keys = array_diff( $required_keys, $a );
			$required_values = array_diff( $required_values, $a );
			$valid_keys = array_diff( $valid_keys, $a );
		}

		if( $validate_for_action )
		{
			return $this->validate_args_for_action( 
				$args, 
				$required_keys, 
				$required_values, 
				$valid_keys, 
				$valid_regex_keys );
		}
		
		return $this->validate_args_for_db( 
			$args, 
			$required_keys, 
			$required_values, 
			$valid_keys, 
			$valid_regex_keys );
	}
	
	
	/**
	 * Adds a link if another link of the same name does not exist.
	 * @param  array  $item  The data for the batch item.
	 * @return  bool  True if the action was successful, otherwise false.
	 */
	private function add_link( &$item )
	{
		extract($item);
		
		$link = $this->get_link_by_name( $name );
		if( $link )
		{
			$this->model->last_error = 'Link already exist "'.$name.'". ';
			return false;
		}
		
		$link_data = array(
			'link_url'			=> $this->get_string_value( $url, true ),
			'link_name'			=> $name,
			'link_target'		=> $this->get_string_value( $target, true ),
			'link_description'	=> $this->get_string_value( $description, true ),
			'link_category'		=> $this->get_link_category( $categories, true ),
		);
		
		$result = wp_insert_link( $link_data, true );
		
		if( is_wp_error($result) )
		{
			$this->model->last_error = 'Unable to insert link "'.$name.'". '.$result->get_error_message();
			return false;
		}
		
		return true;
	}
	
	
	/**
	 * Updates an existing link that matches the link name.
	 * @param  array  $item  The data for the batch item.
	 * @return  bool  True if the action was successful, otherwise false.
	 */
	private function update_link( &$item )
	{
		extract($item);
		
		$link = $this->get_link_by_name( $name );
		if( !$link )
		{
			$this->model->last_error = 'Link does not exist "'.$name.'". ';
			return false;
		}
		
		$link_data = array(
			'link_id'			=> $link->link_id,
			'link_url'			=> $this->get_string_value( $url, true ),
			'link_name'			=> $name,
			'link_target'		=> $this->get_string_value( $target, true ),
			'link_description'	=> $this->get_string_value( $description, true ),
			'link_category'		=> $this->get_link_category( $categories, true ),
		);
		
		$result = wp_insert_link( $link_data, true );
		
		if( is_wp_error($result) )
		{
			$this->model->last_error = 'Unable to insert link "'.$name.'". '.$result->get_error_message();
			return false;
		}
		
		return true;
	}
	
	
	/**
	 * Adds a new link if it doesn't exist or updates an existing link of the same name.
	 * @param  array  $item  The data for the batch item.
	 * @return  bool  True if the action was successful, otherwise false.
	 */
	private function replace_link( &$item )
	{
		extract($item);
		
		$link = $this->get_link_by_name( $name );
		
		if( !$link ) return $this->add_link( $item );
		
		return $this->update_link( $item );
	}
	
	
	/**
	 * Delete a link that matches the link name.
	 * @param  array  $item  The data for the batch item.
	 * @return  bool  True if the action was successful, otherwise false.
	 */
	private function delete_link( &$item )
	{
		extract($item);
		
		$link = $this->get_link_by_name( $name );
		if( !$link )
		{
			$this->model->last_error = 'Link does not exist "'.$name.'". ';
			return false;
		}
		
		if( !wp_delete_link($link->link_id) )
		{
			$this->model->last_error = 'Unable to delete link "'.$name.'". ';
			return false;
		}
		return true;
	}
	
	
	/**
	 * Renames a link's name to a new name.
	 * @param  array  $item  The data for the batch item.
	 * @return  bool  True if the action was successful, otherwise false.
	 */
	private function rename_link( &$item )
	{
		extract($item);
		
		$link = $this->get_link_by_name( $name );
		if( !$link )
		{
			$this->model->last_error = 'Unable to find orginal link "'.$name.'". ';
			return false;
		}
		
		$link_data = array(
			'link_id'			=> $link->link_id,
			'link_name'			=> $new_name,
		);
		
		$result = wp_insert_link( $link_data, true );
		
		if( is_wp_error($result) )
		{
			$this->model->last_error = 'Unable to insert link "'.$name.'". '.$result->get_error_message();
			return false;
		}
		
		return true;
	}
	
	
	/**
	 * Searches a link or all links for a match, then replaces that match with replacment text.
	 * @param  array  $item  The data for the batch item.
	 * @return  bool  True if the action was successful, otherwise false.
	 */
	private function grep_link( &$item )
	{
		extract($item);
		
		$links = array();
		
		if( !empty($name) )
		{
			$link = $this->get_link_by_name( $name );
			if( !$link )
			{
				$this->model->last_error = 'Link does not exist "'.$name.'". ';
				return false;
			}
			
			$links[] = $link;
		}
		else
		{
			global $wpdb;
			$links = $wpdb->get_results( "SELECT * FROM $wpdb->links" );
			if( !$links ) return true;
		}
		
		$errors = array();
		foreach( $links as $link )
		{
			$link_data = array( 'link_id' => $link->link_id );
			
			switch( $subject )
			{
				case 'url':
					$url = preg_replace( "/{$regex}/", $replace_text, $link->link_url, -1, $count );
					if( $count === 0 ) continue;

					$link_data['link_url'] = $url;
					break;
					
				case 'description':
					$description = preg_replace( "/{$regex}/", $replace_text, $link->link_description, -1, $count );
					if( $count === 0 ) continue;

					$post_data['link_description'] = $description;
					break;
				
				default:
					continue; break;
			}
			
			$result = wp_insert_link( $link_data, true );
		
			if( is_wp_error($result) )
			{
				$errors[] = 'Unable to insert link "'.$name.'". '.$result->get_error_message();
				continue;
			}
		}
		
		if( !empty($errors) )
		{
			$this->model->last_error = $this->format_errors_into_string( $errors );
			return false;
		}
				
		return true;
	}
	
	
//========================================================================================
//=================================================================== TYPE: Taxonomy =====
	
	
	/**
	 * Verifies that all the required fields are present for "taxonomy" type.
	 * @param  array  $args  An array of values.
	 * @return  bool  True if the args are valid, otherwise False.
	 */
	protected function check_taxonomy_args( &$args, $validate_for_action = false )
	{
		$required_keys = array();
		$required_values = array();
		$valid_keys = array();
		$valid_regex_keys = array();
		
		switch( $args['action'] )
		{
			case 'add':
				/*
				required fields:
					type, action, site, name, terms
				all supported fields:
					type, action, site, name, terms
				*/
				$required_keys = array( 'site', 'name', 'terms' );
				$required_values = array( 'site', 'name', 'terms' );
				break;
			
			case 'delete':
				/*
				required fields:
					type, action, site, name, terms
				all supported fields:
					type, action, site, name, terms
				*/
				$required_keys = array( 'site', 'name', 'terms' );
				$required_values = array( 'site', 'name', 'terms' );
				break;
			
			case 'rename':
				/*
				required fields:
					type, action, site, name, terms, new-terms
				all supported fields:
					type, action, site, name, terms, new-terms
				*/
				$required_keys = array( 'site', 'name', 'terms', 'new-terms' );
				$required_values = array( 'site', 'name', 'terms', 'new-terms' );
				break;
				
			default:
				$this->model->last_error = 'Invalid action for type: "taxonomy" => "'.$args['action'].'".';
				return false;
				break;
		}
		
		if( !is_network_admin() )
		{
			$a = array( 'site' );
			$required_keys = array_diff( $required_keys, $a );
			$required_values = array_diff( $required_values, $a );
			$valid_keys = array_diff( $valid_keys, $a );
		}

		if( $validate_for_action )
		{
			return $this->validate_args_for_action( 
				$args, 
				$required_keys, 
				$required_values, 
				$valid_keys, 
				$valid_regex_keys );
		}
		
		return $this->validate_args_for_db( 
			$args, 
			$required_keys, 
			$required_values, 
			$valid_keys, 
			$valid_regex_keys );
	}
	
	
	/**
	 * Add a new taxonomy terms to a site.
	 * @param  array  $item  The data for the batch item.
	 * @return  bool  True if the action was successful, otherwise false.
	 */
	private function add_taxonomy( &$item )
	{
		extract($item);
		
		if( !taxonomy_exists($name) )
		{
			$this->model->last_error = 'The taxonomy "'.$name.'" does not exist. ';
			return false;
		}
		
		$taxname = $name;
		$term_list = str_getcsv( $terms, ',', '"', "\\" );
		
		$errors = array();		
		if( !is_taxonomy_hierarchical($taxname) )
		{
			foreach( $term_list as $term )
			{
				if( term_exists($term, $taxname) ) continue;

				$result = wp_insert_term( $term, $taxname );
				if( is_wp_error($result) )
				{
					$errors[] = 'Unable to insert "'.$taxname.'" term "'.$term.'". '.$result->get_error_message();
					continue;
				}
			}
		}
		else
		{
			foreach( $term_list as $term )
			{
				$heirarchy = array_map( 'trim', explode('>', $term) );
				
				$parent = null;
				for( $i = 0; $i < count($heirarchy); $i++ )
				{
					if( term_exists($heirarchy[$i], $taxname, $parent) ) continue;

					$args = array();
					if( $parent ) $args['parent'] = $parent;
			
					$result = wp_insert_term( $heirarchy[$i], $taxname, $args );
					if( is_wp_error($result) )
					{
						$errors[] = 'Unable to insert "'.$taxname.'" hierarchy terms "'.implode(' > ', $hierarchy).'". '.$result->get_error_message();
						break;
					}
					
					$parent = $result['term_id'];
				}
			}
		}
		
		if( !empty($errors) )
		{
			$this->model->last_error = $this->format_errors_into_string( $errors );
			return false;
		}
		
		return true;
	}
	
	
	/**
	 * Delete taxonomy terms from a site.
	 * @param  array  $item  The data for the batch item.
	 * @return  bool  True if the action was successful, otherwise false.
	 */
	private function delete_taxonomy( &$item )
	{
		extract($item);
		
		if( !taxonomy_exists($name) )
		{
			$this->model->last_error = 'The taxonomy "'.$name.'" does not exist. ';
			return false;
		}
		
		$taxname = $name;
		$term_list = str_getcsv( $terms, ',', '"', "\\" );
		
		$errors = array();
		foreach( $term_list as $term )
		{
			if( $term_object = term_exists($term, $taxname) )
			{
				$result = wp_delete_term( $term_object['term_id'], $taxname );
				if( is_wp_error($result) )
				{
					$errors[] = 'Unable to delete "'.$taxname.'" term "'.$term.'". '.$result->get_error_message();
					continue;
				}
			}
		}
		
		if( !empty($errors) )
		{
			$this->model->last_error = $this->format_errors_into_string( $errors );
			return false;
		}
		
		return true;
	}
	
	
	/**
	 * Rename taxonomy terms for a site.
	 * @param  array  $item  The data for the batch item.
	 * @return  bool  True if the action was successful, otherwise false.
	 */
	private function rename_taxonomy( &$item )
	{
		extract($item);
		
		if( !taxonomy_exists($name) )
		{
			$this->model->last_error = 'The taxonomy "'.$name.'" does not exist. ';
			return false;
		}
		
		$taxname = $name;
		$term_list = str_getcsv( $terms, ',', '"', "\\" );
		$new_terms_list = str_getcsv( $new_terms, ',', '"', "\\" );
		
		$errors = array();
		foreach( $term_list as $i => $term )
		{
			if( !isset($new_terms_list[$i]) ) continue;
			$new_term = $new_terms_list[$i];
			
			if( $term_object = term_exists( $term, $name ) )
			{
				$tax_data = array(
					'name' => $new_term,
					'slug' => sanitize_title( $new_term ),
				);
				
				$result = wp_update_term( $term_object['term_id'], $name, $tax_data );
				if( is_wp_error($result) )
				{
					$errors[] = 'Unable to update "'.$taxname.'" term "'.$term.'". '.$result->get_error_message();
					continue;
				}
			}
		}
		
		if( !empty($errors) )
		{
			$this->model->last_error = $this->format_errors_into_string( $errors );
			return false;
		}
		
		return true;
	}	
	
	
//========================================================================================
//======================================================================= TYPE: Site =====
	
	
	/**
	 * Verifies that all the required fields are present for "site" type.
	 * @param  array  $args  An array of values.
	 * @return  bool  True if the args are valid, otherwise False.
	 */
	protected function check_site_args( &$args, $validate_for_action = false )
	{
		$required_keys = array();
		$required_values = array();
		$valid_keys = array();
		$valid_regex_keys = array();
		
		switch( $args['action'] )
		{
			case 'add':
				/*
				required fields:
					type, action, site, title, user
				all supported fields:
					type, action, site, title, description, domain, user, password, email, option-{name}
				*/
				$required_keys = array( 'site', 'title', 'user' );
				$required_values = array( 'site', 'title', 'user' );
				$valid_keys = array( 'description', 'domain', 'password', 'email' );
				$valid_regex_keys = array( '(option)\-([a-zA-Z0-9\-_]+)' );
				break;
			
			case 'update':
				/*
				required fields:
					type, action, site, title, user
				all supported fields:
					type, action, site, title, description, user, password, email, option-{name}
				*/
				$required_keys = array( 'site' );
				$required_values = array( 'site' );
				$valid_keys = array( 'title', 'description', 'user', 'password', 'email' );
				$valid_regex_keys = array( '(option)\-([a-zA-Z0-9\-_]+)' );
				break;
				
			case 'delete':
				/*
				required fields:
					type, action, site
				all supported fields:
					type, action, site
				*/
				$required_keys = array( 'site' );
				$required_values = array( 'site' );
				break;
				
			case 'archive':
				/*
				required fields:
					type, action, site
				all supported fields:
					type, action, site
				*/
				$required_keys = array( 'site' );
				$required_values = array( 'site' );
				break;
				
			case 'grep':
				/*
				required fields:
					type, action, site, subject, regex, replace-text
				all supported fields:
					type, action, site, subject, regex, replace-text
				*/
				$required_keys = array( 'site', 'subject', 'regex', 'replace-text' );
				$required_values = array( 'site', 'subject', 'regex' );
				break;
				
			default:
				$this->model->last_error = 'Invalid action for type: "site" => "'.$args['action'].'".';
				return false;
				break;
		}
		
		if( $validate_for_action )
		{
			return $this->validate_args_for_action( 
				$args, 
				$required_keys, 
				$required_values, 
				$valid_keys, 
				$valid_regex_keys );
		}
		
		return $this->validate_args_for_db( 
			$args, 
			$required_keys, 
			$required_values, 
			$valid_keys, 
			$valid_regex_keys );
	}
	
	
	/**
	 * Add a new site, if it does not already exist.
	 * @param  array  $item  The data for the batch item.
	 * @return  bool  True if the action was successful, otherwise false.
	 */
	private function add_site( &$item )
	{
		extract($item);
		
		$admin_id = $this->get_author_id( $user, true, $password, $email );
		if( !$admin_id )
		{
			$this->model->last_error = 'Unable to find or create admin user account.';
			return false;
		}
		
		$blog_id = get_id_from_blogname( $site );
		if( $blog_id )
		{
			$this->model->last_error = 'The site already exists: "'.$site.'".';
			return false;
		}
		
		$meta_data = array(
			'blogdescription'	=> $this->get_string_value( $description ),
		);
		foreach( $option as $key => $value )
		{
			$meta_data[$key] = $value;
		}
		
		$path = $site;
		$this->model->get_site_url( $domain, $path );

		// Create the blog.
		$blog_id = wpmu_create_blog( $domain, '/'.$path, $title, $admin_id, $meta_data );
		
		if( is_wp_error($blog_id))
		{
			$this->model->last_error = $blog_id->get_error_message();
			return false;
		}
		
		return true;
	}
	
	
	/**
	 * Update an existing site.
	 * @param  array  $item  The data for the batch item.
	 * @return  bool  True if the action was successful, otherwise false.
	 */
	private function update_site( &$item )
	{
		extract($item);
		
		$admin_id = $this->get_author_id( $user, true, $password, $email );
		if( !$admin_id )
		{
			$this->model->last_error = 'Unable to find or create admin user account.';
			return false;
		}

		$blog_id = get_id_from_blogname( $site );
		if( !$blog_id )
		{
			$this->model->last_error = 'Unable to find blog: "'.$site.'".';
			return false;
		}
		
		if( $title ) update_option( 'blogname', $title );
		if( $description ) update_option( 'blogdescription', $description );

		foreach( $option as $key => $value )
		{
			update_option( $key, $value );
		}
		
		// Verify the user is administrator of the blog and update blog options.
		$user = get_user_by( 'id', $admin_id );
		
		if( $user )
		{
			add_user_to_blog( $blog_id, $admin_id, 'administrator' );
			update_option( 'admin_email', $user->user_email );
		}
		
		return true;
	}
	
	
	/**
	 * Mark an existing site as deleted.
	 * @param  array  $item  The data for the batch item.
	 * @return  bool  True if the action was successful, otherwise false.
	 */
	private function delete_site( &$item )
	{	
		extract($item);
		
		$blog_id = get_id_from_blogname( $site );
		if( !$blog_id )
		{
			$this->model->last_error = 'Unable to find blog: "'.$site.'".';
			return false;
		}
		
		global $wpdb;
		$result = $wpdb->update(
			$wpdb->blogs,
			array( 
				'deleted' => 1,
			),
			array( 'blog_id' => intval($blog_id) ),
			array( '%d' ),
			array( '%d' )
		);
		
		if( !$result )
		{
			$this->model->last_error = 'Unable to update site "'.$site.'". '.$wpdb->last_error;
			return false;
		}
		
		return true;
	}
	
	
	/**
	 * Mark an existing site as archived.
	 * @param  array  $item  The data for the batch item.
	 * @return  bool  True if the action was successful, otherwise false.
	 */
	private function archive_site( &$item )
	{
		extract($item);
		
		$blog_id = get_id_from_blogname( $site );
		if( !$blog_id )
		{
			$this->model->last_error = 'Unable to find blog: "'.$site.'".';
			return false;
		}
		
		global $wpdb;
		$result = $wpdb->update(
			$wpdb->blogs,
			array( 
				'archived' => 1,
			),
			array( 'blog_id' => intval($blog_id) ),
			array( '%d' ),
			array( '%d' )
		);
		
		if( !$result )
		{
			$this->model->last_error = 'Unable to update site "'.$site.'". '.$wpdb->last_error;
			return false;
		}
		
		return true;
	}
	
	
	/**
	 * Search a site's option for match then replace match with replacement text.
	 * @param  array  $item  The data for the batch item.
	 * @return  bool  True if the action was successful, otherwise false.
	 */
	private function grep_site( &$item )
	{
		extract($item);
		
		switch( $subject )
		{
			case 'title':
				$title = preg_replace( "/{$regex}/", $replace_text, get_option('blogname'), -1, $count );
				if( $count === 0 ) continue;

				update_option( 'blogname', $title );
				break;
				
			case 'description':
				$description = preg_replace( "/{$regex}/", $replace_text, get_option('blogdescription'), -1, $count );
				if( $count === 0 ) continue;

				update_option( 'blogdescription', $description );
				break;
			
			default:
				break;
		}
		
		return true;		
	}
	
	
//========================================================================================
//======================================================================= TYPE: User =====
	
	
	/**
	 * Verifies that all the required fields are present for "user" type.
	 * @param  array  $args  An array of values.
	 * @return  bool  True if the args are valid, otherwise False.
	 */
	protected function check_user_args( &$args, $validate_for_action = false )
	{
		$required_keys = array();
		$required_values = array();
		$valid_keys = array();
		$valid_regex_keys = array();
		
		switch( $args['action'] )
		{
			case 'add':
				/*
				required fields:
					type, action, user
				all supported fields:
					type, action, site, user, password, email, role, meta-{name}
				*/
				$required_keys = array( 'user', 'password', 'email' );
				$required_values = array( 'user', 'email' );
				$valid_keys = array( 'site', 'role' );
				$valid_regex_keys = array( '(meta)\-([a-zA-Z0-9\-_]+)' );
				break;
			
			case 'update':
				/*
				required fields:
					type, action, user
				all supported fields:
					type, action, site, user, password, email, role, meta-{name}
				*/
				$required_keys = array( 'user' );
				$required_values = array( 'user' );
				$valid_keys = array( 'site', 'password', 'email', 'role' );
				$valid_regex_keys = array( '(meta)\-([a-zA-Z0-9\-_]+)' );
				break;
				
			case 'replace':
				/*
				required fields:
					type, action, user
				all supported fields:
					type, action, site, user, password, email, role, meta-{name}
				*/
				$required_keys = array( 'user' );
				$required_values = array( 'user' );
				$valid_keys = array( 'site', 'role', 'password', 'email' );
				$valid_regex_keys = array( '(meta)\-([a-zA-Z0-9\-_]+)' );
				break;
			
			case 'delete':
				/*
				required fields:
					type, action, site, user
				all supported fields:
					type, action, site, user
				*/
				$required_keys = array( 'user' );
				$required_values = array( 'user' );
				$valid_keys = array( 'site' );
				break;

			case 'add-meta':
				/*
				required fields:
					type, action, user, name, value
				all supported fields:
					type, action, user, name, value
				*/
				$required_keys = array( 'user', 'name', 'value' );
				$required_values = array( 'user', 'name' );
				break;
			
			case 'update-meta':
				/*
				required fields:
					type, action, user, name, value
				all supported fields:
					type, action, user, name, value
				*/
				$required_keys = array( 'user', 'name', 'value' );
				$required_values = array( 'user', 'name' );
				break;
			
			case 'replace-meta':
				/*
				required fields:
					type, action, user, name, value
				all supported fields:
					type, action, user, name, value
				*/
				$required_keys = array( 'user', 'name', 'value' );
				$required_values = array( 'user', 'name' );
				break;
			
			case 'delete-meta':
				/*
				required fields:
					type, action, user, name
				all supported fields:
					type, action, user, name
				*/
				$required_keys = array( 'user', 'name' );
				$required_values = array( 'user', 'name' );
				break;
							
			case 'copy-meta':
				/*
				required fields:
					type, action, user, name, new-name
				all supported fields:
					type, action, user, name, new-name
				*/
				$required_keys = array( 'user', 'name', 'new-name' );
				$required_values = array( 'user', 'name', 'new-name' );
				break;
							
			default:
				$this->model->last_error = 'Invalid action for type: "user" => "'.$args['action'].'".';
				return false;
				break;
		}
		
		if( !is_network_admin() )
		{
			$a = array( 'site' );
			$required_keys = array_diff( $required_keys, $a );
			$required_values = array_diff( $required_values, $a );
			$valid_keys = array_diff( $valid_keys, $a );
		}
		
		if( $validate_for_action )
		{
			return $this->validate_args_for_action( 
				$args, 
				$required_keys, 
				$required_values, 
				$valid_keys, 
				$valid_regex_keys );
		}
		
		return $this->validate_args_for_db( 
			$args, 
			$required_keys, 
			$required_values, 
			$valid_keys, 
			$valid_regex_keys );
	}
	
	
	/**
	 * Add a new user if they don't already exist.
	 * @param  array  $item  The data for the batch item.
	 * @return  bool  True if the action was successful, otherwise false.
	 */
	private function add_user( &$item )
	{
		extract($item);
		
		if( !($user_id = $this->get_author_id($user, true, $password, $email)) )
		{
			if( !$this->model->last_error )
				$this->model->last_error = 'Unable to create user account: "'.$user.'". ';
			return false;
		}
		
		foreach( $meta as $key => $value )
		{
			update_user_meta( $user_id, $key, $value );
		}
		
		if( !isset($site) ) return true;
		if( !isset($role) ) return true;
		
		$blog_id = get_id_from_blogname( $site );
		if( !$blog_id )
		{
			$this->model->last_error = 'Unable to find site: '.$site;
			return false;
		}
		
		add_user_to_blog( $blog_id, $user_id, $role );
		
		return true;
	}
	
	
	/**
	 * Update an existing user.
	 * @param  array  $item  The data for the batch item.
	 * @return  bool  True if the action was successful, otherwise false.
	 */
	private function update_user( &$item )
	{
		extract($item);
		
		if( !($user_id = $this->get_author_id($user)) )
		{
			$this->model->last_error = 'Unable to find user account: "'.$user.'". ';
			return false;
		}
		
		foreach( $meta as $key => $value )
		{
			update_user_meta( $user_id, $key, $value );
		}
		
		if( !isset($site) ) return true;
		if( !isset($role) ) return true;
		
		$blog_id = get_id_from_blogname( $site );
		if( !$blog_id )
		{
			$this->model->last_error = 'Unable to find site: '.$site;
			return false;
		}
		
		add_user_to_blog( $blog_id, $user_id, $role );
		
		return true;
	}
	
	
	/**
	 * Add user if they don't exist or update the user if they do already exist.
	 * @param  array  $item  The data for the batch item.
	 * @return  bool  True if the action was successful, otherwise false.
	 */
	private function replace_user( &$item )
	{
		extract($item);
		
		if( $user_id = $this->get_author_id($user, true, $password, $email) );
		{
			$this->model->last_error = 'Unable to find or create user account: "'.$user.'". ';
			return false;
		}
		
		foreach( $meta as $key => $value )
		{
			update_user_meta( $user_id, $key, $value );
		}
		
		if( !isset($site) ) return true;
		if( !isset($role) ) return true;
		
		$blog_id = get_id_from_blogname( $site );
		if( !$blog_id )
		{
			$this->model->last_error = 'Unable to find site: '.$site;
			return false;
		}
		
		add_user_to_blog( $blog_id, $user_id, $role );
		
		return true;
	}
	
	
	/**
	 * Delete an existing user.
	 * @param  array  $item  The data for the batch item.
	 * @return  bool  True if the action was successful, otherwise false.
	 */
	private function delete_user( &$item )
	{
 		extract($item);
		
		$this->model->last_error = 'Delete user is not implemented.';
		return false;
	}


	/**
	 * Add user data for an existing user.
	 * @param  array  $item  The data for the batch item.
	 * @return  bool  True if the action was successful, otherwise false.
	 */
	private function add_meta_user( &$item )
	{
		extract($item);
		
		if( !($user_id = $this->get_author_id($user)) )
		{
			$this->model->last_error = 'Unable to find user account: "'.$user.'". ';
			return false;
		}
		
		$result = add_user_meta( $user_id, $name, $value, true );
		
		if( !$result )
		{
			$this->model->last_error = 'Unable to add user data "'.$name.'" for user "'.$user.'". ';
			return false;
		}
		
		return true;
	}


	/**
	 * Update user data for an existing user.
	 * @param  array  $item  The data for the batch item.
	 * @return  bool  True if the action was successful, otherwise false.
	 */
	private function update_meta_user( &$item )
	{
		extract($item);
		
		if( !($user_id = $this->get_author_id($user)) )
		{
			$this->model->last_error = 'Unable to find user account: "'.$user.'". ';
			return false;
		}
		
		$user_meta = get_user_meta( $user_id, $name, false );
		if( count($user_meta) === 0 )
		{
			$this->model->last_error = '';
			return false;
		}
		
		$result = update_user_meta( $user_id, $name, $value );
		
		if( !$result )
		{
			$this->model->last_error = 'Unable to update user data "'.$name.'" for user "'.$user.'". ';
			return false;
		}
		
		return true;
	}


	/**
	 * Add or update user data for existing user.
	 * @param  array  $item  The data for the batch item.
	 * @return  bool  True if the action was successful, otherwise false.
	 */
	private function replace_meta_user( &$item )
	{
		extract($item);
		
		if( !($user_id = $this->get_author_id($user)) )
		{
			$this->model->last_error = 'Unable to find user account: "'.$user.'". ';
			return false;
		}
		
		$user_meta = get_user_meta( $user_id, $name, false );
		if( (count($user_meta) === 0 && !add_user_meta($user_id, $name, $value, true)) ||
			(count($user_meta) > 0 && !update_user_meta($user_id, $name, $value)) )
		{
			$this->model->last_error = 'Unable to add or update user data "'.$name.'" for user "'.$user.'". ';
			return false;
		}
		
		return true;
	}


	/**
	 * Delete user data for existing user.
	 * @param  array  $item  The data for the batch item.
	 * @return  bool  True if the action was successful, otherwise false.
	 */
	private function delete_meta_user( &$item )
	{
		extract($item);
		
		if( !($user_id = $this->get_author_id($user)) )
		{
			$this->model->last_error = 'Unable to find user account: "'.$user.'". ';
			return false;
		}
		
		if( !delete_user_meta($user_id, $name) )
		{
			$this->model->last_error = 'Unable to delete user data "'.$name.'" for user "'.$user.'". ';
			return false;
		}
		
		return true;
	}
	
	
	/**
	 * Copy user data to a new name for an existing user.
	 * @param  array  $item  The data for the batch item.
	 * @return  bool  True if the action was successful, otherwise false.
	 */
	private function copy_meta_user( &$item )
	{
		extract($item);
		
		if( !($user_id = $this->get_author_id($user)) )
		{
			$this->model->last_error = 'Unable to find user account: "'.$user.'". ';
			return false;
		}
		
		$user_meta = get_user_meta( $user_id, $name, false );
		if( count($user_meta) === 0 )
		{
			$this->model->last_error = 'Unable to get original user data "'.$name.'" for user "'.$user.'". ';
			return false;
		}
		
		$user_meta = $user_meta[0];
		$result = update_user_meta( $user_id, $new_name, $user_meta );
		
		if( !$result )
		{
			$this->model->last_error = 'Unable to update user data "'.$name.'" for user "'.$user.'". ';
			return false;
		}
		
		return true;
	}
	
	
//========================================================================================
//===================================================================== TYPE: Option =====

	
	/**
	 * Verifies that all the required fields are present for "option" type.
	 * @param  array  $args  An array of values.
	 * @return  bool  True if the args are valid, otherwise False.
	 */
	protected function check_option_args( &$args, $validate_for_action = false )
	{
		$required_keys = array();
		$required_values = array();
		$valid_keys = array();
		$valid_regex_keys = array();
		
		switch( $args['action'] )
		{
			case 'add':
				/*
				required fields:
					type, action, site, name, value
				all supported fields:
					type, action, site, name, value
				*/
				$required_keys = array( 'site', 'name', 'value' );
				$required_values = array( 'site', 'name' );
				break;
			
			case 'update':
				/*
				required fields:
					type, action, site, name, value
				all supported fields:
					type, action, site, name, value
				*/
				$required_keys = array( 'site', 'name', 'value' );
				$required_values = array( 'site', 'name' );
				break;
			
			case 'replace':
				/*
				required fields:
					type, action, site, name, value
				all supported fields:
					type, action, site, name, value
				*/
				$required_keys = array( 'site', 'name', 'value' );
				$required_values = array( 'site', 'name' );
				break;
			
			case 'delete':
				/*
				required fields:
					type, action, site, name
				all supported fields:
					type, action, site, name
				*/
				$required_keys = array( 'site', 'name' );
				$required_values = array( 'site', 'name' );
				break;
			
			case 'copy':
				/*
				required fields:
					type, action, site, name, new-name
				all supported fields:
					type, action, site, name, new-name
				*/
				$required_keys = array( 'site', 'name', 'new-name' );
				$required_values = array( 'site', 'name', 'new-name' );
				break;
				
			case 'grep':
				/*
				required fields:
					type, action, site, regex, replace-text
				all supported fields:
					type, action, site, name, regex, replace-text
				*/
				$required_keys = array( 'site', 'regex', 'replace-text' );
				$required_values = array( 'site', 'regex' );
				$valid_keys = array( 'name' );
				break;
				
			default:
				$this->model->last_error = 'Invalid action for type: "option" => "'.$args['action'].'".';
				return false;
				break;
		}
		
		if( !is_network_admin() )
		{
			$a = array( 'site' );
			$required_keys = array_diff( $required_keys, $a );
			$required_values = array_diff( $required_values, $a );
			$valid_keys = array_diff( $valid_keys, $a );
		}
		
		if( $validate_for_action )
		{
			return $this->validate_args_for_action( 
				$args, 
				$required_keys, 
				$required_values, 
				$valid_keys, 
				$valid_regex_keys );
		}
		
		return $this->validate_args_for_db( 
			$args, 
			$required_keys, 
			$required_values, 
			$valid_keys, 
			$valid_regex_keys );
	}
	
	
	/**
	 * Add a option key/value to a site.
	 * @param  array  $item  The data for the batch item.
	 * @return  bool  True if the action was successful, otherwise false.
	 */
	private function add_option( &$item )
	{
		extract($item);
		
		if( !add_option($name, $value) )
		{
			$this->model->last_error = 'Unable to add option "'.$name.'" for site "'.$site.'". ';
			return false;
		}
		
		return $result;
	}
	
	
	/**
	 * Update an existing option value for a site.
	 * @param  array  $item  The data for the batch item.
	 * @return  bool  True if the action was successful, otherwise false.
	 */
	private function update_option( &$item )
	{
		extract($item);
		
		if( (get_option($name) !== false) && (!update_option($name, $value)) )
		{
			$this->model->last_error = 'Unable to update option "'.$name.'" for site "'.$site.'". ';
			return false;
		}
		
		return true;
	}
	
	
	/**
	 * Add a new option or update an existing option for a site.
	 * @param  array  $item  The data for the batch item.
	 * @return  bool  True if the action was successful, otherwise false.
	 */
	private function replace_option( &$item )
	{
		extract($item);
		
		if( !update_option($name, $value) )
		{
			$this->model->last_error = 'Unable to update option "'.$name.'" for site "'.$site.'". ';
			return false;
		}
		
		return true;
	}
	
	
	/**
	 * Delete an existing option for a site.
	 * @param  array  $item  The data for the batch item.
	 * @return  bool  True if the action was successful, otherwise false.
	 */
	private function delete_option( &$item )
	{
		extract($item);
		
		if( !delete_option($name) )
		{
			$this->model->last_error = 'Unable to delete option "'.$name.'" for site "'.$site.'". ';
			return false;
		}
		
		return true;
	}
	
	
	/**
	 * Copy an existing option to a new name for a site.
	 * @param  array  $item  The data for the batch item.
	 * @return  bool  True if the action was successful, otherwise false.
	 */
	private function copy_option( &$item )
	{
		extract($item);
		
		$value = get_option( $name );
		if( $value === false )
		{
			$this->model->last_error = 'Unable to retrieve original option "'.$name.'" for site "'.$site.'". ';
			return false;
		}
		
		if( !update_option($new_name, $value) )
		{
			$this->model->last_error = 'Unable to update option "'.$new_name.'" for site "'.$site.'". ';
			return false;
		}
		
		return true;
	}	
	
	
	/**
	 * Search an option's value or search all options for a match that will be replaced
	 * with replacement text.
	 * @param  array  $item  The data for the batch item.
	 * @return  bool  True if the action was successful, otherwise false.
	 */
	private function grep_option( &$item )
	{
		extract($item);
		
		$options = array();
		
		if( !empty($name) )
		{
			$value = get_option($name);
			if( $value !== false )
				$options[$name] = $value;
		}
		else
		{
			$options = wp_load_alloptions();
		}
		
		foreach( $options as $key => $value )
		{
			$value = preg_replace( "/{$regex}/", $replace_text, $value, -1, $count );
			if( $count === 0 ) continue;
			
			if( !update_option($key, $value) )
			{
				$errors[] = 'Unable to update option "'.$key.'" for site "'.$site.'". ';
				continue;
			}
		}
		
		if( !empty($errors) )
		{
			$this->model->last_error = $this->format_errors_into_string( $errors );
			return false;
		}
		
		return true;
	}
	
	
//========================================================================================
//=================================================================== Util Functions =====
	
	
	/**
	 * Gets the author's id.  If the author does not exist, then they will be created if
	 * $create is set to true.
	 * @param  string  $author  The author's username.
	 * @param  bool  $create  True if author should be created if they don't exist.
	 * @param  string  $password  Password to use if user is created.
	 * @param  string  $email  Email to use if user is created.
	 * @return  int|null  The author's id if they exist, otherwise null.
	 */
	protected function get_author_id( $author, $create = false, $password = false, $email = false )
	{
		$author_data = get_user_by( 'login', $author );
		if( !$author_data && $create && $email )
		{
			$author_data = $this->model->create_user( $author, $password, $email );
		}
		
		return ( $author_data ? $author_data->ID : null );
	}
	
	
	/**
	 * Parses the a date string and returns a SQL formatted string.
	 * @param  string  $date  The date string to parse.
	 * @param  bool  $supports_null  True if null should be returned on failure or date string is empty.
	 * @return  string  The SQL formatted datetime string.
	 */
	protected function parse_date( $date, $supports_null = false )
	{
		if( $date === '' && $supports_null ) return null;
		
		$timestamp = strtotime( $date );
		
		if( $timestamp === false ) return '';
		
		return date( 'Y-m-d H:i:s', $timestamp );
	}
	
	
	/**
	 * Gets the post type.
	 * @param  string  $post_type  The post type.
	 * @return  string  The post type on non-empty strings, otherwise 'post'.
	 */
	protected function get_post_type( $post_type )
	{
		if( empty($post_type) ) return 'post';
		return $post_type;
	}

	
	/**
	 * Gets the post id of a post that will be or currently is a parent.
	 * @param  string|int  The parent title or post id.
	 * @return  The parent post's id.
	 */
	protected function get_post_parent( $parent )
	{
		if( empty($parent) ) return 0;
		if( is_numeric($parent) ) return intval($parent);
		
		$parent = $this->get_post_by_title( $parent );
		if( !$parent ) return 0;
		
		return $parent->ID;
	}
	
	
	/**
	 * Gets a list of taxonomies with matching terms or ids (for heirarchical taxonomies).
	 * Creates any terms that do not currently exist.
	 * @param  array  $taxonomies  An array of taxonomies with terms in comma-seperated string form.
	 * @param  bool  $supports_null  True if null should be returned on failure or taxonomy list is empty.
	 * @return  array|null  An array of taxonomies with their terms or ids on success, 
	 *                      otherwise null or empty array (based on supports_null).
	 */
	public function get_taxonomies( $taxonomies, $supports_null = false )
	{
		if( $taxonomies === '' && $supports_null ) return null;
		
		$new_taxonomies = array();
		
		foreach( $taxonomies as $taxname => $terms )
		{
			if( !taxonomy_exists($taxname) )
			{
				// TODO: error.
				continue;
			}
			
			$new_taxonomies[$taxname] = array();
			
			if( is_array($terms) ) $term_list = $terms;
			elseif( is_string($terms) ) $term_list = str_getcsv( $terms, ",", '"', "\\" );
			else $term_list = array();
			
			if( !is_taxonomy_hierarchical($taxname) )
			{
				$new_taxonomies[$taxname] = $term_list;
			}
			else
			{
				$term_ids = array();
			
				foreach( $term_list as $term )
				{
					$heirarchy = array_map( 'trim', explode('>', $term) );
					
					$parent = null;
					for( $i = 0; $i < count($heirarchy); $i++ )
					{
						if( !term_exists($heirarchy[$i], $taxname, $parent) )
						{
							$args = array();
							if( $parent ) $args['parent'] = $parent;
					
							$result = wp_insert_term( $heirarchy[$i], $taxname, $args );
							if( is_wp_error($result) )
							{
								//TODO: error: 'Unable to insert '.$taxonomy_name.'term: '.$heirarchy[$i];
								break;
							}
						}
						
						$termobject = get_term_by( 'name', $heirarchy[$i], $taxname );
						if( is_wp_error($termobject) )
						{
							//TODO: error: 'Invalid '.$taxonomy_name.'term: '.$heirarchy[$i];
							break;
						}
						
						$parent = $termobject->term_id;
					}
					
					if( isset($termobject) && !is_wp_error($termobject) )
						$term_ids[] = $termobject->term_id;
				}
				
				$new_taxonomies[$taxname] = $term_ids;
			}
		}
		
		return $new_taxonomies;
	}
	
	
	/**
	 * Gets a post with the matching title.
	 * @param  string  $title  The post title.
	 * @param  string  $post_type  The post type.
	 * @return  object|null  The post object if found, otherwise null.
	 */
	protected function get_post_by_title( $title, $post_type = 'post' )
	{
		if( empty($title) ) return null;
		if( empty($post_type) ) $post_type = 'post';
		
		return get_page_by_title( $title, OBJECT, $post_type );
	}
	
	
	/**
	 * Gets a link with the matching name.
	 * @param  string  $name  The name of the link.
	 * @return  object|null  The link object if found, otherwise null.
	 */
	protected function get_link_by_name( $name )
	{
		if( empty($name) ) return null;
		
		global $wpdb;
		return $wpdb->get_row(
			$wpdb->prepare(
				"SELECT * FROM $wpdb->links WHERE link_name = %s LIMIT 1", 
				$name
			)
		);
	}
	
	
	/**
	 * Gets a list of all link categories' ids and creates any categories that don't exist.
	 * @param  array  $category  A comma-seperated array of link categories.
	 * @return  array|null  An array of link category ids on success, otherwise null.
	 */
	protected function get_link_category( $category )
	{
		if( empty($category) ) return null;
		
		$link_categories = array();
		$term_list = str_getcsv( $category, ",", '"', "\\" );

		foreach( $term_list as &$term )
		{
			$term = trim( $term );
			
			$term_object = term_exists( $term, 'link_category' );
			if( $term_object )
				$term_id = $term_object['term_id'];
			else
				$term_id = wp_insert_term( $term, 'link_category' );
			
			$link_categories[] = $term_id;
		}
		
		return $link_categories;
	}
	
	
	/**
	 * Switches to a blog/site for network admin processing.
	 * @param  array  $item  The item being processed.
	 * @param  bool  $site_required  True if site is a required field for the item.
	 * @return  bool  True if the switch occured without errors, otherwise false.
	 */
	protected function switch_to_blog( &$item, $site_required = true )
	{
		if( !is_network_admin() ) return true;
		if( !array_key_exists('site', $item) ) return !$site_required;
		if( empty($item['site']) ) return !$site_required;
		
		$blog_id = get_id_from_blogname( $item['site'] );
		if( !$blog_id ) return false;
		
		switch_to_blog( $blog_id );
		return true;
	}
	
	
	/**
	 * Restores to the current blog for network admin processing.
	 */
	protected function restore_blog()
	{
		if( !is_network_admin() ) return true;
		
		restore_current_blog();
		return true;
	}
	
	
	/**
	 * Get the string value of the value.
	 * @param  mixed  $value  The value to evaluate.
	 * @param  bool  $supports_null  True if null can be returned if not an string or empty string.
	 * @return  string|null  The evaluated string value.
	 */
	protected function get_string_value( $value, $supports_null = false )
	{
		if( $value === '' && $supports_null ) return null;
		return ''.$value;
	}
	
	
	/**
	 * Get the int value of the value.
	 * @param  mixed  $value  The value to evaluate.
	 * @param  bool  $supports_null  True if null can be returned if not an int.
	 * @return  int|null  The evaluated int value.
	 */
	protected function get_int_value( $value, $supports_null = false )
	{
		if( (!is_numeric($value) || $value === '') && ($supports_null) ) return null;
		return intval($value);
	}
	
	
	/**
	 * Inserts a string into another string at the offset.
	 * @param  string  $insert  The string to insert.
	 * @param  string  $subject  The original string to insert into.
	 * @param  int  $offset  The offset of the insert.
	 * @return  string  The altered string with the inserted string.
	 */
	public function str_insert( $insert, $subject, $offset )
	{
		return substr( $subject, 0, $offset ) . $insert . substr( $subject, $offset );
	}


	/**
	 * Find the first start tag, if one is found that matches one of the search tags.
	 * @param  string  $content  The post content.
	 * @param  array  $search_tags  The html tags to search for at the start of content.
	 * @return  string|null  The start tag if found, otherwise null.
	 */
    protected function find_start_tag( $content, $search_tags )
    {
 		$found_tag = null;
		
		$content = str_replace( array("\n", "\r"), '', $content );
		foreach( $search_tags as $tag )
		{
			$search_tag = "<$tag>";
			$first_tag = substr( $content, 0, strlen($search_tag) );
			if( $first_tag === $search_tag )
			{
				$found_tag = $tag;
				break;
			}
			
			$search_tag = "<$tag ";
			$first_tag = substr( $content, 0, strlen($search_tag) );
			if( $first_tag === $search_tag )
			{
				$found_tag = $tag;
				break;
			}
		}
		
		return $found_tag;
	}



	/**
	 * Find the last end tag, if one is found that matches one of the search tags.
	 * @param  string  $content  The post content.
	 * @param  array  $search_tags  The html tags to search for at the end of content.
	 * @return  string|null  The end tag if found, otherwise null.
	 */
    protected function find_end_tag( $content, $search_tags )
    {
		$found_tag = null;
		
		$content = trim( str_replace(array("\n", "\r"), '', $content) );
		
		foreach( $search_tags as $tag )
		{
			$search_tag = "</$tag>";
			$last_tag = substr( $content, strlen($content) - strlen($search_tag) );
			if( $last_tag === $search_tag )
			{
				$found_tag = $tag;
				break;
			}
		}
		
		return $found_tag;
    }
    
    
	/**
	 * Prepends content to the original content.
	 * @param  string  $post_content  The original content.
	 * @param  string  $prepend_content  The prepend content.
	 * @return  string  The content with prepended content.
	 */
    public function prepend_to_content( $post_content, $prepend_content )
    {
    	if( empty($post_content) ) return $post_content;

		if( ($this->find_end_tag($prepend_content, array('p','div')) === NULL) &&
			($this->find_start_tag($post_content, array('p','div')) !== NULL) )
		{
			$offset = strpos( $post_content, '>' );
			if( $offset !== FALSE )
				$post_content = $this->str_insert($prepend_content, $post_content, $offset+1);
		}
		else
		{
			$post_content = $prepend_content.$post_content;
		}
		
		return $post_content;
    }
    
    
    /**
	 * Appends content to the original content.
	 * @param  string  $post_content  The original content.
	 * @param  string  $append_content  The append content.
	 * @return  string  The content with appended content.
	 */
    public function append_to_content( $post_content, $append_content )
    {
    	if( empty($post_content) ) return $post_content;
    	
		if( ($this->find_start_tag($append_content, array('p','div')) === NULL) &&
		    ($this->find_end_tag($post_content, array('p','div')) !== NULL) )
		{
			$offset = strrpos( $post_content, '<' );
			if( $offset !== FALSE )
				$post_content = $this->str_insert($append_content, $post_content, $offset);
		}
		else
		{
	   		$post_content = $post_content.$append_content;
		}
		
		return $post_content;
	}
	
	
	/**
	 * Filters out any null values from the post data array.
	 * @param  array  $post_data  The post data array.
	 */
	protected function filter_post_data( &$post_data )
	{
		$post_data = array_filter( $post_data );
	}
	
	
	/**
	 * Gets the post status.
	 * @param  string  $status  The post's status.
	 * @param  string  $default  The default value if status is empty.
	 * @return  string  The post status value.
	 */
	protected function get_post_status( $status, $default = 'publish' )
	{
		if( empty($status) ) return $default;
		return $this->get_string_value( $status, false );
	}
	
	
	/**
	 * Gets the items description based on action and type values.
	 * @param  array  $item  The item to process to create a description.
	 * @return  string  The item's description.
	 */
	protected function get_item_description( $id, &$item )
	{
		$action = ( isset($item['action']) ? $item['action'] : '[no action]' );
		$type = ( isset($item['type']) ? $item['type'] : '[no type]' );
		
		return 'Item #'.$id.': '.$action.' '.$type;
	}

	
	/**
	 * Formats an array of errors into a single string.
	 * @param  array  $errors  An array of errors.
	 * @return  string  The complete error string comprised of all the errors.
	 */
	protected function format_errors_into_string( &$errors )
	{
		if( $errors === null ) return null;
		if( !is_array($errors) ) return print_r($errors, true);
		
		if( count($errors) === 1 ) return $errors[0];
		
		$error = count($errors).' errors occured. ';
		foreach( $errors as $err )
		{
			$error .= '<br/>'.$err;
		}
		
		return $error;
	}
}
endif;

