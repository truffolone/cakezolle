
<?php echo $this->Html->css('BikesquareMessaging.datatables.min'); ?>  
<?php echo $this->Html->css('BikesquareMessaging.animate.min'); ?>
<?php echo $this->Html->css('BikesquareMessaging.prenota.messaging'); ?> 

<?php echo $this->Html->script('//cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js',array('inline' => false));?>
<?php echo $this->Html->css('//cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css');?>

<?php echo $this->Html->script("BikesquareMessaging.datatables.min", ['inline' => false]); ?>
	<?php echo $this->Html->script('BikesquareMessaging.spin.min', ['inline' => false]);?>
		
	<?php echo $this->Html->script('BikesquareMessaging.kenn.common', ['inline' => false]);?>
	<?php echo $this->Html->script('BikesquareMessaging.inspinia', ['inline' => false]);?>

	<?php
		// labels traducibili di datatables
		echo $this->Js->set('dtLabels', array(
			'mostra' => __('Mostra'),
			'record_per_pagina' => __('messaggi per pagina'),
			'cerca' => __('Cerca'),
			'zero_records' => __('Nessun messaggio da visualizzare'),
			'processing' => __('Attendere prego'),
			'loading' => __("Caricamento dati in corso. Attendere prego ..."),
			'empty' => __("Non è presente nessun messaggio"),
			'info' => __("Record da _START_ a _END_ di _TOTAL_"),
			'filtered' => __('filtrati di _MAX_ record'),
			'last' => __('Ultima'),
			'first' => __('Prima'),
			'next' => __('Succ'),
			'previous' => __('Prec'),
			'all' => __('Tutti')
 		));
		// la funzione che segue viene usata dai datatables per ottenere velocemente il blocco language
	?>
	<script>
		function getDtLanguage(lengthOpts) {
			var optsStr = '';
			for(var i=0;i<lengthOpts.length;i++) {
				var lValue = lengthOpts[i];
				var lLabel = lValue;
				if(lValue == -1) {
					lLabel = window.app.dtLabels.all;
				}
				optsStr += '<option value="' + lValue + '">' + lLabel + '</option>';
			}
			return {
				"sLengthMenu": window.app.dtLabels.mostra + ' <select>'+
				optsStr +
				'</select> ' + window.app.dtLabels.record_per_pagina,
				"sSearch": window.app.dtLabels.cerca,
				"sZeroRecords": window.app.dtLabels.zero_records,
				"sProcessing": window.app.dtLabels.processing, 
				"sLoadingRecords": window.app.dtLabels.loading,
				"sEmptyTable" : window.app.dtLabels.empty,
				"sInfo": window.app.dtLabels.info,
				"sInfoFiltered": " - " + window.app.dtLabels.filtered,
				"oPaginate": {
					"sLast": window.app.dtLabels.last,
					"sFirst": window.app.dtLabels.first,
					"sNext": window.app.dtLabels.next,
					"sPrevious": window.app.dtLabels.previous
				}
			};
		}
	</script>
