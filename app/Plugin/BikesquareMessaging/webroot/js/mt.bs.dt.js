
/**
 * mt.bs.dt = bootstrap datatables ... a custom baby to blend datatables with bootstrap
 * 
 * @author: Marco Toldo
 */
jQuery.fn.extend({
	/**
	 * @param config mixed dataTables + custom (tpl) configuration
	 */
	DataTableBootstrap: function(config) {
		
		if(!config) { // no configuration available, build default one
			config = {};
		}
		
		// prepare templates
		config.rowTpl = buildTpl( config.rowTpl );
		if(config.formTpl) config.formTpl = buildTpl( config.formTpl );
		
		var dtTable = this; // reference to this table
		// overwrite DataTables configuration
		config.drawCallback = function( config ) {
			
			// refresh the number of visible columns (in case a column was hidden)
			config.oInit.visibleColsNum = getVisibleColsNum(dtTable);
			
			if( $(dtTable).DataTable().settings().data().length > 0 ) {
				$('tbody tr', dtTable).each(function(){
					$(this).addClass('hide'); // do not remove data rows !! (data are binded to them!)
						
					// append data
					/*var bsTr = $('<tr><td colspan="'+config.oInit.visibleColsNum+'"><div class="dt-bs-row-view-cont"></div><div class="dt-bs-row-edit-cont hide"></div></td></tr>');
					// build the actual row from template
					var trData = $(dtTable).DataTable().row(this).data(); 
						
					$('.dt-bs-row-view-cont', bsTr).append( renderTpl(config.oInit.rowTpl, trData) );
					$(this).after(bsTr);*/
					
					// VERSIONE SEMPLIFICATA PER POTER AGGIUNGERE LO STILE AI TR: IL TPL CONTIENE GIÀ ANCHE IL TR
					var trData = $(dtTable).DataTable().row(this).data();
					if(trData !== undefined)
					$(this).after( renderTpl(config.oInit.rowTpl, trData) );
				});
			}
			
			$('thead tr', dtTable).each(function(){
				/*var headTr = $('<tr><td colspan="'+config.oInit.visibleColsNum+'"></td></tr>');
				if( config.oInit.head ) {
					$('td', headTr).append( config.oInit.head );
					$(this).after(headTr);
				}*/
				$(this).addClass('hide');
			});
			
			
			$('tfoot tr', dtTable).each(function(){
				/*var footTr = $('<tr><td colspan="'+config.oInit.visibleColsNum+'"></td></tr>');
				if( config.oInit.foot ) {
					$('td', footTr).append( config.oInit.foot );
					$(this).after(footTr);
				}*/
				$(this).addClass('hide');
			});
		};
		
		$(this).DataTable(config);
	},
 
});

function buildTpl(tplDef) {
	var tplTokens = [];
	var chunks = tplDef.split('}}');
	for(i in chunks) {
		tokens = chunks[i].split('{{');
		token = {
			prefix : tokens[0]
		};
		if (tokens.length > 1) { // placeholder exists
			token.dataCol = tokens[1]; //parseInt(tokens[1]); stranamente non funziona sull'indice 0 ma tanto non serve per il controllo se esiste la chiave fatto prima di usare ogni pezzo del template
		}
		tplTokens.push(token);
	}
	return tplTokens;
}

function renderTpl(tpl, rowData) {
	
	var renderedTpl = '';
	for( i in tpl ) {
		renderedTpl += tpl[i].prefix;
		if( tpl[i].dataCol ) {
			if( rowData[ tpl[i].dataCol ] ) renderedTpl += rowData[tpl[i].dataCol];
		}
	}
	return renderedTpl;
}

// get the number of visible columns (for the colspan)
function getVisibleColsNum(aTable) {
	var colVisibility = aTable.DataTable().settings().columns().visible();
	var visibleColsNum = 0;
	for(var i=0;i<colVisibility.length;i++) {
		if( colVisibility[i] ) visibleColsNum++;
	}
	return visibleColsNum;
} 
