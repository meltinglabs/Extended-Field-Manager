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
			}, function(response){
				$('.main').html(response);
			});
		} else {
			$('.main').html('<p class="centered"><em>Select the type of field you want to create<br/>Once selected, the appopriate options will replace this text.</em></p>');
		}		
	});
});