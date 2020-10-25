<?php 
	$this->assign('title', 'Spese');
	$this->assign('subtitle', 'Spesa');
?> 

<h4 class="header blue">
	<div class="row">
	<span class="col-sm-8">
		Conferma cancellazione Spesa | Cliente <?php echo $a['Cliente']['id'];?> - <?php echo $a['Cliente']['displayName'];?>
	</span>
	</div>
</h4>

<p>
	Cancellare la spesa selezionata ?
</p>

<div class="widget-toolbox padding-8 clearfix">
	<?php echo $this->Html->link('<i class="ace-icon fa fa-times"></i><span class="bigger-110">Indietro</span>', array('action' => 'view', $a['Addebito']['id']), array('escape' => false, 'class' => 'btn btn-xs btn-danger pull-left'));?>
	<?php echo $this->Html->link('<span class="bigger-110">Procedi</span><i class="ace-icon fa fa-arrow-right icon-on-right"></i>', array('action' => 'delete', $a['Addebito']['id']), array('escape' => false, 'class' => 'btn btn-xs btn-success pull-right'));?>
</div> 
