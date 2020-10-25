<?php 
	$this->assign('title', 'Utenti');
	$this->assign('subtitle', 'Login admin');
?> 

				<div class="row">
					<div class="col-sm-10 col-sm-offset-1">
						<div class="login-container">


							<div class="position-relative">
								<div id="login-box" class="login-box visible widget-box no-border">
									<div class="widget-body">
										<div class="widget-main">
											<h4 class="header blue lighter bigger">
												<i class="ace-icon fa fa-coffee green"></i>
												Inserisci le tue credenziali di accesso
											</h4>

											<div class="space-6"></div>

											<?php echo $this->Form->create('User');?>
												<fieldset>
													<label class="block clearfix">
														<span class="block input-icon input-icon-right">
															<?php echo $this->Form->input('username', array('label' => false, 'div' => false, 'class' => 'form-control', 'placeholder' => 'Username'));?>
															<i class="ace-icon fa fa-user"></i>
														</span>
													</label>

													<label class="block clearfix">
														<span class="block input-icon input-icon-right">
															<?php echo $this->Form->input('password', array('label' => false, 'div' => false, 'class' => 'form-control', 'placeholder' => 'Password'));?>
															<i class="ace-icon fa fa-lock"></i>
														</span>
													</label>

													<div class="space"></div>

													<div class="clearfix">
														<label class="inline">
															<!--<input type="checkbox" class="ace" />
															<span class="lbl"> Remember Me</span>-->
														</label>

														<?php echo $this->Form->button('<i class="ace-icon fa fa-key"></i><span class="bigger-110">Login</span>', array('type' => 'submit', 'escape' => false, 'div' => false, 'class' => 'width-35 pull-right btn btn-sm btn-primary'));?>
													</div>

													<div class="space-4"></div>
												</fieldset>
											<?php echo $this->Form->end();?>

										</div><!-- /.widget-main -->

									</div><!-- /.widget-body -->
								</div><!-- /.login-box -->

							</div><!-- /.position-relative --> 
						</div>
					</div>
				</div>
