<div class="attachment">
<?php foreach($attachments as $a):?>

	<?php 
		if(!isset($a['Attachment'])) $a['Attachment'] = $a;
	?>

	<div class="file-box">
        <div class="file">
            
                <span class="corner"></span>

                <div class="icon">
                     <i class="fa fa-<?php echo $this->Kenn->getFontAwesomeIcon($a['Attachment']['path']);?>"></i>
                </div>
                <div class="file-name">
                    <?php echo $this->Html->link(
						$a['Attachment']['name'], 
						array('controller' => 'attachments', 'action' => 'download', $a['Attachment']['id']), 
						array('class' => '', 'escape' => false, 'title' => __('Visualizza %s', array($a['Attachment']['name']))));
					?>
                </div>
        </div>
    </div>
    
<?php endforeach;?>  
</div>
