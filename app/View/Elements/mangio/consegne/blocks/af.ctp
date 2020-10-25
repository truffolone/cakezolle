<?php if(sizeof($af) > 0):?>

	<?php foreach($af as $a):?>
	<?php
		$articolo = $a['articolo'];
		$nomeCompleto = $articolo['Articolo']['nomeCompleto'];
		$periodicita = $this->ConsegneHelper->getDescrizionePeriodicita($a['record']['PERIODICITA']); 
		if(isset($a['record']['DATA_FINE'])) {
			if($a['record']['DATA_FINE'] == date('Y-m-d', strtotime('next Friday', strtotime($a['record']['DATA_INIZIO'])))) $periodicita = __('solo questa settimana');
		}	
		if( $articolo['Articolo']['quantita_minima'] > 0 ) {
			$qta = intval((float)$a['record']['QUANTITA']/$articolo['Articolo']['quantita_minima']);			
		}
		else {
			$qta = null;
		}
	?>

	<div class="row riga-consegne" style="position: relative">
		
		<div class="col-xs-12 col-md-8">
			<div>
				<?php
					//rendo linkabile l'articolo solo se effettivamente disponibile per il cliente
					if($articolo['Articolo']['mlv']['canShow'] || $articolo['Articolo']['mlp']['canShow']) { 
						echo $this->Html->link($nomeCompleto, array('controller' => 'articoli', 'action' => 'view', $a['record']['ID_ARTICOLI']));
					}
					else {
						echo $nomeCompleto;
					}
				?>
			</div>
			<div class="confezione">
				<?php
					if(!empty($articolo['Articolo']['confezione'])) {
						echo 'Confezione '.$articolo['Articolo']['confezione'];
					}
				?>
				&nbsp;&nbsp;&nbsp;
				<?php echo $articolo['Articolo']['prefissoPrezzoUnitario'];?>
				<?php echo $this->Number->currency($articolo['Articolo']['prezzoUnitario'], 'EUR');?>/<?php echo $articolo['Articolo']['udm'];?>
			</div>
			<!-- blocco periodicità disabilitato - richiesta Zolle 2015/10 -->
			<!--<div>
				<span>Periodicità:&nbsp;</span> 
				<span class="text-orange"><?php echo $periodicita;?></span>
			</div>-->
		</div>
		
		<div class="col-xs-6 col-md-2">
			<div class="carrello-content">
				Qtà: <?php echo $qta == null ? 'n.d.' : $qta;?>
			</div>
		</div>
		
		<div class="col-xs-6 col-md-2 text-right">
			<span class="prezzo"><?php 
				echo $qta == null ? 'n.d.' : $this->Number->currency($qta*$articolo['Articolo']['prezzo_porzione'], 'EUR');
			?></span>
			<span><?php echo $articolo['Articolo']['suffissoPrezzo'];?></span>
		</div>
		
	</div>
	<?php endforeach;?>

<!--<div class="row riga-consegne subtotale">
	<div class="col-xs-6 text-orange"><?php echo __("Subtotale");?></div>
	<div class="col-xs-6 text-right"><span class="prezzo"><?php echo $this->Number->currency($subtotale, 'EUR');?></div> 
</div>-->
	
<?php endif;?> 
