<?php 
	
	$modificheDaConfermareInQualcheSettimana = false;

	$idx = 0;
	foreach($consegne as $data => $consegna) {
		$modificheDaConfermareInQualcheSettimana = $modificheDaConfermareInQualcheSettimana || !empty($consegna['ml']['da_confermare']);
		echo $this->element('mangio/consegne/consegna', array(
			'data' => $data,
			'consegna' => $consegna,
			'isMlvWeek' => $data == $dataAcquistoMlv,
			'isMlpWeek' => $data == $dataAcquistoMlp,
			'userCanShop' => $userCanShop,
			'displayMode' => $displayMode,
			'showInfoSpesa' => $idx == 0
		));
		$idx++;
	}
?>

<?php if($modificheDaConfermareInQualcheSettimana):?>
<div style="position:fixed; bottom:0; left:0; background: #fff; z-index: 100000; padding:10px; width:100%; box-shadow: 0 -8px 6px -8px #787878;" class="text-center">
	<a href="<?php echo Router::url(array('controller' => 'consegne', 'action' => 'finalizza'));?>" class="btn white bkg-green finalizza-modifiche" style="text-transform:uppercase"> <?php echo __("conferma le modifiche");?></a> 
</div>
<?php endif;?>
