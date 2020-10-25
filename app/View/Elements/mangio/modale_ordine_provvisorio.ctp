<?php

	$Mlv = ClassRegistry::init('Mlv');
	$Mlp = ClassRegistry::init('Mlp');
	
	// raggruppa le operazioni provvisorie relative a MLV e MLP (altre operazioni anche se previste non ci sono)
	$operazioniProvvisorieMLV = array();
	$operazioniProvvisorieMLP = array();
	foreach($operazioniProvvisorie as $o) {
		if($o['Ml']['record_type'] == 'MLV') {
			$operazioniProvvisorieMLV[] = $o;
		}
		else if($o['Ml']['record_type'] == 'MLP') {
			$operazioniProvvisorieMLP[] = $o;
		}
	}
?>

<?php if(!empty($operazioniProvvisorieMLV)):?>
	<h4><?php echo __('Acquisto del %s', array(date('d/m/Y', strtotime($Mlv->getDataAcquistoOrNull()))));?></h4>
	<?php foreach($operazioniProvvisorieMLV as $o):?>

		<?php if($o['Ml']['qty'] > 0):?>
			<?php
				$str_conf = $o['Ml']['qty'] == 1 ? __('confezione') : __('confezioni');
			?>
			<p><?php echo __("Hai acquistato %s %s di %s", array('<span class="text-orange">'.$o['Ml']['qty'].'</span>', $str_conf, '<span class="text-orange">'.$o['Ml']['PRODOTTO'].'</span>'));?></p>
			<hr/>
		<?php else:?>
			<?php
				$str_conf = $o['Ml']['qty'] == -1 ?__( 'confezione') : __('confezioni');
			?>
			<p><?php echo __("Hai rimosso %s %s di %s", array('<span class="text-orange">'.abs($o['Ml']['qty']).'</span>', $str_conf, '<span class="text-orange">'.$o['Ml']['PRODOTTO'].'</span>'));?></p>
			<hr/>
		<?php endif;?>
	<?php endforeach;?>
<?php endif;?>

<?php if(!empty($operazioniProvvisorieMLP)):?>
	<h4><?php echo __('Acquisto del %s', array(date('d/m/Y', strtotime($Mlp->getDataAcquistoOrNull()))));?></h4>
	<?php foreach($operazioniProvvisorieMLP as $o):?>

		<?php if($o['Ml']['qty'] > 0):?>
			<?php
				$str_conf = $o['Ml']['qty'] == 1 ? __('confezione') : __('confezioni');
			?>
			<p><?php echo __("Hai aggiunto %s %s di %s", array('<span class="testoMLP">'.$o['Ml']['qty'].'</span>', $str_conf, '<span class="testoMLP">'.$o['Ml']['PRODOTTO'].'</span>'));?></p>
			<hr/>
		<?php else:?>
			<?php
				$str_conf = $o['Ml']['qty'] == -1 ?__( 'confezione') : __('confezioni');
			?>
			<p><?php echo __("Hai rimosso %s %s di %s", array('<span class="testoMLP">'.abs($o['Ml']['qty']).'</span>', $str_conf, '<span class="testoMLP">'.$o['Ml']['PRODOTTO'].'</span>'));?></p>
			<hr/>
		<?php endif;?>
	<?php endforeach;?>
<?php endif;?>


<?php if(empty($operazioniProvvisorie)):?>
	<br/>
	<?php echo __("Non hai operazioni da confermare");?>
	<br/>
<?php endif;?>

	


