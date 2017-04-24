<?php
/**
 * The sites model for the Organization Hub plugin.
 * 
 * @package    organization-hub
 * @subpackage classes/model
 * @author     Crystal Barton <atrus1701@gmail.com>
 */
if( !class_exists('OrgHub_SitesModel') ):
class OrgHub_SitesModel
{
	/**
	 * The only instance of the current model.
	 * @var  OrgHub_SitesModel
	 */	
	private static $instance = null;

	/**
	 * The main model for the Organization Hub.
	 * @var  OrgHub_Model
	 */	
	private $model = null;
	
	/**
	 * The base name (without prefix) for the site table.
	 * @var  string
	 */
	private static $site_table = 'orghub_site';	// 
	
	
	/**
	 * Private Constructor.  Needed for a Singleton class.
	 */
	protected function __construct()
	{
		global $wpdb;
		self::$site_table        = $wpdb->base_prefix.self::$site_table;
		
		$this->model = OrgHub_Model::get_instance();
	}


	/**
	 * Get the only instance of this class.
	 * @return  OrgHub_SitesModel  A singleton instance of the sites model class.
	 */
	public static function get_instance()
	{
		if( self::$instance	=== null )
		{
			self::$instance = new OrgHub_SitesModel();
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
				  last_post_author text NOT NULL DEFAULT '',
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
	 * Drop the required database tables.
	 */
	public function delete_tables()
	{
		global $wpdb;
		$wpdb->query( 'DROP TABLE '.self::$site_table.';' );
	}


	/**
	 * Clear the required database tables.
	 */
	public function clear_tables()
	{
		global $wpdb;
		$wpdb->query( 'DELETE FROM '.self::$site_table.';' );
	}
	
	
	
//========================================================================================
//================================================ Import / Updating database tables =====
	
	
	/**
	 * Adds an OrgHub site to the database.
	 * @param  array  $args  An array of data about a site.
	 * @return  int|bool  The id of the inserted site or false on failure.
	 */
	public function add_site( &$args )
	{
		// If site already exists, then update the user.
		$db_site = $this->get_site_by_blog_id( $args['blog_id'] );
		if( $db_site )
		{
			return $this->update_site( $db_site['id'], $args );
		}
		
		global $wpdb;
		

		// Insert new site into Sites table.
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
				'last_post_author'  => $args['last_post_author'],
				'last_post_status'	=> $args['last_post_status'],
				'last_comment_url'	=> $args['last_comment_url'],
				'last_comment_date'	=> $args['last_comment_date'],
				'admin_email'		=> $args['admin_email'],
				'status'			=> $args['status'],
			),
			array( '%d', '%s', '%s', '%d', '%d', '%d', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s' )
		);
		

		// Check to make sure insertion was successful.
		$site_id = $wpdb->insert_id;
		if( !$site_id )
		{
			$this->model->last_error = 'Unable to insert site.';
			return false;
		}

		return $site_id;
	}
	
	
	/**
	 * Updates an OrgHub site in the database.
	 * @param  int  $id  The site's id (not the WordPress blog id).
	 * @param  array  $args  An array of data about a site.
	 * @return  int|bool  The id of the updated site or false on failure.
	 */
	public function update_site( $id, &$args )
	{
		global $wpdb;
		

		// Update user in Users table.
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
				'last_post_author'  => $args['last_post_author'],
				'last_post_status'	=> $args['last_post_status'],
				'last_comment_url'	=> $args['last_comment_url'],
				'last_comment_date'	=> $args['last_comment_date'],
				'admin_email'		=> $args['admin_email'],
				'status'			=> $args['status'],
			),
			array( 'id' => intval( $id ) ),
			array( '%d', '%s', '%s', '%d', '%d', '%d', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s' ),
			array( '%d' )
		);


		// Check to make sure update was successful.
		if( $result === false )
		{
			$this->model->last_error = 'Unable to update site.';
			return false;
		}
		
		return $id;
	}



//========================================================================================
//================================================= Retrieve site data from database =====
	

