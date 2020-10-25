var $tabToRemove = ''; // gestione chiusura tabs

// jQuery $('document').ready(); function 
$('document').ready(function(){

	// inizializza i tab (se ce ne sono)
	$('#kenn-tabs a').on('click', handleTabClose);

	// --- gestione progress ---
	$('#modalWn').modal({
		backdrop: 'static',
		keyboard: false,
		show: false
	});
	var opts = {
	  lines: 13 // The number of lines to draw
	, length: 25 // The length of each line
	, width: 14 // The line thickness
	, radius: 25 // The radius of the inner circle
	, scale: 1 // Scales overall size of the spinner
	, corners: 1 // Corner roundness (0..1)
	, color: '#000' // #rgb or #rrggbb or array of colors
	, opacity: 0.25 // Opacity of the lines
	, rotate: 0 // The rotation offset
	, direction: 1 // 1: clockwise, -1: counterclockwise
	, speed: 1 // Rounds per second
	, trail: 60 // Afterglow percentage
	, fps: 20 // Frames per second when using setTimeout() as a fallback for CSS
	, zIndex: 2e9 // The z-index (defaults to 2000000000)
	, className: 'spinner' // The CSS class to assign to the spinner
	, top: '50%' // Top position relative to parent
	, left: '50%' // Left position relative to parent
	, shadow: false // Whether to render a shadow
	, hwaccel: false // Whether to use hardware acceleration
	, position: 'absolute' // Element positioning
	};
	var target = document.getElementById('modalWn')
	var spinner = new Spinner(opts).spin(target);
	// --- /gestione progress ---

	// PANELS

	// panel close
	$('.panel-close').click(function(e){
		e.preventDefault();
		$(this).parent().parent().parent().parent().fadeOut();
	});

	$('.panel-minimize').click(function(e){
		e.preventDefault();
		var $target = $(this).parent().parent().parent().next('.panel-body');
		if($target.is(':visible')) { $('i',$(this)).removeClass('fa-chevron-up').addClass('fa-chevron-down'); }
		else { $('i',$(this)).removeClass('fa-chevron-down').addClass('fa-chevron-up'); }
		$target.slideToggle();
	});
	$('.panel-settings').click(function(e){
		e.preventDefault();
		$('#myModal').modal('show');
	});

	$('.fa-hover').click(function(e){
		e.preventDefault();
		var valued= $(this).find('i').attr('class');
		$('.modal-title').html(valued);
		$('.icon-show').html('<i class="' + valued + ' fa-5x "></i>&nbsp;&nbsp;<i class="' + valued + ' fa-4x "></i>&nbsp;&nbsp;<i class="' + valued + ' fa-3x "></i>&nbsp;&nbsp;<i class="' + valued + ' fa-2x "></i>&nbsp;&nbsp;<i class="' + valued + ' "></i>&nbsp;&nbsp;');
		$('.modal-footer span.icon-code').html('"' + valued + '"');
		$('#myModal').modal('show');
	});
});

function openPanel(panelID) {
	var $target = $('.panel-body', panelID);
	if(!$target.is(':visible')) { 
		$('i',$('.panel-minimize', panelID)).removeClass('fa-chevron-down').addClass('fa-chevron-up'); 
		$target.slideToggle();
	}
}

// ===========================================================
// Sweet alert
// ===========================================================
	/**
	 * 
	 */
	function confirmDelete(onDeleteConfirmed) {
		swal({
			title: window.app.sweetAlertDeleteQuestion,
			text: "",
			type: "warning",
			showCancelButton: true,
			confirmButtonColor: "#DD6B55",
			confirmButtonText: window.app.sweetAlertDeleteBtnDeleteLabel,
			cancelButtonText: window.app.sweetAlertDeleteBtnCancelLabel,
			closeOnConfirm: true
		}, function(){ // senza questo wrap il callback viene eseguito sempre!
			onDeleteConfirmed();
		});
	}

