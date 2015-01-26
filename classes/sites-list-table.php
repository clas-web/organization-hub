<?php

if( !defined('ORGANIZATION_HUB') ) return;

if( !class_exists('WP_List_Table') )
	require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );

if( !class_exists('OrgHub_Model') )
	require_once( ORGANIZATION_HUB_PLUGIN_PATH . '/classes/model.php' );

/**
 * OrgHub_SitesListTable
 * 
 * 
 * 
 * @package    orghub
 * @subpackage classes
 * @author     Crystal Barton <cbarto11@uncc.edu>
 */

if( !class_exists('OrgHub_SitesListTable') ):
class OrgHub_SitesListTable extends WP_List_Table
{

	private $parent;
	private $model;			// 
	
	
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
                'singular' => 'orghub-site',
                'plural'   => 'orghub-sites',
                'ajax'     => false,
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
	public function prepare_items( $filter = array(), $search = array(), $orderby = null )
	{
		$sites_count = $this->model->get_sites_count( $filter, $search, $orderby );
	
		$current_page = $this->get_pagenum();
		$per_page = $this->parent->get_screen_option( 'orghub_sites_per_page' );

		$this->set_pagination_args( array(
    		'total_items' => $sites_count,
    		'per_page'    => $per_page
  		) );
  		
  		$this->items = $this->model->get_sites( $filter, $search, $orderby, ($current_page-1)*$per_page, $per_page );
	}


