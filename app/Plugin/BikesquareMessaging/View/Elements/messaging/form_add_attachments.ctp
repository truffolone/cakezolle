<?php echo $this->Html->css('BikesquareMessaging.jquery.fileupload');?>

<?php
	$upload_max_filesize = ini_get('upload_max_filesize');
	$upload_max_filesize = substr($upload_max_filesize, 0, strlen($upload_max_filesize)-1).' '.substr($upload_max_filesize, strlen($upload_max_filesize)-1).'B';
?>

<div class="row">
	
	<div class="col-md-12">
		<!-- The fileinput-button span is used to style the file input field as button -->
		<span id="add-files-<?php echo $suffix;?>" class="btn btn-default btn-sm fileinput-button">
			<i class="glyphicon glyphicon-plus"></i>
			<span><?php echo __('Attachments');?></span>
			<!-- The file input field used as target for the file upload widget -->
			<input id="fileupload-<?php echo $suffix;?>" type="file" name="files[]" multiple>
		</span>
		<br/>
		<br/>
		<!-- The global progress bar -->
		<div id="progress-container-<?php echo $suffix;?>" class="hide panel">
			<div class="panel-body">
				<div id="progress-<?php echo $suffix;?>" class="progress">
					<div class="progress-bar progress-bar-primary"></div>
				</div>
				<small><?php echo __('Dimensione massima: ').$upload_max_filesize;?></small>
				
				<div class="text-right">
					<a id="close-form-add-attachments-<?php echo $suffix;?>" href="#"><?php echo __('Close');?></a>
				</div>
			</div>
		</div>
		<!-- The container for the uploaded files -->
		<div id="files-<?php echo $suffix;?>" class="files"></div>
	</div>
	
</div>

<?php echo $this->Html->script('BikesquareMessaging.jquery.ui.widget', array('inline' => false));?>
<!-- The Iframe Transport is required for browsers without support for XHR file uploads -->
<?php echo $this->Html->script('BikesquareMessaging.jquery.iframe-transport', array('inline' => false));?>
<!-- The basic File Upload plugin -->
<?php echo $this->Html->script('BikesquareMessaging.jquery.fileupload', array('inline' => false));?>
				
<?php $this->Html->scriptStart(array('inline' => false)); ?>
/*jslint unparam: true */
/*global window, $ */
$(function () {
	
	$('#close-form-add-attachments-<?php echo $suffix;?>').click(function(e){
		e.preventDefault();
		
		$('#progress-container-<?php echo $suffix;?>').addClass('hide');
	});
	
	$('#add-files-<?php echo $suffix;?>').click(function(){
		
		$('#progress-container-<?php echo $suffix;?>').removeClass('hide');
		
		$('#progress-<?php echo $suffix;?> .progress-bar').css(
			'width',
			'0%'
		);
	});
	
	'use strict';
	// Change this to the location of your server-side upload handler:
	var base_url = "<?php echo Router::url(array('controller' => 'attachments', 'action' => 'upload', $suffix));?>";
	$('#fileupload-<?php echo $suffix;?>').fileupload({
		url: base_url,
		dataType: 'json',
		done: function (e, data) {
			/*$.each(data.result.files, function (index, file) {
				$('<p/>').text(file.name).appendTo('#files-<?php echo $suffix;?>');
			});*/
			
			//$('#progress-<?php echo $suffix;?>').delay(1000).addClass('hide');
			
			$.each(data.response().result.content, function(key, value) {
				$('#'+key).html(value);
			});
	
			// torna alla situazione standard
			$('#progress-container-<?php echo $suffix;?>').addClass('hide');
	
		},
		submit: function(e, data) {
			// IMPORTANTE!: aggiorno l'url con il link id aggiornato (che cambia dinamicamente)
			data.url = base_url  + "/" + $("#attachment-link-id-<?php echo $suffix;?>").val();
			return true; // starts the upload
        },
		error: function() {
			alert('communication error, please reload the page');
		},
		progressall: function (e, data) {
			var progress = parseInt(data.loaded / data.total * 100, 10);
			$('#progress-<?php echo $suffix;?> .progress-bar').css(
				'width',
				progress + '%'
			);
		}
	}).prop('disabled', !$.support.fileInput)
	.parent().addClass($.support.fileInput ? undefined : 'disabled');
});
<?php $this->Html->scriptEnd();?> 