	/**
	 * Retrieve a complete list of OrgHub site from the database after filtering.
	 * @param  array  $filter  An array of filter name and values.
	 * @param  array  $search  An array of search columns and phrases.
	 * @param  string  $orderby  The column to orderby.
	 * @param  int  $offset  The offset of the users list.
	 * @param  int  $limit  The amount of users to retrieve.
	 * @return  array  An array of sites given the filtering.
	 */
	public function get_sites( $filter = array(), $search = array(), $orderby = array(), $offset = 0, $limit = -1 )
	{
		global $wpdb;
		
		$list = array();
		$list[self::$site_table] = array(
			'id', 'blog_id', 'url', 'title', 'num_posts', 'num_pages', 'num_comments',
			'last_post_url', 'last_post_date', 'last_post_author', 'last_post_status',
			'last_comment_url', 'last_comment_date', 'admin_email',
		);
		$list[$wpdb->users] = array(
			'display_name','user_login'
		);
		$list[$wpdb->blogs] = array(
			'archived', 'deleted'
		);
		
		$list = $this->model->get_column_list( $list );
		
		$groupby = 'blog_id';
		$filter = $this->filter_sql($filter, $search, $groupby, $orderby, $offset, $limit);
		
// 		apl_print( 'SELECT '.$list.' FROM '.self::$site_table.' '.$filter );
		return $wpdb->get_results( 'SELECT '.$list.' FROM '.self::$site_table.' '.$filter, ARRAY_A );
	}
	
	
	/**
	 * The amount of OrgHub sites from the database after filtering.
	 * @param  array  $filter  An array of filter name and values.
	 * @param  array  $search  An array of search columns and phrases.
	 * @param  string  $orderby  The column to orderby.
	 * @return  array  The amount of sites given the filtering.
	 */
	public function get_sites_count( $filter, $search, $orderby )
	{
		global $wpdb;
 		$groupby = null;
		return $wpdb->get_var( "SELECT COUNT(DISTINCT ".self::$site_table.".id) FROM ".self::$site_table.' '.$this->filter_sql($filter, $search, $groupby, $orderby) );
	}


	/**
	 * Get a site's information based on its blog id.
	 * @param  int  $blog_id  The blog's id.
	 * @return  array|bool  The site's data on success, otherwise false.
	 */
	public function get_site_by_blog_id( $blog_id )
	{
		global $wpdb;
		
		$list = array();
		$list[self::$site_table] = array(
			'id', 'blog_id', 'url', 'title', 'num_posts', 'num_pages', 'num_comments',
			'last_post_url', 'last_post_date', 'last_post_author', 'last_post_status',
			'last_comment_url', 'last_comment_date', 'admin_email',
		);
		$list[$wpdb->users] = array(
			'display_name','user_login'
		);
		
		$list = $this->model->get_column_list( $list );

		$site = $wpdb->get_row(
			$wpdb->prepare(
				'SELECT '.$list.' FROM '.self::$site_table.' LEFT JOIN '.$wpdb->users.' ON '.$wpdb->users.'.user_email = '.self::$site_table.'.admin_email WHERE blog_id = %d',
				$blog_id
			),
			ARRAY_A
		);
		
		if( $site ) return $site;
		return false;
	}
	
	
	/**
	 * Gets the ids of all blogs on the site.
	 * @return  An array of all blog ids.
	 */
	public function get_blog_ids()
	{
		global $wpdb;
		return $wpdb->get_col( 'SELECT blog_id FROM '.$wpdb->blogs );
	}
	
	
	/**
	 * Creates the SQL needed to complete an SQL statement.
	 * @param  array  $filter  An array of filter name and values.
	 * @param  array  $search  An array of search columns and phrases.
	 * @param  string  $orderby  The column to orderby.
	 * @param  int  $offset  The offset of the users list.
	 * @param  int  $limit  The amount of users to retrieve.
	 * @return  string  The constructed SQL needed to complete an SQL statement.
	 */
	protected function filter_sql( $filter = array(), $search = array(), $groupby = null, $orderby = null, $offset = 0, $limit = -1 )
	{
		global $wpdb;
		
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
		$join .= 'LEFT JOIN '.$wpdb->users.' ON '.$wpdb->users.'.user_email = '.self::$site_table.'.admin_email ';
		$join .= 'LEFT JOIN '.$wpdb->blogs.' ON '.$wpdb->blogs.'.blog_id = '.self::$site_table.'.blog_id ';
		
		if( !$groupby ) $groupby = ''; else $groupby = 'GROUP BY '.$groupby;
			
		return $join.' '.$where_string.' '.$groupby.' '.$orderby.' '.$limit_string;
	}
	
	

//========================================================================================
//========================================================= Get/Set from/to database =====
	

