<div class="row riga-consegne totale">
	<div class="col-xs-12">
		<h4><?php echo __('MERCATO LIBERO');?></h4>
	</div>
</div>

<?php
	if(!isset($mostraRiepilogoML)) $mostraRiepilogoML = false;
?>

<?php if(sizeof($ml['attivi']) > 0):?>

	<?php if($displayMode == 'summary'):?>
	
		<div class="row riga-consegne" style="position: relative">
			
			<div class="col-xs-12 col-md-8">
				<div>
				<?php echo $this->Html->link(__('Mercato Libero'), array('controller' => 'consegne', 'action' => 'index'));?>
				</div>
				<div class="confezione">
					<?php echo sizeof($ml);?> <?php echo sizeof($ml) > 1 ? 'prodotti' : 'prodotto';?> da mercato libero
				</div>
			</div>
			<div class="col-xs-6 col-md-2">
			</div>
			<div class="col-xs-6 col-md-2 text-right">
				<span class="<?php echo $isMlpWeek ? 'testoMLP' : 'testoML';?>"><?php 
					echo $this->Number->currency($subtotale, 'EUR');
				?></span>
			</div>
			

		</div>
	
	<?php else:?>
	
		<?php
			$recordsDaConfermare = array_map(function($r){
				return $r['record']['DATA'].'-'.$r['record']['ID_ARTICOLO'].'-'.$r['record']['record_type'];
			}, $ml['da_confermare']);
		?>
	
		<?php foreach($ml['attivi'] as $m):?>
		<?php			
			$articolo = $m['articolo'];
			$m = $m['record'];
			$nomeCompleto = $articolo['Articolo']['nomeCompleto'];
			$isRigaProvvisoria = in_array($m['DATA'].'-'.$m['ID_ARTICOLO'].'-'.$m['record_type'], $recordsDaConfermare);
			$qta = $m['qty']; 	
			
			if( in_array($m['record_type'], ['MLV', 'MLP']) && 
				$articolo['Articolo']['quantita_minima'] > 0 && // altrimenti articolo a qty = 0 ma perche' non acquistabile
				$m['qty'] == 0 ) continue; // record completamente rimosso, fa parte delle modifiche ma non e' visibile
			
			// 2019-01-17: i dettagli (disponibilità e pulsanti carrello) devono essere accessibili solo se il record
			// e' effettivamente di tipo MLV o MLP e se la settimana e' quella giusta
			$isEditable = $userCanShop && (($m['record_type'] == 'MLV' && $isMlvWeek) || ($m['record_type'] == 'MLP' && $isMlpWeek));
		?>
		<div class="row riga-consegne <?php echo $isRigaProvvisoria ? 'riga-provvisoria' : '';?>" style="position: relative">

			<div class="col-xs-12 col-md-8">
				<div>
				<?php
					//rendo linkabile l'articolo solo se effettivamente disponibile per il cliente
					if($articolo['Articolo']['mlv']['canShow'] || $articolo['Articolo']['mlp']['canShow']) { 
						echo $this->Html->link($nomeCompleto, array('controller' => 'articoli', 'action' => 'view', $m['ID_ARTICOLO'], '?' => array(
							$isMlpWeek ? 'mlp' : 'mlv' => 1
						)));
					}
					else {
						echo $nomeCompleto;
					}
				?>
				<?php if($isEditable):?>
					&nbsp;
					<?php
						if($m['PREZZO'] > 0) {
							echo $this->Html->link(
								'rimuovi', 
								array('controller' => 'articoli', 'action' => 'rimuovi_ml', $m['ID_ARTICOLO'].'.json', '?' => array(
									$isMlvWeek ? 'mlv' : 'mlp' => 1
								)),
								array('class' => 'rimuovi-ml btn btn-xs white '.($isMlvWeek ? 'bkgML' : 'bkgMLP'), 'title' => __('clicca qui per rimuovere questo articolo di mercato libero da questa consegna'))
							);
						}
						else {
							//è un regalo, non visualizzare il pulsante per rimuovere ma una label
							echo '<span class="text-orange bold-text">'.__('INTEGRAZIONE').'</span>';
						}
					?>
				<?php endif;?>
				</div>
				<div class="confezione">
					<?php
						if(!empty($articolo['Articolo']['confezione'])) {
							echo __('Confezione').' '.$articolo['Articolo']['confezione'];
						}
					?>
					&nbsp;&nbsp;&nbsp;
					<?php echo $articolo['Articolo']['prefissoPrezzoUnitario'];?>
					<?php echo $this->Number->currency($articolo['Articolo']['prezzoUnitario'], 'EUR');?>/<?php echo $articolo['Articolo']['udm'];?>
				</div>
				
				<?php if($isEditable):?>
				<div class="disponibilita">
					<?php echo $this->element('mangio/articolo/disponibilita_in_ordine', array(
						'articolo' => $articolo,
						'recordML' => $m,
						'showAs' => $isMlvWeek ? 'mlv' : 'mlp'
					));?>
				</div>
				<?php else: // al posto della disponibilità visualizza una specifica label per il record?>
				<?php if( !in_array($m['record_type'], ['MLV', 'MLP']) ):?>
						<span class="label label-info"><?php echo __("INTEGRAZIONE");?></span>
					<?php endif;?>
					<?php if( $m['record_type'] == 'MLP' && $isMlvWeek ):?>
						<span class="label label-info"><?php echo __("PRENOTAZIONE SETTIMANA PRECEDENTE");?></span>
					<?php endif;?>
				<?php endif;?>
				
			</div>
			
			<div class="col-xs-6 col-md-2">
				
				<?php if($isEditable):?>
					<?php if($m['PREZZO'] > 0): //se il prezzo è 0 ML è un regalo o sostituzione quindi non posso fare modifiche?>
						<?php if($qta > 0): 
							//le sostituzioni hanno uno o più campi non valorizzati che non consentono di calcolare prezzo e/o qta a carrello 
								//-> semplicemente non consento di gestirli e visualizzo la qtà originale con la sua unità di misura?>
							<span><?php echo __("Qtà:");?></span>
							<div class="input-group" style="width:120px;">
								<a href="<?php echo Router::url(array('controller' => 'articoli', 'action' => 'aggiungi_ml', $articolo['Articolo']['id'], '-1.json', '?' => array(
									$isMlvWeek ? 'mlv' : 'mlp' => 1
								)));?>" class="riduci-qta-ml input-group-addon"><i class="fa fa-minus"></i></a>
								<input type="text" value="<?php echo $qta?>" class="qta form-control" placeholder="1" aria-describedby="" style="text-align:center" readonly="readonly">
								<a href="<?php echo Router::url(array('controller' => 'articoli', 'action' => 'aggiungi_ml', $articolo['Articolo']['id'], '1.json', '?' => array(
									$isMlvWeek ? 'mlv' : 'mlp' => 1
								)));?>" class="aumenta-qta-ml input-group-addon"><i class="fa fa-plus"></i></a>
							</div>
						<?php else:?>
							<?php echo __("Qtà:");?> <?php echo $m['QUANTITA'].' '.$m['UDM'];?>
						<?php endif;?>
					<?php else: //regalo, non visualizzo il carrello?>
						<div class="carrello-content">
							<?php if($qta > 0): 
								// le sostituzioni hanno uno o più campi non valorizzati che non consentono di calcolare prezzo e/o qta a carrello 
								//-> semplicemente non consento di gestirli e visualizzo la qtà orignale con la sua unità di misura?>
								<?php echo __("Qtà:");?> <?php echo $qta;?>
							<?php else:?>
								<?php echo __("Qtà:");?> <?php echo $m['QUANTITA'].' '.$m['UDM'];?>
							<?php endif;?>
						</div>
					<?php endif;?>				
				<?php else: // sostituzione, record da fattoria oppure prenotazione di una settimana precedente?>
					<div class="carrello-content">
						<?php if($qta > 0): 
							//le sostituzioni hanno uno o più campi non valorizzati che non consentono di calcolare prezzo e/o qta a carrello
							// -> semplicemente non consento di gestirli e visualizzo la qtà originale con la sua unità di misura?>
							<?php echo __("Qtà:");?> <?php echo $qta;?>
						<?php else:?>
							<?php echo __("Qtà:");?> <?php echo $m['QUANTITA'].' '.$m['UDM'];?>
						<?php endif;?>
					</div>
				<?php endif;?>
			</div>
			
			
			<div class="col-xs-6 col-md-2 text-right">
				<span class="<?php echo $isMlvWeek ? 'testoML' : 'testoMLP';?>"><?php 
					if($m['PREZZO'] > 0) echo $this->Number->currency($m['PREZZO'], 'EUR');
					else echo __('COSTO ZERO');
				?></span>
				<span><?php echo $articolo['Articolo']['suffissoPrezzo'];?></span>
				
				<?php if($isRigaProvvisoria):?>
				<span style="display:inline-block; width:50px" class="hidden-xs hidden-sm"></span>
				<?php endif;?>
			</div>
			
			
			<?php 
				if($isRigaProvvisoria) {
					echo $this->Html->image($isMlvWeek ? 'mangio/ordine-provvisorio.png' : 'mangio/ordine-provvisorio.png'/*'mangio/prenota-alt.png'*/, array('class' => 'triangle-label'));
				}
			?>
			
		</div>
		<?php endforeach;?>
		
	<!--<div class="row riga-consegne subtotale">
		<div class="col-xs-6 text-orange"><?php echo __("Subtotale");?></div>
		<div class="col-xs-6 text-right"><span class="prezzo"><?php echo $this->Number->currency($subtotale, 'EUR');?></div> 
	</div>-->
	
	<?php endif;?>

<?php endif;?> 
