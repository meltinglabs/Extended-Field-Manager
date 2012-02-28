jQuery(document).ready(function($) {
    $('.sortable').sortable({
		placeholder: "highlight-drop"
	});
	$('.item-edit').click(function(){
		var item = $(this).parentsUntil('.sortable', 'li');
		item.children('.menu-item-settings').slideToggle('fast', function(){
			item.toggleClass('menu-item-edit-active');
		});
	})
});