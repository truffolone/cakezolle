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
	$config['area_riservata']['lun']['ml']['giorno_apertura'] = -7; 
	//$config['area_riservata']['lun']['ml']['orario_apertura'] = '16:30';    //OK
	$config['area_riservata']['lun']['ml']['orario_apertura'] = '09:00';      //PER PROVE
	$config['area_riservata']['lun']['ml']['giorno_chiusura'] = -5; 
	$config['area_riservata']['lun']['ml']['orario_chiusura'] = '23:59';
 
	$config['area_riservata']['mar']['ml']['giorno_apertura'] = -6; //OK
    //$config['area_riservata']['mar']['ml']['giorno_apertura'] = -8;	//test
	$config['area_riservata']['mar']['ml']['orario_apertura'] = '23:58'; //ok 
	//$config['area_riservata']['mar']['ml']['orario_apertura'] = '08:30';  //test
	$config['area_riservata']['mar']['ml']['giorno_chiusura'] = -6; // OK
	//$config['area_riservata']['mar']['ml']['giorno_chiusura'] = -1; //test aperto fino a lu
	$config['area_riservata']['mar']['ml']['orario_chiusura'] = '23:59';

/*
	$config['area_riservata']['mer']['ml']['giorno_apertura'] = -9; 
	$config['area_riservata']['mer']['ml']['orario_apertura'] = '16:30';
	$config['area_riservata']['mer']['ml']['giorno_chiusura'] = -7; 
	$config['area_riservata']['mer']['ml']['orario_chiusura'] = '23:59';
*/
	
	$config['area_riservata']['mer']['ml']['giorno_apertura'] = -7; 
	$config['area_riservata']['mer']['ml']['orario_apertura'] = '23:58';
	$config['area_riservata']['mer']['ml']['giorno_chiusura'] = -7; 
	$config['area_riservata']['mer']['ml']['orario_chiusura'] = '23:59';
	

	$config['area_riservata']['gio']['ml']['giorno_apertura'] = -8; 
	$config['area_riservata']['gio']['ml']['orario_apertura'] = '23:58';
	$config['area_riservata']['gio']['ml']['giorno_chiusura'] = -8;     // OK
	//$config['area_riservata']['gio']['ml']['giorno_chiusura'] = -6;     //test aperto fino a venerdi
	$config['area_riservata']['gio']['ml']['orario_chiusura'] = '23:59';

	$config['area_riservata']['ven']['ml']['giorno_apertura'] = -9; 
	$config['area_riservata']['ven']['ml']['orario_apertura'] = '23:58';
	$config['area_riservata']['ven']['ml']['giorno_chiusura'] = -9; 
	$config['area_riservata']['ven']['ml']['orario_chiusura'] = '23:59';


	//per testare l'area su clienti che ricevono sab
	$config['area_riservata']['sab']['ml']['giorno_apertura'] = -14; 
	$config['area_riservata']['sab']['ml']['orario_apertura'] = '00:01';
	$config['area_riservata']['sab']['ml']['giorno_chiusura'] = -7; 
	$config['area_riservata']['sab']['ml']['orario_chiusura'] = '23:59';
	
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
	$config['area_riservata']['lun']['af']['giorno_chiusura'] = -4; 
	$config['area_riservata']['lun']['af']['orario_chiusura'] = '12:00';
 
	$config['area_riservata']['mar']['af']['giorno_chiusura'] = -5; 
	$config['area_riservata']['mar']['af']['orario_chiusura'] = '12:00';

	$config['area_riservata']['mer']['af']['giorno_chiusura'] = -6; 
	$config['area_riservata']['mer']['af']['orario_chiusura'] = '12:00';

	$config['area_riservata']['gio']['af']['giorno_chiusura'] = -7; 
	$config['area_riservata']['gio']['af']['orario_chiusura'] = '12:00';

	$config['area_riservata']['ven']['af']['giorno_chiusura'] = -8; 
	$config['area_riservata']['ven']['af']['orario_chiusura'] = '12:00';

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
	$config['area_riservata']['lun']['spese']['giorno_chiusura'] = -1; 
	$config['area_riservata']['lun']['spese']['orario_chiusura'] = '12:00';
 
	$config['area_riservata']['mar']['spese']['giorno_chiusura'] = -1; 
	$config['area_riservata']['mar']['spese']['orario_chiusura'] = '12:00';

	$config['area_riservata']['mer']['spese']['giorno_chiusura'] = -1; 
	$config['area_riservata']['mer']['spese']['orario_chiusura'] = '12:00';

	$config['area_riservata']['gio']['spese']['giorno_chiusura'] = -1; 
	$config['area_riservata']['gio']['spese']['orario_chiusura'] = '12:00';

	$config['area_riservata']['ven']['spese']['giorno_chiusura'] = -1; 
	$config['area_riservata']['ven']['spese']['orario_chiusura'] = '12:00';
	
	//test
	$config['area_riservata']['sab']['spese']['giorno_chiusura'] = -1; 
	$config['area_riservata']['sab']['spese']['orario_chiusura'] = '12:00';
	
?> 
