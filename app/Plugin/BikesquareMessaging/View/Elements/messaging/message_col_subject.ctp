<?php
	if( $message['Message']['is_read'] ) $row_class = 'read';
	else $row_class = 'unread';
?>
<div class="mail-subject <?php echo $row_class;?>">
	<div class="">
	<?php 
		$subjectPrefix = '';
				
		if(!$message['Message']['is_first']) $subjectPrefix .= 'Re: ';
				
		echo $this->Html->link($subjectPrefix.$message['Conversation']['subject'], array('controller' => 'messages', 'action' => 'conversation', $message['Conversation']['id']));
	?>
	</div>
	
	<!-- tags veri e propri-->
	<?php
		$tags = array();
		foreach($message['Conversation']['Tag'] as $t) {
			if(empty($attivita_id)) { 
				// cliccando su un tag filtro x tag sul messaging generale
				$tag_url = Router::url(array('controller' => 'messages', 'action' => 'index', 'tag' => $t['id']));
			}
			else { 
				// cliccando su un tag filtro x tag sul messaging di progetto 
				$tag_url = Router::url(array('controller' => 'messages', 'action' => 'attivita', $attivita_id, 'tag' => $t['id']));
			}
			// specifico in ogni caso label-warning per la struttura della label
			$tags[] = '<a href="'.$tag_url.'"><span class="label label-warning" style="background:'.$t['color'].'">'.$t['name'].'</span></a>';
		}
		// aggiungo in visualizzazione l'eventuale tag che indica che la conversazione è chiusa
		if( !empty($message['Conversation']['closed']) ) {
			$tag_url = Router::url(array('controller' => 'messages', 'action' => 'index', 'tag' => 'closed'));
			$tags[] = '<a href="'.$tag_url.'"><span class="label">'.__('Closed').'</span></a>';
		}
		if(!empty($tags)) echo '<div style="margin-bottom:3px">'.implode(' ', $tags).'</div>';
	?>
	<!-- tag di progetto -->
	<?php
		if( !empty($message['Conversation']['Attivita']['id']) ) {
			$tag_url = Router::url(array('controller' => 'messages', 'action' => 'attivita', $message['Conversation']['Attivita']['id']));
			echo '<div style="margin-bottom:3px">
				<a href="'.$tag_url.'"><span class="label label-danger">'.$message['Conversation']['Attivita']['name'].'</span></a>
			</div>';
		}
	?>
	
</div>
	

