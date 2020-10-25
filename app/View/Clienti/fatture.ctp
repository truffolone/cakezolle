<?php 
	$this->assign('title', strtoupper('Profilo e fatture'));
	$this->assign('breadcrumb', $this->App->breadcrumb(
		array(
			'Home' => '#',
			__('il tuo profilo') => Router::url(array('action' => 'profilo'))
		),
		__('elenco fatture')
	));
?>  

<?php
	// su staging le fatture non ci sono ...
	$baseUrl = Router::url('/', true);
	if( strpos($baseUrl, 'staging') !== false ):
?>
<div class="alert alert-info"><p>Gentile cliente, a causa di un problema tecnico le fatture non sono accessibili.</p><p>Ci scusiamo per il disagio</p></div>
<?php endif;?>

<div class="panel panel-default">
	<div class="panel-body">
		<i class="text-orange fa fa-phone-square"></i> <?php echo __("Se riscontri delle imprecisioni contattaci al %s", array('<a href="tel:'.__('+390692917616').'">'.__('06.9291.7616').'</a>'));?>
	</div>
</div>

<!--<div class="container-fluid">-->
					
	<div class="row">
		<div class="col-md-12">
			<h2 class="titolo-profilo">
				<?php echo ucfirst(strtolower(__("elenco fatture")));?>
			</h2>
		</div>
	</div>
	
	<div class="row">
		<div class="col-md-12">
			
			
				
			<?php if(empty($fatture)):?>
				<span><?php echo __("Non ci sono fatture da visualizzare");?></span>
			<?php else:?>
				<div class="list-group">
					<?php
						$m['01'] = 'Gennaio';
						$m['02'] = 'Febbraio';
						$m['03'] = 'Marzo';
						$m['04'] = 'Aprile';
						$m['05'] = 'Maggio';
						$m['06'] = 'Giugno';
						$m['07'] = 'Luglio';
						$m['08'] = 'Agosto';
						$m['09'] = 'Settembre';
						$m['10'] = 'Ottobre';
						$m['11'] = 'Novembre';
						$m['12'] = 'Dicembre';
					?>
					<?php foreach($fatture as $fattura):?>
					<div class="list-group-item">
						<div class="row">
							<div class="col-xs-8"><h4 class="text-grey"><i class="fa fa-calendar"></i> <?php echo $fattura['giorno'];?> <?php echo $m[ $fattura['mese'] ];?> <?php echo $fattura['anno'];?></h4></div>
							<div class="col-xs-4 text-right"><a class="text-orange" href="<?php echo Router::url(array('action' => 'download_fattura', $fattura['anno'].$fattura['mese'], $fattura['nome']));?>" title="<?php echo __("clicca qui per scaricare la fattura");?>"><i class="fa fa-2x fa-file-pdf-o"></i></a></div>
						</div>
					</div>
					<?php endforeach;?>
				</div>
			<?php endif;?>
			
		</div>
	</div>


	<div class="row">
		<div class="col-md-12">
			<h2 class="titolo-profilo">
				<?php echo ucfirst(strtolower(__("riepiloghi")));?>
			</h2>
		</div>
	</div>
	
	<div class="row">
		<div class="col-md-12">
			
			
				
			<?php if(empty($riepiloghi)):?>
				<span><?php echo __("Non ci sono riepiloghi da visualizzare");?></span>
			<?php else:?>
				<div class="list-group">
					<?php foreach($riepiloghi as $riepilogo):?>
					<div class="list-group-item">
						<div class="row">
							<div class="col-xs-8"><h4 class="text-grey"><i class="fa fa-calendar"></i> <?php echo $m[ $riepilogo['mese'] ];?> <?php echo $riepilogo['anno'];?></h4></div>
							<div class="col-xs-4 text-right"><a class="text-orange" href="<?php echo Router::url(array('action' => 'download_fattura', $riepilogo['anno'].$riepilogo['mese'], $riepilogo['nome']));?>" title="<?php echo __("clicca qui per scaricare il riepilogo");?>"><i class="fa fa-2x fa-file-pdf-o"></i></a></div>
						</div>
					</div>
					<?php endforeach;?>
				</div>
			<?php endif;?>
			
		</div>
	</div>


<!--</div>-->

