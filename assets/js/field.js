jQuery(document).ready(function($) {
    $('.sortable').sortable({
		placeholder: "highlight-drop"
	});
	$('#type').change(function(){
		if($(this).val() !== 'none'){
			$.post(ajaxurl,{
				'action' : 'efmrequest'
				,'handle' : 'getfield'
				,'value' : $(this).val()
				,'current_field' : $('#current_field_id').val() || 0
			}, function(response){
				$('.main').html(response);
			});
		} else {
			$('.main').html('<p class="centered"><em>Select the type of field you want to create<br/>Once selected, the appopriate options will replace this text.</em></p>');
		}		
	});
	
	/* Duplicable block of fields */
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