<?php
	$this->assign('title', 'Home');
	$this->assign('subtitle', 'Dashboard');

?>  

<h4 class="header blue">
	<div class="row">
	<span class="col-sm-8">
		Dashboard
	</span>
	</div>
</h4> 

								<div class="row">
									<div class="space-6"></div>

									<div class="col-sm-7 infobox-container">
										<div class="infobox infobox-green">
											<div class="infobox-icon">
												<i class="ace-icon fa fa-credit-card"></i>
											</div>

											<div class="infobox-data">
												<span class="infobox-data-number"><?php echo $carte_scadute_num;?></span>
												<div class="infobox-content">carte scadute</div>
											</div>

										</div>

										<div class="infobox infobox-blue">
											<div class="infobox-icon">
												<i class="ace-icon fa fa-credit-card"></i>
											</div>

											<div class="infobox-data">
												<span class="infobox-data-number"><?php echo $carte_non_attive_num;?></span>
												<div class="infobox-content">contratti carta non attivati</div>
											</div>
										</div>

										<div class="infobox infobox-pink">
											<div class="infobox-icon">
												<i class="ace-icon fa fa-bank"></i>
											</div>

											<div class="infobox-data">
												<span class="infobox-data-number"><?php echo $rid_non_attivi_num;?></span>
												<div class="infobox-content">autorizzazioni rid non attivate</div>
											</div>
										</div>

										<div class="infobox infobox-red">
											<div class="infobox-icon">
												<i class="ace-icon fa fa-credit-card"></i>
											</div>

											<div class="infobox-data">
												<span class="infobox-data-number"><?php echo $spese_carta_ko_num;?></span>
												<div class="infobox-content">spese carta KO</div>
											</div>
										</div>

										<div class="infobox infobox-orange2">
											<div class="infobox-icon">
												<i class="ace-icon fa fa-credit-card"></i>
											</div>

											<div class="infobox-data">
												<span class="infobox-data-number"><?php echo $spese_carta_bloccate_num;?></span>
												<div class="infobox-content">spese carta bloccate</div>
											</div>
										</div>

										<div class="infobox infobox-blue2">
											<div class="infobox-icon">
												<i class="ace-icon fa fa-credit-card"></i>
											</div>

											<div class="infobox-data">
												<span class="infobox-data-number"><?php echo $spese_carta_non_pagabili_num;?></span>
												<div class="infobox-content">spese carta non pagabili (carta non attiva)</div>
											</div>
										</div>
										
										<div class="infobox infobox-orange2">
											<div class="infobox-icon">
												<i class="ace-icon fa fa-credit-card"></i>
											</div>

											<div class="infobox-data">
												<span class="infobox-data-number"><?php echo $spese_carta_non_ancora_pagate_num;?></span>
												<div class="infobox-content">spese carta non ancora pagate (pagabili)</div>
											</div>
										</div>
										
										<div class="infobox infobox-pink">
											<div class="infobox-icon">
												<i class="ace-icon fa fa-bank"></i>
											</div>

											<div class="infobox-data">
												<span class="infobox-data-number"><?php echo $spese_rid_non_pagabili_num;?></span>
												<div class="infobox-content">spese rid non pagabili (rid non attivo)</div>
											</div>

										</div>

										
									</div>

									<div class="vspace-12-sm"></div>

									<div class="col-sm-5">
										
									</div><!-- /.col -->
								</div><!-- /.row -->

								<div class="hr hr32 hr-dotted"></div>

								
								
								<!-- PAGE CONTENT ENDS -->
								
								<!-- inline scripts related to this page -->
		
