<?php
	if( $message['Message']['is_read'] ) $row_class = 'read';
	else $row_class = 'unread';
?>
	
<div class="text-right mail-date <?php echo $row_class;?>">
	<?php echo $this->Kenn->niceDatetime($message['Message']['created']);?>
</div>
	

