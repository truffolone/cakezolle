<?php 
	$this->assign('title', 'Newsletter');
	if( empty($this->request->data['Newsletter'] ) ) $this->assign('subtitle', 'Nuova newsletter');
	else $this->assign('subtitle', 'Modifica newsletter');
?> 

<script>
$(function(){
	 $( "#seleziona-destinatari" ).dialog({
     	autoOpen: false,
     	height: 350,
     	width: 500,
     	modal: true,
		buttons: {
        	"Inserisci i destinatari selezionati": function() {
        		
				var submitData = '';
				$('.recipient-query-checkbox').each(function(){
					if( $(this).is(':checked') ) {
						submitData += '&'+$(this).attr('name')+'=1';
						//gestisci la query per le fatture
						if( $(this).attr('id') == 'fattura-ckb' ) {
							submitData += '&'+$('#data-fattura').attr('name')+'='+$('#data-fattura').val();
						}
					}
				});
				if(submitData == '') $( this ).dialog( "close" );

				submitData = submitData.substring(1);

				$.ajax({
				  	type: 'POST',
				  	url: $('#form-destinatari-selezionati').attr("action"),
				  	data: submitData,
				  	success: function(data) {
						var recipients = jQuery.parseJSON( $.trim(data) );
						//carica i valori nel field 'destinatari'
						var currRecipients = $('#destinatari').val().split(';');
						var newRecipients = new Array();
						for(i=0;i<recipients.length;i++) {
							newRecipients.push( recipients[i].email );
						}
						$('#destinatari').val( ($.unique($.merge(currRecipients, newRecipients))).join('; ') );
						$( '#progress-bar' ).fadeOut();
						$( '#open-seleziona-destinatari' ).fadeIn();
					},
					error: function (xhr, ajaxOptions, thrownError) {
						alert(thrownError);
					},
				  	dataType: "text"
				});           

				$( this ).dialog( "close" );
				$( '#open-seleziona-destinatari' ).fadeOut();
				$( '#progress-bar' ).fadeIn();
                                   
           	},
           	Cancel: function() {
           		$( this ).dialog( "close" );
           	}
       	}
	});

	$( "#open-seleziona-destinatari" ).button({
    	icons: {
    		primary: "ui-icon-newwin"
       	},
       	text: 'seleziona destinatari'
   	});

	$( "#open-seleziona-destinatari" ).click(function(e){
		$( "#seleziona-destinatari" ).dialog( "open" );
		e.preventDefault();
	});

	var config = {
			toolbar:
			[
				['Bold', 'Italic', 'Underline', '-', 'NumberedList', 'BulletedList', '-', 'Link', 'Unlink'],
				[ 'TextColor','BGColor' ],
				['Table'],
				['Undo','Redo','-','Find','Replace','-','SelectAll','RemoveFormat'],
				['Format','Font','FontSize'],
				['Image', 'Iframe', 'Flash'],
				['Source']
			],
			baseHref: 'http://zolle.impronta48.it',
			baseUrl: 'http://zolle.impronta48.it',
			//devo forzare il filebrowserBrowseUrl e il filebrowserImageBrowseUrl perchè se edito una precedente newsletter aggiunge nel path il nome della view e non trova kcfinder!
			filebrowserBrowseUrl : '<?php echo $this->webroot;?>kcfinder/browse.php', 			
			filebrowserImageBrowseUrl : '<?php echo $this->webroot;?>kcfinder/browse.php?type=images&CKEditor=NewsletterContent&CKEditorFuncNum=2&langCode=it',
			language: 'it'
		};
	$( 'textarea.ckeditor').each( function() {
    		CKEDITOR.replace( $(this).attr('id'), config);
	});
});
</script>


