<div class="row" name="<?php echo $id;?>">
	<div class="col-xs-12">
		<div class="clearfix">
			<div class="pull-right tableTools-container"></div>
		</div>
		<div class="table-header">
			<?php echo $titolo;?>
		</div>

		<!-- div.table-responsive -->

		<!-- div.dataTables_borderWrap -->
		<div>
			<table id="<?php echo $id;?>" class="table table-striped table-bordered table-hover">
				<thead>
					<tr>
						<th>ID Spesa</th>
						<th>ID Cliente</th>
						<th>Cliente</th>
						<th>Importo</th>
						<th>Mese</th>
						<th>Anno</th>
						<th>Tipo</th>
						<th>Stato</th>
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
	$('#<?php echo $id;?>')
	//.wrap("<div class='dataTables_borderWrap' />")   //if you are applying horizontal scrolling (sScrollX)
	.dataTable( {
		bAutoWidth: true,
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
				"mData": "importo",
				"sClass": "" 
			},
			{ 
				"mData": "mese",
				"sClass": "" 
			},
			{ 
				"mData": "anno",
				"sClass": "" 
			},
			{ 
				"mData": "tipo_addebito",
				"sClass": "",
				"bSearchable": false,
				"bSortable" : false
			},
			{ 
				"mData": "stato",
				"sClass": "",
				"bSearchable": false,
				"bSortable" : false
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
        "sAjaxSource": '<?php echo $this->Html->url(array('controller' => 'addebiti', 'action' => 'datatables_processing'));?>.json',
		"fnServerParams": function ( aoData ) {
			aoData.push( 
				{  
					name: "tipo", 
					value: <?php echo $tipo;?>
				}
			);
        }
        <?php if(isset($toggleable)):?>
        ,"createdRow": function ( row, data, index ) {
			if ( data.excluded == 1 ) {
				$(row).addClass('unselected');
			}
		}
        <?php endif;?>
	});
	
	<?php if(isset($toggleable)):?>
	var table = $('#addebiti').DataTable();
	
	$('#addebiti tbody').on('click', 'tr', function (event) {
	
		if( ! (event.target.nodeName.toUpperCase() == 'TR' || event.target.nodeName.toUpperCase() == 'TD' ) ) return;
	
		var $tr = $(this); // reference for later
		$('#modal-progress-toggle').show();
		
		$.post( "<?php echo Router::url(array('controller' => 'addebiti', 'action' => 'toggle_excluded'));?>.json", {
			id: table.row( this ).data().id
		})
		.done(function(data) {
			if( data.success ) $tr.toggleClass('unselected');
		})
		.fail(function() {
			alert( "error" );
		})
		.always(function() {
			$('#modal-progress-toggle').hide();
		});
		 
    });
	<?php endif;?>
});


<?php $this->Html->scriptEnd(); ?>
 
