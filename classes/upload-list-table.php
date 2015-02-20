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

if( !class_exists('OrgHub_UploadListTable') ):
class OrgHub_UploadListTable extends WP_List_Table
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
                'singular' => 'orghub-upload',
                'plural'   => 'orghub-upload',
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
	public function prepare_items( $orderby = null )
	{
		$items_count = $this->model->upload->get_items_count();
	
		$this->set_pagination_args( array(
    		'total_items' => $items_count,
    		'per_page'    => $items_count
  		) );
  		
  		$this->items = $this->model->upload->get_items( $orderby );
	}


	/**
	 * Get the columns for the table.
	 * @return  array  An array of columns for the table.
	 */
	public function get_columns()
	{
		return array(
			'cb'        => '<input type="checkbox" />',
			'data' 	    => 'Data',
			'timestamp' => 'Timestamp',
		);
	}
	
	
	/**
	 * Get the column that are hidden.
	 * @return  array  An array of hidden columns.
	 */
	public function get_hidden_columns()
	{
		return array();
	}

	
	/**
	 * Get the sortable columns.
	 * @return  array  An array of sortable columns.
	 */
	public function get_sortable_columns()
	{
		return array(
			'timestamp' => array( 'timestamp', false ),
		);
	}
	
	
	/**
	 * Get the selectable (throught Screen Options) columns.
	 * @return  array  An array of selectable columns.
	 */
	public function get_selectable_columns()
	{
		return array();
	}


	/**
	 * Get the bulk action for the users table.
	 * @return  array  An array of bulk actions.
	 */
	public function get_bulk_actions()
	{
		$actions = array(
			'remove-items' => 'Remove Items',
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
		$items = ( isset($_REQUEST['item']) ? $_REQUEST['item'] : array() );
		
		switch( $action )
		{
			case 'remove-items':
				foreach( $items as $item_id )
					$this->model->upload->remove_item( $item_id );
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
	 * Echos the text to display when no users are found.
	 */
	public function no_items()
	{
  		_e( 'No items found.' );
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
            '<input type="checkbox" name="item[]" value="%s" />', $item['id']
        );
    }
	
	
	/**
	 * Generates the html for the data column.
	 * @param   array   $item         The item for the current row.
	 * @return  string  The heml for the username column.
	 */
	public function column_data( $item )
	{
		$actions = array(
            'delete' => sprintf( '<a href="%s">Delete</a>', 'admin.php?page=orghub-upload&tab=list&id='.$item['id'].'&action=delete' ),
            'process' => sprintf( '<a href="%s">Process</a>', 'admin.php?page=orghub-upload&tab=list&id='.$item['id'].'&action=process' ),
		);

		$html = '';
		
		foreach( $item['data'] as $key => $value )
		{
			$html .= '<div class="key-'.$key.'"><label>'.$key.'</label>'.$value.'</div>';
		}
		
		return sprintf( '%1$s%2$s', $html,  $this->row_actions($actions) );
	}
	
	
	/**
	 * Generates the html for the name/description column.
	 * @param   array   $item         The item for the current row.
	 * @return  string  The heml for the name/description column.
	 */
	public function column_timestamp( $item )
	{
		return $item['timestamp'];
	}
	
} // class OrgHub_UsersListTable extends WP_List_Table
endif; // if( !class_exists('OrgHub_UsersListTable') ):

