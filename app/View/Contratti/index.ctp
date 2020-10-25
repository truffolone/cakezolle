<?php 
	$this->assign('title', 'Contratti');
	$this->assign('subtitle', 'Elenco contratti '.$tipo);
?> 

<div class="row">
	<div class="col-xs-12">
		<div class="clearfix">
			<div class="pull-right tableTools-container"></div>
		</div>
		<div class="table-header">
			Elenco contratti <?php echo $tipo;?>
		</div>

		<!-- div.table-responsive -->

		<!-- div.dataTables_borderWrap -->
		<div>
			<table id="dynamic-table" class="table table-striped table-bordered table-hover">
				<thead>
					<tr>
						<th>ID contratto</th>
						<th>ID cliente</th>
						<th class="hidden-480">Nome cliente</th>
						<th>ID cliente fatt</th>
						<th class="hidden-480">Nome cliente fatt</th>
						<th>Azioni</th>
					</tr>
				</thead>

				<tbody>
				</tbody>
			</table>
		</div>
	</div>
</div>

		
<?php echo $this->Html->script('jquery.dataTables.min', array('inline' => false));?>
<?php echo $this->Html->script('jquery.dataTables.bootstrap.min', array('inline' => false));?>
<?php echo $this->Html->script('dataTables.tableTools.min', array('inline' => false));?>
<?php echo $this->Html->script('dataTables.colVis.min', array('inline' => false));?>

<?php $this->Html->scriptStart(array('inline' => false)); ?>
jQuery(function($) {
	//initiate dataTables plugin
	var oTable1 = 
	$('#dynamic-table')
	//.wrap("<div class='dataTables_borderWrap' />")   //if you are applying horizontal scrolling (sScrollX)
	.dataTable( {
		bAutoWidth: false,
		"oLanguage": {
			"sLengthMenu": 'Mostra <select>'+
			'<option value="10">10</option>'+
			'<option value="25">25</option>'+
			'<option value="50">50</option>'+
			'<option value="100">100</option>'+
			'</select> record per pagina',
			"sSearch": 'Cerca',
			"sZeroRecords": "Nessun record da visualizzare",
			"sProcessing": "Attendere prego ...", 
			"sLoadingRecords": "Caricamento dati in corso. Attendere prego ...",
			"sEmptyTable" : "Non è presente nessun record",
			"sInfo": "Record da _START_ a _END_ di _TOTAL_",
			"sInfoFiltered": " - filtrati di _MAX_ record",
			"oPaginate": {
				"sLast": "Ultima",
				"sFirst": "Prima",
				"sNext": "Succ",
				"sPrevious": "Prec"
			}
		},
		"iDisplayLength": 50,
		"sPaginationType": "full_numbers",
		//"sDom": "Tfrtip",
		"aoColumns": [
            { 
				"mData": "id",
				"sClass": ""
			},
			{ 
				"mData": "cliente_id",
				"sClass": "" 
			},
			{ 
				"mData": "cliente_nome",
				"sClass": "" 
			},
			{ 
				"mData": "cliente_fatturazione_id",
				"sClass": "" 
			},
			{ 
				"mData": "cliente_fatturazione_nome",
				"sClass": "" 
			},
			{ 
				"mData": "actions",
				"sClass": "",
				"bSearchable": false,
				"bSortable" : false
			},
        ],
		//"bStateSave": true,
		"sScrollX": "100%",
		"bProcessing": true,
        "bServerSide": true,
        "sAjaxSource": '<?php echo $this->Html->url(array('controller' => 'contratti', 'action' => 'datatables_processing', $tipo));?>.json',
		"fnServerParams": function ( aoData ) {
			/*aoData.push( 
				{  
					name: "outputTarget", 
					value: currentOutputTarget
				},
				{  
					name: "exportFilename", 
					value: export_filename
				}
			);*/
        } 
	});
});

<?php $this->Html->scriptEnd(); ?>
