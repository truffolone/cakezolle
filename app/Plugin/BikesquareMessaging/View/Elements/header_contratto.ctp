<style>
.space-10 {
	height: 10px;
}
.padding {
	padding: 5px;
}
div.table-header > div {
	background: #eee;
	font-weight: bold;
}
.margin-bottom-10 {
	margin-bottom: 10px;
}
.border-bottom {
	padding-bottom: 10px;
	border-bottom: 1px solid #ccc;
}
#contratto-manager-container {
	position:relative;
}
#loading {
	z-index: 100000;
	position: fixed;
	top: 0;
	left: 0;
	bottom: 0;
	right: 0;
	width: 100%;
	background: rgba(255,255,255,0.5);
}

.step {
	display: inline-block;
	width: 30px;
	height: 30px;
	line-height: 30px;
	border-radius: 50%;
}

.step-completed {
	background: #00aa00;
	color: #fff;
}

.step-current {
	background: #495b79;
	color: #fff;
}

.step-inactive {
	background: #aaaa7f;
	color: #fff;
}

</style>

<div class="row">
		<?php $contactUrl = Router::url(['plugin' => false, 'controller' => 'contacts', 'action' => 'contratto', $contact_id]);?>
		<div class="col-md-2 text-center"><a href="<?=$contactUrl;?>"><div class="step step-inactive">1</div><br><span>Dove e quando</span></a></div>
		<div class="col-md-2 text-center"><a href="<?=$contactUrl;?>"><div class="step step-inactive">2</div><br><span>Anagrafica e contatti</span></a></div>
		<div class="col-md-2 text-center"><a href="<?=$contactUrl;?>"><div class="step step-inactive">3</div><br><span>Bici</span></a></div>
		<div class="col-md-2 text-center"><a href="<?=$contactUrl;?>"><div class="step step-inactive">4</div><br><span>Costo</span></a></div>
		<div class="col-md-2 text-center"><a href="<?=$contactUrl;?>"><div class="step step-inactive">5</div><br><span>Marketing</span></a></div>
		<div class="col-md-2 text-center"><a href="<?=$contactUrl;?>"><div class="step step-inactive">6</div><br><span>Spese, fatturazione e gestione</span></a></div>
		<div class="col-md-2 text-center"><a href="<?=Router::url(['plugin' => 'messaging', 'controller' => 'messages', 'action' => 'contact', $contact_id]);?>"><div class="step step-current"><i class="fa fa-check"></i></div><br><span>Messaggi</span></a></div>
</div>

