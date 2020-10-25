<?php
	$showAs = strtolower($articolo['Articolo']['showAs']);
	$isSoldOut = $showAs == null ? true : !$articolo['Articolo'][$showAs]['canShop']; // devo considerare anche il caso null per i visti di recente nella scheda articolo
	$extraImgStyle = '';
	if($isSoldOut) {
		$extraImgStyle = 'opacity:0.5; filter: grayscale(100%);';
	}
	if($showAs == 'mlv') {
		$confezioneCls = 'confezione-mlv';
	}
	else {
		$confezioneCls = 'confezione-mlp';
	}
?>

<style>
.box-articolo-compact .prezzo {
	font-size: 24px !important;
}

.box-articolo-compact .shopping-block-container {
	background: #fff;
}
</style>

	<div class="row box-articolo-compact" style="position:relative">
		<div class="col-xs-2">
			<?php
				$base_url = FULL_BASE_URL;
				if( strpos($base_url, "staging") !== FALSE ) {
					// sono sull'ambiente di test, setto di default zolle.impronta48.it per fare vedere le immagini prese dall'ambiente di produzione
					$base_url = 'https://zolle.impronta48.it';
				}
				$img_url = $base_url . $this->webroot.'img/'.$articolo['Articolo']['resizedBoxImage'];
			?>
			<a href="<?php echo Router::url(array('action' => 'view', $articolo['Articolo']['id'], '?' => array(
				$showAs => 1
			)));?>" style="width:100%; height:120px; display:block; background-repeat: no-repeat; background-position: center center; background-size: cover; background-image:url('<?php echo $img_url;?>'); <?php echo $extraImgStyle;?>"></a>
		</div>
		<div class="col-xs-5">
					<div class="space-10"></div>
					<div class="nome">
						<?php echo $this->Html->link($articolo['Articolo']['nomeCompleto'], array('action' => 'view', $articolo['Articolo']['id'], '?' => array(
							$showAs => 1
						)), array('class' => $isSoldOut ? 'opacizzato' : ''));?>
					</div>
					
					<div class="space-10"></div>
					
					<div class="confezione <?php echo $confezioneCls;?> <?php echo $isSoldOut ? 'opacizzato' : '';?>">
					<?php if(!empty($articolo['Articolo']['confezione'])):?>
						<?php echo __("Confezione");?> <?php echo $articolo['Articolo']['confezione'];?>
					<?php else:?>
						<?php echo '&nbsp;&nbsp;&nbsp;'; // per avere cmq lo stesso riempimento in verticale?>
					<?php endif;?>
					</div>
		</div>
		<div class="col-xs-5 text-right">
					<!-- prezzo + carrello + disponibilita -->
					<div class="shopping-block-container">
						<?php echo $this->element('mangio/articolo/box_articolo_shopping_block', array(
							'articolo' => $articolo,
							'compact' => true
						));?>
					</div>
					<!-- /prezzo + carrello + disponibilita -->
		</div>
		<?php if($this->Articolo->showPrenotaBadge($articolo)):?>
			<?php echo $this->Html->image('mangio/prenota.png', array('style' => 'position:absolute; top:0; left:0; height:50px'));?>
		<?php endif;?>
	</div>
	<div class="space-10"></div>

