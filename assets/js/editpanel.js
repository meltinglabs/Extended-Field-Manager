jQuery(document).ready(function($) {
    $('.sortable').sortable({
		placeholder: "highlight-drop"
	});
	$('.item-edit').click(function(){
		var item = $(this).parentsUntil('.sortable', 'li');
		item.children('.menu-item-settings').slideToggle('fast', function(){
			item.toggleClass('menu-item-edit-active');
		});
	});
	$('#type').change(function(){
		$.post(ajaxurl,{
			'action' : 'efmrequest'
			,'handle' : 'getfield'
			,'value' : $(this).val()
		}, function(response) {
			console.log('Got this from the server: ' + response);
			console.log(response);
		});
	});
});