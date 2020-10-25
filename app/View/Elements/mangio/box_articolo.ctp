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

	<div class="row" style="position:relative">
		<div class="box-articolo">
			<!--<div class="img col-xs-5" style="background-image:url('<?php echo FULL_BASE_URL . $this->webroot;?>img/mangio/test-cat.jpg')"></div>-->
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
			)));?>" class="img col-xs-5" style="background-image:url('<?php echo $img_url;?>'); <?php echo $extraImgStyle;?>"></a>
			<div class="dettagli col-xs-7">
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
				
				<div class="space-10"></div>
				
				<div class="fornitore">
					<?php echo $this->Html->link($articolo['Prodotto']['Fornitore']['FORNITORE'], array('action' => 'index', 'fornitore:'.$articolo['Prodotto']['Fornitore']['id']), array('class' => $isSoldOut ? 'opacizzato' : ''));?>
				</div>
								
			</div>
			<!-- prezzo + carrello + disponibilita -->
			<div class="col-xs-12 shopping-block-container">
				<?php echo $this->element('mangio/articolo/box_articolo_shopping_block', array(
					'articolo' => $articolo
				));?>
			</div>
			<!-- /prezzo + carrello + disponibilita -->
			<div class="col-xs-12 tags tags-container">
				<?php if(!empty($articolo['Prodotto']['TAGS'])):?>
				<i class="fa fa-tags"></i>
				<?php
					$tags = explode(';', $articolo['Prodotto']['TAGS']);
					for($i=1;$i<sizeof($tags)-1;$i++) { // perchè la struttura è ;tag1;tag2;...;tagN;
						echo '<a href="'.Router::url(array('controller' => 'articoli', 'action' => 'index', 'tag:'.$tags[$i])).'"><span class="label">'.$tags[$i].'</span></a>&nbsp;';
					}
				?>
				<?php endif;?>
			</div>
			
			<!--<?php if(in_array(strtolower($articolo['Articolo']['labelArticolo']), array('circa'))):?>
				<?php echo $this->Html->image('mangio/circa.png', array('class' => 'triangle-label'));?>
			<?php elseif(in_array(strtolower($articolo['Articolo']['labelArticolo']), array('circa*', 'circa *'))):?>
				<?php echo $this->Html->image('mangio/circa-alt.png', array('class' => 'triangle-label'));?>
			<?php elseif(in_array(strtolower($articolo['Articolo']['labelArticolo']), array('minimo'))):?>
				<?php echo $this->Html->image('mangio/minimo.png', array('class' => 'triangle-label'));?>
			<?php elseif(in_array(strtolower($articolo['Articolo']['labelArticolo']), array('minimo*', 'minimo *'))):?>
				<?php echo $this->Html->image('mangio/minimo-alt.png', array('class' => 'triangle-label'));?>
			<?php endif;?>-->
		</div>
		
		<?php if($this->Articolo->showPrenotaBadge($articolo)):?>
			<?php echo $this->Html->image('mangio/prenota.png', array('style' => 'position:absolute; top:0; left:0'));?>
		<?php endif;?>
		
	</div>

