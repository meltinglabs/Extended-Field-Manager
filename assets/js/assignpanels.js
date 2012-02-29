jQuery(document).ready(function($) {
    $('#available, #assigned').sortable({
		placeholder: 'highlight-drop'
		,connectWith: '.sortable'
		,dropOnEmpty: true
	});
	$( '#available, #assigned' ).disableSelection();
});