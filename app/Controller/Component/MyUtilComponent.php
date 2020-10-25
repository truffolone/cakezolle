<?php

App::uses('Component', 'Controller');

/*
MICRO TUTORIAL SUI PARAMETRI DELL'URL DI KEYCLIENT:
Per la restituzione dei parametri di risposta del pagamento, si possono usare due valori da inviare nella richiesta di pagamento:
- 'url': consente di specificare l'url a cui il server della banca redirezionera' il browser con i parametri della risposta. Da usare se si vuole
  ad esempio visualizzare un risultato sul pagamento sul browser
- 'urlpost' : consente di specificare l'url a cui il server della banca inviera' direttamente (naturalmente in modo ASINCRONO compiendo esso stesso
  una richiesta) in modalita' POST i parametri del pagamento

NOTA: entrambi i parametri funzionano SOLO con la porta 80!
*/

define('ALIAS_test','payment_testm_urlmac');
define('CODICEMAC_test','esempiodicalcolomac');
define('ALIAS','payment_197348');
define('MERCHANTNUMBER','197348');
define('CODICEMAC','t0MRI8PBMINkOoyoG7DYNFMgdNs7jK1QgJslPleNAcBCwjKUf0L1RRhVv92TdBQYd0BIzzLD3pPvLgB6VN2Xo3t5f3rPKpuznfK9K1XKytxEhlyuEnLNUlFJ9ZdZednpv2gW8hCJ7WBtxBg4ZhjntQwVqMLHtZd9hhbiSSC6kjH69cwgLxazYSv2B7YcicOAWUzJ2BCiY3U58k31P8HRm3BTfi34URu2wduHowYswWjwGrYI78R6bLF3DBzrS8sf');

class MyUtilComponent extends Component 
{
	var $components = array('Email');
	
	//restituisce l'url completo per eseguire il pagamento.
	//@param spesa: l'array rappresentante i dettagli la spesa
	//@param type: 'non ricorrente','primo pagamento'
	function get_payment_url_simple($transaction_id, $importo,  $contratto_id, $mail)
	{
		$importo = (int)(100*$importo);
		if($importo < 100)
		{
			//normalizza l'importo (almeno 3 caratteri)
			while(strlen($importo) < 3) $importo = '0'.$importo;
		}
		$divisa = 'EUR';
		//il transaction_id e' sempre quello dell'ultimo tentativo di pagamento eseguito
		$codTrans = $transaction_id;
		//url of the program expected to receive response parameters (if merchant requires it)
		$url = Router::url(array('controller' => 'pagamenti_carta', 'action' => 'esito_pagamento'), true);
		$languageId = 'ITA';
		$mac = $this->get_mac_to_be_sent($importo, $codTrans);

		$extra_params = '';
		$num_contratto = $contratto_id;
		$extra_params = "&num_contratto=${num_contratto}&tipo_servizio=paga_rico"; 

		$alias = ALIAS;

		if(TEST)
		{
			$importo = 001;
			$alias = ALIAS_test;
		}

		//$output_url = 'https://ecommerce.cim-italia.it/ecomm/DispatcherServlet?'; //vecchio indirizzo
		//$output_url = "https://ecommerce.keyclient.it/ecomm/ecomm/DispatcherServlet?"; //nuovo indirizzo
		$output_url = "https://ecommerce.nexi.it/ecomm/ecomm/DispatcherServlet?"; //nuovo indirizzo
		$output_url .= 'alias='.$alias;
		$output_url .= '&importo='.$importo;
		$output_url .= '&divisa='.$divisa;
		$output_url .= '&codTrans='.$codTrans;
		$output_url .= '&mail='.$mail;
		$output_url .= '&url='.$url;
		$output_url .= '&languageId='.$languageId;
		$output_url .= '&mac='.$mac;
		$output_url .= $extra_params;

		return $output_url;	
	}

