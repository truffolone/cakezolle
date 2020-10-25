<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">
    <!-- The above 3 meta tags *must* come first in the head; any other head content must come *after* these tags -->
    <meta name="description" content="">
    <meta name="author" content="">
    <link rel="shortcut icon" type="image/x-icon" href="https://zolle.it/wp-content/uploads/2018/09/zolle-logo_cerchio_magenta.png">
    <link rel="apple-touch-icon" href="https://zolle.it/wp-content/uploads/2018/09/zolle-logo_cerchio_magenta.png"/>

    <title><?php echo __("Zolle | Mangio");?></title>

	<?php 
		$isLayoutStandard = Configure::read('mangio_layout') == 'standard';
	?>

    <!-- Bootstrap core CSS -->
    <?php echo $this->Html->css('mangio/bootstrap.min'); // custom version?>
    <link rel="stylesheet" href="//maxcdn.bootstrapcdn.com/font-awesome/4.3.0/css/font-awesome.min.css">
    <link href='//fonts.googleapis.com/css?family=Montserrat' rel='stylesheet' type='text/css'>

	<?php echo $isLayoutStandard ? 
		$this->Html->css('mangio/stylesheets/zolle.css') : 
		$this->Html->css('mangio/stylesheets/zolle.natale.css');?>

    <!-- HTML5 shim and Respond.js for IE8 support of HTML5 elements and media queries -->
    <!--[if lt IE 9]>
      <script src="https://oss.maxcdn.com/html5shiv/3.7.2/html5shiv.min.js"></script>
      <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
    <![endif]-->
    
    <!-- Begin Cookie Consent plugin by Silktide - http://silktide.com/cookieconsent -->
	<script type="text/javascript">
		window.cookieconsent_options = {"message":"Questo sito utilizza i cookies per offrirti la migliore esperienza possibile","dismiss":"Accetto","learnMore":"Cookies Policy","link":"http://www.zolle.it/web/wp-content/themes/zolle/cookies-policy.html","theme":"dark-bottom"};
	</script>

	<script type="text/javascript" src="//cdnjs.cloudflare.com/ajax/libs/cookieconsent2/1.0.10/cookieconsent.min.js"></script>
	<!-- End Cookie Consent plugin -->
    
    <style>
		.opacizzato {
			color: #ccc !important;
		}
		
		#toast > .close > span {
			color: #000 !important;
			font-size: 40px !important;
		}
		
		.apri-ordine-provvisorio {
			color: #000 !important;
		}
	</style>
	
	<!-- toastr -->
	<link href="//cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css" rel="stylesheet" type="text/css">
	
  </head>

	<?php 
		$user = $this->Session->read('Auth.User');
		$Mlp = ClassRegistry::init('Mlp');
		$Mlv = ClassRegistry::init('Mlv');
		
		$dataAcquistoMlv = $Mlv->getDataAcquistoOrNull();
		$dataAcquistoMlp = $Mlp->getDataAcquistoOrNull();
	?>

	<body role="document">

		<!-- Fixed navbar -->
		<nav class="navbar navbar-default navbar-fixed-top">
			<div class="container">
				<div class="navbar-header">
					<?php echo $this->Html->link($this->Html->image($isLayoutStandard ? 'mangio/logo_zolle.png' : 'mangio/logo_zolle.png', array('id' => 'logo')), array('plugin' => null, 'controller' => 'categorie_web', 'action' => 'index'), array('class' => '', 'escape' => false));?>
					<span id="sitephrase" class="hidden-xs hidden-sm"><?php echo __("cibo con i piedi per terra");?></span>
				</div>
				<div id="navbar" class="navbar-collapse collapse">
					<?php if(!empty($user)):?>
					<ul class="nav navbar-nav">		
						<li id="sidemenu-toggle-cont">
							<a href="#" id="sidemenu-toggle"><i class="fa fa-list fa-2x"></i></a>
						</li>			
					</ul>
					<?php endif;?>
					<ul class="nav navbar-nav navbar-right" id="header-right-menu">
						<?php if(!empty($user)):?>
						<?php
							$cliente = $this->Session->read('cliente');
							$contrattoDaRiattivare = $this->Session->read('contrattoDaRiattivare');
						?>
						
						<!--<?php $num_unread = $this->Session->read('num_unread');?>
						<?php if($num_unread > 0):?>
						<li class="reminder-adyen">
							<a href="<?=Router::url(['plugin' => 'messaging', 'controller' => 'messages', 'action' => 'chat', $user['cliente_id']]);?>">
								<i class="fa fa-envelope fa-2x icon-animated-bell"></i> <span class="badge" style="background:red"><?= $num_unread;?></span>
							</a>
						</li> 
						<?php endif;?>-->
						
						<?php if($contrattoDaRiattivare != null) echo $this->element('mangio/reminder-passaggio-adyen');?>
						<?php
							$operazioniProvvisorie = $this->Session->read('operazioniProvvisorie');
							$numOperazioniProvvisorie = empty($operazioniProvvisorie) ? 0 : sizeof($operazioniProvvisorie);
							echo $this->element('mangio/reminder', array('numOperazioniDaConfermare' => $numOperazioniProvvisorie));
						?>
						<li class="dropdown orange">
							<a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-expanded="false"><i class="fa fa-user fa-2x"></i>&nbsp;&nbsp;&nbsp;<span class="hidden-xs hidden-sm"><?php echo $this->Session->read('NOME');?></span><span class="hidden-lg hidden-md"></span> <span class="caret"></span></a>
							<ul class="dropdown-menu" role="menu">
								<li><a href="<?php echo Router::url(array('plugin' => null, 'controller' => 'clienti', 'action' => 'profilo'));?>"><?php echo __("Profilo e fatture");?></a></li>
								<li><a href="<?php echo Router::url(array('plugin' => null, 'controller' => 'consegne', 'action' => 'index'));?>"><?php echo __("Le tue zolle");?></a></li>
								<?php if($user['group_id'] != CLIENTE_NO_ML):?>
									<li><a href="<?php echo Router::url(array('plugin' => null, 'controller' => 'categorie_web', 'action' => 'index'));?>"><?php echo __("Mercato Libero");?></a></li>
								<?php endif;?>
								<?php if($user['group_id'] != CLIENTE_SENZA_OPERATIVITA):?>
									<li><a href="#" class="apri-ordine-provvisorio"><?php echo __("Ordine provvisorio");?></a></li>
								<?php endif;?>	
								<li class="divider"></li>
								
								<!--<li><a href="<?php echo Router::url(array('plugin' => 'messaging', 'controller' => 'messages', 'action' => 'chat', $user['cliente_id']));?>"><?php echo __("Chat");?></a></li>
								<li class="divider"></li>-->
								
								<li><a href="<?php echo Router::url(array('plugin' => null, 'controller' => 'users', 'action' => 'logout'));?>"><?php echo __("Logout");?></a></li>
							</ul>
						</li>
						<?php endif;?>
					</ul>
				</div><!--/.nav-collapse -->
			</div>
		</nav>

		<?php if($isLayoutStandard):?>
		<div id="subheader" style="background-image: url(<?php echo FULL_BASE_URL . $this->webroot;?>img/<?php echo $subheaderBkg;?>), url(<?php echo FULL_BASE_URL . $this->webroot;?>img/mangio/bg-01.jpg);">
		<?php else:?>
		<div id="subheader" style="background-image: url(<?php echo FULL_BASE_URL . $this->webroot;?>img/mangio/bg-natale-new.jpg), url(<?php echo FULL_BASE_URL . $this->webroot;?>img/<?php echo $subheaderBkg;?>?t=natale), url(<?php echo FULL_BASE_URL . $this->webroot;?>img/mangio/bg-01.jpg?t=natale);">
		<?php endif;?>
		
		
			<div class="container">

				<h1 class="title white"><?php echo $this->fetch('title'); ?></h1>
				
			</div>
			
		</div>


	<div class="container" role="main" style="position: relative; top:-35px">
		
				<!-- submenu -->
				<?php
					$clsProfilo = '';
					$clsZolle = '';
					$clsMercatoLibero = '';
					$clsMlp = '';
					
					if( $this->params['controller'] == 'clienti' ) {
						$clsProfilo = 'bkg-orange white';
					}
					if( in_array($this->params['controller'], array('consegne', 'zolle')) ) {
						$clsZolle = 'bkg-yellow white';
					}
					if( in_array($this->params['controller'], array('articoli', 'categorie_web')) ) {
						$env = $this->Session->read('env');
						if($env == 'MLV') $clsMercatoLibero = 'bkg-green white';
						else $clsMlp = 'bkg-mlp white';
					}
				?>
				
				<?php if(!empty($user)):?>
				
				<div class="row" id="submenu">
					<div class="col-xs-12">
						<div class="btn-group btn-group-justified">
							
							<?php if($Mlv->isTabVisible()):?>
							<?php if($user['group_id'] != CLIENTE_NO_ML):?>
							<a href="<?php echo Router::url(array('plugin' => null, 'controller' => 'categorie_web', 'action' => 'index', '?' => array('env' => 'mlv')));?>" class="<?php echo $clsMercatoLibero;?> green btn btn-default">
								<span>
									<?php
										if(!empty($dataAcquistoMlv)) {
											if(date('W') == date('W', strtotime($dataAcquistoMlv))) {
												echo __("PRODOTTI PER QUESTA SETTIMANA");
											}
											else {
												echo __("PRODOTTI PER IL %s", array(date('d/m/Y', strtotime($dataAcquistoMlv))));
											}
										}
										else {
											echo __('PRODOTTI PER QUESTA SETTIMANA');
										}
									?>
								</span>
							</a>
							<?php endif;?>
							<?php endif;?>
							
							<?php if($Mlp->isTabVisible()):?>
							<?php if($user['group_id'] != CLIENTE_NO_ML):?>
							<a href="<?php echo Router::url(array('plugin' => null, 'controller' => 'categorie_web', 'action' => 'index', '?' => array('env' => 'mlp')));?>" class="<?php echo $clsMlp;?> mlp btn btn-default">
								<span>
									<?php
										if(!empty($dataAcquistoMlp)) {
											if(date('W') == date('W', strtotime($dataAcquistoMlp))) {
												echo __("PRODOTTI PER LA PROSSIMA SETTIMANA");
											}
											else {
												echo __("PRODOTTI PER IL %s", array(date('d/m/Y', strtotime($dataAcquistoMlp))));
											}
										}
										else {
											echo __('PRODOTTI PER LA PROSSIMA SETTIMANA');
										}
									?>
								</span>
							</a>
							<?php endif;?>
							<?php endif;?>
							
							<a href="<?php echo Router::url(array('plugin' => null, 'controller' => 'consegne', 'action' => 'index'));?>" class="<?php echo $clsZolle;?> yellow btn btn-default">
								<span><?php echo __("LE TUE ZOLLE");?></span>
							</a>
							
							<a href="<?php echo Router::url(array('plugin' => null, 'controller' => 'clienti', 'action' => 'profilo'));?>" class="<?php echo $clsProfilo;?> orange btn btn-default">
								<span><?php echo __("PROFILO E FATTURE");?></span>
							</a>
							
							
						</div>
					</div>
					
				</div>
				<!-- /submenu -->
	
				<?php else:?>
				
				<br/><br/><br/><br/>
				
				<?php endif;?>

		<br/>
		
		<!-- info contratto -->
		<?php if(!empty($user)):?>
		<?php $infoContratto = $this->Session->read('infoContratto');?>
		<?php if(!empty($infoContratto)):?>
		<div class="alert alert-info">
			<button type="button" class="close" data-dismiss="alert">
				<i class="ace-icon fa fa-times"></i>
			</button>
			<?php echo $infoContratto;?>
		</div>
		<?php endif;?>
		<?php endif;?>
		<!-- /info contratto -->
		
		<!-- INFO MERCATO LIBERO -->
		<?php if(!empty($user)):?>
		<?php if($user['group_id'] != CLIENTE_STANDARD):?>
		<div class="alert alert-warning">
			<?php echo __("L'acquisto su Mercato Libero è attivo dalle ore 18.00 del <u>lunedì alle ore 24.00 del mercoledì: in ogni momento puoi consultare il listino dei prodotti di Mercato Libero e i tuoi ordini.</u>");?>
		</div>
		<?php endif;?>
		<?php endif;?>
		<!-- /INFO MERCATO LIBERO -->
		
		<!-- breadcrumb -->
		<?php echo $this->fetch('breadcrumb');?>
		<!-- /breadcrumb -->
		
		<div class="row">
		
			<!-- side menu -->
			<?php if(!empty($user)):?>
			<div id="sidemenu-cont" class="col-md-3">
				
				<!-- prossimo MLV -->
				<?php if(empty($dataAcquistoMlv)): // visibile solo a ML CHIUSO?>
				<?php if( $user['group_id'] == CLIENTE_STANDARD ):?>
				<div class="bkg-yellow">
					<div class="panel-body white" id="sidemenu-ml">
						<?php if(!empty($user)):?>
						<?php 
							echo $this->element('mangio/sidemenu_ml');
						?>
						<?php endif;?>
					</div>
				</div>	
				<?php endif;?>
				<?php endif;?>
				<!-- /prossimo MLV -->
				
				<!-- categorie mercato libero -->
				<?php 
					$showCategorie = $this->Session->read('showCategorie');
					$env = $this->Session->read('env');
					$env = strtolower($env);
					$shopping_type = Configure::read('shopping_type.' . $env);
					if($showCategorie && $shopping_type == 'normal') { // posso vedere le categorie solo in modalità normal
						echo $this->element('mangio/categorie_web', array('user' => $user));
					}
				?>
				<!-- /categorie mercato libero -->
				
			</div>
			<?php endif;?>
			<!-- /side menu -->
		
			<!-- page content -->
			<div id="main-cont" class="<?php echo empty($user) ? 'col-md-12' : 'col-md-9';?>">
				
				<!-- PAGE CONTENT BEGINS -->
				<?php echo $this->Session->flash(); ?>
				<?php echo $this->fetch('content'); ?>
				<!-- PAGE CONTENT ENDS -->
				
			</div>
			<!-- /page content -->
				
		</div>

    </div> <!-- /container -->

	<footer class="footer">
		<div class="container">
			<div class="row">
				
				<div class="col-md-3 footer-col">
					<h3 class="markerfelt">Zolle</h3>
					<h5 class="markerfelt"><?php echo __("cibo con i piedi per terra");?></h5>
					<p>
					<?php echo __("footer descrizione zolle");?> 
					</p>
					<br/>
					<div class="container-fluid">
						<div class="row" id="footer-social-links-cont">
							<div class="col-xs-3">
								<a href="https://www.facebook.com/Zolle.it/" target="_blank">
									<i class="fa fa-2x fa-facebook-official"></i>
								</a>
							</div>
							<div class="col-xs-3">
								<a href="https://www.youtube.com/user/ZolleACasa" target="_blank">
									<i class="fa fa-2x fa-youtube"></i>
								</a>
							</div>
							<div class="col-xs-3">
								<a href="http://www.zolle.it" target="_blank">
									<i class="fa fa-2x fa-home"></i>
								</a>
							</div>
							<div class="col-xs-3">
								<!--<a href="#"><i class="fa fa-2x fa-pinterest-square"></i></a>-->
								<a href="mailto:mangio@zolle.it">
									<i class="fa fa-2x fa-envelope"></i>
								</a>
							</div>
						</div>
					</div>
				</div>
			
				<div class="col-md-3 footer-col">
					<?php if(!empty($user)):?>
					<h4 class="title"><?php echo __("NAVIGAZIONE");?></h4>
					<ul id="navigazione-footer">
						<li><a href="<?php echo Router::url(array('plugin' => null, 'controller' => 'clienti', 'action' => 'profilo'));?>"><?php echo __("Profilo e fatture");?></a></li>
						<li><a href="<?php echo Router::url(array('plugin' => null, 'controller' => 'consegne', 'action' => 'index'));?>"><?php echo __("Le tue zolle");?></a></li>
						<?php if( $user['group_id'] != CLIENTE_NO_ML ):?>
							<li><a href="<?php echo Router::url(array('plugin' => null, 'controller' => 'categorie_web', 'action' => 'index'));?>"><?php echo __("Mercato Libero");?></a></li>
						<?php endif;?>
					</ul>
					<?php endif;?>
				</div>
				
				<div class="col-md-3 footer-col">
					<?php if(!empty($user)):?>
					<?php if( $user['group_id'] != CLIENTE_NO_ML ):?>
						<h4 class="title"><?php echo __("TAG");?></h4>
						<?php if(!empty($categorieWeb)):?>
						<div id="tags-footer-container">
							<!--<?php foreach($categorieWeb as $c):?>
								<a href="<?php echo Router::url(array('plugin' => null, 'controller' => 'articoli', 'action' => 'index', 'categoria_web:'.$c['CategoriaWeb']['id']));?>"><span class="label"><?php echo $c['CategoriaWeb']['NOME'];?></span></a>&nbsp;
							<?php endforeach;?>-->
							<?php if(isset($tags[0])):?>
							<?php foreach($tags[0] as $t):?>
								<a href="<?php echo Router::url(array('plugin' => null, 'controller' => 'articoli', 'action' => 'index', 'tag:'.$t['TagCategoriaWeb']['tag']));?>"><span class="label"><?php echo $t['TagCategoriaWeb']['tag'];?></span></a>&nbsp;
							<?php endforeach;?>
							<?php endif;?>
						</div>
						<?php endif;?>
					<?php endif;?>
					<?php endif;?>
				</div>
				
				<div class="col-md-3 footer-col" id="footer-contatti">
					<h4 class="title"><?php echo __("CONTATTI");?></h4>
					
					<h4><?php echo __("Le Zolle S.r.l.");?></h4>
					<p>
						<?php echo __("via Giuseppe Belluzzo 55
						<br/>
						00149 Roma");?>
					</p>
					<p>
						<?php echo __("P.IVA: 09848941002");?>
					</p>
					<p>
						<?php echo __("Tel 06.9291.7616
						<br/>
						Fax 06.4547.7298");?>
					</p>
					
				</div>
			
			</div>
		</div>
		
		<div id="footer-last">
			<div class="container">
				<div class="row">
					<div class="col-xs-4">
						<a href="http://www.zolle.it" target="_blank"><?php echo __("Le Zolle S.r.l. unipersonale");?></a>
					</div>
					<div class="col-xs-4">
						<a href="http://www.zolle.it/web/wp-content/themes/zolle/cookies-policy.html" target="_blank"><?php echo __("Privacy");?></a>
					</div>
					<div class="col-xs-4">
						<a href="http://impronta48.it" target="_blank"><?php echo __("Credits");?></a>
					</div>
				</div>
			</div>
		</div>
	</footer>
	
	<div id="modalWn" class="modal">
	</div>
	
	<?php if(!empty($user)):?>
	<div id="ordine-provvisorio" class="modal">
		<div class="modal-dialog">
			<div class="modal-content">
				<div class="modal-header">
					<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
					<h4 class="modal-title text-orange"><?php echo __("Ancora da confermare");?></h4>
				</div>
				<div class="modal-body">
					<div id="modal-operazioni-da-confermare">
						<?php
							$operazioniProvvisorie = $this->Session->read('operazioniProvvisorie');
							echo $this->element('mangio/modale_ordine_provvisorio', array('operazioniProvvisorie' => $operazioniProvvisorie, 'tipiSpese' => $this->Session->read('tipiSpese')));
							
							// nascondo i pulsanti di azione via css se non ci sono operazioni provvisorie
							// NOTA: NON posso usare un if/else in php perchè devo visualizzarli dinamicamente via ajax all'occorrenza !
							$modaleOrdineProvvisorioStyle = empty($operazioniProvvisorie) ? 'display:none' : 'display:block';
						?>
					</div>
					<div id="ordine-provvisorio-actions-container" style="<?php echo $modaleOrdineProvvisorioStyle;?>" class="btn-group-vertical" role="group" >
						<a href="#" class="btn btn-default bkg-orange white" data-dismiss="modal"><?php echo __("Conferma dopo");?></a>
						<a href="<?php echo Router::url(array('plugin' => null, 'controller' => 'consegne', 'action' => 'index'));?>" class="btn btn-default bkg-orange white"><?php echo __("Vai a modificare o confermare il tuo ordine");?></a>
					</div>
				</div>
				<!--<div class="modal-footer">
					
				</div>-->
			</div><!-- /.modal-content -->
		</div><!-- /.modal-dialog -->
	</div><!-- /.modal -->
	
	<!-- modale di info -->
	<div id="info-modal" class="modal">
		<div class="modal-dialog">
			<div class="modal-content">
				<div class="modal-header">
					<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
					<h4 class="modal-title text-orange"><?php echo __("Info");?></h4>
				</div>
				<div class="modal-body">	
				</div>
				<!--<div class="modal-footer">
					
				</div>-->
			</div><!-- /.modal-content -->
		</div><!-- /.modal-dialog -->
	</div><!-- /.modal -->
	<!-- /modale di info -->
	
	<!-- modale dettaglio spesa -->
	<div id="dettaglio-zolle-modal" class="modal">
		<div class="modal-dialog">
			<div class="modal-content" style="overflow:auto">
				<div class="modal-header">
					<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
					<h4 class="modal-title text-orange"></h4>
				</div>
				<div class="modal-body">
					<div class="alert alert-warning">
						<?php echo __('Questo è quello che abbiamo previsto per la tua Zolla.<br/>Ricorda: è una previsione! Qualcosa potrebbe ancora cambiare.');?>
					</div>	
					<div class="dettaglio-zolle-content"></div>
				</div>
				<!--<div class="modal-footer">
					
				</div>-->
			</div><!-- /.modal-content -->
		</div><!-- /.modal-dialog -->
	</div><!-- /.modal -->
	<!-- /modale dettaglio spesa -->
	
	
	<!-- modale adyen -->
	<?php if($contrattoDaRiattivare != null):?>
	<div id="reminder-adyen-modal" class="modal">
		<div class="modal-dialog">
			<div class="modal-content" style="overflow:auto">
				<div class="modal-header">
					<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
					<h4 class="modal-title text-orange">La tua carta di credito è da riattivare</h4>
				</div>
				<div class="modal-body">
					<p>Gentile <b><?php echo $cliente['Cliente']['NOME'];?> <?php echo $cliente['Cliente']['COGNOME'];?></b>,</p>
					<p>ti ricordiamo che a partire dal mese di marzo non potremo più utilizzare la tua carta di credito per i pagamenti delle Zolle. 
Abbiamo cambiato la piattaforma che li gestiva, passando ad un sistema più efficiente. 
Ti chiediamo di registrare nuovamente la tua carta utilizzando questo link.</p> 
					<p><?php echo $this->Html->link('Riattiva la tua carta di credito', array('plugin' => null, 'controller' => 'carte_di_credito', 'action' => 'contratto', $contrattoDaRiattivare));?></p>
					<p>Non esitare a contattarci per qualsiasi dubbio o informazione al numero <b>0692917616</b> o scrivendo a <a href="mailto:mangio@zolle.it">mangio@zolle.it</a>. </p>
					<p>Un caro saluto,</p>
					<p>Zolle</p>
				</div>
				<!--<div class="modal-footer">
					
				</div>-->
			</div><!-- /.modal-content -->
		</div><!-- /.modal-dialog -->
	</div><!-- /.modal -->
	<?php endif;?><!-- end check se contratto da ri-attivare -->
	<!-- /modale adyen -->
	
	<?php endif;?> <!-- end check user autenticato -->

    <!-- Bootstrap core JavaScript
    ================================================== -->
    <!-- Placed at the end of the document so the pages load faster -->
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.3/jquery.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.4/js/bootstrap.min.js"></script>
    <?php echo $this->Html->script('mangio/ordine.provvisorio');?>
    <?php echo $this->Html->script('mangio/spin.min');?>
    
    <!-- toastr -->
	<script src="//cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
    
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
			
			// configurazione di toastr
			toastr.options = {
			  "closeButton": true,
			  "debug": false,
			  "newestOnTop": false,
			  "progressBar": true,
			  "positionClass": "toast-bottom-full-width",
			  "preventDuplicates": false,
			  "onclick": null,
			  "showDuration": "5000",
			  "hideDuration": "1000",
			  "timeOut": "5000",
			  "extendedTimeOut": "1000",
			  "showEasing": "swing",
			  "hideEasing": "linear",
			  "showMethod": "fadeIn",
			  "hideMethod": "fadeOut"
			};
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
			$.get( '<?php echo Router::url(array('plugin' => null, 'controller' => 'consegne', 'action' => 'sidemenu_visible.json'));?>', function() {});
		}
		
		function closeSideMenu() {
			$('#sidemenu-cont').hide();	// close menu
			$('.width-from-6-to-4-on-sidemenu-closed').removeClass('col-md-6').addClass('col-md-4');
			$('.width-from-6-to-8-on-sidemenu-closed').removeClass('col-md-6').addClass('col-md-8');
			$('#main-cont').attr('class', 'col-md-12');
			$.get( '<?php echo Router::url(array('plugin' => null, 'controller' => 'consegne', 'action' => 'sidemenu_hidden.json'));?>', function() {});
		}
		
		function toast(level, content) {
			/*$('#toast').remove(); // rimuovi l'eventuale toast esistente
			$('body').append('<div id="toast" class="alert alert-' + level + ' alert-dismissible" role="alert">\
					<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>\
					<span id="toast-content">' + content + '</span>\
				</div>'
			);*/
			if(level == 'info' || level == 'success' || level == 'zolle') {
				toastr.info(content, "Info");
			}
			else if(level == 'warning') {
				toastr.warning(content, "Attenzione");
			}
			else {
				toastr.error(content, "Attenzione");
			}
			
		}
    </script>
    <?php echo $scripts_for_layout; ?>
    
    <?php
		// labels traducibili di datatables
		$dtLabels = array(
			'mostra' => __('Mostra'),
			'record_per_pagina' => __('record per pagina'),
			'cerca' => __('Cerca'),
			'zero_records' => __('Nessun record da visualizzare'),
			'processing' => __('Attendere prego'),
			'loading' => __("Caricamento dati in corso. Attendere prego ..."),
			'empty' => __("Non è presente nessun record"),
			'info' => __("Record da _START_ a _END_ di _TOTAL_"),
			'filtered' => __('filtrati di _MAX_ record'),
			'last' => __('Ultima'),
			'first' => __('Prima'),
			'next' => __('Succ'),
			'previous' => __('Prec'),
			'all' => __('Tutti')
 		);
		// la funzione che segue viene usata dai datatables per ottenere velocemente il blocco language
	?>
  
	<!-- useful constant to be used anywhere -->
  <script type="text/javascript">
      SERVER_BASE_URL = "<?php echo $this->Html->url('/', true);?>";  
      if(window.app === undefined) window.app = {};
	   window.app.dtLabels = JSON.parse('<?=json_encode($dtLabels);?>');
  </script>
  </body>
</html>
