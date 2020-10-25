<?php
	/*
		TEMPISTICHE ML

		giorno_apertura: 
			giorno di apertura ML in base al giorno di consegna.
			Es: -9: apertura ML 9 giorni prima del giorno di consegna
    
		orario_apertura:
			orario di apertura ML in base al giorno di consegna.
			Es. XX:XX su 24 h  (es. 23:15)
 
		giorno_chiusura: 
			giorno di chiusura ML in base al giorno di consegna.
			Es: -3: chiusura ML 3 giorni prima del giorno di consegna
    
		orario_chiusura:
			orario di chiusura ML in base al giorno di consegna.
			Es. 'XX:XX' su 24 h  (es. 23:15)




			
	*/

	
	
	$config['mlp']['lun']['ml']['giorno_apertura'] = -9; 
	$config['mlp']['lun']['ml']['orario_apertura'] = '08:00';
	$config['mlp']['lun']['ml']['giorno_chiusura'] = -1; 
	$config['mlp']['lun']['ml']['orario_chiusura'] = '23:59';
 
	$config['mlp']['mar']['ml']['giorno_apertura'] = -15; 
	$config['mlp']['mar']['ml']['orario_apertura'] = '12:00';  
	$config['mlp']['mar']['ml']['giorno_chiusura'] = -8; 
	$config['mlp']['mar']['ml']['orario_chiusura'] = '12:00';

	$config['mlp']['mer']['ml']['giorno_apertura'] = -14; 
	$config['mlp']['mer']['ml']['orario_apertura'] = '11:00';
	$config['mlp']['mer']['ml']['giorno_chiusura'] = -11; 
	$config['mlp']['mer']['ml']['orario_chiusura'] = '14:00';

	$config['mlp']['gio']['ml']['giorno_apertura'] = -9; 
	$config['mlp']['gio']['ml']['orario_apertura'] = '11:00';
	$config['mlp']['gio']['ml']['giorno_chiusura'] = -1; 
	$config['mlp']['gio']['ml']['orario_chiusura'] = '14:00';

	$config['mlp']['ven']['ml']['giorno_apertura'] = -16; 
	$config['mlp']['ven']['ml']['orario_apertura'] = '11:00';
	$config['mlp']['ven']['ml']['giorno_chiusura'] = -8; 
	$config['mlp']['ven']['ml']['orario_chiusura'] = '15:00';
	

	//per testare l'area su clienti che ricevono sab
	$config['mlp']['sab']['ml']['giorno_apertura'] = -9; 
	$config['mlp']['sab']['ml']['orario_apertura'] = '00:01';
	$config['mlp']['sab']['ml']['giorno_chiusura'] = -7; 
	$config['mlp']['sab']['ml']['orario_chiusura'] = '23:59';
	
	/*
		TEMPISTICHE AF

		giorno_chiusura: 
			giorno di chiusura AF in base al giorno di consegna.
			Es: -3: chiusura AF 3 giorni prima del giorno di consegna
    
		orario_chiusura:
			orario di chiusura AF in base al giorno di consegna.
			Es. 'XX:XX' su 24 h  (es. 23:15)

		Superata la deadline impostata, le modifiche sono possibili solo a partire dalla settimana successiva
	*/
	$config['mlp']['lun']['af']['giorno_chiusura'] = -4; 
	$config['mlp']['lun']['af']['orario_chiusura'] = '12:00';
 
	$config['mlp']['mar']['af']['giorno_chiusura'] = -5; 
	$config['mlp']['mar']['af']['orario_chiusura'] = '12:00';

	$config['mlp']['mer']['af']['giorno_chiusura'] = -6; 
	$config['mlp']['mer']['af']['orario_chiusura'] = '12:00';

	$config['mlp']['gio']['af']['giorno_chiusura'] = -7; 
	$config['mlp']['gio']['af']['orario_chiusura'] = '12:00';

	$config['mlp']['ven']['af']['giorno_chiusura'] = -8; 
	$config['mlp']['ven']['af']['orario_chiusura'] = '12:00';

	/*
		TEMPISTICHE SPESE

		giorno_chiusura: 
			giorno di chiusura modifica spesa in base al giorno di consegna.
			Es: -3: chiusura modifica spesa 3 giorni prima del giorno di consegna
    
		orario_chiusura:
			orario di chiusura modifica spesa in base al giorno di consegna.
			Es. 'XX:XX' su 24 h  (es. 23:15)

		Superata la deadline impostata, le modifiche sono possibili solo a partire dalla settimana successiva
	*/
	$config['mlp']['lun']['spese']['giorno_chiusura'] = -1; 
	$config['mlp']['lun']['spese']['orario_chiusura'] = '12:00';
 
	$config['mlp']['mar']['spese']['giorno_chiusura'] = -1; 
	$config['mlp']['mar']['spese']['orario_chiusura'] = '12:00';

	$config['mlp']['mer']['spese']['giorno_chiusura'] = -1; 
	$config['mlp']['mer']['spese']['orario_chiusura'] = '12:00';

	$config['mlp']['gio']['spese']['giorno_chiusura'] = -1; 
	$config['mlp']['gio']['spese']['orario_chiusura'] = '12:00';

	$config['mlp']['ven']['spese']['giorno_chiusura'] = -1; 
	$config['mlp']['ven']['spese']['orario_chiusura'] = '12:00';
	
	//test
	$config['mlp']['sab']['spese']['giorno_chiusura'] = -1; 
	$config['mlp']['sab']['spese']['orario_chiusura'] = '12:00';
	
?> 
