<?php
	$embed = $this->request->is('json'); // se embed vuol dire che sto visualizzando il messaging di un contratto direttamente nel contratto
?>

<?php if(!$embed):?>
<h5><?php echo __('CERCA PER CONTATTO');?></h5>
                            
<?php echo $this->Form->input('project', array(
	'id' => 'quick-view-project',
	'class' => 'chosen-select',
	'empty' => true,
	'options' => $attivitas,
	'label' => false,
	'data-baseurl' => Router::url(array('controller' => 'messages', 'action' => 'attivita'))
));?>
                            
<div class="space-25"></div>
<?php endif;?>
        
<?php
	$user = $this->Session->read('Auth.User');
?>        
                
<?php if( in_array($user[USER_ROLE_KEY], [ROLE_ADMIN, ROLE_BAM]) ):?>
<h5><?php echo __('SELECT FILTER');?></h5>    
                          
<ul class="category-list" style="padding: 0">
	<?php foreach($visible_tags as $tag):?>
        <li>
			<?php
				if(empty($attivita_id)) {
					// cliccando su un tag filtra x tag tutti i messaggi
					$url = Router::url(array('controller' => 'messages', 'action' => 'index', 'tag' => $tag['Messagingtag']['id']));
				}
				else {
					// cliccando su un tag filtra x tag i messaggi della conversazione di progetto
					$url = Router::url(array('controller' => 'messages', 'action' => 'attivita', $attivita_id, 'tag' => $tag['Messagingtag']['id']));
				}
			?>
			<a href="<?php echo $url;?>">
				<i class="fa fa-circle" style="color: <?php echo $tag['Messagingtag']['color'];?>"></i> <?php echo $tag['Messagingtag']['name'];?> 
			</a>
		</li>
    <?php endforeach;?>
        <li>
			<?php 
				if(empty($attivita_id)) {
					$url = Router::url(array('controller' => 'messages', 'action' => 'index', 'tag' => 'closed'));
				}
				else {
					$url = Router::url(array('controller' => 'messages', 'action' => 'attivita', $attivita_id, 'tag' => 'closed'));
				}
			?>
			<a href="<?php echo $url;?>">
				<i class="fa fa-circle" style="color: #D1DADE"></i> <?php echo __('Closed');?> 
			</a>
		</li>
</ul> 
<?php endif;?>
