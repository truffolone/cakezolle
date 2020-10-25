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

	
	
	$config['mlv']['lun']['ml']['giorno_apertura'] = -9; 
	$config['mlv']['lun']['ml']['orario_apertura'] = '08:00';
	$config['mlv']['lun']['ml']['giorno_chiusura'] = -3; 
	$config['mlv']['lun']['ml']['orario_chiusura'] = '23:59';
 
	$config['mlv']['mar']['ml']['giorno_apertura'] = -6; 
	$config['mlv']['mar']['ml']['orario_apertura'] = '8:00';  
	$config['mlv']['mar']['ml']['giorno_chiusura'] = -1; 
	$config['mlv']['mar']['ml']['orario_chiusura'] = '13:00';

	$config['mlv']['mer']['ml']['giorno_apertura'] = -9; 
	$config['mlv']['mer']['ml']['orario_apertura'] = '11:00';
	$config['mlv']['mer']['ml']['giorno_chiusura'] = -3; 
	$config['mlv']['mer']['ml']['orario_chiusura'] = '14:00';

	$config['mlv']['gio']['ml']['giorno_apertura'] = -9; 
	$config['mlv']['gio']['ml']['orario_apertura'] = '11:00';
	$config['mlv']['gio']['ml']['giorno_chiusura'] = -1; 
	$config['mlv']['gio']['ml']['orario_chiusura'] = '14:00';

	$config['mlv']['ven']['ml']['giorno_apertura'] = -9; 
	$config['mlv']['ven']['ml']['orario_apertura'] = '11:00';
	$config['mlv']['ven']['ml']['giorno_chiusura'] = -1; 
	$config['mlv']['ven']['ml']['orario_chiusura'] = '20:00';
	

	//per testare l'area su clienti che ricevono sab
	$config['mlv']['sab']['ml']['giorno_apertura'] = -2; 
	$config['mlv']['sab']['ml']['orario_apertura'] = '00:01';
	$config['mlv']['sab']['ml']['giorno_chiusura'] = -1; 
	$config['mlv']['sab']['ml']['orario_chiusura'] = '23:59';
	
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
	$config['mlv']['lun']['af']['giorno_chiusura'] = -4; 
	$config['mlv']['lun']['af']['orario_chiusura'] = '12:00';
 
	$config['mlv']['mar']['af']['giorno_chiusura'] = -5; 
	$config['mlv']['mar']['af']['orario_chiusura'] = '12:00';

	$config['mlv']['mer']['af']['giorno_chiusura'] = -6; 
	$config['mlv']['mer']['af']['orario_chiusura'] = '12:00';

	$config['mlv']['gio']['af']['giorno_chiusura'] = -7; 
	$config['mlv']['gio']['af']['orario_chiusura'] = '12:00';

	$config['mlv']['ven']['af']['giorno_chiusura'] = -8; 
	$config['mlv']['ven']['af']['orario_chiusura'] = '12:00';

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
	$config['mlv']['lun']['spese']['giorno_chiusura'] = -1; 
	$config['mlv']['lun']['spese']['orario_chiusura'] = '12:00';
 
	$config['mlv']['mar']['spese']['giorno_chiusura'] = -1; 
	$config['mlv']['mar']['spese']['orario_chiusura'] = '12:00';

	$config['mlv']['mer']['spese']['giorno_chiusura'] = -1; 
	$config['mlv']['mer']['spese']['orario_chiusura'] = '12:00';

	$config['mlv']['gio']['spese']['giorno_chiusura'] = -1; 
	$config['mlv']['gio']['spese']['orario_chiusura'] = '12:00';

	$config['mlv']['ven']['spese']['giorno_chiusura'] = -1; 
	$config['mlv']['ven']['spese']['orario_chiusura'] = '12:00';
	
	//test
	$config['mlv']['sab']['spese']['giorno_chiusura'] = -1; 
	$config['mlv']['sab']['spese']['orario_chiusura'] = '12:00';
	
?> 