	/**
	 * Gets a column in the OrgHub sites table.
	 * @param  int  $user_id  The OrgHub site's id (not WordPress site id).
	 * @param  string  $column  The column name.
	 * @return  bool  The requested value or false on failure.
	 */
	public function get_site_column( $blog_id, $column )
	{
		global $wpdb;
		return $wpdb->get_var( 
			$wpdb->prepare( 
				"SELECT $column FROM ".self::$site_table." WHERE blog_id=%d",
				intval( $blog_id )
			)
		);
	}
	
	
	/**
	 * Sets a column in the OrgHub sites table to a value.
	 * @param  int  $user_id  The OrgHub site's id (not WordPress site id).
	 * @param  string  $column  The column name.
	 * @param  strint  $value  The value to set column to.
	 * @return  bool  True if update was successful, otherwise false.
	 */
	protected function set_site_column( $user_id, $column, $value )
	{
		global $wpdb;
		
		$type = '%s';
		if( is_int($value) ) $type = '%d';
		
		if( $value === null )
		{
			$return = $wpdb->query( 
				$wpdb->prepare( 
					"UPDATE ".self::$site_table." SET $column = NULL WHERE id = %d",
					intval( $user_id )
				)
			);
		}
		else
		{
			$return = $wpdb->query( 
				$wpdb->prepare( 
					"UPDATE ".self::$site_table." SET $column = $type WHERE id = %d",
					$value,
					intval( $user_id )
				)
			);
		}
		
		if( $return ) return true;
		return false;
	}	
	
	
	
//========================================================================================
//============================================================= Actions / Refreshing =====
	
	
	/**
	 * Refresh all the sites.
	 */
	public function refresh_all_sites()
	{
		$sites = wp_get_sites( array( 'limit' => 1000000 ) );
		
		foreach( $sites as &$site )
		{
			$this->refresh_site( $site['blog_id'] );
		}
		
		$this->model->update_option( 'sites-refresh-time', date('Y-m-d H:i:s') );
	}
	
	
	/**
	 * Refresh a single site.
	 * @param  int  $blog_id  The blog id of the site.
	 * @return  array|bool  The site's info on success, otherwise false.
	 */
	public function refresh_site( $blog_id )
	{
		global $wpdb;
		
		$site = $wpdb->get_row( 'SELECT * FROM '.$wpdb->blogs.' WHERE blog_id = '.intval($blog_id), ARRAY_A );
		if( !$site ) return false;
		
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
		
		$post_types = array( 'post', 'page');
		$post_types = array_merge( $post_types, get_post_types($args, 'names', 'and') );
		
		$recent_post = $wpdb->get_row( 'SELECT * FROM '.$wpdb->posts." WHERE post_type IN ('".implode("','", $post_types)."') ORDER BY post_modified_gmt desc LIMIT 1" );
		if( !$recent_post )
		{
			$site['last_post_url'] = '';
			$site['last_post_date'] = '0000-00-00 00:00:00';
			$site['last_post_status'] = '';
			$site['last_post_author'] = '';
		}
		else
		{
			$site['last_post_url'] = get_permalink( $recent_post->ID );
			$site['last_post_date'] = $recent_post->post_modified;
			$site['last_post_status'] = $recent_post->post_status." (".$recent_post->post_type.")";
			//find all revisions of last post, sort by most recent revision and get author information
 			$last_author = $wpdb->get_row( 'SELECT * FROM '.$wpdb->posts." WHERE post_parent =".$recent_post->ID." ORDER BY post_modified_gmt desc LIMIT 1" );
			$author = get_user_by( 'id', $last_author->post_author );
			$site['last_post_author'] = $author->user_login;
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
	
	
	/**
	 * Mark a blog as deleted.
	 * @param  int  $blog_id  The blog's id.
	 */
	function delete_blog( $blog_id )
	{
		do_action( 'deactivate_blog', $blog_id );
		update_blog_status( $blog_id, 'deleted', '1' );
	}


	/**
	 * Mark a blog as archived.
	 * @param  int  $blog_id  The blog's id.
	 */
	function archive_blog( $blog_id )
	{
		update_blog_status( $blog_id, 'archived', '1' );
	}


	/**
	 * Change the active theme of a blog.
	 * @param  int  $blog_id  The blog's id.
	 * @param  string  $theme  The new theme's name.	 
	 */
	function change_theme( $blog_id, $theme )
	{
		switch_to_blog( $blog_id );

		switch_theme( $theme );
	
		restore_current_blog();
	}


	/**
	 * Change the active theme of a blog.
	 * @param  int  $blog_id  The blog's id.
	 * @param  int  $admin_user_id  The admin's user id.
	 * @param  string  $admin_mail  The admin's email.
	 */
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
	
	
	
//========================================================================================
//=========================================================================== Export =====
	
	
	/**
	 * Exports a list of sites to a CSV.
	 * @param  array  $filter  An array of filter name and values.
	 * @param  array  $search  An array of search columns and phrases.
	 * @param  string  $orderby  The column to orderby.
	 */
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
			'last_post_author',
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
				$u['last_post_author'], // last_post_author
				$u['last_post_status'], // last_post_status
				$u['last_comment_url'], // last_comment_url
				$u['last_comment_date'], // last_comment_date
				$u['admin_email'], // admin_email
				$u['user_login'], // admin_username
				$u['display_name'], // admin_name
			);
		}
		
		PHPUtil_CsvHandler::export( 'sites', $headers, $users );
		exit;
	}	
	
} // class OrgHub_SitesModel
endif; // if( !class_exists('OrgHub_SitesModel') ):