	/**
	 * 
	 */
	public function get_columns()
	{
		return array(
			'cb'                     => '<input type="checkbox" />',
			'site_title'             => 'Title',
			'site_num_comments'      => '# of Comments',
			'site_num_posts'         => '# of Posts',
			'site_num_pages'         => '# of Pages',
			'site_comment_post'      => 'Comment / Post',
			'site_post_page'         => 'Post / Page',
			'site_last_post_date'    => 'Last Post Date',
			'site_last_comment_date' => 'Last Comment Date',
			'site_administrator'     => 'Administrator',
		);
	}
	
	
	/**
	 * 
	 */
	public function get_hidden_columns()
	{
		$screen = get_current_screen();
		$hidden = get_user_option( 'manage'.$screen->id.'columnshidden' );
		
		if( $hidden === false )
		{
			$hidden = array(
				'site_comment_post',
				'site_post_page',
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
			'site_title'             => array( 'title', false ),
			'site_num_comments'      => array( 'num_comments', true ),
			'site_num_posts'         => array( 'num_posts', true ),
			'site_num_pages'         => array( 'num_pages', true ),
			'site_last_post_date'    => array( 'last_post_date', true ),
			'site_last_comment_date' => array( 'last_comment_date', true ),
			'site_administrator'     => array( 'administrator', false ),
		);
	}
	
	
	public function get_selectable_columns()
	{
		return array(
			'site_num_comments'      => '# of Comments',
			'site_num_posts'         => '# of Posts',
			'site_num_pages'         => '# of Pages',
			'site_comment_post'      => 'Comment / Post',
			'site_post_page'         => 'Post / Page',
			'site_last_post_date'    => 'Last Post Date',
			'site_last_comment_date' => 'Last Comment Date',
			'site_administrator'     => 'Administrator',
		);
	}


	/**
	 * 
	 */
	public function column_default( $item, $column_name )
	{
		$html = '';

		switch( $column_name )
		{
			case 'site_num_comments':
				$html = $item['num_comments'];
				break;
				
			case 'site_num_posts':
				$html = $item['num_posts'];
				break;

			case 'site_num_pages':
				$html = $item['num_pages'];
				break;
			
			case 'site_comment_post':
				if( $item['num_posts'] > 0 )
					$html = number_format( (float)$item['num_comments'] / $item['num_posts'], 2, '.', '' );
				else
					$html = number_format(0, 2, '.', '');
				break;
			
			case 'site_post_page':
				if( $item['num_pages'] > 0 )
					$html = number_format( (float)$item['num_posts'] / $item['num_pages'], 2, '.', '' );
				else
					$html = number_format(0, 2, '.', '');
				break;
			
			case 'site_last_post_date':
				if( !empty($item['last_post_url']) )
				{
					$html = '<a href="'.$item['last_post_url'].'">'.$item['last_post_date'].'</a>';
					$html .= '<br/>';
					$html .= 'Post Status: '.$item['last_post_status'];
				}
				else
					$html = 'No Posts';
				break;
				
			case 'site_last_comment_date':
				if( !empty($item['last_comment_url']) )
					$html = '<a href="'.$item['last_comment_url'].'">'.$item['last_comment_date'].'</a>';
				else
					$html = 'No Comments';
				break;
			
			case 'site_administrator':
				if( empty($item['admin_email']) )
				{
					$html = 'NO ADMIN EMAIL';
				}
				elseif( $item['display_name'] )
				{
					$url = network_admin_url( 'users.php?s='.$item['user_login'] );
					$html = '';
					$html .= '<a href="'.$url.'" target="_blank">'.$item['display_name'].'</a><br/>';
					$html .= $item['admin_email'];
				}
				else
				{
					$html = 'INVALID ADMIN EMAIL: '.$item['admin_email'];
				}
				break;
			
			default:
				$html = '<strong>ERROR:</strong><br/>'.$column_name;
		}
		
		return $html;
	}
	
	
	/**
	 * 
	 */
	public function column_cb($item)
	{
        return sprintf(
            '<input type="checkbox" name="site[]" value="%s" />', $item['blog_id']
        );
    }

	
	/**
	 * 
	 */
	public function column_site_title( $item )
	{
		$actions = array(
            'dashboard' => sprintf( '<a href="%s" target="_blank">Dashboard</a>', $item['url'].'/wp-admin' ),
            'view' => sprintf( '<a href="%s" target="_blank">View</a>', $item['url'] ),
            'info' => sprintf( '<a href="%s" target="_blank">Info</a>', network_admin_url('site-info.php?id='.$item['blog_id']) ),
        );
        
		return sprintf( '%1$s<br/>%2$s', $item['title'],  $this->row_actions($actions) );
	}

	
	/**
	 * 
	 */
	public function no_items()
	{
  		_e( 'No sites found.' );
	}
	
	
	public function single_row( $item )
	{
		static $row_class = '';
		$row_class = ( $row_class == '' ? 'alternate' : '' );

		echo '<tr class="blog-'.$item['blog_id'].' '.$row_class.'">';
		$this->single_row_columns( $item );
		echo '</tr>';
	}


	/**
	 * 
	 */
	public function get_bulk_actions()
	{
		$actions = array(
			'delete' => 'Delete',
			'archive' => 'Archive',
			'change-theme' => 'Change Theme',
			'change-site-admin' => 'Change Site Admin',
		);
  		return $actions;
	}
	
	
	/**
	 * 
	 */
	public function process_batch_action()
	{
		$action = $this->current_action();
		$sites = ( isset($_REQUEST['site']) ? $_REQUEST['site'] : array() );
		$bulk_input = ( isset($_REQUEST['bulk']) ? $_REQUEST['bulk'] : array() );
				
		switch( $action )
		{
			case 'delete':
				foreach( $sites as $site_id )
					$this->model->delete_blog( $site_id );
				break;
			
			case 'archive':
				foreach( $sites as $site_id )
					$this->model->archive_blog( $site_id );
				break;
			
			case 'change-theme':
				if( !isset($bulk_input['theme']) )
				{
					// set error
					return;
				}
				foreach( $sites as $site_id )
					$this->model->change_theme( $site_id, $bulk_input['theme'] );
				break;
			
			case 'change-site-admin':
				apl_print( 'create-site-admin' );
				if( !isset($bulk_input['admin']) )
				{
					// set error
					return;
				}
				apl_print( $bulk_input['admin'] );
				$admin = get_user_by( intval($bulk_input['admin']), 'id' );
				apl_print( $admin );
				global $wpdb;
				$admin_email = $wpdb->get_var( "SELECT user_email FROM $wpdb->users WHERE id = ".$bulk_input['admin'] );
				apl_print( $admin_email );
				if( !$admin_email )
				{
					// set error
					return;
				}
				foreach( $sites as $site_id )
					$this->model->change_site_admin( $site_id, $bulk_input['admin'], $admin_email );
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
	public function extra_tablenav( $which )
	{
		?>
		<a href="<?php echo apl_get_page_url(); ?>&action=export" class="export" />Export Sites</a>
		<?php				
	}
	

	public function inline_change_admin()
	{
		global $wpdb;
		$users = $wpdb->get_results( "SELECT id, display_name FROM $wpdb->users ORDER BY display_name" );
//		$users = get_users( 'orderby=nicename' );
//		$user_query = new WP_User_Query( array( 'orderby' => 'display_name' ) );
//		$users = $user_query->results;
		
		?>
		<form method="get" action="">
			<table id="inline-change-admin"
			       class="list-table-inline-bulk-action"
			       table="orghub-sites"
			       action="change-site-admin"
			       style="display:none">

			<tr class="inline-bulk-action">
				<td colspan="<?php echo $this->get_column_count(); ?>" class="colspanchange">
					<fieldset class="inline-change-admin-col-left">
					<div class="inline-change-admin-col">
						<h4>Change Administrator</h4>
						
						<select name="bulk[admin]">
						<?php foreach( $users as $user ): ?>
							<option value="<?php echo $user->id; ?>"><?php echo $user->display_name; ?></option>
						<?php endforeach; ?>
						</select>
						
						<button class="bulk-save">Save</button>
						<button class="bulk-cancel">Cancel</button>
						
					</div>
					</fieldset>
				</td>
			</tr>
			
			</table>
		</form>
		<?php
	}
	
	
	public function inline_change_theme()
	{
		$themes = wp_get_themes(
			array(
				'errors' => null,
				'allowed' => null,
				'blog_id' => 1,
			)
		);
		
		?>
		<form method="get" action="">
			<table id="inline-change-theme"
			       class="list-table-inline-bulk-action"
			       table="orghub-sites"
			       action="change-theme"
			       style="display:none">

			<tr class="inline-bulk-action">
				<td colspan="<?php echo $this->get_column_count(); ?>" class="colspanchange">
					<fieldset class="inline-change-theme-col-left">
					<div class="inline-change-theme-col">
						<h4>Change Theme</h4>
						
						<select name="bulk[theme]">
						<?php foreach( $themes as $theme ): ?>
							<option value="<?php echo $theme->name; ?>"><?php echo $theme->name; ?></option>
						<?php endforeach; ?>
						</select>

						<button class="bulk-save">Save</button>
						<button class="bulk-cancel">Cancel</button>						
						
					</div>
					</fieldset>
				</td>
			</tr>
			
			</table>
		</form>
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