	//restituisce l'url completo per eseguire il pagamento.
	function get_payment_url_recurrent($transaction_id, $importo, $contratto_id, $cliente_email, $scadenza_pan)
	{
		$merchantnumber = MERCHANTNUMBER;
		$importo = (int)(100*$importo);
		if($importo < 100)
		{
			//normalizza l'importo (almeno 3 caratteri)
			while(strlen($importo) < 3) $importo = '0'.$importo;
		}
		$divisa = 'EUR';
		$codTrans = $transaction_id;
		//url used in the communication server to server (inserito anche se non funziona ...)
		$url = Router::url(array('controller' => 'pagamenti_carta', 'action' => 'esito_pagamento_ricorrente'), true);
		$mac = $this->get_mac_to_be_sent($importo, $codTrans);
		$num_contratto = $contratto_id;
		$mail = $cliente_email;
		$EXPIRY = $scadenza_pan;//coincide con il parametro 'scadenza_pan' restituito con il primo pagamento
		$tipo_servizio = 'paga_rico';

		if(TEST)
		{
			$importo = 001;
			$merchantnumber = ALIAS_test;
		}

		//$output_url = 'https://ecommerce.cim-italia.it/ecomm/CassaMultiL_url.jsp'; //vecchio indirizzo
		//$output_url = "https://ecommerce.keyclient.it/ecomm/ecomm/CassaMultiL.jsp"; //nuovo indirizzo. Senza _url finale (ovvero CassaMultiL invece di CassaMultiL_url) il sistema restituisce immediatamente e correttamente la risposta senza necessità di URL o PostURL
		$output_url = "https://ecommerce.nexi.it/ecomm/ecomm/CassaMultiL.jsp"; //nuovo indirizzo
		$output_rel_path = 'merchantnumber='.$merchantnumber;
		$output_rel_path .= '&importo='.$importo;
		$output_rel_path .= '&divisa='.$divisa;
		$output_rel_path .= '&codTrans='.$codTrans;
		$output_rel_path .= '&mac='.$mac;
		$output_rel_path .= '&num_contratto='.$num_contratto;
		$output_rel_path .= '&mail='.$mail;
		$output_rel_path .= '&$EXPIRY='.$EXPIRY;
		$output_rel_path .= '&tipo_servizio='.$tipo_servizio;

		return array('base_url' => $output_url, 'rel_path' => $output_rel_path);
	}

	function get_mac_to_be_sent($amount, $tran_id)
	{
		$codicemac = CODICEMAC;
		$importo = $amount;
		if(TEST)
		{ 
			$importo = 001;
			$codicemac = CODICEMAC_test;
		}
		$mac = "codTrans=${tran_id}divisa=EURimporto=${importo}${codicemac}";
		
		return urlencode(urlencode(base64_encode(md5($mac))));
	}

	//calcolo del mac dato che deve essere restituito dalla banca (attenzione: l'algoritmo e' diverso rispetto al mac inviato!)
	//ATTENZIONE: IN ALCUNI CASI IL MAC RICEVUTO DALLA BANCA PUO' RISULTARE DIVERSO DA QUELLO CALCOLATO IN LOCALE.
	//PUO' SUCCEDERE AD ESEMPIO LA SEGUENTE SITUAZIONE:
	//Ricevuto:  NmM3ZTFiN2RkZjUxODI2OGRlYWI5ZmYzYTA1NWU2ZWQ%253D
	//Calcolato: NmM3ZTFiN2RkZjUxODI2OGRlYWI5ZmYzYTA1NWU2ZWQ%3D
	//OVVERO LA PRESENZA DI UN VALORE FISSO '25' DOPO '%'
	//DOPO AVER CONTATTATO KEYCLIENT SI E' POTUTO SCOPRIRE CHE TALE VALORE ('25') IDENTIFICA L'INVIO IN GET E CHE IL MODO MIGLIORE
	//PER CONFRONTARE IL MAC RICEVUTO E' DI ESCLUDERE LA PARTE FINALE DOPO '%'
	function get_mac_to_be_received($transaction_id, $importo, $returned_params)
	{
		$codTrans = $transaction_id;
		$importo = (int)(100*$importo);
		if($importo < 100)
		{
			//normalizza l'importo (almeno 3 caratteri)
			while(strlen($importo) < 3) $importo = '0'.$importo;
		}
		$divisa = 'EUR'; //solo pagamenti in euro
		$esito = $returned_params['esito']; //quello restituito dalla banca
		$data = $returned_params['data']; //quella restituita dal server (interessa relativamente)
		$orario = $returned_params['orario']; //quella restituita dal server (non coincide con quella locale!)
		$codAut = $returned_params['codAut'];
		$codicemac = CODICEMAC;

		if(TEST)
		{
			$importo = 001;
			$codicemac = CODICEMAC_test;
		}

		$mac="codTrans=${codTrans}esito=${esito}importo=${importo}divisa=${divisa}data=${data}orario=${orario}codAut=${codAut}${codicemac}";
		return urlencode(base64_encode(md5($mac)));
	}
}

