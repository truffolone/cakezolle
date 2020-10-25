<?php
	if( $message['Message']['is_read'] ) $row_class = 'read';
	else $row_class = 'unread';
?>
<div class="<?php echo $row_class;?>">
	<?php if(!empty($message['Attachment'])):?>
		<i class="fa fa-paperclip"></i>
	<?php endif;?>
</div>
	

