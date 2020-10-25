<?php 
	$this->assign('title', 'Carte di Credito');
	
	if($tipo == CARTE_TUTTE) $titolo = 'Elenco carte';
	elseif($tipo == CARTE_SCADUTE) $titolo = 'Carte scadute';
	elseif($tipo == CARTE_NON_ATTIVE) $titolo = 'Carte non attive';
	
	$this->assign('subtitle', $titolo);
?> 

<div class="row" name="carte">
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
			<table id="carte" class="table table-striped table-bordered table-hover">
				<thead>
					<tr>
						<th>ID Carta</th>
						<th>Cliente</th>
						<th>PAN</th>
						<th>Scadenza PAN</th>
						<th>Data invio contratto</th>
						<th>Data attivazione</th>
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
	$('#carte')
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
				"mData": "cliente_nome",
				"sClass": "" 
			},
			{ 
				"mData": "pan",
				"sClass": "" 
			},
			{ 
				"mData": "scadenza_pan",
				"sClass": "" 
			},
			{ 
				"mData": "sent",
				"sClass": "" 
			},
			{ 
				"mData": "signed",
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
        "sAjaxSource": '<?php echo $this->Html->url(array('controller' => 'carte_di_credito', 'action' => 'datatables_processing'));?>.json',
		"fnServerParams": function ( aoData ) {
			aoData.push( 
				{  
					name: "tipo", 
					value: <?php echo $tipo;?>
				}
			);
        }
	});
	
});


<?php $this->Html->scriptEnd(); ?>
 
 
