<?php echo $this->element('BikesquareMessaging.jsCss');?>

<?php echo $this->Html->script('BikesquareMessaging.messaging', array('inline' => false));?>

<?php echo $this->Html->script('BikesquareMessaging.mt.bs.dt', array('inline' => false));?>

<?php $user = $this->Session->read('Auth.User');?>
<?php if($user[USER_ROLE_KEY] == ROLE_USER):?>
<br/>
<a href="<?=Router::url(['plugin' => false, 'controller' => 'contacts', 'action' => 'inquiry']);?>" class="btn btn-primary btn-block"><i class="fa fa-plus-circle"></i> Nuova richiesta</a>
<?php endif;?>

<h3><i class="fa fa-inbox"></i> <?=__('Da leggere');?></h3>

<button style="margin-left:5px" id="refresh-to-read" class="btn btn-white btn-sm" data-toggle="tooltip" data-placement="left" title="Refresh inbox"><i class="fa fa-refresh"></i> Refresh</button>

<table id="to-read" class="table table-hover table-mail chat-discussion" style="width:100%">
	<tbody></tbody>
</table>


<h3><i class="fa fa-inbox"></i> <?=__('Tutto il resto');?></h3>

<table id="else" class="table table-hover table-mail chat-discussion" style="width:100%">
	<tbody></tbody>
</table>
					
<?php $this->Html->scriptStart(array('inline' => false)); ?>
jQuery(function($) {
	
	$('#to-read')
	.DataTableBootstrap( {
		bAutoWidth: true,
		"oLanguage": getDtLanguage([10,20,50,100]),
		"iDisplayLength": <?php echo MESSAGE_PAGE_LENGTH;?>,
		"sPaginationType": "full_numbers",
		"dom": '<"row"<"col-sm-6"<"#messaging-dt-left-header">><"col-sm-6">><"row"<"col-sm-12"tr>><"row dt-footer-container"<"col-sm-3"i><"col-sm-3"l><"col-sm-6"p>>',
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
        "sAjaxSource": '<?php echo $this->Html->url(array('plugin' => 'messaging', 'controller' => 'conversations', 'action' => 'inbox', '?inbox=true'));?>.json',
		"fnServerParams": function ( aoData ) {
			
        }, 
        "initComplete" : function(settings, json){
			if($('#refresh-to-read').length) {
				var $refreshConversationBtn = $('#refresh-to-read').detach();
				$('#messaging-dt-left-header').append( $refreshConversationBtn );
			}
		},
        // custom config part
        'rowTpl' : '{{content}}', // potrei utilizzare come tpl qualunque frammento XML con i dati della riga, ma per semplicità lo generico tutto server-side
		//'head' : '<div class="row text-center"><div class="col-md-12">HEADER</div></div>',
		//'foot' : '<div class="row text-center"><div class="col-md-12">FOOTER</div></div>',
	});
	
	
	$('#else')
	.DataTableBootstrap( {
		bAutoWidth: true,
		"oLanguage": getDtLanguage([10,20,50,100]),
		"iDisplayLength": <?php echo MESSAGE_PAGE_LENGTH;?>,
		"sPaginationType": "full_numbers",
		"dom": '<"row"<"col-sm-6"<"#messaging-dt-left-header">><"col-sm-6">><"row"<"col-sm-12"tr>><"row dt-footer-container"<"col-sm-3"i><"col-sm-3"l><"col-sm-6"p>>',
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
        "sAjaxSource": '<?php echo $this->Html->url(array('plugin' => 'messaging', 'controller' => 'conversations', 'action' => 'inbox'));?>.json',
		"fnServerParams": function ( aoData ) {
			
        }, 
        "initComplete" : function(settings, json){
			if($('#refresh-else').length) {
				var $refreshConversationBtn = $('#refresh-else').detach();
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