<div id="newsletter-form-dialog" class="ui-dialog ui-widget ui-widget-content ui-corner-all">
	<div class="ui-dialog-titlebar ui-widget-header ui-corner-all ui-helper-clearfix">
		<span id="ui-dialog-title-newsletter-form-dialog" class="ui-dialog-title">
			<?php
				if(isset($this->data['Newsletter']['id'])) echo 'Modifica Newsletter '.$this->data['Newsletter']['id'];
				else echo 'Crea nuova Newsletter';
			?>
		</span>
	</div>
	<div class="ui-dialog-content ui-widget-content">
		
		<?php echo $this->Form->create('Newsletter');?>
	
		<label>Destinatari</label>&nbsp;&nbsp;&nbsp;
		<a href="#" id="open-seleziona-destinatari">seleziona destinatari</a>
		<span id="progress-bar" style="display:none"><?php echo $this->Html->image('loading-trans.gif');?> attendere prego, caricamento destinatari in corso...</span>
		<div class="ui-state-highlight ui-corner-all alert">
			<p><span class="ui-icon ui-icon-info" style="float: left; margin-right: .3em;"></span>
			<strong>Formato:</strong> separare eventuali destinatari inseriti manualmente con il carattere punto e virgola (;) I destinatari possono esseri indirizzi email o codici cliente</p>
		</div>
		<?php echo $this->Form->input('destinatari', array('type' => 'textarea', 'required' => true, 'label' => false, 'style' => 'width:100%', 'id' => 'destinatari'));?>
	
		<br>
		<label>Oggetto</label><br>
		<?php echo $this->Form->input('subject', array('required' => true, 'label' => false));?>
	
		<br>
		<label>Messaggio</label><br>
		<div class="ui-state-highlight ui-corner-all alert">
			<p><span class="ui-icon ui-icon-info" style="float: left; margin-right: .3em;"></span>
			<strong>Substitution Tags</strong><br><br>
				<span style="color:green">$NOME$</span>: nome cliente
				<br>
				<span style="color:green">$LINK_ACCESSO$</span>: link di accesso all'area riservata (pagina del profilo cliente)
				<br>
				<span style="color:green">$LINK_FATTURE$</span>: link di accesso per visualizzare le fatture sull'area riservata
				<br>
				<span style="color:green">$LINK_MERCATO_LIBERO$</span>: link di accesso diretto al Mercato Libero
			</p>
		
			<br>
			<div class="ui-state-error ui-corner-all">
				<p><span class="ui-icon ui-icon-info" style="float: left; margin-right: .3em;"></span>
					<span>
						se il contenuto della newsletter è un frammento html che viene copiato/incollato nell'editor, controllare
						l'esattezza dei substitution tag visualizzando direttamente il sorgente (pulsante "Codice Sorgente")
					</span>
				</p>
			</div>
			<br>

		</div>
		<?php echo $this->Form->input('content', array('type' => 'textarea', 'label' => false, 'style' => 'width:100%;', 'class' => 'ckeditor'));?>

		<br>
		<?php echo $this->Form->end( array('id' => 'procedi', 'label' => 'Procedi', 'title' => 'è possibile confermare nella schermata successiva prima dell\'effettivo invio', 'class' => 'button') );?>

	</div>
</div> 

<?php echo $this->element('operazione_in_corso', array('dialog_id' => 'edit_in_corso', 'button_id' => 'procedi'));?>

<div id="seleziona-destinatari" title="Seleziona i destinatari della Newsletter">
	<div class="ui-state-highlight ui-corner-all alert">
		<p><span class="ui-icon ui-icon-info" style="float: left; margin-right: .3em;"></span>
		<strong>Nota:</strong> i destinatari selezionati verranno aggiunti al campo "destinatari". Se un destinatario è già presente verrà sovrascritto</p>
	</div>
	<?php echo $this->Form->create('Newsletter', array('id' => 'form-destinatari-selezionati', 'action' => 'select_recipients'));?>
	<?php
		echo $this->Form->input('lun', array('type' => 'checkbox', 'hiddenField' => false, 'label' => 'clienti che ricevono il lunedi', 'class' => 'recipient-query-checkbox'));
		echo '<hr>';		
		echo $this->Form->input('mar', array('type' => 'checkbox', 'hiddenField' => false, 'label' => 'clienti che ricevono il martedi', 'class' => 'recipient-query-checkbox'));
		echo '<hr>';			
		echo $this->Form->input('mer', array('type' => 'checkbox', 'hiddenField' => false, 'label' => 'clienti che ricevono il mercoledi', 'class' => 'recipient-query-checkbox'));
		echo '<hr>';			
		echo $this->Form->input('gio', array('type' => 'checkbox', 'hiddenField' => false, 'label' => 'clienti che ricevono il giovedi', 'class' => 'recipient-query-checkbox'));
		echo '<hr>';	
		echo $this->Form->input('ven', array('type' => 'checkbox', 'hiddenField' => false, 'label' => 'clienti che ricevono il venerdi', 'class' => 'recipient-query-checkbox'));
		echo '<hr>';	
		echo $this->Form->input('fattura', array('id' => 'fattura-ckb', 'type' => 'checkbox', 'hiddenField' => false, 'label' => 'clienti con almeno una fattura emessa nel giorno ', 'class' => 'recipient-query-checkbox'));
		echo $this->Form->input('data_fattura', array('id' => 'data-fattura', 'label' => false, 'style' => 'width:100px'));
		echo '<hr>';	
	?>
	<script>
		$(function(){
			$('#data-fattura').datepicker({ dateFormat: "yymmdd" });
		});
	</script>
	<?php echo $this->Form->end(array('style' => 'display:none'));?>
</div>
