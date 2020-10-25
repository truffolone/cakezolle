<?php echo $this->element('BikesquareMessaging.jsCss');?>

<?php 
	echo $this->Js->set('compose_to', array());
	echo $this->Js->set('is_admin', $is_admin);
?>

<?php echo $this->Html->css('BikesquareMessaging.summernote');?>
<?php echo $this->Html->css('BikesquareMessaging.summernote-bs3');?>

<?php echo $this->Html->script('BikesquareMessaging.summernote.min', array('inline' => false));?>
<?php echo $this->Html->script('BikesquareMessaging.toastr.min');?>
<?php echo $this->Html->script('BikesquareMessaging.messaging', array('inline' => false));?>

<?php 
	$this->assign('title', $conversation['Conversation']['subject']);
	$crumbs = array(
		array(
			__('Home'), 
			Router::url("/"), 
			false),
		array(
			__('Messaging'), 
			Router::url(array('plugin' => 'messaging', 'controller' => 'messages', 'action' => 'index')), 
			false)
	);
	if(!empty($conversation['Attivita']['id'])) {
		$crumbs[] = array(
			$conversation['Attivita']['name'],
			Router::url(array('plugin' => 'messaging', 'controller' => 'messages', 'action' => 'attivita', $conversation['Attivita']['id'])),
			false
		);
	}
	$crumbs[] = array(
		$conversation['Conversation']['subject'],
		'#',
		true
	);
	
	$this->assign('crumbs', $this->Kenn->crumbs($crumbs));
	
	if($is_admin && !empty($conversation['Attivita']['id'])) {
		$this->assign('right-header', $this->element('right_header', array(
			'nome_progetto' => $conversation['Attivita']['name'],
			'url' => Router::url(array('plugin' => null, 'controller' => 'contacts', 'action' => 'contratto', $conversation['Attivita']['id']))
		)));
	}
?>

<?php echo $this->element('BikesquareMessaging.crumb');?>

