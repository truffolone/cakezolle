<?php 
	//$this->assign('title', strtoupper(__('Profilo e fatture')));
	$this->assign('title', 'MERCATO LIBERO'); // 2018-07: dovunque titolo fisso "mercato libero
	$this->assign('breadcrumb', $this->App->breadcrumb(
		array(
			'Home' => '#',
		),
		__('il tuo profilo')
	));
?>  

<div class="panel panel-default">
	<div class="panel-body">
		<i class="text-orange fa fa-phone-square"></i> <?php echo __("Se riscontri delle imprecisioni contattaci al %s", array('<a href="tel:'.__('+390692917616').'">'.__('06.9291.7616').'</a>'));?>
	</div>
</div>

<!--<div class="container-fluid">-->
					
	<div class="row">
		<div class="col-md-12">
			<h2 class="titolo-profilo">
				<?php echo ucfirst(strtolower(__('il tuo profilo')));?>
			</h2>
		</div>
	</div>
	
	<div class="row">
		<div class="col-md-12">
			
			<div class="panel panel-default">
				<div class="panel-body">
					<?php $clienteLoggato = $this->Session->read('clienteLoggato');?>
					<h4 class="text-orange"><?php echo $clienteLoggato['displayName'];?></h4>
					<hr/>
					<p><i class="fa fa-info-circle"></i> <?php echo __("Il tuo codice cliente è %s", array('<span class="text-orange">'.$clienteLoggato['id'].'</span>'));?></p>
				</div>
			</div>
			
			<div class="panel panel-default">
				<div class="panel-body">
					
					<div class="row">
						<div class="col-md-4"><span><i class="fa fa-info-circle"></i>  <?php echo __("Consegnamo le zolle a:");?></span></div>
						<div class="col-md-8">
							<span class="text-orange">
								<?php 
									$recapito = null;
									if( !empty($contratto['Cliente']['Indirizzo']) ) {
										foreach($contratto['Cliente']['Indirizzo'] as $indirizzo) {
											if(strtoupper($indirizzo['PRINCIPALE']) == 'SI') {
												$recapito = $indirizzo; // può esistere un solo indirizzo principale
											}
										}
									}
									
									if(empty($recapito)) {
										$recapito = $contratto['Cliente']['Indirizzo'][0]; // TODO: quale prendo se ce n'è più di uno ?
									}
									
									if( !empty($recapito) ) {
										//$recapitoStr = empty($recapito['NOME_CITOFONO']) ? $clienteLoggato['displayName'] : $recapito['NOME_CITOFONO'];
										$recapitoStr = $clienteLoggato['displayName'];
										$recapitoStr .= ' - ';
										$recapitoStr .= empty($recapito['INDIRIZZO']) ? '' : ucwords($recapito['INDIRIZZO']).' ';
										$recapitoStr .= empty($recapito['CAP']) ? '' : ucwords($recapito['CAP']).' ';
										$recapitoStr .= empty($recapito['CITTA']) ? '' : ucwords($recapito['CITTA']).' ';
										$recapitoStr .= empty($recapito['PROVINCIA']) ? '' : '('.$recapito['PROVINCIA'].')';
									}
									else $recapitoStr = __('Informazione non disponibile');
								?>
								<?php echo $recapitoStr;?>
							</span>
						</div>
					</div>
					
					<hr/>
					
					<div class="row">
						<div class="col-md-4"><span><i class="fa fa-info-circle"></i>  <?php echo __("Intestiamo le fatture a:");?></span></div>
						<?php if($clienteLoggato['isClienteFatturazione']):?>
						<div class="col-md-4">
						<?php else:?>
						<div class="col-md-8">
						<?php endif;?>
							<span class="text-orange">
								<?php 
									$cliente = $contratto['Cliente'];
								
									$intFatture = '';
									$intFatture .= empty($cliente['NOME_FATTURA']) ? '' : $cliente['NOME_FATTURA'].' - ';
									$intFatture .= empty($cliente['INDIRIZZO_FATTURA']) ? '' : $cliente['INDIRIZZO_FATTURA'].' ';
									$intFatture .= empty($cliente['CAP_FATTURA']) ? '' : $cliente['CAP_FATTURA'].' ';
									$intFatture .= empty($cliente['CITTA_FATTURA']) ? '' : $cliente['CITTA_FATTURA'].' ';
									$intFatture .= empty($cliente['PROVINCIA_FATTURA']) ? '' : '('.$cliente['PROVINCIA_FATTURA'].')';
								?>
								<?php echo $intFatture;?>
							</span>
							<br/><br/>
						</div>
						<?php if($clienteLoggato['isClienteFatturazione']):?>
						<div class="col-md-4 text-right">
							<?php echo $this->Html->link(__('Visualizza fatture'), array('action' => 'fatture'), array('class' => 'btn bkg-orange white'));?>
						</div>
						<?php endif;?>
					</div>
					
				</div>
			</div>
			
			<!--<div class="panel panel-default">
				<div class="panel-heading profilo-heading">
					<h4 class="panel-title"><?php echo __("Metodi di pagamento attivati");?></h4>
				</div>
				<div class="panel-body">
					
						<div class="row">
							<div class="col-xs-8"><?php echo __("Carta di credito");?> <span class="text-orange">XXXX-XXXX-XXXX-4567</span></div>
							<div class="col-xs-4 text-right"><span class="text-green"><?php echo __("ATTIVO");?></span></div>
						</div>
						<hr/>
					
						<div class="row">
							<div class="col-xs-8"><?php echo __("Carta di credito");?> <span class="text-orange">XXXX-XXXX-XXXX-1234</span></div>
							<div class="col-xs-4 text-right"><span class="text-light-grey"><?php echo __("NON ATTIVO");?></span></div>
						</div>
						<hr/>
					
						<div class="row">
							<div class="col-xs-8"><?php echo __("Autorizzazione RID. Iban");?> <span class="text-orange">IT55546676778</span></div>
							<div class="col-xs-4 text-right"><span class="text-light-grey"><?php echo __("NON ATTIVO");?></span></div>
						</div>
						<hr/>
				
				</div>
			</div>-->
			
		</div>
	</div>

<!--</div>-->

<!-- settimana ordine + le 2 settimane successive (tutto in read only) -->
<div class="row">
		<div class="col-md-12">
			<h2 class="titolo-zolle">
				<?php echo ucfirst(strtolower(__('le tue zolle')));?>
			</h2>
		</div>
	</div>
<div id="consegne-container">
<?=$consegne?>
</div>
