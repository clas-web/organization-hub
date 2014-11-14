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
		$this->_nonce_field = wp_nonce_field( ORGANIZATION_HUB_PLUGIN_PATH, 'organization-hub-form', false, false );
		
		$columns = $this->get_columns();
		$hidden = $this->get_hidden_columns();
		$sortable = $this->get_sortable_columns();
		$this->_column_headers = array($columns, $hidden, $sortable);

		$this->get_items( $filter );
		usort( $this->items, array( &$this, 'sort_data' ) );
	}


	/**
	 * 
	 */
	function get_items( $filter = array() )
	{
		$model = OrganizationHub_Model::get_instance();
		$this->items = $model->get_users( $filter );
	}
	
	
	/**
	 * 
	 */
	function get_columns()
	{
		return array(
			'username' => 'Username',
			'name'     => 'Name / Description',
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
	

	/**
	 * 
	 */
	function column_username( $item )
	{
		$actions = array(
            'view' => sprintf( '<a href="%s" target="_blank">View</a>', 'view-url' ),
            'edit' => sprintf( '<a href="%s" target="_blank">Edit</a>', 'edit-url' ),
        );

		return sprintf( '%1$s<br/>%2$s', $item['username'],  $this->row_actions($actions) );
	}
	

	function column_name( $item )
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

}

