<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">
    <!-- The above 3 meta tags *must* come first in the head; any other head content must come *after* these tags -->
    <meta name="description" content="">
    <meta name="author" content="">
    <link rel="icon" href="../../favicon.ico">

    <title><?php echo __("Zolle | Mangio");?></title>

    <!-- Bootstrap core CSS -->
    <?php echo $this->Html->css('mangio/bootstrap.min'); // custom version?>
    <link rel="stylesheet" href="//maxcdn.bootstrapcdn.com/font-awesome/4.3.0/css/font-awesome.min.css">
    <link href='http://fonts.googleapis.com/css?family=Montserrat' rel='stylesheet' type='text/css'>

	<?php echo $this->Html->css('mangio/style.css');?>

    <!-- HTML5 shim and Respond.js for IE8 support of HTML5 elements and media queries -->
    <!--[if lt IE 9]>
      <script src="https://oss.maxcdn.com/html5shiv/3.7.2/html5shiv.min.js"></script>
      <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
    <![endif]-->
    
  </head>

	<body role="document">

		


    <div class="container" role="main" style="position: relative; top:-35px">

		
		<div class="row">
		
			<!-- page content -->
			<div id="main-cont" class="col-md-12">
				
				<!-- PAGE CONTENT BEGINS -->
				<?php echo $this->Session->flash(); ?>
				<?php echo $this->fetch('content'); ?>
				<!-- PAGE CONTENT ENDS -->
				
			</div>
			<!-- /page content -->
				
		</div>

    </div> <!-- /container -->

    <!-- Bootstrap core JavaScript
    ================================================== -->
    <!-- Placed at the end of the document so the pages load faster -->
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.2/jquery.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.4/js/bootstrap.min.js"></script>
    <?php echo $this->Html->script('mangio/ordine.provvisorio');?>
    <?php echo $this->Html->script('mangio/spin.min');?>
    <script>
		$(function(){
			
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
			}
			var target = document.getElementById('modalWn')
			var spinner = new Spinner(opts).spin(target);
			
			$('#sidemenu-toggle').click(function(e){
				e.preventDefault();
				if( $('#sidemenu-cont').is(':hidden') ) { // open menu
					openSideMenu();
				}
				else {
					closeSideMenu();
				}
			});
			
			<?php if(!empty($user)):?>
			<?php if( $this->Session->read('showSideMenu') === true ):?>
			openSideMenu();
			<?php else:?>
			closeSideMenu();
			<?php endif;?>
			<?php endif;?>
			
			<?php 
				$extraScript = $this->Session->read('extraScript');
				if(!empty($extraScript)) {
					// usato per visualizzare il toast per le operazioni di modifica spesa / sospesione e attivazione consegna
					echo $extraScript;
					// la variabile extraScript in sessione viene resettata in ConsegneController::afterFilter (qui non posso farlo)
				}
			?>
		});
		
		$(document).on('click', '.open-info', function(e){
			e.preventDefault();
			$('.modal-body', '#info-modal').html( $(e.target).attr('data-content') );
			$('#info-modal').modal('show');
		});
		
		$(document).on('click', '.finalizza-modifiche, .abbandona-modifiche', function(e){
			// il default behavior viene eseguito, visualizzo solo lo spinner come aggiunta dato che sono operazioni lunghe
			$('#modalWn').modal('show');
		});
		
		function openSideMenu() {
			$('#main-cont').attr('class', 'col-md-9');
			$('.width-from-4-to-6-on-sidemenu-open').removeClass('col-md-4').addClass('col-md-6');
			$('.width-from-8-to-6-on-sidemenu-open').removeClass('col-md-8').addClass('col-md-6');
			$('#sidemenu-cont').show();
			$('html, body').animate({ scrollTop: 0 }, 'medium');
			$.get( '<?php echo Router::url(array('controller' => 'consegne', 'action' => 'sidemenu_visible.json'));?>', function() {});
		}
		
		function closeSideMenu() {
			$('#sidemenu-cont').hide();	// close menu
			$('.width-from-6-to-4-on-sidemenu-closed').removeClass('col-md-6').addClass('col-md-4');
			$('.width-from-6-to-8-on-sidemenu-closed').removeClass('col-md-6').addClass('col-md-8');
			$('#main-cont').attr('class', 'col-md-12');
			$.get( '<?php echo Router::url(array('controller' => 'consegne', 'action' => 'sidemenu_hidden.json'));?>', function() {});
		}
		
		function toast(level, content) {
			$('#toast').remove(); // rimuovi l'eventuale toast esistente
			$('body').append('<div id="toast" class="alert alert-' + level + ' alert-dismissible" role="alert">\
					<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>\
					<span id="toast-content">' + content + '</span>\
				</div>'
			);
		}
    </script>
    <?php echo $scripts_for_layout; ?>
  </body>
</html>
