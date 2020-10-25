<h4><?php echo __("Ricevi il prossimo mercato libero");?></h4>
<?php
	$Mlv = ClassRegistry::init('Mlv');
	try {
		$dataAcquisto = $Mlv->getDataAcquisto();
		$dataStr = $this->Ml->getDataConsegna($dataAcquisto['date']);
	}
	catch(Exception $e) {
		try {
			$prossimaApertura = $Mlv->getProssimaDataApertura();
			$dataStr = __('Informazione non disponibile. Il Mercato Libero è chiuso. Potrai nuovamente ordinare prodotti a partire da %s', array($this->Ml->getGiornoAperturaMlAsStr($prossimaApertura, 'MLV', $Mlv->getCurrGiornoConsegna()))); 
		}
		catch(Exception $e) {
			$dataStr = "n.d.";
		}
	}
	
?>
<h5 id="data-prossimo-ml"><?php echo $dataStr;?></h5>	
