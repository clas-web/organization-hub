

jQuery(document).ready(
	function()
	{
		// setup all Upload Table Extended Data.
		jQuery('table.orghub-upload tr').UploadExtendedData();
	}
);


( function( $ ) {
	
	/**
	 * UploadExtendedData
	 * 
	 * The UploadExtendedData jQuery plugin ...
	 * 
	 * @package    apl
	 * @author     Crystal Barton <cbarto11@uncc.edu>
	 */
	$.fn.UploadExtendedData = function()
	{
		/**
		 * Setup each Upload Table Extended Data.
		 */
		return this.each(function() {
			
			// get the extended-data div and button.
			var extended = $(this).find('div.extended-data');
			var button = $(this).find('div.more-data-button');
			
			if( !extended || !button ) return;
			
			// hide the extended data div.
			$(extended).hide();
			
			// extended data div will toggle show/hide when clicked.
			$(button).click(
				function()
				{
					$(extended).toggle();
				}
			);
			
		});
	}

})( jQuery )

