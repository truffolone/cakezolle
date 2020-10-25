<!DOCTYPE html>
<html lang="en">
	<head>
		<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1" />
		<meta charset="utf-8" />
		<title><?php echo $this->fetch('title'); ?></title>

		<meta name="description" content="overview &amp; stats" />
		<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0" />

		<link rel="shortcut icon" type="image/x-icon" href="https://zolle.it/wp-content/uploads/2018/09/zolle-logo_cerchio_magenta.png">
        <link rel="apple-touch-icon" href="https://zolle.it/wp-content/uploads/2018/09/zolle-logo_cerchio_magenta.png"/>

		<!-- bootstrap & fontawesome -->
		<?php echo $this->Html->css('bootstrap.min');?>
		<link rel="stylesheet" href="//maxcdn.bootstrapcdn.com/font-awesome/4.2.0/css/font-awesome.min.css" />

		<!-- page specific plugin styles -->

		<!-- text fonts -->
		<link rel="stylesheet" href="//fonts.googleapis.com/css?family=Open+Sans:400,300" />

		<!-- ace styles -->
		<?php echo $this->Html->css('ace.min', array('class' => "ace-main-stylesheet", 'id' => "main-ace-style"));?>

		<!--[if lte IE 9]>
			<?php echo $this->Html->css('ace-part2.min', array('class' => "ace-main-stylesheet"));?>
		<![endif]-->

		<!--[if lte IE 9]>
			<?php echo $this->Html->css('ace-ie.min');?>
		<![endif]-->

		<?php echo $this->Html->css('jquery-ui.min');?>

		<?php echo $this->Html->css('zolle.admin');?>

		<!-- inline styles related to this page -->

		<!-- HTML5shiv and Respond.js for IE8 to support HTML5 elements and media queries -->

		<!--[if lte IE 8]>
		<?php echo $this->Html->script('html5shiv.min');?>
		<?php echo $this->Html->script('respond.min');?>
		<![endif]-->
	</head>

	<?php 
		$user = $this->Session->read('Auth.User');
		if( empty($user) ) {
			$user = array(
				'group_id' => 10
			);
		} 
	?>

	<body class="no-skin">
		<div id="navbar" class="navbar navbar-default navbar-fixed-top">
			<script type="text/javascript">
				try{ace.settings.check('navbar' , 'fixed')}catch(e){}
			</script>

			<div class="navbar-container" id="navbar-container">
				<button type="button" class="navbar-toggle menu-toggler pull-left" id="menu-toggler" data-target="#sidebar">
					<span class="sr-only">Toggle sidebar</span>

					<span class="icon-bar"></span>

					<span class="icon-bar"></span>

					<span class="icon-bar"></span>
				</button>

				<div class="navbar-header pull-left">
					<a href="<?php echo Router::url('/');?>" class="navbar-brand">
						<small>
							<i class="fa fa-cogs"></i>
							<?php if(!in_array($user['group_id'], array(1,2,3))):?>
							&nbsp;&nbsp;ZOLLE
							<?php else:?>
							&nbsp;&nbsp;ZOLLE - Amministrazione
							<?php endif;?>
						</small>
					</a>
				</div>

				<div class="navbar-buttons navbar-header pull-right" role="navigation">
					
					<?php if(in_array($user['group_id'], array(1,2,3))):?>
					<ul class="nav ace-nav">

						<li class="light-blue">
							<a data-toggle="dropdown" href="#" class="dropdown-toggle">
								<?php //echo $this->Html->image('laura.jpg', array('class' => 'nav-user-photo', 'alt'=>"Foto di Laura"));?>
								<span class="user-info">
									<small>Ciao,</small>
									<?php echo ucfirst(strtolower($user['username']));?>
								</span>

								<i class="ace-icon fa fa-caret-down"></i>
							</a>

							<ul class="user-menu dropdown-menu-right dropdown-menu dropdown-yellow dropdown-caret dropdown-close">
								<!--<li>
									<a href="#">
										<i class="ace-icon fa fa-cog"></i>
										Settings
									</a>
								</li>

								<li>
									<a href="profile.html">
										<i class="ace-icon fa fa-user"></i>
										Profile
									</a>
								</li>-->

								<li class="divider"></li>

								<li>
									<a href="<?php echo Router::url(array('plugin' => null, 'controller' => 'users', 'action' => 'logout'));?>">
										<i class="ace-icon fa fa-power-off"></i>
										Logout
									</a>
								</li>
							</ul>
						</li>
					</ul>
					<?php endif;?>
					
				</div>
			</div><!-- /.navbar-container -->
		</div>

		<div class="main-container" id="main-container">
			<script type="text/javascript">
				try{ace.settings.check('main-container' , 'fixed')}catch(e){}
			</script>

			<div id="sidebar" class="sidebar responsive">
				
				<?php if(in_array($user['group_id'], array(1,2,3))):?>
				
				<script type="text/javascript">
					try{ace.settings.check('sidebar' , 'fixed')}catch(e){}
				</script>

				<div class="sidebar-shortcuts" id="sidebar-shortcuts">
					<div class="sidebar-shortcuts-large" id="sidebar-shortcuts-large">
						<a href="<?php echo Router::url('/');?>" class="btn btn-success">
							<i class="ace-icon fa fa-signal"></i>
						</a>

						<a href="<?php echo Router::url(array('plugin' => null, 'controller' => 'contratti', 'action' => 'index', 'attivi'));?>" class="btn btn-info">
							<i class="ace-icon fa fa-pencil"></i>
						</a>

						<a href="<?php echo Router::url(array('plugin' => null, 'controller' => 'clienti', 'action' => 'index'));?>" class="btn btn-warning">
							<i class="ace-icon fa fa-users"></i>
						</a>

						<a href="<?php echo Router::url(array('plugin' => null, 'controller' => 'addebiti', 'action' => 'index'));?>" class="btn btn-danger">
							<i class="ace-icon fa fa-money"></i>
						</a>
					</div>

					<div class="sidebar-shortcuts-mini" id="sidebar-shortcuts-mini">
						<span class="btn btn-success"></span>

						<span class="btn btn-info"></span>

						<span class="btn btn-warning"></span>

						<span class="btn btn-danger"></span>
					</div>
				</div><!-- /.sidebar-shortcuts -->

				<?php echo $this->element('left_menu');?>
		

				<div class="sidebar-toggle sidebar-collapse" id="sidebar-collapse">
					<i class="ace-icon fa fa-angle-double-left" data-icon1="ace-icon fa fa-angle-double-left" data-icon2="ace-icon fa fa-angle-double-right"></i>
				</div>

				<script type="text/javascript">
					try{ace.settings.check('sidebar' , 'collapsed')}catch(e){}
				</script>
				
				<?php endif;?>
				
			</div>

			<div class="main-content">
				<div class="main-content-inner">
					<div class="breadcrumbs" id="breadcrumbs">
						<script type="text/javascript">
							try{ace.settings.check('breadcrumbs' , 'fixed')}catch(e){}
						</script>

						<ul class="breadcrumb page-header">
							<h1>
								<small>
								<?php echo $this->fetch('title'); ?>
								<i class="ace-icon fa fa-angle-double-right"></i>
								<?php echo $this->fetch('subtitle'); ?>
								</small>	
							</h1>
						</ul><!-- /.breadcrumb -->

					</div>

					<div class="page-content">

						<div class="row">
							<div class="col-xs-12">
								<!-- PAGE CONTENT BEGINS -->
								<?php echo $this->Session->flash(); ?>
								<?php echo $this->fetch('content'); ?>
								<!-- PAGE CONTENT ENDS -->
								
							</div><!-- /.col -->
						</div><!-- /.row -->
					</div><!-- /.page-content -->
				</div>
			</div><!-- /.main-content -->

			<div class="footer">
				<div class="footer-inner">
					<div class="footer-content">
						<span class="bigger-120">
							<span class="blue bolder">Zolle</span>
							<?php if(!in_array($user['group_id'], array(1,2,3))):?>
							&nbsp
							<?php else:?>
							| Amministrazione
							<?php endif;?>
						</span>

						&nbsp; &nbsp;
						<span class="action-buttons">
						</span>
					</div>
				</div>
			</div>

			<a href="#" id="btn-scroll-up" class="btn-scroll-up btn btn-sm btn-inverse">
				<i class="ace-icon fa fa-angle-double-up icon-only bigger-110"></i>
			</a>
		</div><!-- /.main-container -->

		<!-- basic scripts -->

		<!--[if !IE]> -->
			<script src="//ajax.googleapis.com/ajax/libs/jquery/2.1.1/jquery.min.js"></script>

		<!-- <![endif]-->

		<!--[if IE]>
			<script src="//ajax.googleapis.com/ajax/libs/jquery/1.11.1/jquery.min.js"></script>
		<![endif]-->

		<!--[if !IE]> -->
		<script type="text/javascript">
			<?php
				// must split the string to avoid 'SyntaxError: unterminated string literal javascript'
				$str = $this->Html->script('jquery.min');
				$str = substr($str, 0, strlen($str)-7);
			?>
			window.jQuery || document.write('<?php echo $str;?>' + 'script>'); // must break the script like this to prevent a js syntax error
		</script>

		<!-- <![endif]-->

		<!--[if IE]>
		<script type="text/javascript">
			<?php
				// must split the string to avoid 'SyntaxError: unterminated string literal javascript'
				$str = $this->Html->script('jquery1x.min');
				$str = substr($str, 0, strlen($str)-7);
			?>
			window.jQuery || document.write('<?php echo $str;?>' + 'script>'); // must break the script like this to prevent a js syntax error
		</script>
		<![endif]-->
		<script type="text/javascript">
			<?php
				// must split the string to avoid 'SyntaxError: unterminated string literal javascript'
				$str = $this->Html->script('jquery.mobile.custom.min');
				$str = substr($str, 0, strlen($str)-7);
			?>
			if('ontouchstart' in document.documentElement) document.write('<?php echo $str;?>' + 'script>'); // must break the script like this to prevent a js syntax error
		</script>
		<script src="//netdna.bootstrapcdn.com/bootstrap/3.3.1/js/bootstrap.min.js"></script>

		<!-- page specific plugin scripts -->

		<!--[if lte IE 8]>
		  <?php echo $this->Html->script('excanvas.min');?>
		<![endif]-->
		<?php echo $this->Html->script('jquery-ui.min');?>
		<?php echo $this->Html->script('jquery.ui.touch-punch.min');?>
		<?php echo $this->Html->script('jquery.easypiechart.min');?>
		<?php echo $this->Html->script('jquery.sparkline.min');?>
		<?php echo $this->Html->script('jquery.flot.min');?>
		<?php echo $this->Html->script('jquery.flot.pie.min');?>
		<?php echo $this->Html->script('jquery.flot.resize.min');?>

		<!-- ace scripts -->
		<?php echo $this->Html->script('ace-elements.min');?>
		<?php echo $this->Html->script('ace.min');?>
		
		<?php echo $scripts_for_layout; ?>
		
		<script>
			/*jQuery(function($) {
				$('.form-submit').click(function(e){
					$(this).parent().html('<h3><i class="ace-icon fa fa-spinner fa-spin orange bigger-125"></i></h3> attendere prego ...');
				});
			});*/
		</script>
		
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

