<?php

if( !defined('ORGANIZATION_HUB') ) return;

if( !class_exists('WP_List_Table') )
	require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );

if( !class_exists('OrgHub_Model') )
	require_once( ORGANIZATION_HUB_PLUGIN_PATH . '/classes/model.php' );

/**
 * OrgHub_UsersListTable
 * 
 * The WP_List_Table class for the Users table.
 * 
 * @package    orghub
 * @subpackage classes
 * @author     Crystal Barton <cbarto11@uncc.edu>
 */

if( !class_exists('OrgHub_UsersListTable') ):
class OrgHub_UsersListTable extends WP_List_Table
{

	private $parent;		// The parent admin page.
	private $model;			// The main model.
	
	
	/**
	 * Constructor.
	 * Creates an OrgHub_SitesListTable object.
	 */
	public function __construct( $parent )
	{
		$this->parent = $parent;
		$this->model = OrgHub_Model::get_instance();
	}
	

	/**
	 * Loads the list table.
	 */
	public function load()
	{
		parent::__construct(
            array(
                'singular' => 'orghub-user',
                'plural'   => 'orghub-users',
                'ajax'     => false
            )
        );

		$columns = $this->get_columns();
		$hidden = $this->get_hidden_columns();
		$sortable = $this->get_sortable_columns();
		$this->_column_headers = array( $columns, $hidden, $sortable );
	}
	
	/**
	 * Prepare the table's items.
	 * @param   array   $filter       An array of filter name and values.
	 * @param   array   $search       An array of search columns and phrases.
	 * @param   bool    $only_errors  True if filter out OrgHub users with errors.
	 * @param   string  $orderby      The column to orderby.
	 */
	public function prepare_items( $filter = array(), $search = array(), $only_errors = false, $orderby = null )
	{
		$users_count = $this->model->user->get_users_count( $filter, $search, $only_errors, $orderby );
	
		$current_page = $this->get_pagenum();
		$per_page = $this->parent->get_screen_option( 'orghub_users_per_page' );

		$this->set_pagination_args( array(
    		'total_items' => $users_count,
    		'per_page'    => $per_page
  		) );
  		
  		$this->items = $this->model->user->get_users( $filter, $search, $only_errors, $orderby, ($current_page-1)*$per_page, $per_page );
	}


	/**
	 * Get the columns for the table.
	 * @return  array  An array of columns for the table.
	 */
	public function get_columns()
	{
		return array(
			'cb'       => '<input type="checkbox" />',
			'username' => 'Username',
			'namedesc' => 'Name / Description',
			'type'     => 'Type',
			'category' => 'Category',
			'status'   => 'Status',
			'info'     => 'Site Info',
		);
	}
	
	
	/**
	 * Get the column that are hidden.
	 * @return  array  An array of hidden columns.
	 */
	public function get_hidden_columns()
	{
		$screen = get_current_screen();
		$hidden = get_user_option( 'manage' . $screen->id . 'columnshidden' );
		
		if( $hidden === false )
		{
			$hidden = array(
			);
		}
		
		return $hidden;
	}

	
	/**
	 * Get the sortable columns.
	 * @return  array  An array of sortable columns.
	 */
	public function get_sortable_columns()
	{
		return array(
			'username' => array( 'username', false ),
			'namedesc' => array( 'namedesc', false ),
		);
	}
	
	
	/**
	 * Get the selectable (throught Screen Options) columns.
	 * @return  array  An array of selectable columns.
	 */
	public function get_selectable_columns()
	{
		return array(
			'namedesc' => 'Name / Description',
			'type'     => 'Type',
			'category' => 'Category',
			'status'   => 'Status',
			'info'     => 'Site Info',
		);
	}


