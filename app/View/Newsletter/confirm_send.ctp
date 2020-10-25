<?php if(sizeof($sent) > 0):?>
<div class="ui-widget ui-widget-content ui-widget-header" style="padding:10px">
La newsletter è già stata inviata a <?php echo sizeof($sent);?> <?php echo (sizeof($sent) > 1) ? 'destinatari' : 'destinatario';?> <?php echo $this->Html->link('Visualizza', array('controller' => 'destinatari_newsletter', 'action' => 'index', $newsletter['Newsletter']['id'], 'sent'), array('class' => 'button', 'target' => '_blank'));?>
</div>
<?php endif;?>

<?php if(sizeof($unsent) > 0):?>
<div class="ui-widget ui-widget-content ui-widget-header"  style="padding:10px">
La newsletter verrà inviata a <?php echo sizeof($unsent);?> <?php echo (sizeof($unsent) > 1) ? 'destinatari' : 'destinatario';?> <?php echo $this->Html->link('Visualizza', array('controller' => 'destinatari_newsletter', 'action' => 'index', $newsletter['Newsletter']['id'], 'unsent'), array('class' => 'button', 'target' => '_blank'));?>
</div>
<?php endif;?>

<br>

<div class="ui-widget ui-widget-content ui-widget-header"  style="padding:10px; text-align:right">
<?php echo $this->Html->link('Invia la newsletter', array('action' => 'schedule', $newsletter['Newsletter']['id']), array('class' => 'button', 'style' => 'float:left'));?>
<?php echo $this->Html->link('Modifica la newsletter', array('action' => 'edit', $newsletter['Newsletter']['id']), array('class' => 'button'));?>
</div>

<br>

<div class="ui-corner-all" style="border:1px solid #ccc; padding:10px; margin-top:10px; margin-bottom:10px;">
<h3>Oggetto</h3>
<?php echo $newsletter['Newsletter']['subject'];?>

<h3>Contenuto della Newsletter</h3>
<?php echo $newsletter['Newsletter']['content'];?>
</div>


