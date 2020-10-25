<?php if(sizeof($addebiti) > 0):?>

	<?php
		// suddividi gli addebiti in base a tipo e stato
		$primi_pagamenti = array();
		$ricorrenti_attivi = array(); // in realtà ce n'è uno solo ...
		$ricorrenti_non_attivi = array();
		
		foreach($addebiti as $a) {
			if($a['type'] == PRIMO_PAGAMENTO) $primi_pagamenti[] = $a;
			else {
				if($a['active'] == 1) $ricorrenti_attivi[] = $a;
				else $ricorrenti_non_attivi[] = $a;
			}
		}
	?>

	
	<?php if(!empty($ricorrenti_attivi)):?>
		<?php echo $this->element('lista_addebiti', array(
			'titolo' => 'Spese attive',
			'addebiti' => $ricorrenti_attivi,
			'metodi' => $metodi,
		));?>
	<?php endif;?>
	

	<?php if(!empty($primi_pagamenti)):?>
		<?php echo $this->element('lista_addebiti', array(
			'titolo' => 'Spese attivazione contratto carta',
			'addebiti' => $primi_pagamenti,
			'metodi' => $metodi,
		));?>
	<?php endif;?>
 
	<?php if(!empty($ricorrenti_non_attivi)):?>
		<?php echo $this->element('lista_addebiti', array(
			'titolo' => 'Spese NON attive',
			'addebiti' => $ricorrenti_non_attivi,
			'metodi' => $metodi,
		));?>
	<?php endif;?>


<?php else:?>

	Nessuna spesa trovata

<?php endif;?>
