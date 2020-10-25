$(document).ready(function() {

	$(document).on('click', function(e){
			
		if( $(e.target).hasClass('apri-ordine-provvisorio') || $(e.target).parent().hasClass('apri-ordine-provvisorio') ) {
			
			e.preventDefault();
			
			$('#ordine-provvisorio').modal('show');
			
		}
		
	});

}); 
