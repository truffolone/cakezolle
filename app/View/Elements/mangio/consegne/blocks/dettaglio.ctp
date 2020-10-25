<?php if(!empty($consegna['spese'])):?>
	<?php echo $this->element('mangio/consegne/blocks/spese', array(
		'data' => $data,
		'spese' => $consegna['spese'], 
		'subtotale' => $consegna['totali']['subtotaleSpese'],
		'isMlvWeek' => $isMlvWeek,
		'isMlpWeek' => $isMlpWeek,
		'userCanShop' => $userCanShop,
		'displayMode' => $displayMode,
		'showInfoSpesa' => $showInfoSpesa
	));?>
<?php endif;?>

<?php if(!empty($consegna['af'])):?>
	<?php echo $this->element('mangio/consegne/blocks/af', array(
		'data' => $data,
		'af' => $consegna['af'], 
		'subtotale' => $consegna['totali']['subtotaleAf'],
		'isMlvWeek' => $isMlvWeek,
		'isMlpWeek' => $isMlpWeek,
		'userCanShop' => $userCanShop,
		'displayMode' => $displayMode
	));?>
<?php endif;?>

<?php if(!empty($consegna['ml'])):?>
	<?php echo $this->element('mangio/consegne/blocks/ml', array(
		'data' => $data,
		'ml' => $consegna['ml'], 
		'subtotale' => $consegna['totali']['subtotaleMl'],
		'isMlvWeek' => $isMlvWeek,
		'isMlpWeek' => $isMlpWeek,
		'userCanShop' => $userCanShop,
		'displayMode' => $displayMode
	));?>
<?php endif;?>




