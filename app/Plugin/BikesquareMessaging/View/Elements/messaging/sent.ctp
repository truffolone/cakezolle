<div class="mail-box">

	<div class="space-10"></div>

	<table id="sent-messages" class="table table-hover striped" style="width:100%">
		<thead>
			<tr class="thead-first-tr">
				<th><?php echo __('To');?></th>
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
	
	$('#sent-messages')
	.DataTable( {
		bAutoWidth: true,
		"oLanguage": getDtLanguage([10,20,50,100]),
		"iDisplayLength": <?php echo MESSAGE_PAGE_LENGTH;?>,
		"sPaginationType": "full_numbers",
		"sScrollX": "100%",// mandatory for styling purposes
		"dom": '<"row"<"col-sm-6"><"col-sm-6"f>><"row"<"col-sm-12"tr>><"row dt-footer-container"<"col-sm-3"i><"col-sm-3"l><"col-sm-6"p>>',
		"aoColumns": [
			{ 
				"mData": "to",
				"sClass": "kn-dt-border-right",
				"bVisible" : true,
				"bSearchable" : false,		
				"bSortable" : false, // non posso mai fare il sorting per destinatario perchè può essercene più di uno	
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
        "order": [[ 3, "desc" ]], // order by created desc by default
		//"bStateSave": true,
		"bProcessing": true,
        "bServerSide": true,
        "sAjaxSource": '<?php echo $this->Html->url(array('controller' => 'messages', 'action' => 'sent', '?' => array('attivita_id' => $attivita_id)));?>.json',
		"fnServerParams": function ( aoData ) {
			
        }, 
        
        // custom config part
        'rowTpl' : '{{content}}', // potrei utilizzare come tpl qualunque frammento XML con i dati della riga, ma per semplicità lo generico tutto server-side
		//'head' : '<div class="row text-center"><div class="col-md-12">HEADER</div></div>',
		//'foot' : '<div class="row text-center"><div class="col-md-12">FOOTER</div></div>',
	});
	
});
<?php $this->Html->scriptEnd();?>
