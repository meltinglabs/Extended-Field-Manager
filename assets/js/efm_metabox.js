jQuery(document).ready(function($) {
    $('.efm_box .remove').live('click', function(b,e){
		var me = $(this).parent('li');
		var box = me.parent('ul')
		var sibs = me.siblings().length;
		if( sibs <= 1 ){
			box.addClass('single');
		}
		me.remove();
		return false;
	});
    $('.efm_box .add').live('click', function(b,e){
		var me = $(this).parent('li');
		var box = me.parent('ul');
		me.clone().appendTo(box).children('input').removeAttr('id').val('');
		if( box.hasClass('single') ){
			box.removeClass('single');		
		}			
		return false;
	});
});