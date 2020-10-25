<?php 
	if( !empty($articolo['Prodotto']['Sottocategoria']['CategoriaWeb']) ) { 
		$categoria =  $articolo['Prodotto']['Sottocategoria']['CategoriaWeb']['NOME'];
		$categoriaUrl = Router::url(array('controller' => 'articoli', 'action' => 'index', 'categoria_web:'.$articolo['Prodotto']['Sottocategoria']['CategoriaWeb']['id']));
	}
	else {
		$categoria = $articolo['Prodotto']['Sottocategoria']['SOTTOCATEGORIA'];
		$categoriaUrl = '#';
	}
	
	$showAs = strtolower($articolo['Articolo']['showAs']);
	$isSoldOut = $articolo['Articolo'][$showAs]['porzioni_disponibili'] == 0 && $articolo['Articolo'][$showAs]['canShop'];
	$extraImgStyle = '';
	if($isSoldOut) {
		$extraImgStyle = 'opacity:0.5; filter: grayscale(100%);';
	}
	
	//$this->assign('title', strtoupper($categoria));
	$this->assign('title', 'MERCATO LIBERO'); // 2018-07: dovunque titolo fisso "mercato libero
	$this->assign('breadcrumb', $this->App->breadcrumb(
		array(
			'Home' => '#', 
			__('Mercato libero') => Router::url(array('controller' => 'categorie_web', 'action' => 'index', '?' => array(
				$showAs => 1
			))),
			strtoupper($categoria) => $categoriaUrl,
		),
		$articolo['Articolo']['nomeCompleto']
	));
?>  


