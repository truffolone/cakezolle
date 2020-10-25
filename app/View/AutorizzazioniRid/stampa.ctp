<?php
	
	$this->assign('title', 'CONTRATTO RID');
	
?>
<br/><br/>

	<?php echo $this->element(
		'autorizzazione_rid_stampa',
		array(
			'rid' => $rid
		)
	);?>
