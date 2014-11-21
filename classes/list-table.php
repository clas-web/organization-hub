<?php

if( !defined('ORGANIZATION_HUB_PLUGIN_PATH') ) return;

if( !class_exists('WP_List_Table') )
	require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );

if( !class_exists('OrganizationHub_Model') )
	require_once( ORGANIZATION_HUB_PLUGIN_PATH . '/classes/model.php' );

/**
 * 
 */
class OrganizationsHub_ListTable extends WP_List_Table
{

	private $_nonce_field;
	
	
	/**
	 * 
	 */
	function prepare_items( $filter = array() )
	{
		parent::__construct(
            array(
                'singular' => 'organization-hub-user',
                'plural'   => 'organization-hub-users',
                'ajax'     => false
            )
        );
		
		$this->_nonce_field = wp_nonce_field( ORGANIZATION_HUB_PLUGIN_PATH, 'organization-hub-form', false, false );
		
// 		$columns = $this->get_columns();
// 		$hidden = $this->get_hidden_columns();
// 		$sortable = $this->get_sortable_columns();
// 		$this->_column_headers = array($columns, $hidden, $sortable);
		$this->_column_headers = $this->get_column_info();
		
		$this->process_batch_action();

		$model = OrganizationHub_Model::get_instance();

		$search = array();
		if( !empty($_REQUEST['s']) )
		{
			$search['username'] = array( $_REQUEST['s'] );
			$search['first_name'] = array( $_REQUEST['s'] );
			$search['last_name'] = array( $_REQUEST['s'] );
		}

		$orderby = ( !empty($_GET['orderby']) ? $_GET['orderby'] : 'username' );
		$order = ( !empty($_GET['order']) ? $_GET['order'] : 'asc' );
		
		switch( $orderby )
		{
			case 'username':
			case 'type':
			case 'category':
				$orderby .= ' '.$order;
				break;

			default:
				$orderby = '';
				break;
		}
		
		$users_count = $model->get_users_count( $filter, $search, $orderby );
	
		$current_page = $this->get_pagenum();
		$per_page = $this->get_items_per_page('users_per_page', 100);

		$this->set_pagination_args( array(
    		'total_items' => $users_count,
    		'per_page'    => $per_page
  		) );
  		
  		$this->items = $model->get_users( $filter, $search, $orderby, ($current_page-1)*$per_page, $per_page );
	}


	/**
	 * 
	 */
	function get_items( $filter = array(), $orderby = null )
	{
		
	}
	
	
	/**
	 * 
	 */
	function get_columns()
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
	function get_hidden_columns()
	{
		return array();
	}

	
	/**
	 * 
	 */
	function get_sortable_columns()
	{
		return array(
			'username' => array( 'username', false ),
			'type'     => array( 'type', false ),
			'category' => array( 'category', false ),
		);
	}
	

	/**
	 * 
	 */
	function sort_data( $a, $b )
	{
		$orderby = ( !empty($_GET['orderby']) ? $_GET['orderby'] : 'username' );
		$order = ( !empty($_GET['order']) ? $_GET['order'] : 'asc' );

		switch( $orderby )
		{
			case 'username':
			case 'type':
			case 'category':
			default:
				$result = strcmp( $a[$orderby], $b[$orderby] );
				break;
		}
		
		return ( $order === 'asc' ) ? $result : -$result;
	}


	/**
	 * 
	 */
	function column_default( $item, $column_name )
	{
		return '<strong>ERROR:</strong><br/>'.$column_name;
	}
	
	
	function column_cb($item)
	{
        return sprintf(
            '<input type="checkbox" name="user[]" value="%s" />', $item['id']
        );
    }

	/**
	 * 
	 */
	function column_username( $item )
	{
		$actions = array(
            'edit' => sprintf( '<a href="%s" target="_blank">Edit</a>', 'admin.php?page=organization-hub&tab=edit-user&id='.$item['id'] ),
        );

		return sprintf( '%1$s<br/>%2$s', $item['username'],  $this->row_actions($actions) );
	}
	

	function column_namedesc( $item )
	{
		$html = $item['first_name'].' '.$item['last_name'].'<br/>';
		$html .= $item['email'].'<br/>';
		$html .= $item['description'].'<br/>';
		
		return $html;
	}
	
	function column_type( $item )
	{
		return $item['type'];
	}
	
	function column_category( $item )
	{
		return $item['category'];
	}
	
	function column_status( $item )
	{
		return $item['status'];
	}
	
	function column_info( $item )
	{
		$html = $item['domain'].'<br/>';
		
		if( $item['wp_user_id'] == null )
			$html .= 'User does NOT exist.<br/>';
		else
			$html .= 'User exists: '.$item['wp_user_id'].'<br/>';

		if( $item['connections_post_id'] == null )
			$html .= 'Connections post does NOT exist.<br/>';
		else
			$html .= 'Connection post exists: '.$item['connections_post_id'].'<br/>';

		if( $item['profile_site_id'] == null )
			$html .= 'Profile site does NOT exist<br/>';
		else
			$html .= 'Profile site exists: '.$item['profile_site_id'].'<br/>';		
	
		return $html;
	}
	
	function get_bulk_actions()
	{
		$actions = array(
			'create-users' => 'Create User Accounts',
			'create-sites' => 'Create Profile Sites',
			'create-connection-posts' => 'Create Connection Posts',
			'process-users' => 'Process Users',
		);
  		return $actions;
	}
	
	function process_batch_action()
	{
		$action = $this->current_action();
		$users = ( $_REQUEST['users'] ? $_REQUEST['users'] : array() );
		
		
		switch( $action )
		{
			case 'create-users':
				foreach( $users as $user_id )
					$this->create_user( $user_id );
				break;
			
			case 'create-sites':
				foreach( $users as $user_id )
					$this->create_site( $user_id );
				break;
			
			case 'create-connections-posts':
				foreach( $users as $user_id )
					$this->create_connection_post( $user_id );
				break;
			
			case 'process-users':
				foreach( $users as $user_id )
				{
					$this->create_user( $user_id );
					$this->create_site( $user_id );
					$this->create_connection_post( $user_id );
				}
				break;
		}
	}
	
	function no_items()
	{
  		_e( 'No users found.' );
	}
	
}


