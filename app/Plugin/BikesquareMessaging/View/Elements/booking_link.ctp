<?php $loggedUser = $this->Session->read('Auth.User');?>
<?php if(in_array($loggedUser[USER_ROLE_KEY], [ROLE_ADMIN, ROLE_BAM])):?>

<?php if( ($contract['Attivita']['Persona']['EMail'] || $contract['Attivita']['Persona']['Cellulare']) && $contract['Attivita']['destination_id']):?>
<button type="button" style="white-space:normal" id="copy-booking-link" class="btn btn-default btn-sm btn-block"><i class="fa fa-copy"></i> Copia nel clipboard il link per riprendere la prenotazione</button>
<?php
	$query = [];
	if($contract['Attivita']['Persona']['Cellulare']) {
		$query[] = ['cellulare' => $contract['Attivita']['Persona']['Cellulare']];
	}
	if($contract['Attivita']['Persona']['EMail']) {
		$query[] = ['email' => $contract['Attivita']['Persona']['EMail']];
	}
	$bookingLink = $this->Html->url(['plugin' => null, 'controller' => 'contacts', 'action' => 'prenota', $contract['Attivita']['Destination']['slug'], '?' => $query], true);
?>

<?php echo $this->Html->script("copy.to.clipboard",array('inline' => false)); ?>
<?php $this->Html->scriptStart(array('inline' => false));?>
$('#copy-booking-link').click(function(){
	copyTextToClipboard('<?php echo $bookingLink;?>');
});
<?php $this->Html->scriptEnd();?>

<?php endif;?>

<?php endif;?>
