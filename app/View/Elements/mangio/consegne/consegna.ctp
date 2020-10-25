<?php 
		
	$weekId = date('W', strtotime($data));
	$modificheDaConfermare = !empty($consegna['ml']['da_confermare']);

?>

<div id="settimana-<?php echo $weekId;?>" class="panel panel-default settimana">

	<div class="panel-heading settimana-heading" role="tab" id="heading<?php echo $weekId;?>" style="position:relative">
		<h4 class="panel-title">
			<i class="fa fa-calendar"></i>&nbsp;<?php echo $this->Ml->getDataConsegna($data);?>&nbsp; - &nbsp;Settimana&nbsp;<?=$weekId?>&nbsp;&nbsp;&nbsp;-&nbsp;<b><?php echo __('Ricevi');?></b>
		</h4>
		<?php 
			if($modificheDaConfermare) {
				echo $this->Html->image('mangio/ordine-provvisorio.png', array('class' => 'triangle-label'));
			}
		?>
	</div>	
	
	<div id="<?php echo $weekId;?>" class="" role="" aria-labelledby="heading<?php echo $weekId;?>">
		
		<div class="panel-body <?php echo $modificheDaConfermare ? 'consegna-provvisoria' : '';?>" style="position:relative">

				
			
				<!--dettaglio consegna-->
				<div class="dettagli-consegna">
			
					<?php
						echo $this->element('mangio/consegne/blocks/dettaglio', array(
							'data' => $data,
							'consegna' => $consegna,
							'isMlvWeek' => $isMlvWeek,
							'isMlpWeek' => $isMlpWeek,
							'userCanShop' => $userCanShop,
							'displayMode' => $displayMode,
							'showInfoSpesa' => $showInfoSpesa
						));
					?>
					<br>
				</div>
			
			
				<div class="row riga-consegne totale">
					<div class="col-xs-6 <?php echo $isMlpWeek ? 'testoMLP' : 'testoML';?> totale"><?php echo __("Totale Mercato Libero");?></div>
					<div class="col-xs-6 text-right">
						<span class="totale <?php echo $isMlpWeek == 'mlp' ? 'testoMLP' : 'testoML';?>">
							<?php 
								//echo $this->Number->currency($subtotale_spese+$subtotale_af+$subtotale_ml, 'EUR');
								// 22-03-2018: ora il totale visualizzato deve essere solo più quello di mercato libero
								echo $this->Number->currency($consegna['totali']['subtotaleMl'], 'EUR');
							?>
						</span>
					</div> 
				</div>
			
				<br/>
			
				<?php if($userCanShop && ($isMlvWeek || $isMlpWeek)):?>
				
				<?php
					$lblButtonMl = __('aggiungi prodotti da mercato libero del %s', array(date('d/m/Y', strtotime($data))));
				?>
				<div class="row consegne-actions-container">
					<div class="col-xs-12 text-right">
						<?php echo $this->Html->link($lblButtonMl, array('controller' => 'categorie_web', 'action' => 'index', '?' => array(
								'env' => $isMlvWeek ? 'mlv' : 'mlp'
						)), array('class' => 'btn white '.($isMlvWeek ? 'bkgML' : 'bkgMLP'),  'title' => __('aggiungi un prodotto da mercato libero a questa consegna')));?>  
					</div>
				</div>
					
				<?php endif;?>
			
	
		</div> <!-- /panel-body -->
		
	</div>

</div>

