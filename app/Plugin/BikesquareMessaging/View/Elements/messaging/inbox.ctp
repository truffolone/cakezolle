<div class="mail-box" style="padding:5px">
					
	<button id="refresh-inbox" class="btn btn-white btn-sm" data-toggle="tooltip" data-placement="left" title="Refresh inbox"><i class="fa fa-refresh"></i> <?php echo __("Refresh");?></button>

	<table id="rcvd-messages" class="table table-hover striped" style="width:100%">
		<thead>
			<tr class="thead-first-tr">
				<th><?php echo __('From');?></th>
				<th><?php echo __('Subject');?></th>
				<th class="text-center"><i class="fa fa-paperclip"></i></th>
				<th class="text-right"><?php echo __('Date');?></th>
			</tr>
		</thead>
		<tbody>
		</tbody>
	</table>
</div>

<?php $this->Html->scriptStart(array('inline' => false)); ?>
jQuery(function($) {
	
	$('#rcvd-messages')
	.DataTable( {
		bAutoWidth: true,
		"oLanguage": getDtLanguage([10,20,50,100]),
		"iDisplayLength": <?php echo MESSAGE_PAGE_LENGTH;?>,
		"sPaginationType": "full_numbers",
		//"sScrollX": "100%",// mandatory for styling purposes
		"dom": '<"row"<"col-sm-6"<"#messaging-dt-left-header">><"col-sm-6"f>><"row"<"col-sm-12"tr>><"row dt-footer-container"<"col-sm-3"i><"col-sm-3"l><"col-sm-6"p>>',
		"aoColumns": [
			{ 
				"mData": "from",
				"sClass": "kn-dt-border-right",
				"bVisible" : true,
				"bSearchable" : false,		
				"bSortable" : true,	
			},
			{ 
				"mData": "subject",
				"sClass": "",
				"bVisible" : true,
				"bSearchable" : false,		
				"bSortable" : true,	
			},
			{ 
				"mData": "attachment",
				"sClass": "text-center kn-dt-border-right",
				"bVisible" : true,
				"bSearchable" : false,		
				"bSortable" : false,	
			},
			{ 
				"mData": "date",
				"sClass": "dt-no-right-border",
				"bVisible" : true,
				"bSearchable" : false,		
				"bSortable" : true,	
			},
        ],
        "order": [[ 3, "desc" ]], // order by date desc by default
		//"bStateSave": true,
		"bProcessing": true,
        "bServerSide": true,
        "sAjaxSource": '<?php 
			$url = array(
				'controller' => 'messages',
				'action' => 'inbox'
			);
			if(isset($tag)) $url['tag'] = $tag;
			if(isset($attivita_id)) $url['attivita_id'] = $attivita_id;
			
			echo $this->Html->url($url);
		?>.json',
		"fnServerParams": function ( aoData ) {
			
        },
        "createdRow" : function (row, data, index) {
			if( $('.unread', $(row)).length ) {
				$(row).addClass("tr-unread");
			}
		},
		"initComplete" : function(settings, json){
			if($('#refresh-inbox').length) {
				var $refreshInboxBtn = $('#refresh-inbox').detach();
				$('#messaging-dt-left-header').append( $refreshInboxBtn );
			}
		} 
	});
	
});
<?php $this->Html->scriptEnd();?>

