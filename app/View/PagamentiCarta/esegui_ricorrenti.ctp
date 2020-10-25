<?php
	$this->assign('title', 'Spese ricorrenti');
	$this->assign('subtitle', 'Esecuzione pagamenti ricorrenti - Carte di Credito');

?>  

<h4 class="header blue">
	<div class="row">
	<span class="col-sm-8">
		Esecuzione pagamenti ricorrenti - Carte di Credito
	</span>
	</div>
</h4> 

<div class="row">
	<div class="col-md-12">
		Numeri pagamenti da eseguire: <b><?php echo $num_addebiti;?></b>
	</div>
</div>
<br/>

<div class="row">
	<div class="col-md-6">
		<a href="#" id="start-pagamenti" class="btn btn-sm btn-success">Avvia l'esecuzione dei pagamenti</a>
		
		<div id="modal-progress-pagamenti" class="alert alert-warning" style="display:none;">
			Attendere prego, operazione in corso ...
		</div>
	</div>
	<div class="col-md-6">
		<?php echo $this->Html->link('Abbandona', '/', array('class' => 'btn btn-sm btn-danger pull-right'));?>
	</div>
</div>

<br/><br/>
<div class="row">

	<div class="col-md-6 text-center">
		<div id="pagamenti-carta-progress" class="easy-pie-chart percentage" data-percent="0" data-size="120">
			<span class="percent">0</span>%
		</div>
	</div>
	
	<div class="col-md-6">
		<h4 class="text-primary" id="rimanenti" style="display:none">
			<span id="num-rimanenti"></span> rimanenti
		</h4>
	</div>

</div>

									

<?php $this->Html->scriptStart(array('inline' => false)); ?>

jQuery(function($) {
	$('.easy-pie-chart.percentage').each(function(){
		var $box = $(this).closest('.infobox');
		var barColor = $(this).data('color') || (!$box.hasClass('infobox-dark') ? $box.css('color') : 'rgba(255,255,255,0.95)');
		var trackColor = barColor == 'rgba(255,255,255,0.95)' ? 'rgba(255,255,255,0.25)' : '#E2E2E2';
		var size = parseInt($(this).data('size')) || 50;
		$(this).easyPieChart({
			barColor: '#3983C2',
			trackColor: trackColor,
			scaleColor: false,
			lineCap: 'butt',
			lineWidth: parseInt(size/10),
			animate: /msie\s*(8|7|6)/.test(navigator.userAgent.toLowerCase()) ? false : 1000,
			size: size
		});
	});
				
	var addebiti = jQuery.parseJSON('<?php echo $addebiti;?>');
	var currAddebitoIndex = 0;
	
	$('#start-pagamenti').click(function(e){
		
		e.preventDefault();
		$(this).hide();
		$('#modal-progress-pagamenti').show();
		
		$('#num-rimanenti').text( addebiti.length );
		$('#rimanenti').show();
		
		paga();
		
	});
	
	// recursive function
	function paga(index) {
		$.get( "<?php echo Router::url(array('controller' => 'pagamenti_carta', 'action' => 'paga_ajax'), true);?>/" + addebiti[currAddebitoIndex].Addebito.id + ".json", function() {
			//alert( "success" );
		})
		.done(function() {
			//alert( "second success" );
		})
		.fail(function() {
			//alert( "error" );
		})
		.always(function() {
			
			currAddebitoIndex++;
			
			// update display
			percentage = parseInt(100 * currAddebitoIndex / addebiti.length);
			$('#num-rimanenti').text( parseInt(addebiti.length - currAddebitoIndex) );
			$('#pagamenti-carta-progress').data('easyPieChart').update(percentage);
			$('.percent', '#pagamenti-carta-progress').text(percentage);
			
			if( currAddebitoIndex < addebiti.length  ) {
				paga();
			}
			else {
				window.location = "<?php echo Router::url('/', true);?>";
			}
			
		});
	}
	
});

<?php $this->Html->scriptEnd(); ?>
