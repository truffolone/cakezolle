<?php
	$loggedUser = $this->Session->read('Auth.User');
	$showAs = strtolower($articolo['Articolo']['showAs']);
	$isSoldOut = $showAs == null ? true : !$articolo['Articolo'][$showAs]['canShop']; // devo considerare il caso null per i visti di recente nella scheda articolo
	if($showAs == 'mlv') {
		$prezzoCls = 'testoML';
		$addBtnCls = 'bkgML';
	}
	else {
		$prezzoCls = 'testoMLP';
		$addBtnCls = 'bkgMLP';
	}
	
	$shopping = Configure::read('shopping_type.'.$showAs);
	$isCompact = $shopping == 'quick';
?>

<div id="box-articolo-shopping-data-<?php echo $articolo['Articolo']['id']; // setto l'id per eseguire il refresh ajax della disponibilità/possibilità di acquisto?>">
	
	<div class="row">
	
		<!-- prezzo -->
		<div class="<?php echo $isCompact ? 'col-md-4' : 'col-xs-4';?>">
		
			<div class="prezzo-container <?php echo $isSoldOut ? 'opacizzato' : '';?>">
				<span class="<?php echo $isSoldOut ? 'opacizzato' : 'prezzo '.$prezzoCls;?>" style="<?php echo $isCompact ? 'line-height:inherit' : '';?>"><?php echo $this->Number->currency($articolo['Articolo']['prezzo_porzione'], 'EUR');?></span>
				<?php if(!isset($isCompact) || $isCompact != true):?>
					<span class="<?php echo $isSoldOut ? 'opacizzato' : '';?>"><?php echo $articolo['Articolo']['suffissoPrezzo'];?></span>
				<?php endif;?>
			</div>
				
			<?php if(!isset($isCompact) || $isCompact != true):?>		
			<div class="prezzo-unitario">
				<?php //echo $articolo['Articolo']['labelPrezzoUnitario'];?>
				<?php echo $articolo['Articolo']['prefissoPrezzoUnitario'];?>
				<?php echo $this->Number->currency($articolo['Articolo']['prezzoUnitario'], 'EUR');?>/<?php echo $articolo['Articolo']['udm'];?>
			</div>
			<?php endif;?>
		
		</div>
		
		<!-- prezzo scontato -->
		<div class="<?php echo $isCompact ? 'col-md-4' : 'col-xs-4 text-center';?>">
		</div>
		
		<!-- carrello -->
		<div class="<?php echo $isCompact ? 'col-md-4' : 'col-xs-4 text-right';?>">
			<div class="space-10"></div>
			<?php if($showAs != null):// devo considerarlo per i visti di recente?>
			<?php if( $articolo['Articolo'][$showAs]['porzioni_disponibili'] > 0 ):?> 			
				<?php if( $articolo['Articolo']['DISP_ML'] ): // potrebbe essere disponibile come AF ma non come ML ?>
					<?php if($articolo['Articolo'][$showAs]['canShop']): // MLV/MLP aperto?>
						<?php if($loggedUser['group_id'] == CLIENTE_STANDARD):?>
						<a href="<?php echo Router::url(array('action' => 'aggiungi_ml', $articolo['Articolo']['id'], '1.json', '?' => array($showAs => 1)));?>"  class="btn aggiungi-al-carrello2 white <?php echo $addBtnCls;?>"><i class="fa fa-cart-arrow-down fa-2x"></i> <span style="font-size:14px">+1</span></a>
						<?php endif;?>
					<?php endif;?>
				<?php endif;?>
			<?php endif;?>
			<?php endif;?>
		</div>
	
	</div>
	
	<?php if($showAs != null):// devo considerarlo per i visti di recente?>
	<?php if($articolo['Articolo'][$showAs]['canShop']): // MLV/MLP aperto?>
		<!-- disponibilità -->
		<div class="row">
			<div class="col-xs-12">
				<?php echo $this->element('mangio/articolo/disponibilita', array(
					'articolo' => $articolo
				));?>
			</div>
		</div>
		<?php if(!$isCompact):?>
			<div class="space-5"></div>
		<?php endif;?>
	<?php endif;?>
	<?php endif;?>
			
</div> <!-- shopping data -->
