<?php echo $this->element('BikesquareMessaging.jsCss');?>

<?php 
	echo $this->Js->set('compose_to', $compose_to);
	echo $this->Js->set('is_admin', $is_admin);
?>

<?php //echo $this->Html->script('BikesquareMessaging.jquery.nestable.no.drag', array('inline' => false));?>

<?php echo $this->Html->css('BikesquareMessaging.summernote');?>
<?php echo $this->Html->css('BikesquareMessaging.summernote-bs3');?>

<?php echo $this->Html->script('BikesquareMessaging.summernote.min', array('inline' => false));?>
<?php echo $this->Html->script('BikesquareMessaging.toastr.min');?>
<?php echo $this->Html->script('BikesquareMessaging.messaging', array('inline' => false));?>

<?php 
	$this->assign('title', $title);
	
	if( empty($tag_id) ) {
		$crumbs = array(
			array(
				__('Home'), 
				Router::url('/'), 
				false
			),
			array(
				__('Messaging'), 
				'#',
				true
			)
		);
	}
	else {
		$crumbs = array(
			array(
				__('Home'), 
				Router::url("/"), 
				false
			),
			array(
				__('Messaging'), 
				Router::url(array('plugin' => 'messaging', 'controller' => 'messages', 'action' => 'index')),
				false
			),
			array(
				$title, 
				'#',
				true
			)
		);
	}
		
	$this->assign('crumbs', $this->Kenn->crumbs($crumbs));	
	
		if(!empty($tag_id)) {
		$tag_name = null;
		foreach($visible_tags as $tag) {
			if( $tag['Messagingtag']['id'] == $tag_id ) {
				$tag_name = $tag['Messagingtag']['name'];
			}
		}
		if($tag_id == 'sent') {
			$tag_name = 'Inviati';
		}
		$this->assign('embedTag', $tag_name );
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
      
                            <ul class="folder-list m-b-md no-padding">
                                <li><a href="<?php echo Router::url(array('controller' => 'messages', 'action' => 'index'));?>"> <i class="fa fa-inbox "></i> <?php echo __("Inbox");?> <span class="label label-primary pull-right"><?php echo $num_unread;?></span> </a></li>
                                <?php if($tag_id):?>
									<?php if($tag_id != 'sent'):// 'sent' è un tag particolare che non devono essere visualizzato come tag?>
									<li>
										<?php
											$title_as_tag = strlen($title) > 12 ? substr($title, 0, 12).'...' : $title;
										?>
										<a href="<?php echo Router::url(array('controller' => 'messages', 'action' => 'index', 'tag' => $tag_id));?>">
											<i class="fa fa-tag "></i> <?php echo $title_as_tag;?> <span class="label pull-right"><?php echo $num_unread_in_detail;?></span>
										</a>
									</li>
									<?php endif;?>
                                <?php endif;?>
                                
                               <!-- usato nel caso in cui sto visualizzando i messaggi inviati --> 
                                <?php if($attivita_id):?>
									<li>
										<?php
											$title = $attivitas[ $attivita_id ];
											$title_as_tag = strlen($title) > 12 ? substr($title, 0, 12).'...' : $title;
										?>
										<a href="<?php echo Router::url(array('controller' => 'messages', 'action' => 'attivita', $attivita_id));?>">
											<i class="fa fa-tag "></i> <?php echo $title_as_tag;?> <span class="label pull-right"></span>
										</a>
									</li>
								<?php endif;?>
                                
                                
                                <li><a href="<?php echo Router::url(array('controller' => 'messages', 'action' => 'index', 'tag' => 'sent'));?>"> <i class="fa fa-envelope-o"></i> <?php echo __("Sent");?> </a></li>
                            </ul>
                            
                            <!-- selezione destinatari nuova conversazione -->
                            <button id="open-select-rcpts" class="btn btn-primary btn-sm btn-block"><i class="fa fa-commenting-o"></i>  <?php echo __('NUOVA CONVERSAZIONE');?></button>
                            <!-- /selezione destinatari nuova conversazione -->
                                                       
                            <div class="space-25"></div>
                            <div class="space-25"></div>
                            
                            <?php echo $this->element('messaging/left_sidebar/quick_view', array(
								'attivitas' => $attivitas,
								'visible_tags' => $visible_tags,
								'attivita_id' => $attivita_id
                            ));?>

                            <div class="clearfix"></div>
                        </div>
                    </div>
                </div>
            </div>
            <div id="messaging-body" class="col-md-10 animated fadeInUp no-padding">
				
				<!-- elenco messaggi -->
				<div id="messages-container" class="">
					<?php if($show_sent_messages):?>
						<?php echo $this->element('messaging/sent', array(
							'title' => $title
						));?>
					<?php else:?>
						<?php echo $this->element('messaging/inbox', array(
							'title' => $title,
							'tag' => $tag_id
						));?>
					<?php endif;?>
				</div>
				
				<!-- scheda selezione dei destinatari -->
				<?php echo $this->element('messaging/left_sidebar/generic/compose', array(
					'is_admin' => $is_admin,
					'people' => $people,
					'default_people' => $default_people
				));?>
				
				
				<!-- scheda composizione messaggio -->
				<?php 
					// compongo tutti i possibili destinatari come lista per consentire eventuali modifiche
					// rispetto alle persone selezionate in fase di composizione
					$people_opts = array();
					if($is_admin) {
						foreach($people as $group => $peopleInGroup) {
							$people_opts = $people_opts + $peopleInGroup;
						}
						asort($people_opts);
					}
					else {
						$people_opts = $people;
					}
					
					echo $this->element('messaging/compose', array(
						'title' => $title,
						'is_admin' => $is_admin,
						'people' => $people_opts,
						'default_people' => $default_people,
						'usable_tags' => $usable_tags
					));
				?>
				
				</div>
				
            </div>
            
        </div>
     </div>
  </div>
</div>


<?php $this->Html->scriptStart(array('inline' => false)); ?>
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

