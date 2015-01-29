

jQuery(document).ready(
	function()
	{
		jQuery('table.list-table-inline-bulk-action').ListTableInlineBulkAction();
	}
);


( function( $ ) {
	
	/**
	 * ListTableInlineBulkAction
	 * 
	 * The ListTableInlineBulkAction jQuery plugin ...
	 * 
	 * @package    apl
	 * @author     Crystal Barton <cbarto11@uncc.edu>
	 */
	$.fn.ListTableInlineBulkAction = function( options )
	{
		function disable_bulk_action_lists()
		{
			$('input#doaction').prop('disabled', true);
			$('input#doaction2').prop('disabled', true);
			$('#bulk-action-selector-top').prop('disabled', true);
			$('#bulk-action-selector-bottom').prop('disabled', true);
		}
		
		function enable_bulk_action_lists()
		{
			$('input#doaction').prop('disabled', false);
			$('input#doaction2').prop('disabled', false);
			$('#bulk-action-selector-top').prop('disabled', false);
			$('#bulk-action-selector-bottom').prop('disabled', false);
		}

		function remove_all_inline_bulk_action_rows( settings )
		{
			$( settings.table ).find( 'tr.inline-bulk-action' ).remove();
		}
		
		function show( settings )
		{
			remove_all_inline_bulk_action_rows( settings );
			$( settings.table ).prepend( $(settings.this).html() );
			
			$( settings.table ).find('button.bulk-save').click( 
				function(event) {
					save(settings);
			});
			$( settings.table ).find('button.bulk-cancel').click(
				function(event) {
					event.preventDefault();
					hide(settings);
			});
			disable_bulk_action_lists();
		}
		
		function hide( settings )
		{
			remove_all_inline_bulk_action_rows( settings );
			enable_bulk_action_lists();
		}
		
		function save( settings )
		{
			// just let it submit...
		}
		
		
		/**
		 * Setup each List Table Inline Bulk Action.
		 */
		return this.each(function() {
			
			var settings = {
				'this'     : this,
				'table'    : (($(this).attr('table')) ? $(this).attr('table') : null),
				'action'   : (($(this).attr('action')) ? $(this).attr('action') : null),
			};
			if(options) $.extend(settings, options);

			if( !settings.table || !settings.action )
				return;
			
			settings.table = $('table.wp-list-table.'+settings.table+' #the-list');

			if( !settings.table )
				return;


			$('input#doaction').click( 
				function(event)
				{
					var action = $('#bulk-action-selector-top').val();
					if( action == settings.action )
					{
						event.preventDefault();
						show( settings );
					}
				}
			);
			
			$('input#doaction2').click( 
				function(event)
				{
					var action = $('#bulk-action-selector-bottom').val();
					if( action == settings.action )
					{
						event.preventDefault();
						show( settings );
					}
				}
			);
		
		});
	}
	
})( jQuery )

