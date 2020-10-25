<?php if(sizeof($spese) > 0):?>
	
	<?php foreach($spese as $s):?>
	<?php 
		$tipoSpesa = $s['dettagli']['TipoSpesa'];
		$nome = $tipoSpesa['DESCRIZIONE_ESTESA'];	
		$descr = $nome.' | '.str_replace('"','\'',$tipoSpesa['TESTO']);					
	?>

	
	<div class="row riga-consegne" style="position: relative">
		
		<div class="col-xs-12 col-md-8">
			<div>
				<?php echo $this->Html->link($nome, '#', array('class' => '', 'title' => $tipoSpesa['DESCRIZIONE'].' | '.$tipoSpesa['TESTO']));?>
				
				<?php
					// 2018-01-31: per i clienti starndard a mercato libero aperto se sono ne 'il tuo ordine' mostro pulsante
					// con cui visualizzare il dettaglio spesa nella prossima consegna
					if($userCanShop && ($isMlvWeek || $isMlpWeek) && $displayMode == 'detail' && $showInfoSpesa):
				?>
					<div>
						<?php echo $this->Html->link(__('Guarda dentro la Zolla'), array('controller' => 'spese', 'action' => 'info', $tipoSpesa['TIPO_SPESA'].'.json'), array(
							'data-nome-zolla' => $nome,
							'class' => 'btn btn-xs bkg-orange white dettaglio-zolla'
						));?>
					</div>
				<?php endif;?>
				
				
			</div>
		</div>
		
		
		<div class="col-xs-6 col-md-2">
			Qtà: <?php echo $s['qty'];?>
		</div>
		
			
		<div class="col-xs-6 col-md-2 text-right">
			<span class="prezzo">
				<?php echo $this->Number->currency($tipoSpesa['PREZZO'], 'EUR');?>
			</span>
		</div>
	
	</div>
	
	<?php endforeach;?>

<!--<div class="row riga-consegne subtotale">
	<div class="col-xs-6 text-orange"><?php echo __("Subtotale");?></div>
	<div class="col-xs-6 text-right"><span class="prezzo"><?php echo $this->Number->currency(0, 'EUR');?></div> 
</div>-->

<?php endif;?> 
