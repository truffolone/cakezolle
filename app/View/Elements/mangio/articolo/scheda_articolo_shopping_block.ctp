<div id="scheda-articolo-shopping-data-<?php echo $articolo['Articolo']['id']; // setto l'id per eseguire il refresh ajax della disponibilità/possibilità di acquisto?>">

	<?php 
		$loggedUser = $this->Session->read('Auth.User');
		$showAs = strtolower($articolo['Articolo']['showAs']);
		$isSoldOut = !$articolo['Articolo'][$showAs]['canShop'];
		$shopEnv = strtoupper($showAs);
		$MlEnv = ClassRegistry::init($shopEnv == 'MLV' ? 'Mlv' : 'Mlp');
		try {
			$dataAcquisto = $MlEnv->getDataAcquisto();
		}
		catch(Exception $e) {
			$dataAcquisto = null;
		}
	?>
			
	<?php if( $articolo['Articolo'][$showAs]['porzioni_disponibili'] > 0 ):?>
		<?php if( $articolo['Articolo']['DISP_ML'] ): // potrebbe essere disponibile come AF ma non come ML ?>
			<?php if($articolo['Articolo'][$showAs]['canShop']): // MLV/MLP aperto?>
			<?php if($loggedUser['group_id'] == CLIENTE_STANDARD):?>
				<div class="row">
					<div class="col-md-12">
						<?php if($dataAcquisto):?>
						<div class="label label-danger" style="font-size:85%"><?php echo __("Stai acquistando per la consegna di %s", array($this->Ml->getDataConsegna($dataAcquisto['date'])));?></div>
						<br/><br/>
						<?php endif;?>
						<a href="<?php echo Router::url(array('action' => 'aggiungi_ml', $articolo['Articolo']['id'], '1.json', '?' => array(
							$showAs => 1
						)));?>"  class="btn aggiungi-al-carrello2 white <?php echo $showAs == 'mlp' ? 'bkgMLP' : 'bkgML';?>"><i class="fa fa-cart-arrow-down fa-3x"></i> <span style="font-size:18px">+1</span></a>
					</div>
				</div>
			<?php endif;?>
			<?php endif;?>
		<?php endif;?>
	<?php endif;?>
	
	<?php if($articolo['Articolo'][$showAs]['canShop']):?>
		<br/>	
		<?php echo $this->element('mangio/articolo/disponibilita', array(
			'articolo' => $articolo
		));?>
	<?php endif;?>
	
</div>
