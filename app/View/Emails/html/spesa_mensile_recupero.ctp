<?php
	$mese = array(1=>'gennaio',2=>'febbraio',3=>'marzo',4=>'aprile',5=>'maggio',6=>'giugno',7=>'luglio',8=>'agosto',9=>'settembre',10=>'ottobre',11=>'novembre',12=>'dicembre');
?>
<span>						
Gentile <?php echo $addebito['Cliente']['displayName'];?>
<br><br>
ti informiamo che la transazione di '<b>recupero</b>' della tua carta di credito per il pagamento del saldo di Zolle del mese di 
<span style="font-weight:bold"><?php echo $mese[$addebito['Addebito']['mese']];?></span> <b>non è andata a buon fine</b>.
<br><br>
<b>La consegna delle Zolle viene sospesa: contattaci</b> per regolarizzare il pagamento e <b>riattivare le consegne</b>.
<br><br>
Cordiali saluti
<br><br>
Le Zolle
<br><br>
www.zolle.it
<br>
<!--Tel. 06 5572547-->
Tel. 06 92917616
<br><br>
Questo messaggio e' stato generato in automatico dal sistema di gestione pagamenti di Zolle. Per contattarci e' possibile inviare un'email 
all'indirizzo amministrazione@zolle.it<br><br>
</span>