	/**
	 * Get the bulk action for the users table.
	 * @return  array  An array of bulk actions.
	 */
	public function get_bulk_actions()
	{
		$actions = array(
			'create-users' => 'Create User Accounts',
			'create-sites' => 'Create Profile Sites',
			'create-connections-posts' => 'Create Connections Posts',
			'archive-sites' => 'Archive Sites',
			'draft-connections-posts' => 'Draft Connections Posts',
			'process-users' => 'Process Users',
		);
  		return $actions;
	}
	

	/**
	 * Determine if one of the table's batch action needs to be performed and perform it.
	 * @return  bool  True if an action was processed, otherwise false.
	 */
	public function process_batch_action()
	{
		$action = $this->current_action();
		$users = ( isset($_REQUEST['user']) ? $_REQUEST['user'] : array() );
		
		switch( $action )
		{
			case 'create-users':
				foreach( $users as $user_id )
					$this->model->user->create_wp_user( $user_id );
				break;
			
			case 'create-sites':
				foreach( $users as $user_id )
					$this->model->user->create_profile_blog( $user_id );
				break;
			
			case 'create-connections-posts':
				foreach( $users as $user_id )
					$this->model->user->process_connections_posts( $user_id );
				break;
			
			case 'archive-sites':
				foreach( $users as $user_id )
					$this->model->user->archive_profile_blog( $user_id );
				break;
			
			case 'draft-connections-posts':
				foreach( $users as $user_id )
					$this->model->user->process_connections_posts( $user_id );
				break;
			
			case 'process-users':
				foreach( $users as $user_id )
				{
					$this->model->user->process_user( $user_id );
				}
				break;
			
			default:
				return false;
				break;
		}
		
		return true;
	}
	

	/**
	 * Echos html to display to the area above and below the table.
	 * @param  string  $which  Which tablenav is being displayed (top / bottom).
	 */
	public function display_tablenav( $which )
	{
		?>
		<div class="tablenav <?php echo esc_attr( $which ); ?>">

			<div class="alignleft actions bulkactions">
				<?php $this->bulk_actions( $which ); ?>
			</div>
		
			<?php
			$this->extra_tablenav( $which );
			$this->pagination( $which );
			?>

			<br class="clear" />
		
		</div>
		<?php
	}
	

	/**
	 * Echos html to display to the right of bulk actions.
	 * @param  string  $which  Which tablenav is being displayed (top / bottom).
	 */
	public function extra_tablenav( $which )
	{
		?>
		<a href="<?php echo apl_get_page_url(); ?>&action=export" class="export" />Export Users</a>
		<?php				
	}


