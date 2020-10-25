<?php
	// https://stackoverflow.com/questions/6054033/pretty-printing-json-with-php
	function prettyPrint( $json )
{
    $result = '';
    $level = 0;
    $in_quotes = false;
    $in_escape = false;
    $ends_line_level = NULL;
    $json_length = strlen( $json );

    for( $i = 0; $i < $json_length; $i++ ) {
        $char = $json[$i];
        $new_line_level = NULL;
        $post = "";
        if( $ends_line_level !== NULL ) {
            $new_line_level = $ends_line_level;
            $ends_line_level = NULL;
        }
        if ( $in_escape ) {
            $in_escape = false;
        } else if( $char === '"' ) {
            $in_quotes = !$in_quotes;
        } else if( ! $in_quotes ) {
            switch( $char ) {
                case '}': case ']':
                    $level--;
                    $ends_line_level = NULL;
                    $new_line_level = $level;
                    break;

                case '{': case '[':
                    $level++;
                case ',':
                    $ends_line_level = $level;
                    break;

                case ':':
                    $post = " ";
                    break;

                case " ": case "\t": case "\n": case "\r":
                    $char = "";
                    $ends_line_level = $new_line_level;
                    $new_line_level = NULL;
                    break;
            }
        } else if ( $char === '\\' ) {
            $in_escape = true;
        }
        if( $new_line_level !== NULL ) {
            $result .= "\n".str_repeat( "\t", $new_line_level );
        }
        $result .= $char.$post;
    }

    return $result;
}

?>

<h3>Cliente <?php echo $this->Html->link($cliente['Cliente']['id'], ['controller' => 'clienti', 'action' => 'view', $cliente['Cliente']['id']]);?> - <?php echo $cliente['Cliente']['displayName'];?></h3>

<table class="table table-striped">
<tr>
	<th></th>
	<th>Data</th>
	<th>Sessione</th>
	<th>Tipo</th>
	<th>Livello</th>
	<th>Messaggio</th>
</tr>

<?php $curr_sess_id = null;?>

<?php foreach($data as $d):?>

<?php if($curr_sess_id && $curr_sess_id != $d['LogEntry']['shopping_session_id']):?>
<tr><td colspan="6" style="background:#ddd; width:100%">Iniziata nuova sessione shopping</td></tr>
<?php endif;?>

<?php $curr_sess_id = $d['LogEntry']['shopping_session_id'];?>

<tr>
	<td>
		<?php if($d['LogEntry']['json_args'] != null && $d['LogEntry']['json_args'] != 'null'):?>
		<a href="#" class="open-details" data-target="<?=$d['LogEntry']['id'];?>" id="open-row-<?=$d['LogEntry']['id'];?>"><b>&nbsp;&nbsp;&nbsp;+&nbsp;&nbsp;&nbsp;</b></a>
		<a href="#" class="close-details hidden" data-target="<?=$d['LogEntry']['id'];?>" id="close-row-<?=$d['LogEntry']['id'];?>"><b>&nbsp;&nbsp;&nbsp;-&nbsp;&nbsp;&nbsp;</b></a>
		<?php endif;?>
	</td>
	<td>
		<?=$d['LogEntry']['created'];?>
	</td>
	<td>
		<?=$d['LogEntry']['shopping_session_id'];?>
	</td>
	<td>
		<?=$d['LogEntry']['type'];?>
	</td>
	<td>
		<?=$d['LogEntry']['level'];?>
	</td>
	<td>
		<?=$d['LogEntry']['message'];?>
	</td>
</tr>
<?php if($d['LogEntry']['json_args'] != null && $d['LogEntry']['json_args'] != 'null'):?>
<tr id="details-<?=$d['LogEntry']['id'];?>" class="hidden">
	<td colspan="6">
		<pre><?=prettyPrint($d['LogEntry']['json_args']);?></pre>
	</td>
</tr>
<?php endif;?>

<?php endforeach;?> 
</table>

<?=$this->Paginator->numbers();?>

<?php $this->Html->scriptStart(array('inline' => false)); ?>
jQuery(function($) {
	
	$('.open-details').click(function(e){
		e.preventDefault();
		$('#details-' + $(this).attr('data-target')).removeClass('hidden');
		$('#close-row-' + $(this).attr('data-target')).removeClass('hidden');
		$('#open-row-' + $(this).attr('data-target')).addClass('hidden');
	});
	
	$('.close-details').click(function(e){
		e.preventDefault();
		$('#details-' + $(this).attr('data-target')).addClass('hidden');
		$('#close-row-' + $(this).attr('data-target')).addClass('hidden');
		$('#open-row-' + $(this).attr('data-target')).removeClass('hidden');
	});
	
});


<?php $this->Html->scriptEnd(); ?>
 
