<?php echo $this->Html->script('BikesquareMessaging.mt.bs.dt', array('inline' => false));?>

				<div class="">

					<div class="space-5"></div>
					<?php if (!empty($conversation['Tag']) || !empty($conversation['Conversation']['closed']) ):?>
					<!--<div style="margin:5px">
					<?php
						$tags = array();
						
						if(!empty($conversation['Conversation']['attivita_id'])) {
							// aggiungo il tag di progetto
							$tag_url = Router::url(array('plugin' => 'messaging', 'controller' => 'messages', 'action' => 'attivita', $conversation['Conversation']['attivita_id']));
							$tags[] = '<a href="'.$tag_url.'"><span class="label label-danger"><big>'.$conversation['Attivita']['name'].'</big></span></a>';
						}
						
						foreach($conversation['Tag'] as $t) {
							if(empty($conversation['Conversation']['attivita_id'])) {
								// se clicco sul tag filtra x tag il messaging generale
								$tag_url = Router::url(array('plugin' => 'messaging', 'controller' => 'messages', 'action' => 'index', 'tag' => $t['id']));
							}
							else {
								// se clicco sul tag filtra x tag il messaging di progetto
								$tag_url = Router::url(array('plugin' => 'messaging', 'controller' => 'messages', 'action' => 'attivita', $conversation['Conversation']['attivita_id'], 'tag' => $t['id']));
							}
							// specifico in ogni caso label-warning per la struttura della label
							$tags[] = '<a href="'.$tag_url.'"><span class="label label-warning" style="background:'.$t['color'].'"><big>'.$t['name'].'</big></span></a>';
						}
						// aggiungo in visualizzazione (solo qui, nell'element "reply" non serve perchè se è chiusa non posso
						// rispondere!) l'eventuale tag che indica che la conversazione è chiusa
						if( !empty($conversation['Conversation']['closed']) ) {
							if(empty($conversation['Conversation']['attivita_id'])) {
								// se clicco sul tag filtra x tag il messaging generale
								$tag_url = Router::url(array('controller' => 'messages', 'action' => 'index', 'tag' => 'closed'));
							}
							else {
								// se clicco sul tag filtra x tag il messaging di progetto
								$tag_url = Router::url(array('plugin' => 'messaging', 'controller' => 'messages', 'action' => 'attivita', $conversation['Conversation']['attivita_id'], 'tag' => 'closed'));
							}
							$tags[] = '<a href="'.$tag_url.'"><span class="label"><big>'.__('Closed').'</big></span></a>';
						}
						
						echo implode(' ', $tags);
					?>
					<hr/>
					</div>-->
					<?php endif;?>
					
					<!-- partecipanti -->
					<!--<div style="margin:5px">
					<?php
						$users = array();
						foreach($conversation['Participant'] as $p) {
							if(!$is_admin && !in_array($p[USER_ROLE_KEY], array(ROLE_ADMIN, ROLE_BAM))) continue;
							if($p['id'] == $user['id']) continue;
							$users[] = '<span class="label label-default"><big>'.$this->User->displayName($p).'</big></span>'; // username come display name
						}
						echo implode(' ', $users);
					?>
					</div>-->
					<!-- /partecipanti -->
					
					<hr/>
					
					<button style="margin-left:5px" id="refresh-conversation" class="btn btn-white btn-sm" data-toggle="tooltip" data-placement="left" title="Refresh inbox"><i class="fa fa-refresh"></i> Refresh</button>

					<table id="conversation" class="table table-hover table-mail chat-discussion" style="width:100%">
						<tbody>
							
						</tbody>
					</table>
				</div>


<?php $this->Html->scriptStart(array('inline' => false)); ?>
jQuery(function($) {
	
	$('#conversation')
	.DataTableBootstrap( {
		bAutoWidth: true,
		"oLanguage": getDtLanguage([10,20,50,100]),
		"iDisplayLength": <?php echo MESSAGE_PAGE_LENGTH;?>,
		"sPaginationType": "full_numbers",
		"dom": '<"row"<"col-sm-6"<"#messaging-dt-left-header">><"col-sm-6"f>><"row"<"col-sm-12"tr>><"row dt-footer-container"<"col-sm-3"i><"col-sm-3"l><"col-sm-6"p>>',
		"aoColumns": [
			{ 
				"mData": "content",
				"sClass": "",
				"bVisible" : true,
				"bSearchable" : false,		
				"bSortable" : false,	
			},
			
        ],
        "order": [[ 0, "desc" ]], // order by created desc by default
		//"bStateSave": true,
		"bProcessing": true,
        "bServerSide": true,
        "sAjaxSource": '<?php echo $this->Html->url(array('plugin' => 'messaging', 'controller' => 'messages', 'action' => 'inbox', 'conversation' => $conversation['Conversation']['id']));?>.json',
		"fnServerParams": function ( aoData ) {
			
        }, 
        "initComplete" : function(settings, json){
			if($('#refresh-conversation').length) {
				var $refreshConversationBtn = $('#refresh-conversation').detach();
				$('#messaging-dt-left-header').append( $refreshConversationBtn );
			}
		},
        // custom config part
        'rowTpl' : '{{content}}', // potrei utilizzare come tpl qualunque frammento XML con i dati della riga, ma per semplicità lo generico tutto server-side
		//'head' : '<div class="row text-center"><div class="col-md-12">HEADER</div></div>',
		//'foot' : '<div class="row text-center"><div class="col-md-12">FOOTER</div></div>',
	});
	
});
<?php $this->Html->scriptEnd();?>