<div class="messaging-wrapper">
	<div class="container">
		<div class="row">
            <div class="col-md-2 no-padding">
                <div class="ibox float-e-margins">
                    <div class="ibox-content mailbox-content" style="padding-top:0">
                        <div class="file-manager">
      
							<?php if($is_admin):?>
								<div class="btn-group-vertical btn-block" role="group" aria-label="...">
									<?php if(!$conversation['Conversation']['closed']):?>
									<!-- 2019-02-26: ora il reply è sempre visibile, non serve più -->
									<!--<button id="reply" class="btn btn-primary compose-mail"><i class="fa fa-mail-reply"></i> <?php echo __('Reply');?></button>-->
									<?php endif;?>
									<?php echo $this->Html->link(
										'<i class="fa fa-edit"></i> '.__('Edit'), 
										array('controller' => 'conversations', 'action' => 'update', $conversation['Conversation']['id']), 
										array('escape' => false, 'class' => 'no-ajax btn btn-primary', 'title' => __('Edit this conversation')));
									?>
									<?php if(empty($conversation['Conversation']['closed'])):?>
										<?php echo $this->Html->link(
											'<i class="fa fa-envelope"></i> '.__('Close'), 
											array('controller' => 'conversations', 'action' => 'close', $conversation['Conversation']['id']), 
											array('escape' => false, 'class' => 'no-ajax btn btn-primary', 'title' => __('Close this conversation')));
										?>
									<?php else:?>
										<?php echo $this->Html->link(
											'<i class="fa fa-envelope-o"></i> '.__('Re-open'), 
											array('controller' => 'conversations', 'action' => 'open', $conversation['Conversation']['id']), 
											array('escape' => false, 'class' => 'no-ajax btn btn-primary', 'title' => __('Re-open this conversation')));
										?>
									<?php endif;?>
								</div>
								<div class="space-25"></div> 
							<?php else:?>
								<?php if(!$conversation['Conversation']['closed']):?>
								<!-- 2019-02-26: ora il reply è sempre visibile, non serve più -->
								<!--<button id="reply" class="btn btn-block btn-primary compose-mail"><i class="fa fa-mail-reply"></i> <?php echo __('Reply');?></button>
								<div class="space-25"></div>--> 
								<?php endif;?>
							<?php endif;?>
      
                            <!--<h5>Folders</h5>-->
                            <ul class="folder-list m-b-md no-padding">
                                <li><a href="<?php echo Router::url(array('controller' => 'messages', 'action' => 'index'));?>"> <i class="fa fa-inbox "></i> <?php echo __("Inbox");?> <span class="label label-primary pull-right"><?php echo $num_unread;?></span> </a></li>
                                <?php if( !empty($conversation['Attivita']['id']) ):?>
                                <li>
									<?php
										$attivita_name_as_tag = $conversation['Attivita']['name'];
										$attivita_name_as_tag = strlen($attivita_name_as_tag) > 12 ? substr($attivita_name_as_tag, 0, 12).'...' : $attivita_name_as_tag;
									?>
									<a href="<?php echo Router::url(array('controller' => 'messages', 'action' => 'attivita', $conversation['Attivita']['id']));?>">
										<i class="fa fa-tag "></i> <?php echo $attivita_name_as_tag;?> <span class="label pull-right"><?php echo $num_unread_in_detail;?></span>
									</a>
								</li>
								<?php endif;?>
                                <li><a href="<?php echo Router::url(array('controller' => 'messages', 'action' => 'index', 'tag' => 'sent'));?>"> <i class="fa fa-envelope-o"></i> <?php echo __("Sent");?> </a></li>
                            </ul>
                            
                            <!-- selezione destinatari nuova conversazione -->
                            <!-- DENTRO AD UNA CONVERSAZIONE HA POCO SENSO POTERNE APRIRE UN'ALTRA, NASCONDO IL PULSANTE -->
                            <!--<button id="open-select-rcpts" class="btn btn-primary btn-sm btn-block"><i class="fa fa-commenting-o"></i>  <?php echo __('NUOVA CONVERSAZIONE');?></button>-->
                            <!-- /selezione destinatari nuova conversazione -->
                        
							<br/><br/>
                        
							<?php echo $this->element('messaging/left_sidebar/quick_view', array(
								'attivitas' => $attivitas,
								'visible_tags' => $visible_tags,
								'attivita_id' => $conversation['Conversation']['attivita_id']
                            ));?>

                            <div class="clearfix"></div>
                        </div>
                    </div>
                </div>
            </div>
            
            <?php 
				$embed = false;
				if($this->request->is('json')) {
					// sto facendo il rendering di questa vista via controller per poi iniettarlo in un elemento html
					$embed = true;
				}
			?>
            
            <?php 
				$messagingBodyColCls = empty($conversation['Attivita']['id']) || $embed ? 'col-md-10' : 'col-md-8';
			?>
			
            <div id="messaging-body" class="<?php echo $messagingBodyColCls;?> animated fadeInUp no-padding">
				
				<!-- risposta alla conversazione (NOTA: non va in overlay x specifica richiesta, quindi è prima di tutti gli altri elementi) -->
				<!--<div id="reply-container" class="hidden">-->
				<div id="reply-container">
					<?php echo $this->element('messaging/reply', array(
						'is_admin' => $is_admin,
						'conversation' => $conversation,
						'user' => $user,
						'subtitle' => empty($conversation['Attivita']['id']) ? null : $conversation['Attivita']['name']
					));?>
				</div>
				
				<!-- conversazione -->
				<div id="messages-container" class="">
					<?php echo $this->element('messaging/conversation', array(
						'conversation' => $conversation,
						'user' => $user,
						'subtitle' => empty($conversation['Attivita']['id']) ? null : $conversation['Attivita']['name']
					));?>
				</div>
				
			
				<!-- scheda selezione dei destinatari nuova conversazione (posso creare una nuova conversazione in ogni caso) -->
				<?php echo $this->element('messaging/left_sidebar/generic/compose', array(
					'is_admin' => $is_admin,
					'people' => $people,
					'default_people' => $default_people
				));?>
				
				<!-- scheda composizione messaggio (posso creare una nuova conversazione in ogni caso) -->
				<?php 
					// compongo tutti i possibili destinatari come lista per consentire eventuali modifiche
					// rispetto alle persone selezionate in fase di composizione
					$people_opts = array();
					foreach($people as $group => $people_in_group) {
						$people_opts += $people_in_group;
					}
				
					echo $this->element('messaging/compose', array(
						'is_admin' => $is_admin,
						'people' => $people_opts,
						'default_people' => $default_people,
						'usable_tags' => $usable_tags,
						'subtitle' => empty($conversation['Attivita']['id']) ? null : $conversation['Attivita']['name'],
						// se sono in una conversazione di progetto la nuova conversazione verrà aperta nel medesimo progetto
						'attivita_id' => $conversation['Conversation']['attivita_id'] 
					));
				?>
				
            </div>
            
            <?php if( !empty($conversation['Attivita']['id']) && !$embed ):?>
				<div class="col-md-2 no-padding">
								
					<?php echo $this->element('dettagli_prenotazione', ['contract' => [
						'Attivita' => $conversation['Attivita'],
						'Persona' => $conversation['Attivita']['Persona'],
						'Destination' => $conversation['Attivita']['Destination'],
						'Poi' => $conversation['Attivita']['Poi'],
						'BiciPrenotata' => $conversation['Attivita']['BiciPrenotata'],
						'AddonPrenotato' => $conversation['Attivita']['AddonPrenotato'],
					]]);?>
				
				</div>
			<?php endif;?>
            
        </div>
     </div>
  </div>
</div>


<?php $this->Html->scriptStart(array('inline' => false)); ?>

var workflowIllustratorData = ''; // mi serve per la valorizzazione ajax

$(document).ready(function(){
	$('.chosen-select').chosen();
	
	/*$('.nestable').nestable({
		group: 1
    });
    $('.nestable').nestable('collapseAll');
    $('.nestable').removeClass('hidden'); // li nascondo finchè non sono inizializzati
    */
});
<?php $this->Html->scriptEnd(); ?>
