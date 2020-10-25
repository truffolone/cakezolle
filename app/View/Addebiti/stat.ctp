<br>
<br>

<?php
	$tr_header = '<tr>
				<th>Id Spesa</th>
				<th>Id Cliente</th>
				<th>Importo</th>
				<th>Mese</th>
				<th>Anno</th>
				<th>Pagamento</th>
				<th>Stato</th>
				<th>Attiva</th>
			</tr>';

	$content = '<table>';
	/*if((sizeof($rid_ko_payable)+sizeof($new)) > 0) {
		if(empty($param1) && empty($param2)) {
			$content .= '
				<tr>
					<td>Paga le spese "RID" KO e NUOVE</td>
					<td>'.$html->link('Procedi', array('controller' => 'rid_payments', 'action' => 'genera_flusso_rid'), array('class' => 'button')).'</td>
				</tr>';
		}
		else {
			$content .= '
				<tr>
					<td>Paga le spese "RID" KO e NUOVE del periodo '.$param1.'-'.$param2.'</td>
					<td>'.$html->link('Procedi', array('controller' => 'rid_payments', 'action' => 'genera_flusso_rid_periodo',$param1,$param2), array('class' => 'button')).'</td>
				</tr>';
		}
	}*/
	/*if((sizeof($ko_payable)+sizeof($new)) > 0) {
		if(empty($param1) && empty($param2)) {
			$content .= '
				<tr>
					<td>Paga le spese "Carta di Credito" KO e NUOVE (modalita\' interattiva)</td>
					<td>'.$html->link('Procedi', array('controller' => 'charges', 'action' => 'conferma_ricorrenti'), array('class' => 'button')).'</td>
				</tr>';
		}
		else {
			$content .= '
				<tr>
					<td>Paga le spese "Carta di Credito" KO e NUOVE del periodo '.$param1.'-'.$param2.' (modalita\' interattiva)</td>
					<td>'.$html->link('Procedi', array('controller' => 'charges', 'action' => 'esegui_ricorrenti_periodo_esazione',$param1,$param2,1), array('class' => 'button')).'</td>
				</tr>';
		}
		$content .= '<tr>
				<td>Paga le spese "Carta di Credito" KO e NUOVE del periodo '.$param1.'-'.$param2.' (modalita\' non interattiva)</td>
				<td>'.$html->link('Procedi', array('controller' => 'charges', 'action' => 'esegui_ricorrenti_periodo_esazione',$param1,$param2,0), array('class' => 'button')).'</td>
			</tr>';
	}*/
	$content .= '</table>';
	//echo $this->Generic->displayWindow('exec.png', 'Lista operazioni', $content, 750);
	//echo '<br><br>';

	if(sizeof($blocked_net_err) > 0)
	{	
		$content = '<table>'.$tr_header;
		foreach ($blocked_net_err as $charge)
		{
			$content .= $this->Generic->displayRawChargeAsTableRow($charge, 'Blocked - Errore rete');
		}
		$content .= '</table>';
		echo $this->Generic->displayWindow('error.png', 'Spese bloccate per errore di rete ['.sizeof($blocked_net_err).']', $content, 750);
		echo '<br><br>';
	}
	if(sizeof($blocked_db_err) > 0)
	{	
		$content = '<table>'.$tr_header;
		foreach ($blocked_db_err as $charge)
		{
			$content .= $this->Generic->displayRawChargeAsTableRow($charge, 'Blocked - Errore database');
		}
		$content .= '</table>';
		echo $this->Generic->displayWindow('error.png', 'Spese bloccate per errore database ['.sizeof($blocked_db_err).']', $content, 750);
		echo '<br><br>';
	}
	if(sizeof($ko_payable) > 0)
	{	
		$content = '<table>'.$tr_header;
		foreach ($ko_payable as $charge)
		{
			$content .= $this->Generic->displayRawChargeAsTableRow($charge, 'KO');
		}
		$content .= '</table>';
		echo $this->Generic->displayWindow('error.png', 'Spese Carta di Credito KO ['.sizeof($ko_payable).']', $content, 750);
		echo '<br><br>';
	}
	if(sizeof($ko_not_payable) > 0)
	{	
		$content = '<table>'.$tr_header;
		foreach ($ko_not_payable as $charge)
		{
			$content .= $this->Generic->displayRawChargeAsTableRow($charge, 'KO');
		}
		$content .= '</table>';
		echo $this->Generic->displayWindow('error.png', 'Spese Carta di Credito KO bloccate in attesa che il cliente attivi il contratto ['.sizeof($ko_not_payable).']', $content, 750);
		echo '<br><br>';
	}
	if(sizeof($rid_ko_payable) > 0)
	{	
		$content = '<table>'.$tr_header;
		foreach ($rid_ko_payable as $charge)
		{
			$content .= $this->Generic->displayRawChargeAsTableRow($charge, 'KO');
		}
		$content .= '</table>';
		echo $this->Generic->displayWindow('error.png', 'Spese RID KO ['.sizeof($rid_ko_payable).']', $content, 750);
		echo '<br><br>';
	}
	if(sizeof($rid_ko_not_payable) > 0)
	{	
		$content = '<table>'.$tr_header;
		foreach ($rid_ko_not_payable as $charge)
		{
			$content .= $this->Generic->displayRawChargeAsTableRow($charge, 'KO');
		}
		$content .= '</table>';
		echo $this->Generic->displayWindow('error.png', 'Spese RID KO bloccate in attesa che il cliente attivi il contratto ['.sizeof($rid_ko_not_payable).']', $content, 750);
		echo '<br><br>';
	}
	if(sizeof($new) > 0)
	{	
		$content = '<table>'.$tr_header;
		foreach ($new as $charge)
		{
			$content .= $this->Generic->displayRawChargeAsTableRow($charge, 'Nuova');
		}
		$content .= '</table>';
		echo $this->Generic->displayWindow('warning.png', 'Spese non ancora elaborate ['.sizeof($new).']', $content, 750);
		echo '<br><br>';
	}
	if(sizeof($ok) > 0)
	{	
		$content = '<table>'.$tr_header;
		foreach ($ok as $charge)
		{
			$content .= $this->Generic->displayRawChargeAsTableRow($charge, 'OK');
		}
		$content .= '</table>';
		echo $this->Generic->displayWindow('ok.png', 'Spese OK ['.sizeof($ok).']', $content, 750);
		echo '<br><br>';
	}

	if(sizeof($inattive) > 0)
	{	
		$content = '<table>'.$tr_header;
		foreach ($inattive as $charge)
		{
			$content .= $this->Generic->displayRawChargeAsTableRow($charge, 'OK');
		}
		$content .= '</table>';
		echo $this->Generic->displayWindow('ok.png', 'Spese Precedenti NON ATTIVE ['.sizeof($inattive).']', $content, 750);
		echo '<br><br>';
	}

	if(sizeof($inattive) == 0 && sizeof($rid_ko_payable) == 0 && sizeof($rid_ko_not_payable) == 0 && sizeof($blocked_net_err) == 0 && sizeof($blocked_db_err) == 0 && sizeof($ko_payable) == 0 && sizeof($ko_not_payable) == 0 && sizeof($new) == 0 && sizeof($ok) == 0)
	{
		$content = '<span style="color:red;">Nessun risultato trovato</span>';
		echo $this->Generic->displayWindow('warning.png', 'Spese', $content, 750);
	}
?>

	
