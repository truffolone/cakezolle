<?php
	$this->assign('title', 'Info contratto pagamenti ricorrenti');
?>

<div class="row">
		<div class="col-md-12">
			<h2 class="titolo-profilo">
				Intestatario del contratto Pagamenti ricorrenti - Carta di credito
			</h2>
		</div>
	</div>

<div class="row">

	<div class="col-xs-6">
		Codice contratto
	</div>
	<div class="col-xs-6 text-orange">
		<?php echo $carta['CartaDiCredito']['id_contratto'];?>
	</div>

</div>

<br/>

<div class="row">

	<div class="col-xs-6">
		Codice cliente
	</div>
	<div class="col-xs-6 text-orange">
		<?php echo $carta['Cliente']['id'];?>
	</div>

</div>

<br/>

<div class="row">

	<div class="col-xs-6">
		Cognome e nome
	</div>
	<div class="col-xs-6 text-orange">
		<?php echo $carta['Cliente']['displayName'];?>
	</div>

</div>

<br/>

<div class="row">

	<div class="col-xs-6">
		Data attivazione contratto
	</div>
	<div class="col-xs-6 text-orange">
		<?php if( empty($carta['CartaDiCredito']['signed']) ):?>
			Contratto non attivo
		<?php else:?>
			<?php echo $carta['CartaDiCredito']['signed'];?>
		<?php endif;?>
	</div>

</div>
