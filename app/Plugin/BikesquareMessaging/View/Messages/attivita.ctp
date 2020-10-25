<?php echo $this->element('BikesquareMessaging.jsCss');?>

<?php 
	echo $this->Js->set('compose_to', $compose_to);
	echo $this->Js->set('is_admin', $is_admin);
?>

<?php echo $this->Html->css('BikesquareMessaging.summernote');?>
<?php echo $this->Html->css('BikesquareMessaging.summernote-bs3');?>

<?php echo $this->Html->script('BikesquareMessaging.summernote.min', array('inline' => false));?>
<?php echo $this->Html->script('BikesquareMessaging.toastr.min');?>
<?php echo $this->Html->script('BikesquareMessaging.messaging', array('inline' => false));?>

<?php 
	$this->assign('title', $attivita['Attivita']['name']);
	$crumbs = array(
		array(
			__('Home'), 
			Router::url("/"), 
			false),
		array(
			__('Messaging'), 
			Router::url(array('plugin' => 'messaging', 'controller' => 'messages', 'action' => 'index')), 
			false),
		array(
			$attivita['Attivita']['name'],
			'#',
			true
		)
	);
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
	
	if($is_admin) {
		$this->assign('right-header', $this->element('right_header', array(
			'nome_progetto' => $attivita['Attivita']['name'],
			'url' => Router::url(array('plugin' => null, 'controller' => 'contacts', 'action' => 'contratto', $attivita['Attivita']['id']))
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
      
                            <ul class="folder-list m-b-md no-padding">
                                <li><a class="no-ajax" href="<?php echo Router::url(array('controller' => 'messages', 'action' => 'index'));?>"> <i class="fa fa-inbox "></i> <?php echo __("Inbox");?> <span class="label label-primary pull-right"><?php echo $num_unread;?></span> </a></li>
                                <li>
									<?php
										$attivita_name_as_tag = $attivita['Attivita']['name'];
										$attivita_name_as_tag = strlen($attivita_name_as_tag) > 12 ? substr($attivita_name_as_tag, 0, 12).'...' : $attivita_name_as_tag;
									?>
									<a href="<?php echo Router::url(array('controller' => 'messages', 'action' => 'attivita', $attivita['Attivita']['id']));?>">
										<i class="fa fa-tag "></i> <?php echo $attivita_name_as_tag;?> <span class="label pull-right"><?php echo $num_unread_in_detail;?></span>
									</a>
								</li>
                                <li><a href="<?php echo Router::url(array('controller' => 'messages', 'action' => 'index', 'tag' => 'sent', '?' => array('attivita_id' => $attivita['Attivita']['id'])));?>"> <i class="fa fa-envelope-o"></i> <?php echo __("Sent");?> </a></li>
                            </ul>
                            
                            <!-- selezione destinatari nuova conversazione -->
                            <button id="open-select-rcpts" class="btn btn-primary btn-sm btn-block"><i class="fa fa-commenting-o"></i>  <?php echo __('NUOVA CONVERSAZIONE');?></button>
                            <!-- /selezione destinatari nuova conversazione -->
                            
                            <br/><br/>
                            
                            <?php echo $this->element('messaging/left_sidebar/quick_view', array(
								'attivitas' => $attivitas,
								'visible_tags' => $visible_tags,
								'attivita_id' => $attivita['Attivita']['id']
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
            
            <div id="messaging-body" class="<?php echo $embed ? 'col-md-10' : 'col-md-8';?> animated fadeInUp no-padding">
				
				<!-- elenco messaggi -->
				<div id="messages-container" class="">
					<?php echo $this->element('messaging/inbox', array(
						'title' => $attivita['Attivita']['name'],
						'attivita_id' => $attivita['Attivita']['id'],
						'tag' => $tag_id
					));?>
				</div>	
				
				<!-- scheda selezione dei destinatari -->
				<?php echo $this->element('messaging/left_sidebar/generic/compose', array(
					'is_admin' => $is_admin,
					'people' => $people,
					'default_people' => $default_people,
					'customer_display_name' => $attivita['Persona']['DisplayName']
				));?>
				
				<!-- scheda composizione messaggio -->
				<?php 
					$people_opts = array();
					foreach($people as $group => $people_in_group) {
						$people_opts += $people_in_group;
					}
					
					echo $this->element('messaging/compose', array(
						'title' => __('New conversation'),
						'is_admin' => $is_admin,
						'people' => $people_opts,
						'default_people' => $default_people,
						'usable_tags' => $usable_tags,
						'attivita_id' => $attivita['Attivita']['id'],
						'subtitle' => $attivita['Attivita']['name']
					));
				?>
				
            </div>
            
            <?php if(!$embed):?>
            <div class="col-md-2 no-padding">
				
				<?php echo $this->element('dettagli_prenotazione', [
					'contract' => $attivita
				]);?>
				
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
