<?php
    $showAs = strtolower($articolo['Articolo']['showAs']);
	$isSoldOut = $articolo['Articolo'][$showAs]['porzioni_disponibili'] == 0 && $articolo['Articolo'][$showAs]['canShop'];
	$extraImgStyle = '';
	if($isSoldOut) {
		$extraImgStyle = 'opacity:0.5; filter: grayscale(100%);';
	}
?>

<!-- immagine articolo -->
		<div class="col-md-6 width-from-6-to-4-on-sidemenu-closed width-from-4-to-6-on-sidemenu-open">
			<?php
				$base_url = FULL_BASE_URL;
				if( strpos($base_url, "staging") !== FALSE ) {
					// sono sull'ambiente di test, setto di default zolle.impronta48.it per fare vedere le immagini prese dall'ambiente di produzione
					$base_url = 'https://zolle.impronta48.it';
				}
				$img_url = $base_url . $this->webroot.'img/'.$articolo['Articolo']['resizedSchedaImage'];
			?>
			<img src="<?php echo $img_url;?>" class="img-responsive" style="<?php echo $extraImgStyle;?>"/><!--<?php echo $this->Html->image($articolo['Articolo']['resizedSchedaImage'], array('class' => 'img-responsive'));?>-->
			<div class="alert alert-warning alert-dismissible" role="alert">
				<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
				<span class="text-sm"><?php echo __("le foto degli articoli possono non corrispondere esattamente alla confezione venduta");?></span>
			</div>
			
			<?php if($this->Articolo->showPrenotaBadge($articolo)):?>
				<?php echo $this->Html->image('mangio/prenota.png', array('style' => 'position:absolute; top:0; left:15px'));?>
			<?php endif;?>
		</div>
		<!-- /immagine articolo -->
		
		<!-- dettagli articolo -->
		<div class="col-md-6 width-from-6-to-8-on-sidemenu-closed width-from-8-to-6-on-sidemenu-open">
			
			<!--<div class="container-fluid">-->
				
				<!-- prezzo -->	
				<div class="row">
					<div class="col-md-12">
						<div class="<?php echo $isSoldOut ? 'opacizzato' : '';?>"><?php echo $articolo['Articolo']['prefissoPrezzo'];?></div>
						<div>
							<span class="prezzo <?php echo $showAs == 'mlp' ? 'testoMLP' : 'testoML';?> <?php echo $isSoldOut ? 'opacizzato' : '';?>"><?php echo $this->Number->currency($articolo['Articolo']['prezzo_porzione'], 'EUR');?></span>
							<span class="<?php echo $isSoldOut ? 'opacizzato' : '';?>">
								<?php echo $articolo['Articolo']['suffissoPrezzo'];?>
							</span>
						</div>
					</div>
				</div>
				<!-- /prezzo -->
				
				<!-- prezzo unitario -->
				<div class="row">
					<div class="col-md-12">	
						<div>
							<span class="prezzo-unitario">
								<?php //echo $articolo['Articolo']['labelPrezzoUnitario'];?>
								<?php echo $articolo['Articolo']['prefissoPrezzoUnitario'];?>
								<?php echo $this->Number->currency($articolo['Articolo']['prezzoUnitario'], 'EUR');?>/<?php echo $articolo['Articolo']['udm'];?>
							</span>
						</div>
					</div>
				</div>
				<!-- /prezzo unitario -->
				
				<hr/>
				
				<!-- confezione -->
				<?php if(!empty($articolo['Articolo']['confezione'])):?>
				<div class="row">
					<div class="col-xs-5 text-grey"><i class="fa fa-inbox"></i> <?php echo __("Confezione");?></div>
					<div class="col-xs-7"><?php echo $articolo['Articolo']['confezione'];?></div>
				</div>
				<hr/>
				<?php endif;?>
				<!-- /confezione -->
				
				<!-- porzione -->
				<?php if(!empty($articolo['Articolo']['PORZIONE'])):?>
				<div class="row">
					<div class="col-xs-5 text-grey"><i class="fa fa-certificate"></i> <?php echo __("Porzione");?></div>
					<div class="col-xs-7"><?php echo $articolo['Articolo']['PORZIONE'];?></div>
				</div>
				<hr/>
				<?php endif;?>
				<!-- /porzione -->

				<!-- produttore -->
				<div class="row">
					<div class="col-xs-5 text-grey"><i class="fa fa-leaf"></i> <?php echo __("Produttore");?></div>
					<div class="col-xs-7"><?php echo $this->Html->link($articolo['Prodotto']['Fornitore']['FORNITORE'], array('action' => 'index', 'fornitore:'.$articolo['Prodotto']['Fornitore']['id']));?></div>
				</div>
				<!-- /produttore -->
				<hr/>
				
				<?php if( !empty($articolo['Prodotto']['ORIGINE']) ):?>
				<!-- origine -->
				<div class="row">
					<div class="col-xs-5 text-grey"><i class="fa fa-map-marker"></i> <?php echo __("Origine");?></div>
					<div class="col-xs-7"><?php echo $articolo['Prodotto']['ORIGINE'];?></div>
				</div>
				<!-- /origine -->
				<hr/>
				<?php endif;?>
			
				<?php echo $this->element('mangio/articolo/scheda_articolo_shopping_block', array(
					'articolo' => $articolo
				));?>
				
				<div class="row">
					<div class="col-md-12" id="confezioni_articolo_corrente">
						<?=$this->element('mangio/confezioni_articolo_corrente', ['articolo' => $articolo]);?>						
					</div>
				</div>
				
			<!--</div>-->
				
		</div> 
