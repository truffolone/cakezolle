$(document).ready(function() {
	
	//$('.aggiungi-al-carrello2, .riduci-qta-ml, .aumenta-qta-ml, .rimuovi-ml').click(function(e){
	$(document).on('click', '.aggiungi-al-carrello2, .riduci-qta-ml, .aumenta-qta-ml, .rimuovi-ml', function(e) {
		//alert("clicked");
		e.preventDefault();
		
		$('#modalWn').modal('show');

		$.ajax({
			url: $(this).attr('href'),
			context: document.body,
			dataType: 'json',
			cache: false, // mandatory per IE che fa un aggressive caching !!! Inserisce un timestamp alle richieste HEAD e GET in modo che la richiesta venga sempre effettivamente eseguita !
		})
		/*$.get( $(this).attr('href'), function() {
				
		})*/
		.done(function(data) {
			consegne = jQuery.parseJSON(data.consegne);
			if( $('#consegne-container').length ) {
				$('#consegne-container').html(consegne);
			}
			if( $('#data-prossima-consegna').length ) {
				$('#data-prossima-consegna').text( data.dataProssimaConsegna );
			}
			if( $('#sidemenu-ml').length ) {
				$('#sidemenu-ml').html( data.dettagliML );
			}
			
			$('#bell-reminder').remove();
			$('#header-right-menu').prepend( data.reminder );
			
			$('#modal-operazioni-da-confermare').html( data.dettagliOperazioniProvvisorie );
			// gestisci la visualizzazioni dei pulsanti di azione del modale dell'ordine provvisorio
			if( parseInt(data.numOperazioniProvvisorie) > 0 ) {
				$('#ordine-provvisorio-actions-container').css('display', 'block');
			}
			else {
				$('#ordine-provvisorio-actions-container').css('display', 'none');
			}
			// importante: fai un refresh della finestra modale visto che è cambiato il suo contenuto !
			$('#ordine-provvisorio').modal({show: false});
			
			// se mi trovo nella scheda dell'articolo, aggiorno l'indicazione del numero di confezioni correnti
			if( $('#confezioni_articolo_corrente').length ) {
				$('#confezioni_articolo_corrente').html( data.str_confezioni_articolo_corrente );
			}
			
			if( $('.finalizza-modifiche-container').length ) {
				$('.finalizza-modifiche-container').html( data.finalizzaModifiche );
			}
			
			// 2017-03-20: refresh di tutti gli eventuali blocchi inviati (identificati via id)
			if(data.contentHTML) {
				$.each(data.contentHTML, function(key, value) {
					$('#'+key).html(value);
				});
			}
			
			// la riga seguente è commentata e sostituita con il blocco subito dopo per gestire il fatto che il messaggio
			// del toast è ora generico su richiesta di zolle (se si rimuove quel blocco in appcontroller si può usare di nuovo questa istruzione)   
			//toast('zolle', data.ultimaOperazione + ' <a href="#" class="apri-ordine-provvisorio btn btn-sm btn-default">consulta</a>');
			if( parseInt(data.numOperazioniProvvisorie) > 0 ) {
				toast('zolle', data.ultimaOperazione + ' <a href="#" class="apri-ordine-provvisorio btn btn-sm btn-default">consulta</a>');
			}
			else {
				toast('zolle', data.ultimaOperazione );
			}
		})
		.fail(function(jqXHR, status, error) {
			msg = jQuery.parseJSON(jqXHR.responseText);
			toast('danger', msg.message);
		})
		.always(function() {
			$('#modalWn').modal('hide');
		});
		
	});
	
	// 2018-01-31: visualizzazione dettaglio spese
	$(document).on('click', '.dettaglio-zolla', function(e){
		e.preventDefault();
		
		tipoZollaSelezionata = $(this).attr('data-nome-zolla');
		
		$('#modalWn').modal('show');
		$.ajax({
			url: $(this).attr('href'),
			context: document.body,
			dataType: 'json',
			cache: false, // mandatory per IE che fa un aggressive caching !!! Inserisce un timestamp alle richieste HEAD e GET in modo che la richiesta venga sempre effettivamente eseguita !
		})
		/*$.get( $(this).attr('href'), function() {
				
		})*/
		.done(function(data) {
			$('#dettaglio-zolle-modal .modal-title').html( tipoZollaSelezionata );
			$('#dettaglio-zolle-modal .dettaglio-zolle-content').html( data.content );
			$('#dettaglio-zolle-modal .modal-content').css('height', (window.innerHeight - 60) + 'px');
			$('#dettaglio-zolle-modal').modal({show: true});
		})
		.fail(function(jqXHR, status, error) {
			msg = jQuery.parseJSON(jqXHR.responseText);
			toast('danger', msg.message);
		})
		.always(function() {
			$('#modalWn').modal('hide');
		});
		
		
		
	});
	
});
