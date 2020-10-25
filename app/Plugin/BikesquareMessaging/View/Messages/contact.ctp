<?php $user = $this->Session->read('Auth.User');?>
<?php if(in_array($user[USER_ROLE_KEY], [ROLE_ADMIN, ROLE_BAM, ROLE_RENTER, ROLE_EON_PLUS])):?>
<?=$this->element('BikesquareMessaging.header_contratto', [
    'contact_id' => $contact['Contact']['id']
]);?>
<?php endif;?>

<div class="row">
	<div class="col-md-3">
		<?php if($contact['Contact']['legendastati_id'] == 0):?>
			<h4><?=__('Richiesta informazioni del %s', $contact['Contact']['created']);?></h4>
		<?php else:?>
			<?= $this->element('BikesquareMessaging.dettagli_prenotazione', ['contract' => $contact]);?> 
		<?php endif;?>
	</div>
	
	<div class="col-md-9">
		<h3><i class="fa fa-comments-o"></i> <?=__('Chats disponibili');?></h3>
		<?php if(in_array($user[USER_ROLE_KEY], [ROLE_ADMIN, ROLE_BAM])):?>
			<button type="button" class="btn btn-primary btn-block" onClick="alert('Coming soon: Admin e Bam possono creare tutte le chat che desiderano per il contratto corrente')"><i class="fa fa-plus-circle"></i> Nuova Chat</button>
		<?php endif;?>
		<?= $this->element('BikesquareMessaging.contact_chats', ['contact_id' => $contact['Contact']['id'], 'showTitle' => false]);?> 
	</div>
</div>