<!--<div class="container-fluid">-->
					
	<div class="row">
		<div class="col-md-12">
			<h2 class="<?php echo $showAs == 'mlp' ? 'titolo-mlp' : 'titolo-ml';?> <?php echo $isSoldOut ? 'opacizzato' : '';?>">
				<?php echo $articolo['Articolo']['nomeCompleto']?>
				<!--<?php if(!empty($articolo['Articolo']['labelArticolo'])):?>
					<span class="label-articolo white <?php echo $showAs == 'mlp' ? 'bkgMLP' : 'bkgML';?>"><?php echo $articolo['Articolo']['labelArticolo'];?></span> 
				<?php endif;?>-->
			</h2>
		</div>
	</div>
					
	<div class="row scheda-articolo" id="scheda-articolo-<?=$articolo['Articolo']['id']?>">
		<?= $this->element('mangio/scheda_articolo', [
			'articolo' => $articolo
		]);?>
	</div>
	
	<br/>
	
	<!--tecnica-->
	<?php if(!empty($articolo['Prodotto']['TECNICA'])):?>
		<?php
			// 2016-11-02: come da richiesta del cliente faccio vedere il campo "TECNICA"
			// solo se Sottocategoria.ABILITATO = (0, 1, 5)
			if( in_array($articolo['Prodotto']['Sottocategoria']['ABILITATO'], array(0, 1, 5)) ):
		?>
		<hr/>
		<div class="row">
			<div class="col-md-12">
				<?php echo $articolo['Prodotto']['TECNICA'];?>
			</div>
		</div>
		<hr/>
		<?php endif;?>
	<?php endif;?>	
	<!-- /tecnica -->
	
	<!--descrizione commerciale -->
	<?php if(!empty($articolo['Prodotto']['DESC_COMM'])):?>
	<div class="row">
		<div class="col-md-12">
			<div class="jumbotron">
				<?php echo str_replace("\r\n", "<br/>", $articolo['Prodotto']['DESC_COMM']);?>
			</div>
		</div>
	</div>	
	<?php endif;?>	
	<!-- /descrizione commerciale -->
	
	<!--ingredienti-->
	<?php if(!empty($articolo['Prodotto']['INGREDIENTI'])):?>
		<hr/>
		<div class="row">
			<div class="col-md-12">
				<span class="text-grey"><i class="fa fa-info-circle"></i> <?php echo __("Ingredienti");?></span>
				<br><br>
				<?php if( preg_match('/[A-Z]{4,}/', $articolo['Prodotto']['INGREDIENTI']) !== FALSE ):?>
					<div class="alert alert-info">
						<?php echo __('Gli ingredienti che contengono allergeni sono segnati in maiuscolo');?>
					</div>
				<?php endif;?>
				<?php echo str_replace("\r\n", "<br/>", $articolo['Prodotto']['INGREDIENTI']);?>
			</div>
		</div>
		<hr/>
	<?php endif;?>	
	<!-- /ingredienti -->
	
	<!--dettagli bio-->
	<?php if(!empty($articolo['Prodotto']['DETTAGLI_BIO'])):?>
		<hr/>
		<div class="row">
			<div class="col-md-12">
				<span class="text-grey"><i class="fa fa-info-circle"></i> <?php echo __("Dettagli Bio");?></span>
				<br><br>
				<?php echo str_replace("\r\n", "<br/>", $articolo['Prodotto']['DETTAGLI_BIO']);?>
			</div>
		</div>
		<hr/>
	<?php endif;?>	
	<!-- /dettaglio bio -->
	
	<!--allergeni-->
	<?php if(!empty($articolo['Prodotto']['ALLERGENI'])):?>
		<hr/>
		<div class="row">
			<div class="col-md-12">
				<span class="text-grey"><i class="fa fa-info-circle"></i> <?php echo __("Allergeni");?></span>
				<br><br>
				<?php echo str_replace("\r\n", "<br/>", $articolo['Prodotto']['ALLERGENI']);?>
			</div>
		</div>
		<hr/>
	<?php endif;?>	
	<!-- /allergeni -->
	
	<!--impiego-->
	<?php if(!empty($articolo['Prodotto']['IMPIEGO'])):?>
		<hr/>
		<div class="row">
			<div class="col-md-12">
				<span class="text-grey"><i class="fa fa-info-circle"></i> <?php echo __("Impiego");?></span>
				<br><br>
				<?php echo str_replace("\r\n", "<br/>", $articolo['Prodotto']['IMPIEGO']);?>
			</div>
		</div>
		<hr/>
	<?php endif;?>	
	<!-- /impiego -->
	
	<!--indicazioni d'uso-->
	<?php if(!empty($articolo['Articolo']['NOME'])):?>
		<hr/>
		<div class="row">
			<div class="col-md-12">
				<span class="text-grey"><i class="fa fa-info-circle"></i> <?php echo __("Indicazioni d'uso");?></span>
				<br><br>
				<?php echo str_replace("\r\n", "<br/>", $articolo['Articolo']['NOME']);?>
			</div>
		</div>
		<hr/>
	<?php endif;?>	
	<!-- /indicazioni d'uso -->
	
	<!--valori nutrizionali-->
	<?php if(!empty($articolo['Prodotto']['VALNUT'])):?>
		<hr/>
		<div class="row">
			<div class="col-md-12">
				<span class="text-grey"><i class="fa fa-info-circle"></i> <?php echo __("Valori Nutrizionali");?></span>
				<br><br>
				<?php echo str_replace("\r\n", "<br/>", $articolo['Prodotto']['VALNUT']);?>
			</div>
		</div>
		<hr/>
	<?php endif;?>	
	<!-- /valori nutrizionali -->
	
	
	
	<?php if($articolo['Articolo']['QUANTITA_PRECISIONE'] == 1):?>
	<br/>
	<div class="row">
		<div class="col-md-12">
			<div class="alert alert-warning alert-dismissible" role="alert">
				<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
				<span class="text-sm"><?php echo __("* la porzione e quindi l'importo finale può variare leggermente rispetto a quanto indicato");?></span>
			</div>
		</div>
	</div>
	<?php endif;?>
	
	<!-- tags -->
	<?php if(!empty($articolo['Prodotto']['TAGS'])):?>
	<hr/>
	<div class="row">
		<div class="col-md-12 tags tags-container tags-scheda-articolo-container">
				<i class="fa fa-tags"></i>
				<?php
					$tags = explode(';', $articolo['Prodotto']['TAGS']);
					for($i=1;$i<sizeof($tags)-1;$i++) { // perchè la struttura è ;tag1;tag2;...;tagN;
						echo '<a href="'.Router::url(array('controller' => 'articoli', 'action' => 'index', 'tag:'.$tags[$i])).'"><span class="label">'.$tags[$i].'</span></a>&nbsp;';
					}
				?>
		</div>
	</div>
	<hr/>
	<?php endif;?>
	<!-- /tags -->
	
	<!-- visti di recente -->
	<?php if(sizeof($visti_di_recente) > 0):?>
	<br/><br/>
	<h3 class="<?php echo $showAs == 'mlp' ? 'titolo-mlp' : 'titolo-ml';?>"><?php echo __("Visti di recente");?></h3>
	<?php 
		$Mlv = ClassRegistry::init('Mlv');
		$Mlp = ClassRegistry::init('Mlp');
		$dataAcquistoMlv = $Mlv->getDataAcquistoOrNull();
		$dataAcquistoMlp = $Mlp->getDataAcquistoOrNull();
		echo $this->element('mangio/lista_articoli', array('articoli' => $visti_di_recente, 'isMlAperto' => $dataAcquistoMlv || $dataAcquistoMlp));
	?>
	<?php endif;?>
	<!-- /visti di recente -->
	
<!--</div>-->

<?php echo $this->Html->script('mangio/ml.1-1', array('inline' => FALSE));?>

<!-- mantengo fissa la dimensione dell'immagine dell'articolo sia quando il menu laterale è visibile sia quando è nascosto --> 
<?php $this->Html->scriptStart(array('inline' => false)); ?>

<?php $this->Html->scriptEnd(); ?>