// =============================================================
// AJAX POST E GET
// =============================================================
	
	/**
	 * makes two chained post without caring about result of first post
	 */
	function postThenPost(firstFormID, secondFormID, successCallback, alwaysCallback) {
		
		$('#modalWn').modal('show');
		
		var postData1 = getPostData(firstFormID);
		var postUrl1 = getPostAction(firstFormID);
		
		var postData2 = getPostData(secondFormID);
		var postUrl2 = getPostAction(secondFormID);
		
		$.post( postUrl1, postData1)
		.done(function(data) {
			$.post( postUrl2, postData2)
			.done(function(data) {
				handleAjaxResponse(data);
				successCallback();
			})
			.fail(function() {
				alert( "error" );
			})
			.always(function() {
				$('#modalWn').modal('hide'); // come prima istruzione per evitare che alwaysCallback abbia un errore e blocchi
				alwaysCallback();
			});
		
		})
		.fail(function() {
			alert( "error" );
			$('#modalWn').modal('hide');
		});
		
	}

	/**
	 * makes a post (without caring about result) and a successive get
	 */
	function postThenGet(formID, getUrl, successCallback, alwaysCallback) {
	
		$('#modalWn').modal('show');
		
		var postData = getPostData(formID);
		var postUrl = getPostAction(formID);
		
		$.post( postUrl, postData)
		.done(function(data) {
			$.get(getUrl)
			.done(function(data) {
				handleAjaxResponse(data);
				successCallback();
			})
			.fail(function() {
				alert( "error" );
			})
			.always(function() {
				$('#modalWn').modal('hide'); // come prima istruzione per evitare che alwaysCallback abbia un errore e blocchi
				alwaysCallback();
			});
		
		})
		.fail(function() {
			alert( "error" );
			$('#modalWn').modal('hide');
		});
	
	}

	/**
	 * perform a get via ajax
	 */
	function ajaxGet($aAnchor, successCallback, alwaysCallback) {
		
		$('#modalWn').modal('show');
		
		$.get( $aAnchor.attr('href'))
		.done(function(data) {
			handleAjaxResponse(data);
			successCallback();
		})
		.fail(function() {
			alert( "error" );
		})
		.always(function() {
			$('#modalWn').modal('hide'); // come prima istruzione per evitare che alwaysCallback abbia un errore e blocchi
			alwaysCallback();
		});
	}
	
	/**
	 * perform a get via ajax directly from a url
	 */
	function ajaxGetFromURL($aUrl, successCallback, alwaysCallback) {
		
		$('#modalWn').modal('show');
		
		$.get( $aUrl )
		.done(function(data) {
			handleAjaxResponse(data);
			successCallback();
		})
		.fail(function() {
			alert( "error" );
		})
		.always(function() {
			$('#modalWn').modal('hide'); // come prima istruzione per evitare che alwaysCallback abbia un errore e blocchi
			alwaysCallback();
		});
	}

	/**
	 * 
	 */
	function submitFormAjax(id, successCallback, alwaysCallback) {
		
		$('#modalWn').modal('show');
		
		var postData = getPostData(id);
		var url = getPostAction(id);
		
		$.post( url, postData)
		.done(function(data) {
			if( !data.res ) {
				if( !data.errorMessage ) alert('Errore salvataggi dati');
			}
			
			if( data.errorMessage ) {
				$.each(data.errorMessage, function(key, value) {
					$('#'+key).prepend( getDangerAlert(value) );
				});
			}
			else {
				handleAjaxResponse(data);
				
				successCallback();
			}
		
		})
		.fail(function() {
			alert( "error" );
		})
		.always(function() {
			$('#modalWn').modal('hide');  // come prima istruzione per evitare che alwaysCallback abbia un errore e blocchi
			alwaysCallback();
		});
		
	}
	
	/**
	 * 
	 */
	function getPostData(id) {
		
		var postData = new Object();
		$('input', '#'+id).each(function(){
			postData[$(this).attr('name')] = $(this).val();
		});
		$('textarea', '#'+id).each(function(){
			postData[$(this).attr('name')] = $(this).val();
		});
		$('select', '#'+id).each(function(){
			postData[$(this).attr('name')] = $(this).val();
		});
		return postData;
	}
	
	/**
	 * 
	 */
	function getPostAction(id) {
		var url = $('#'+id).attr( "action" );
		if( url.indexOf('.json') == -1 ) url += '.json';
		return url;
	}
	
	/**
	 * 
	 */
	function handleAjaxResponse(data) {
		$.each(data.content, function(key, value) {
			$('#'+key).html(value);
		});
		if(data.jsondata) {
			$.each(data.jsondata, function(key, value) { // aggiorna le variabili globali passate
				window[key] = jQuery.parseJSON(value);
			});
		}
	}


// ------------------ FUNZIONI GESTIONE TABS -------------------------
			function appendTabs(tabs) {
	
				$('#modalWn').modal('show');
				
				var aData = {}; // deve essere un oggetto (e non un array!)
				for(var i in tabs) {
					aData[i] = {
						url : tabs[i].url, // non posso usarlo come chiave perchè i dot (.) verrebbero interpretati come sotto chiavi js
						name: tabs[i].name, 
						cls: '',
						canBeClosed: true,
					};
				}
		
				$.ajax({
					type: "POST",
					url: window.app.tabsAppendURL + '.json',
					data: aData,
					dataType: "json"
				}).
				done(function(data){
					
					// redirect to first tab added
					window.location.href = tabs[0].url;
					/*if(data.tabs) {
						$('#tabs-container').html(data.tabs);
						// important! refresh event handler
						$('#kenn-tabs a').on('click', handleTabClose);
					}*/
					
				})
				.fail(function() {
					alert( "error" );
					$('#modalWn').modal('hide');
				})
				.always(function() {
				});
			}
		
			function handleTabClose(e){				
				$anchor = $(e.target);
				if( !$anchor.hasClass('btn') ) { // cliccato sul testo e non sul bottone, passa al parent
					$anchor = $anchor.parent();
				} 
				
				if( !$anchor.hasClass('close-tab') ) return;
				
				e.preventDefault(); // dopo!
				
				$tabToRemove = $($anchor.parents('.kenn-tab')[0]);
				
				var aUrl = $('a.job-tab', $tabToRemove).attr('href');
				
				if( $anchor.hasClass('active') ) {
					window.location = window.app.tabsCloseActiveURL + '?url=' + aUrl;
				}
				else {
					$('#modalWn').modal('show');
					$.ajax({
						type: "POST",
						url: window.app.tabsCloseInactiveURL + '.json',
						data: { 
							url: aUrl,
						},
						dataType: "json"
					}).
					done(function(data){
						if(data.tabs) {
							$('#tabs-container').html(data.tabs);
							// important! refresh event handler
							$('#kenn-tabs a').on('click', handleTabClose);
						}
					})
					.fail(function() {
						alert( "error" );
					})
					.always(function() {
						$('#modalWn').modal('hide');
					});
				}
			}
			// ------------------ FUNZIONI GESTIONE TABS -------------------------
