<?php $user = $this->Session->read('Auth.User');?>
			
			<!-- titolo -->
			<?php if( in_array($user[USER_ROLE_KEY], [ROLE_ADMIN, ROLE_BAM]) && !$this->request->is('json') ):?>
			<div id="page-title-crumb" class="row wrapper white-bg page-heading">
				<div class="container no-padding"> <!-- aggiunto rispetto al tema base -->
					<div class="col-lg-9 no-padding">
						<h4 id="page-title">
							<?php echo $this->fetch('title'); ?>
						</h4>
						
						<?php $subtitle = $this->fetch('subtitle');?>
						<?php if(!empty($subtitle)):?>
							<small style="text-transform: none">
								<?php echo $subtitle;?>
							</small>
						<?php endif;?>
					</div>
					<?php $rightHeader = $this->fetch('right-header');?>
					<?php if(!empty($rightHeader) && in_array($user[USER_ROLE_KEY], [ROLE_ADMIN, ROLE_BAM])):?>
					<div class="col-lg-3 no-padding">
						<?php echo $rightHeader;?>
					</div>
					<?php endif?>
				</div>
			</div>
			<?php endif?>
			<!-- /titolo -->
			<!-- crumb -->
			<?php $crumb = $this->fetch('crumbs');?>
			<?php if(!empty($crumb)):?>
				<div class="row wrapper border-bottom page-heading" id="crumb-outer-container">
					<div class="container no-padding"> <!-- aggiunto rispetto al tema base -->
						<div class="col-lg-12 no-padding" id="crumb-inner-container">
							<ol class="breadcrumb">
								<?php echo $crumb;?>
							</ol>
						</div>
					</div>
				</div>	
			<?php endif;?>
			<!-- /crumb -->
			<div class="space-20"></div> 
