<?php
	$Mlv = ClassRegistry::init('Mlv');
	$Mlp = ClassRegistry::init('Mlp');
	
	$dataAcquistoMlv = $Mlv->getDataAcquistoOrNull();
	$dataAcquistoMlp = $Mlp->getDataAcquistoOrNull();
	
	$dataAcquistoMlvStr = date('d/m/Y', strtotime($dataAcquistoMlv));
	$dataAcquistoMlpStr = date('d/m/Y', strtotime($dataAcquistoMlp));
	
	$qtyMlv = $dataAcquistoMlv ? $Mlv->getCurrentQty($articolo['Articolo']['id']) : 0;
	$qtyMlp = $dataAcquistoMlp ? $Mlp->getCurrentQty($articolo['Articolo']['id']) : 0;
?>

<?php if( $qtyMlv > 0 ):?>
	<hr/>
	<?php if($qtyMlv == 1):?>
		<span class="text-orange"><?php echo __("Hai %s confezione di questo prodotto nella prossima spesa del %s", array('<span class="badge">1</span>', $dataAcquistoMlvStr));?></span>
	<?php else:?>
		<span class="text-orange"><?php echo __("Hai %s confezioni di questo prodotto nella prossima spesa del %s", array('<span class="badge">'.$qtyMlv.'</span>', $dataAcquistoMlvStr));?></span>
	<?php endif;?>
<?php endif;?>

<?php if( $qtyMlp > 0 ):?>
	<hr/>
	<?php if($qtyMlp == 1):?>
		<span class="text-orange"><?php echo __("Hai %s confezione di questo prodotto nella prossima prenotazione del %s", array('<span class="badge">1</span>', $dataAcquistoMlpStr));?></span>
	<?php else:?>
		<span class="text-orange"><?php echo __("Hai %s confezioni di questo prodotto nella prossima prenotazione del %s", array('<span class="badge">'.$qtyMlp.'</span>', $dataAcquistoMlpStr));?></span>
	<?php endif;?>
<?php endif;?>

