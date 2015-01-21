

jQuery(document).ready(
	function()
	{
		jQuery('button.apl-ajax-button').AplAjaxButton();
	}
);


( function( $ ) {
	
	/**
	 * AplAjaxButton
	 * 
	 * The AplAjaxButton jQuery plugin performs an AJAX call or a series of AJAX calls
	 * when the button is clicked.  For each form specified in the button's attributes or
	 * options, an AJAX call is performed.
	 * 
	 * @package    apl
	 * @author     Crystal Barton <cbarto11@uncc.edu>
	 */
	$.fn.AplAjaxButton = function( options )
	{
		/**
		 * Perform an AJAX call for the current form at form index (fi).
		 * When the this AJAX call is complete, the next form is processed.
		 * @param  int    fi        The current index of the form being processed.
		 * @param  array  settings  The AJAX button's settings, as outlined below.
		 *
		 * Settings key values:
         *  - page: The page that should process the request.
		 *  - tab: The tab that should process the request.
		 *  - action: The "action" to send to the page/tab.
		 *  - forms: The forms to be serialized and sent to processing page/tab.
		 *  - inputs: The input fields to processed in each form.
		 *  - cb_start: The JS function to call when starting forms processing.
		 *  - cb_end: The JS function to call when completed forms processing.
		 *  - cb_loop_start: The JS function to call when starting a form's AJAX call.
		 *  - cb_loop_end: The JS function to call when completed a form's AJAX call.
		 *  - nonce: A unique nonce to send for security and validation purposes.
		 */
		function perform_form_ajax( fi, settings )
		{
			if( fi === 0 && settings.cb_start )
				settings.cb_start( settings );
			
			if( settings.forms.length == 0 || fi >= settings.forms.length )
			{
				if( settings.cb_end ) settings.cb_end( settings );
				return;
			}
			
			var current_form = settings.forms[fi];

			if( settings.cb_loop_start )
				settings.cb_loop_start( fi, settings );
			
			// Setup up AJAX data.
			var data = {};
			data['admin-page'] = settings.page;
			data['admin-tab'] = settings.tab;
			data['action'] = 'apl-ajax-action';
			data['nonce'] = settings.nonce;
			data['apl-ajax-action'] = settings.action;
			
			// Serialize data from form/input data.
			if( settings.inputs && settings.inputs.length > 0 )
			{
				data['input'] = {};
				for( var i in settings.inputs )
				{
					data['input'][settings.inputs[i]] = $(current_form).find('[name="'+settings.inputs[i]+'"]').val();
				}
			}
			else
			{
				data['input'] = $(current_form).serialize();
			}
			
			// Perform the AJAX request.
			$.ajax({
				type: 'POST',
				url: ajaxurl,
				data: data,
				dataType: 'json'
			})
			.done(function( data )
			{
				if( settings.cb_loop_end )
					settings.cb_loop_end( fi, settings, true, data );
				
				if( data.ajax )
				{
					data.ajax.cb_start = (data.ajax.cb_start ? window[data.ajax.cb_start] : null);
					data.ajax.cb_end = (data.ajax.cb_end ? window[data.ajax.cb_end] : null);
					data.ajax.cb_loop_start = (data.ajax.cb_loop_start ? window[data.ajax.cb_loop_start] : null);
					data.ajax.cb_loop_end = (data.ajax.cb_loop_end ? window[data.ajax.cb_loop_end] : null);
					perform_data_ajax( fi, settings, 0, data.ajax );
				}
				else
				{
					perform_form_ajax( fi+1, settings );
				}
			})
			.fail(function( jqXHR, textStatus )
			{
				if( settings.cb_loop_end )
					settings.cb_loop_end( fi, settings, false, { message: jqXHR.responseText+': '+textStatus } );
				
				perform_form_ajax( fi+1, settings );
			});
		}
		
		
		/**
		 * Perform an AJAX call for the current item in ajax.items at ajax index (ai).
		 * When the this AJAX call is complete, the next item is processed.
		 * @param  int    fi        The current index of the form being processed.
		 * @param  array  settings  The AJAX button's settings.
		 * @param  int    ai        The current index of the ajax input data.
		 * @param  array  ajax      The AJAX settings received via a previous AJAX request.
		 *
		 * AJAX key values:
         *  - page: The page that should process the request.
		 *  - tab: The tab that should process the request.
		 *  - action: The "action" to send to the page/tab.
		 *  - cb_start: The JS function to call when starting forms processing.
		 *  - cb_end: The JS function to call when completed forms processing.
		 *  - cb_loop_start: The JS function to call when starting a form's AJAX call.
		 *  - cb_loop_end: The JS function to call when completed a form's AJAX call.
		 *  - nonce: A unique nonce to send for security and validation purposes.
		 *  - items: An array of arrays which is the data to send as input for request.
		 */
		function perform_data_ajax( fi, settings, ai, ajax )
		{
			if( ai === 0 && ajax.cb_start )
				ajax.cb_start( ajax );
			
			if( ajax.items.length == 0 || ai >= ajax.items.length )
			{
				if( ajax.cb_end ) ajax.cb_end( ajax );
				perform_form_ajax( fi+1, settings );
				return;
			}
			
			if( ajax.cb_loop_start )
				ajax.cb_loop_start( fi, settings, ai, ajax );
			
			// Setup up AJAX data.
			var data = {};
			data['admin-page'] = ajax.page;
			data['admin-tab'] = ajax.tab;
			data['action'] = 'apl-ajax-action';
			data['nonce'] = ajax.nonce;
			data['apl-ajax-action'] = ajax.action;
			data['input'] = ajax.items[ai];
						
			// Perform the AJAX request.
			$.ajax({
				type: 'POST',
				url: ajaxurl,
				data: data,
				dataType: 'json'
			})
			.done(function( data )
			{
				if( ajax.cb_loop_end )
					ajax.cb_loop_end( fi, settings, ai, ajax, true, data );
				
				perform_data_ajax( fi, settings, ai+1, ajax );
			})
			.fail(function( jqXHR, textStatus )
			{
				if( settings.cb_loop_end )
					settings.cb_loop_end( index, settings, false, { message: jqXHR.responseText+': '+textStatus } );
				
				perform_data_ajax( fi, settings, ai+1, ajax );
			});
		}
		
		
		/**
		 * Setup each AJAX button's settings and click action.
		 */
		return this.each(function() {
			
			var settings = {
				'page'     : (($(this).attr('page')) ? $(this).attr('page') : null),
				'tab'      : (($(this).attr('tab')) ? $(this).attr('tab') : null),
				'action'   : (($(this).attr('action')) ? $(this).attr('action') : null),
				'forms'    : (($(this).attr('form')) ? $(this).attr('form').split(',') : []),
				'inputs'   : (($(this).attr('input')) ? $(this).attr('input').split(',') : null),
				'cb_start' : (($(this).attr('cb_start')) ? window[$(this).attr('cb_start')] : null),
				'cb_end'   : (($(this).attr('cb_end')) ? window[$(this).attr('cb_end')] : null),
				'cb_loop_start' : (($(this).attr('cb_loop_start')) ? window[$(this).attr('cb_loop_start')] : null),
				'cb_loop_end'   : (($(this).attr('cb_loop_end')) ? window[$(this).attr('cb_loop_end')] : null),
				'nonce'    : (($(this).attr('nonce')) ? $(this).attr('nonce') : null),
			};
			if(options) $.extend(settings, options);
			
			if( !settings.page || !settings.action || !settings.nonce )
				return;

			var form_objects = [];
			if( settings.forms.length === 1 && settings.forms[0] === '' )
			{
				form_objects.push( $(this).closest('form') );
			}
			else
			{
				for( var i in settings.forms )
				{
					var f = $('form.'+forms[i]);
					form_objects = $.merge( form_objects, f );
				}
			}
			
			if( form_objects.length == 0 )
			{
				form_objects = [ $(this).closest('form') ];
			}

			settings.forms = form_objects;
			
			if( settings.inputs && settings.inputs.length === 1 && settings.inputs[0] === '' )
			{
				settings.inputs = null;
			}
			
			$(this)
				.click( function() {
					perform_form_ajax( 0, settings );
					return false;
				});					
		});
	}
	
})( jQuery )
