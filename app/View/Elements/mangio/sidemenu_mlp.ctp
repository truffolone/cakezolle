<h4><?php echo __("Ricevi la prossima prenotazione");?></h4>
<?php
	$Mlp = ClassRegistry::init('Mlp');
	try {
		$dataAcquisto = $Mlp->getDataAcquisto();
		$dataStr = $this->Ml->getDataConsegna($dataAcquisto['date']);
	}
	catch(Exception $e) {
		try {
			$prossimaApertura = $Mlp->getProssimaDataApertura();
			$dataStr = __('Informazione non disponibile. La Prenotazioni Articoli è chiusa. Potrai nuovamente prenotare prodotti a partire da %s', array($this->Ml->getGiornoAperturaMlAsStr($prossimaApertura, 'MLP', $Mlp->getCurrGiornoConsegna()))); 
		}
		catch(Exception $e) {
			$dataStr = "n.d.";
		}
	}
	
?>
<h5 id="data-prossimo-ml"><?php echo $dataStr;?></h5>	