	/**
	 * Echos the text to display when no users are found.
	 */
	public function no_items()
	{
  		_e( 'No users found.' );
	}
	
				
	/**
	 * Generates the html for a column.
	 * @param   array   $item         The item for the current row.
	 * @param   string  $column_name  The name of the current column.
	 * @return  string  The heml for the current column.
	 */
	public function column_default( $item, $column_name )
	{
		return '<strong>ERROR:</strong><br/>'.$column_name;
	}
	
	
	/**
	 * Generates the html for the checkbox column.
	 * @param   array   $item         The item for the current row.
	 * @return  string  The heml for the checkbox column.
	 */
	public function column_cb($item)
	{
        return sprintf(
            '<input type="checkbox" name="user[]" value="%s" />', $item['id']
        );
    }
	
	
	/**
	 * Generates the html for the username column.
	 * @param   array   $item         The item for the current row.
	 * @return  string  The heml for the username column.
	 */
	public function column_username( $item )
	{
		$actions = array(
            'edit' => sprintf( '<a href="%s">Edit</a>', 'admin.php?page=orghub-users&tab=edit&id='.$item['id'] ),
        );

		return sprintf( '%1$s<br/>%2$s', $item['username'],  $this->row_actions($actions) );
	}
	
	
	/**
	 * Generates the html for the name/description column.
	 * @param   array   $item         The item for the current row.
	 * @return  string  The heml for the name/description column.
	 */
	public function column_namedesc( $item )
	{
		$html =  '<span class="name" title="'.$item['first_name'].' '.$item['last_name'].'">'.$item['first_name'].' '.$item['last_name'].'</span><br/>';
		$html .= '<span class="email" title="'.$item['email'].'">'.$item['email'].'</span><br/>';
		$html .= '<span class="description" title="'.$item['description'].'">'.$item['description'].'</span><br/>';
		
		return $html;
	}
	
	
	/**
	 * Generates the html for the type column.
	 * @param   array   $item         The item for the current row.
	 * @return  string  The heml for the type column.
	 */
	public function column_type( $item )
	{
		$html = '';
		
		foreach( $item['type'] as $t )
		{
			$html .= '<span title="'.$t.'">'.$t.'</span><br/>';
		}
		
		return $html;
	}
	
	
	/**
	 * Generates the html for the category column.
	 * @param   array   $item         The item for the current row.
	 * @return  string  The heml for the category column.
	 */
	public function column_category( $item )
	{
		$html = '';
		
		foreach( $item['category'] as $c )
		{
			$html .= '<span title="'.$c.'">'.$c.'</span><br/>';
		}
		
		return $html;
	}
	
	
	/**
	 * Generates the html for the status column.
	 * @param   array   $item         The item for the current row.
	 * @return  string  The heml for the status column.
	 */
	public function column_status( $item )
	{
		return $item['status'];
	}
	
	
	/**
	 * Generates the html for the info column.
	 * @param   array   $item         The item for the current row.
	 * @return  string  The heml for the info column.
	 */
	public function column_info( $item )
	{
		extract( $item );
		
		$html = '';
		if( $item['blog_path'] )
			$html = "<span class=\"url\" title=\"$blog_domain/$blog_path\">$blog_domain/$blog_path</span><br/>";
		
		$class = 'wp_user_id';
		if( $this->model->user->get_user_column( $id, 'wp_user_error' ) ) $class .= ' error';
		elseif( $this->model->user->get_user_column( $id, 'wp_user_warning' ) ) $class .= ' warning';
		if( $wp_user_id == null )
			$text = 'Username: none';
		else
			$text = 'Username: '.$wp_user_id;
		$html .= '<span class="'.$class.'" title="'.$text.'">'.$text.'</span><br/>';

		$class = 'profile_site_id';
		if( $this->model->user->get_user_column( $id, 'profile_site_error' ) ) $class .= ' error';
		elseif( $this->model->user->get_user_column( $id, 'profile_site_warning' ) ) $class .= ' warning';
		if( $profile_site_id == null )
		{
			if( !$blog_path )
				$text = 'Profile Site: n/a';
			else
				$text = 'Profile Site: none';
		}
		else
		{
			if( $archived )
				$text = '<strike>Profile Site</strike>: '.$profile_site_id;
			else
				$text = 'Profile Site: '.$profile_site_id;
		}
		$html .= '<span class="'.$class.'" title="'.$text.'">'.$text.'</span><br/>';
	
		foreach( $connections_sites as $cs )
		{
			$class = 'connections_site_id';
			if( $this->model->user->get_connections_column( $id, $cs['site'], 'error' ) ) $class .= ' error';
			elseif( $this->model->user->get_connections_column( $id, $cs['site'], 'warning' ) ) $class .= ' warning';
			if( $cs['post_id'] == null )
			{
				$text = $cs['site'].' Post: none';
			}
			else
			{			
				$connections_post = $this->model->user->get_connections_post( $cs['post_id'], $cs['site'] );
				if( $connections_post['post_status'] == 'draft' )
					$text = '<strike>'.$cs['site'].' post</strike>: '.$cs['post_id'];
				else
					$text = $cs['site'].' Post: '.$cs['post_id'];
			}
			$html .= '<span class="'.$class.'" title="'.$text.'">'.$text.'</span><br/>';
		}

		return $html;
	}
		
} // class OrgHub_UsersListTable extends WP_List_Table
endif; // if( !class_exists('OrgHub_UsersListTable') ):

