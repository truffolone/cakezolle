var selectedPeople = new Array();

jQuery(function($) {
	
	/**
	 * 
	 */
	$(document).ready(function(){
	
		// non serve più perchè se ri-clicco sulla stessa action pulisco lo stack delle card
		//$('#open-select-rcpts').removeAttr('disabled'); // mandatory (altrimenti facendo F5 potrebbe rimanere disabled)
	
		// gestisci il compose_to
		if(window.app.compose_to !== undefined && window.app.compose_to.length) {
			$('.admin-compose-select').val(window.app.compose_to);
			$('.admin-compose-select').trigger("chosen:updated");
			// trigger the event manually
			$('#open-select-rcpts')[0].click();
			
		}
	
		// con le successive 2 chiamate gestisco la selezione destinatari sia dei select sia delle checkbox dovunque,
		// sia nel caso di F5 con una selezione sia nel caso del compose_to precedente
		mainViewAdmin_refreshSelectedPeople();
		projectViewAdmin_refreshSelectedPeople();
		
		if(window.app.compose_to !== undefined && window.app.compose_to.length) {
			// apri automaticamente il form per comporre una nuova conversazione con gli utenti selezionati in automatico
			openCard('select-rcpts-container', 'select-rcpts-shadow', openImmediately = true);
			composeNewConversation();
		}
	
	});
	
	$('#conversation').on('click', '.btn-reply-to-message', function(){
		var from = $(this).attr('data-from');
		var to = $(this).attr('data-to').split(',');
		// disabilita tutti i partecipanti
		$('.btn-chat-participant').each(function(){
			$('.opt-recipient').prop('selected', false);
			$(this).removeClass('selected').addClass('not-selected');
			$('i', this).removeClass('fa-check').addClass("fa-times");
		});
		var participantsToEnable = [from];
		for(var i=0;i<to.length;i++) {
			participantsToEnable.push(to[i]);
		}
		for(var i=0;i<participantsToEnable.length;i++) {
			$('#recipient-opt-' + participantsToEnable[i]).prop('selected', true);
			$('#recipient-btn-' + participantsToEnable[i]).removeClass('not-selected').addClass('selected');
			$('i', '#recipient-btn-' + participantsToEnable[i]).removeClass('fa-times').addClass("fa-check");
		}
		$('#selected-recipients-num').text( $('.btn-chat-participant.selected').length );
		$("html, body").animate({ scrollTop: 0 }, "slow");
	});
	
	$('.btn-chat-participant').click(function(){
		var participantId = $(this).attr('data-participant-id');
		if( $(this).hasClass('selected') ) {
			$('#recipient-opt-'+participantId).prop('selected', false);
			$(this).removeClass('selected').addClass('not-selected');
			$('i', this).removeClass('fa-check').addClass("fa-times");
		}
		else {
			$('#recipient-opt-'+participantId).prop('selected', true);
			$(this).addClass('selected').removeClass('not-selected');
			$('i', this).removeClass('fa-times').addClass("fa-check");
		}
		
		$('#selected-recipients-num').text( $('.btn-chat-participant.selected').length );
		
	});
	
	/**
	 * 
	 */
	$('.open-wiki').click(function(e){
		e.preventDefault();
		
		ajaxGet($(this), function() {
			openCard('wiki-container', 'wiki-shadow');
		}, function() {
		});
		
		
	});
	
	/**
	 * 
	 */
	$('.open-wiki-target-selection-card').click(function(e){
		e.preventDefault();
		var tipoMetatestoID = $(this).attr('data-tipometatesto-id');
		openCard('mywiki-tipometatesto-container-'+tipoMetatestoID, 'mywiki-tipometatesto-shadow-'+tipoMetatestoID);
		
	});
	
	/**
	 * 
	 */
	$('.close-wiki-target-selection-card').click(function(e){
		e.preventDefault();
		
		var tipoMetatestoID = $(this).attr('data-tipometatesto-id');
		closeCard('mywiki-tipometatesto-container-'+tipoMetatestoID, 'mywiki-tipometatesto-shadow-'+tipoMetatestoID);
	});
	
	/**
	 * 
	 */
	$('.close-wiki-card').click(function(e){
		e.preventDefault();
		closeCard('wiki-container', 'wiki-shadow');
		
	});
	
	/**
	 * 
	 */
	$('#timeline-job-select').change(function(){
		
		url = $(this).attr('data-baseurl') + '/' + $(this).val() + '.json';
		$('#timeline-anchor').attr('href', url);
		
		ajaxGet($('#timeline-anchor'), function() {
			openCard('timeline-container', 'timeline-shadow');
		}, function() {
			// important: reset the value
			$('#timeline-job-select').val('');
			$('#timeline-job-select').trigger("chosen:updated");
		});
		
	});
	
	/**
	 * 
	 */
	$('#close-timeline-card').click(function(e){
		e.preventDefault();
		closeCard('timeline-container', 'timeline-shadow');
		
	});
	
	/**
	 * 
	 */
	$('#workflow-illustrator-job-select').change(function(){
		
		url = $(this).attr('data-baseurl') + '/' + $(this).val() + '.json';
		$('#workflow-illustrator-anchor').attr('href', url);
		
		$('#workflow-illustrator').html(""); // reset perchè raphael deve "disegnare" quando il paper (la finestra) è già visibile (per via delle dimensioni che in hidden non sono corrette)
		
		ajaxGet($('#workflow-illustrator-anchor'), function() {
			openCard('wi-container', 'wi-shadow');
			drawJobWorkflow(workflowIllustratorData, 500);
			 
		}, function() {
			// important: reset the value
			$('#workflow-illustrator-job-select').val('');
			$('#workflow-illustrator-job-select').trigger("chosen:updated");
		});
		
	});
	
	/**
	 * 
	 */
	$('#close-wi-card').click(function(e){
		e.preventDefault();
		closeCard('wi-container', 'wi-shadow');
		
	});
	
	/**
	 * selezione destinatari messaggio per pm
	 */	
	$('.admin-compose-select').change(function(){
		mainViewAdmin_refreshSelectedPeople();
	});
	
	/**
	 * 
	 */
	$(document).on('click', '.delete-attachment', function(e){
		e.preventDefault();
		
		$('#modalWn').modal('show');
		
		$.get( $(this).attr('href'))
		.done(function(data) {
			if( !data.res ) alert('Errore salvataggi dati')
			$.each(data.content, function(key, value) {
				$('#'+key).html(value);
			});
		})
		.fail(function() {
			alert( "network error" );
		})
		.always(function() {
			$('#modalWn').modal('hide');
		});
	});
	
	/**
	 * 
	 */
	$('#quick-view-project').change(function(){
		
		window.location.href = $(this).attr('data-baseurl') + '/' + $(this).val();
		
	});
	
	/**
	 * 
	 */
	$('#admin-compose-progetto :checkbox').change(function(){
		var is_checked = $(this).is(':checked');
		$parentItem = $($(this).parents('.dd-item')[0]);
		$('input[type=checkbox]', $parentItem).prop('checked', is_checked);
		
		projectViewAdmin_refreshSelectedPeople();
	});
	
	/**
	 * 
	 */
	$('#new-conversation').click(function(e){
		
		e.preventDefault();
	
		composeNewConversation();
		
	});
	
	/**
	 * gestice l'apertura del form per iniziare una nuova conversazione.
	 * dichiarato come funzione perchè invocato anche programmaticamente oltre al click su "new conversation"
	 */
	function composeNewConversation() {
		$('#reply').attr('disabled', true);
	
		mainViewAdmin_refreshSelectedPeople(); // importante nel caso in cui chiuda e riapra senza modificare i destinatari nel menu sx
		projectViewAdmin_refreshSelectedPeople(); // importante nel caso in cui chiuda e riapra senza modificare i destinatari nel menu sx
	
		$('#compose-shadow').show();
		$('#compose-container').fadeIn("fast");
	}
	
	/**
	 * 
	 */
	$('#reply').click(function(e){
		
		e.preventDefault();
	
		$('#reply').attr('disabled', true);
	
		mainViewAdmin_refreshSelectedPeople(); // importante nel caso in cui chiuda e riapra senza modificare i destinatari nel menu sx
		projectViewAdmin_refreshSelectedPeople(); // importante nel caso in cui chiuda e riapra senza modificare i destinatari nel menu sx
	
		// a differenza di "new conversation" con "reply" vogliono continuare a vedere i messaggi esistenti
		$('#reply-container').fadeIn("fast", function(){
			$('#reply-container').removeClass('hidden');
		});
		
	});
	
	/**
	 * 
	 */
	$('.discard-composition').click(function(e){
		
		e.preventDefault();
	
		if( window.app.default_people.length == 0) {
			// messaging standard
			closeCompose();
		}
		else {
			// messaging vincolato
			closeCard('select-rcpts-container', 'select-rcpts-shadow', true);
			closeCompose();
		}
		
		$(window).scrollTop(0);
		
	});
	
	/**
	 * 
	 */
	$('.discard-reply').click(function(e){
		
		e.preventDefault();
	
		closeReply();
		
	});
	
	/**
	 * 
	 */
	$('#refresh-inbox').click(function(e){
		
		e.preventDefault();
		if( $('#rcvd-messages').length ) {
			$('#rcvd-messages').dataTable().fnDraw();
		} 
		
	});
	
	/**
	 * 
	 */
	$('#refresh-conversation').click(function(e){
		
		e.preventDefault();
		if( $('#conversation').length ) {
			$('#conversation').dataTable().fnDraw();
		} 
		
	});
	
	/**
	 * 
	 */
	$('#submit-new-conversation').click(function(e){
		
		e.preventDefault();
		
		$('#Message0Content').val( $('#summernote-content').summernote('code') );
			
		submitMessagingFormAjax('SendMsgForm', function(){
			toastr.options = {
				closeButton: true,
                progressBar: true,
                preventDuplicates: true,
                onclick: null,
                timeOut: 2000
            };
			toastr.success('Your message has been successfully delivered to the selected people','Conversation created');
			
			// IMPORTANTE: messaggio inviato correttamente, aggiorna il link id degli allegati
			currLinkID = $('#attachment-link-id-compose').val().split("-");
			$('#attachment-link-id-compose').val( currLinkID[0] + '-' + Date.now() );
			
			// resetta la visualizzazione degli allegati
			$('#attachments-container-compose').html('');
			
			// chiudi il compose message
			closeCompose();
			// oltre a chiudere il compose chiudo anche la selezione dei destinatari
			closeCard('select-rcpts-container', 'select-rcpts-shadow');
			
			if( $('#sent-messages').length ) { // se sto visualizzando i messaggi inviati faccio un refresh
				$('#sent-messages').dataTable().fnDraw();
			} 
			
			// scroll in cima
			window.scrollTo(0,0);
			
		}, function(){});
		
	});
	
	/**
	 * 
	 */
	$('#submit-reply').click(function(e){
		
		e.preventDefault();
		
		$('#ReplyContent').val( $('#summernote-reply').summernote('code') );
			
		submitMessagingFormAjax('ReplyMsgForm', function(){
			toastr.options = {
				closeButton: true,
                progressBar: true,
                preventDuplicates: true,
                onclick: null,
                timeOut: 2000
            };
			toastr.success('Your message has been successfully delivered to the selected people', 'Message sent');
			
			// IMPORTANTE: messaggio inviato correttamente, aggiorna il link id degli allegati
			currLinkID = $('#attachment-link-id-reply').val().split("-");
			$('#attachment-link-id-reply').val( currLinkID[0] + '-' + Date.now() );
			
			// resetta la visualizzazione degli allegati
			$('#attachments-container-reply').html('');
			
			closeReply();
			
			// refresh della conversazione 
			$('#conversation').dataTable().fnDraw(); 
			
			// scroll in cima
			window.scrollTo(0,0);
			
		}, function(){});
		
	});
	
	/**
	 * 
	 */
	function closeCompose() {
		// torna alla scheda di selezione dei destinatari
		$('#compose-container').fadeOut("fast");
		$('#compose-shadow').fadeOut(500);
		// resetta tutti i campi
		$('#ConversationSubject').val('');
		$('#ParticipantParticipant').val([]);
		$('#ParticipantParticipant').trigger("chosen:updated");
		$('#TagTag').val([]);
		$('#TagTag').trigger("chosen:updated");
		$('#JobJob').val([]);
		$('#JobJob').trigger("chosen:updated");
		$('#summernote-content').summernote('code', '');
		$('#Message0Content').val('');
		$('.alert-danger', '.mail-body').remove(); // rimuovi ogni messaggio di validazione
		
		// riabilita i pulsanti
		$('#reply').removeAttr('disabled');
		
		$(window).scrollTop(0);
	}
	
	/**
	 * 
	 */
	function closeReply() {
		// torna alla visualizzazione dei messaggi
		// 2019-02-27: ora il blocco reply è sempre visibile, NON lo chiudo più
		/*$('#reply-container').fadeOut("fast", function(){
			$('#reply-container').addClass('hidden');
			$('#messages-container').fadeIn("fast", function(){
				$('#messages-container').removeClass('hidden');
			});
		});*/
		// resetta tutti i campi
		$('#summernote-reply').summernote('code', '');
		$('#ReplyContent').val('');
		$('.alert-danger', '.mail-body').remove(); // rimuovi ogni messaggio di validazione
		
		// riabilita i pulsanti
		$('#reply').removeAttr('disabled');
		
		$(window).scrollTop(0);
	}
	
	/**
	 * 
	 */
	function submitMessagingFormAjax(id, successCallback, alwaysCallback) {
		
		$('#modalWn').modal('show');
		
		var postData = new Object();
		$('input[type=text]', '#'+id).each(function(){
			postData[$(this).attr('name')] = $(this).val();
		});
		$('input[type=hidden]', '#'+id).each(function(){
			postData[$(this).attr('name')] = $(this).val();
		});
		$('input[type=checkbox]', '#'+id).each(function(){
			postData[$(this).attr('name')] = $(this).is(':checked') ? 1 : 0;
		});
		$('textarea', '#'+id).each(function(){
			postData[$(this).attr('name')] = $(this).val();
		});
		$('select', '#'+id).each(function(){
			postData[$(this).attr('name')] = $(this).val();
		});
		
		
		var url = $('#'+id).attr( "action" );
		if( url.indexOf('.json') == -1 ) url += '.json';
		
		$.post( url, postData)
		.done(function(data) { // messaggio inviato correttamente
						
			if( !data.success ) {
				if(id == 'SendMsgForm') {
					$('#validation-errors-compose').html( getMessagingDangerAlert(data.errorMessage) );
				}
				else {
					$('#validation-errors-reply').html( getMessagingDangerAlert(data.errorMessage) );
				}
			}
			else {
				if(data.content) {
					$.each(data.content, function(key, value) {
						$('#'+key).html(value);
					});
				}
				
				successCallback();
			}
		})
		.fail(function() {
			alert( "communication error" );
		})
		.always(function() {
			$('#modalWn').modal('hide');
			alwaysCallback();
		});
		
	}
	
	/**
	 * 
	 */
	function mainViewAdmin_refreshSelectedPeople() {
	
		if(!$('#generic-compose-rcpts-container').length) return;  
	
		selectedPeople = new Array();
		$('.admin-compose-select').each(function(){
			
			var uids = $(this).val() || [];
			selectedPeople = selectedPeople.concat(uids);
		});
		
		if(selectedPeople.length > 0) {
			$('#new-conversation').removeClass('hidden');
			$('#new-conversation-num-rcpts').text('('+selectedPeople.length+')');
			// aggiorna il chosen dei partecipanti
			$('#ParticipantParticipant').val( selectedPeople );
			$('#ParticipantParticipant').trigger("chosen:updated");
		}
		else {
			$('#new-conversation').addClass('hidden');
		}

	}
	
	/**
	 * 
	 */
	function projectViewAdmin_refreshSelectedPeople() {
	
		if(!$('#project-compose-rcpts-container').length) return;  
	
		selectedPeople = new Array();
		$('.person-chk').each(function(){
			
			if( $(this).is(':checked') ) {
				selectedPeople.push( $(this).val() );
			}
				
		});
		
		selectedPeople = jQuery.unique(selectedPeople); // mandatory
		
		if(selectedPeople.length > 0) {
			$('#new-conversation').removeClass('hidden');
			$('#new-conversation-num-rcpts').text('('+selectedPeople.length+')');
			// aggiorna il chosen dei partecipanti
			$('#ParticipantParticipant').val( selectedPeople );
			$('#ParticipantParticipant').trigger("chosen:updated");
		}
		else {
			$('#new-conversation').addClass('hidden');
		}

	}
	
	/**
	 * 
	 */
	$('#open-select-rcpts').click(function(e){
		e.preventDefault();
		
		if(window.app.default_people.length == 0) { 
			// messaging standard, apri selezione destinatari
			openCard('select-rcpts-container', 'select-rcpts-shadow');
		}
		else {
			// apro come gli utenti di default già selezionati (shortcut per clienti)
			$('.admin-compose-select').val(Object.keys(window.app.default_people));
			$('.admin-compose-select').trigger("chosen:updated");
			// select rcpts è sempre spento
			$('#select-rcpts-container').addClass('hidden');
			$('#select-rcpts-shadow').addClass('hidden');
			// i destinari nella composizione del messaggio non sono visibili
			$('#compose-recipients').addClass('hidden');
			// i tag del messaggio non sono visibili
			$('#compose-tags').addClass('hidden');
			// pre-compila il campo subject
			$('#ConversationSubject').val( window.app.defaultConversationSubject );
			// apro direttamente la composizione del messaggio by-passando la selezione
			composeNewConversation();
		}
	});
	
	/**
	 * 
	 */
	$('#close-select-rcpts').click(function(e){
		e.preventDefault();
		closeCard('select-rcpts-container', 'select-rcpts-shadow');
		
	});
	
	/**
	 * 
	 */
	function openCard(cardID, shadowID, openImmediately) {
		
		// importante: disabilito reply (presente se sono in una conversazione)
		$('#reply').attr('disabled', true);
		
		if(cardID == 'select-rcpts-container') {
			$('#open-select-rcpts').hide();
		}
		
		// per praticità non blocco la possibilità per l'utente di aprire un'altra scheda di 
		// una differente funzionalità quando c'è già una scheda aperta, pertanto:
		// verifico la classe della scheda che sto per aprire:
		// - se ha la classe 'messaging-overlay-200' è una scheda al 1° livello -> rimuovi qualunque scheda già aperta
		// - altrimenti è una scheda di secondo livello quindi lo "stack" delle schede rimane com'è
		if( $('#'+cardID).hasClass('messaging-overlay-200') ) { 
			// pulisci lo stack
			$('.messaging-overlay-shadow').hide();
			$('.messaging-overlay-container').hide();
		}
		
		// importante: devo sempre impostare l'altezza della shadow in base all'effettiva altezza del blocco 
		// più alto tra tutti figli VISIBILI del blocco principale (questo perchè il contenitore messaging-body non adatta la sua altezza
		// in base a quella dei children diretti posizionati in modo assoluto)
		// NOTA: devo però considerare anche l'altezza del padre "messaging-body" perchè in certe situazioni (es. reply nella conversazione) ci sono più figli visibili
		// uno sotto all'altro ( = l'altezza del padre è maggiore di quella dei singoli figli)
		var shadowHeight = $('#messaging-body').height(); // init con l'altezza di messaging-body per la precedente considerazione
		$('#messaging-body > div').each(function(){
			if( $(this).is(':visible') ) { // importante, solo quelli visibili!
				var currCardHeight = $(this).height();
				if(currCardHeight > shadowHeight) shadowHeight = currCardHeight;
			}
		});
		$('#'+shadowID).css('height', shadowHeight+'px');
		
		if( openImmediately ) { // in certi casi voglio aprire subito senza animazioni
			$('#'+shadowID).show();
			$('#'+cardID).show();
		}
		else {
			$('#'+shadowID).show();
			$('#'+cardID).fadeIn();
		}
	}
	
	/**
	 * 
	 */
	function closeCard(cardID, shadowID, immediate) {
		
		if(cardID == 'select-rcpts-container') {
			$('#open-select-rcpts').show();
		}
		
		if(immediate) {
			$('#' + cardID).hide();
			$('#' + shadowID).hide();
		}
		else {
			$('#'+cardID).fadeOut({
				complete : function() {
					// nel caso in cui mi trovo in una conversazione:
					// - se non ci sono più schede visibili e il reply non è visibile (solo in questo caso) riabilito il pulsante di reply
					if( $('.messaging-overlay-container:visible').length == 0 && !$('#reply-container').is(':visible') ) {
						$('#reply').removeAttr('disabled');
					}
				}
			});
			$('#'+shadowID).fadeOut(750);	
		}
	}
	
});



function getMessagingDangerAlert(message) {
		return '<div class="alert alert-danger alert-dismissible" role="alert" style="text-align:left">\
			<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>\
			' + message + '\
		</div>';
}
