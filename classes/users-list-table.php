<?php

if( !defined('ORGANIZATION_HUB') ) return;

if( !class_exists('WP_List_Table') )
	require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );

if( !class_exists('OrgHub_Model') )
	require_once( ORGANIZATION_HUB_PLUGIN_PATH . '/classes/model.php' );

/**
 * OrgHub_UsersListTable
 * 
 * 
 * 
 * @package    orghub
 * @subpackage classes
 * @author     Crystal Barton <cbarto11@uncc.edu>
 */

if( !class_exists('OrgHub_UsersListTable') ):
class OrgHub_UsersListTable extends WP_List_Table
{

	private $parent;
	private $model;				// 
	
	
	/**
	 * 
	 */
	public function __construct( $parent )
	{
		$this->parent = $parent;
		$this->model = OrgHub_Model::get_instance();
	}
	

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
	 * 
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
	 * 
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
	 * 
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
	 * 
	 */
	public function get_sortable_columns()
	{
		return array(
			'username' => array( 'username', false ),
			'namedesc' => array( 'namedesc', false ),
		);
	}
	
	
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
	 * 
	 */
	public function column_default( $item, $column_name )
	{
		return '<strong>ERROR:</strong><br/>'.$column_name;
	}
	
	
	/**
	 * 
	 */
	public function column_cb($item)
	{
        return sprintf(
            '<input type="checkbox" name="user[]" value="%s" />', $item['id']
        );
    }
	
	
	/**
	 * 
	 */
	public function column_username( $item )
	{
		$actions = array(
            'edit' => sprintf( '<a href="%s">Edit</a>', 'admin.php?page=orghub-users&tab=edit&id='.$item['id'] ),
        );

		return sprintf( '%1$s<br/>%2$s', $item['username'],  $this->row_actions($actions) );
	}
	
	
	/**
	 * 
	 */
	public function column_namedesc( $item )
	{
		$html =  '<span class="name" title="'.$item['first_name'].' '.$item['last_name'].'">'.$item['first_name'].' '.$item['last_name'].'</span><br/>';
		$html .= '<span class="email" title="'.$item['email'].'">'.$item['email'].'</span><br/>';
		$html .= '<span class="description" title="'.$item['description'].'">'.$item['description'].'</span><br/>';
		
		return $html;
	}
	
	
	/**
	 * 
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
	 * 
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
	 * 
	 */
	public function column_status( $item )
	{
		return $item['status'];
	}
	
	
	/**
	 * 
	 */
	public function column_info( $item )
	{
		$html = '<span class="url" title="'.$item['site_domain'].'/'.$item['site_path'].'">'.$item['site_domain'].'/'.$item['site_path'].'</span><br/>';
		
		$username_class = ''; //($this->model->user->get_user_exception($item['id'], 'username') ? 'user-exception' : '');
		$site_class = ''; //($this->model->user->get_user_exception($item['id'], 'site') ? 'user-exception' : '');
		$connections_class = ''; //($this->model->user->get_user_exception($item['id'], 'connections') ? 'user-exception' : '');
		
		$class = 'wp_user_id';
		if( $this->model->user->get_wp_user_error( $item['id'] ) ) $class .= ' error';
		elseif( $this->model->user->get_wp_user_warning( $item['id'] ) ) $class .= ' warning';
		if( $item['wp_user_id'] == null )
			$text = 'username: NONE';
		else
			$text = 'username: '.$item['wp_user_id'];
		$html .= '<span class="'.$class.'" title="'.$text.'">'.$text.'</span><br/>';

		$class = 'profile_site_id';
		if( $this->model->user->get_profile_site_error( $item['id'] ) ) $class .= ' error';
		elseif( $this->model->user->get_profile_site_warning( $item['id'] ) ) $class .= ' warning';
		if( $item['profile_site_id'] == null )
		{
			$text = 'profile site: NONE';
		}
		else
		{
			$profile_site = $this->model->user->get_profile_site( $item['profile_site_id'] );
			if( $profile_site['archived'] )
				$text = '<strike>profile site</strike>: '.$item['profile_site_id'];
			else
				$text = 'profile site: '.$item['profile_site_id'];
		}
		$html .= '<span class="'.$class.'" title="'.$text.'">'.$text.'</span><br/>';
	
		foreach( $item['connections_sites'] as $cs )
		{
			$class = 'connections_site_id';
			if( $this->model->user->get_connections_error( $item['id'], $cs['site'] ) ) $class .= ' error';
			elseif( $this->model->user->get_connections_warning( $item['id'], $cs['site'] ) ) $class .= ' warning';
			if( $cs['post_id'] == null )
			{
				$text = $cs['site'].' post: NONE';
			}
			else
			{			
				$connections_post = $this->model->user->get_connections_post( $cs['post_id'], $cs['site'] );
				if( $connections_post['post_status'] == 'draft' )
					$text = '<strike>'.$cs['site'].' post</strike>: '.$cs['post_id'];
				else
					$text = $cs['site'].' post: '.$cs['post_id'];
			}
			$html .= '<span class="'.$class.'" title="'.$text.'">'.$text.'</span><br/>';
		}

		return $html;
	}
	
	
	/**
	 * 
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
	 * 
	 */
	public function process_batch_action()
	{
		$action = $this->current_action();
		$users = ( isset($_REQUEST['user']) ? $_REQUEST['user'] : array() );
		
		switch( $action )
		{
			case 'create-users':
				foreach( $users as $user_id )
					$this->model->user->create_username( $user_id );
				break;
			
			case 'create-sites':
				foreach( $users as $user_id )
					$this->model->user->create_site( $user_id, true );
				break;
			
			case 'create-connections-posts':
				foreach( $users as $user_id )
					$this->model->user->create_connections_posts( $user_id, true );
				break;
			
			case 'archive-sites':
				foreach( $users as $user_id )
					$this->model->user->archive_site( $user_id );
				break;
			
			case 'draft-connections-posts':
				foreach( $users as $user_id )
					$this->model->user->process_connections_posts( $user_id, true );
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
	 * 
	 */
	public function no_items()
	{
  		_e( 'No users found.' );
	}
	
	
	/**
	 * 
	 */
	public function extra_tablenav( $which )
	{
		?>
		<a href="<?php echo apl_get_page_url(); ?>&action=export" class="export" />Export Users</a>
		<?php				
	}


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
	
		
}
endif;

