<?php 
	$this->assign('title', 'Metodi di pagamento');
	$this->assign('subtitle', 'Contanti');
?> 

<h4 class="header blue">
	<div class="row">
	<span class="col-sm-8">
		Conferma cancellazione Contanti | Cliente <?php echo $c['Cliente']['id'];?> - <?php echo $c['Cliente']['displayName'];?>
	</span>
	</div>
</h4>

<p>
	Cancellare il metodo di pagamento selezionato ?
</p>

<div class="widget-toolbox padding-8 clearfix">
	<?php echo $this->Html->link('<i class="ace-icon fa fa-times"></i><span class="bigger-110">Indietro</span>', array('action' => 'edit', $c['Contante']['id']), array('escape' => false, 'class' => 'btn btn-xs btn-danger pull-left'));?>
	<?php echo $this->Html->link('<span class="bigger-110">Procedi</span><i class="ace-icon fa fa-arrow-right icon-on-right"></i>', array('action' => 'delete', $c['Contante']['id']), array('escape' => false, 'class' => 'btn btn-xs btn-success pull-right'));?>
</div> 
