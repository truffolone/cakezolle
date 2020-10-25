<?php

App::uses('Component', 'Controller');

//implementation based on technical documentation "Rapporti Interbancari Diretti - RID - CBI-RID-001 v6.05 from http://www.cbi-org.eu/"

Configure::write("CODICE_SIA_MITTENTE", "AWPQS"); //5 caratteri - codice assegnato dalla Sia all'Azienda Mittente; è censita sul Directory;

//COORDINATE BANCARIE COMPLETE DELL'ORDINANTE
Configure::write("CHECK_DIGIT_IBAN", "70"); //le 2 cifre dopo l'indicazione del paese (IT)
//segue BBAN (IBAN senza indicazione paese e check digit)
Configure::write("CIN", "U"); //lettera dopo check digit
Configure::write("ABI", "05216"); //Codice ABI della banca: deve corrispondere con il codice ABI del ricevente (pos. 9-13) presente sul record di testa;
Configure::write("CAB", "03207");
Configure::write("NUMERO_CONTO", "000000000852"); //12 cifre - zeri di riempimento a sinistra

Configure::write("ABI_BANCA_ASSUNTRICE", Configure::read("ABI")); //5 caratteri - Codice ABI della banca assuntrice delle richieste di incasso (BANCA DEL CREDITORE)
Configure::write("CAB_BANCA_ASSUNTRICE", Configure::read("CAB")); //5 caratteri - Codice CAB dello sportello della banca
Configure::write("CONTO_ORDINANTE", Configure::read("NUMERO_CONTO")); //12 caratteri - Codice conto corrente del cliente ordinante l'incasso
	
Configure::write("CREDITOR_IDENTIFIER", "IT74ZZZ0000009848941002"); //da specifiche CBI-ORG

//Codice SIA del cliente ordinante; tale codice
//può essere diverso dal codice SIA
//dell’azienda mittente presente sul record di
//testa, ma deve comunque essere lo stesso per
//tutte le singole disposizioni contenute nel
//supporto logico; non necessariamente è
//censito tra i codici SIA censiti sul Directory
Configure::write("CODICE_SIA_ORDINANTE", Configure::read("CODICE_SIA_MITTENTE")); //5 caratteri

//DESCRIZIONE DEL CLIENTE CREDITORE - 3 segmenti da 30 caratteri ciascuno - (1° obbligatorio, 2° e 3° facoltativi)
Configure::write("DESC_1", "Zolle srl - Roma");
Configure::write("DESC_2", "");
Configure::write("DESC_3", "");

class RidUtilComponent extends Component
{ 
	function getFlussoRID($aNome_supporto, $aTipoIncassoRID, $charges)
	{
		$flussoRID = $this->_getRecordIR($aNome_supporto, $aTipoIncassoRID);
		
		for($i=0;$i<sizeof($charges);$i++) {
			$recordDisposizione = $this->_getRecordDisposizione($charges[$i], $i+1, $aTipoIncassoRID); //numero progressivo comincia da 1	
			$flussoRID .= $recordDisposizione;		
		}
		$recordEF = $this->_getRecordEF($aNome_supporto, $aTipoIncassoRID, $charges, strlen($flussoRID)/120);
		$flussoRID .= $recordEF;
		return $flussoRID;
	}

	//record di testa - 120 caratteri
	//@param $nome_supporto: Campo di libera composizione da parte dell'Azienda Mittente; dev'essere univoco nell'ambito della data di creazione e a 
	//						parità di mittente e ricevente (20 caratteri)
	//@param $aTipoIncassoRID: può assumere i seguenti valori: “blank” = RID commerciale (= ordinario), “U” = RID Utenze (= ordinario), “V” = RID Veloce
	//						Questo campo deve essere valorizzato allo stesso modo anche in tutte le disposizioni contenute nel supporto e sui 
	//						record di coda e di testa
	function _getRecordIR($aNome_supporto, $aTipoIncassoRID)
	{
		//blank
		$filler1 = str_repeat(" ", 1);
		//tipo record
		$tipo_record = "IR";
		//codice assegnato dalla Sia all'azienda mittente (5 caratteri)
		$mittente = Configure::read('CODICE_SIA_MITTENTE'); 
		//codice abi della banca assuntrice a cui devono essere inviate le disposizioni (5 caratteri)
		$ricevente = Configure::read('ABI_BANCA_ASSUNTRICE'); 
		//data nel format GGMMAA
		$data_creazione = date('dmy'); 
		//20 caratteri. 
		$nome_supporto = substr($aNome_supporto, 0, 20).str_repeat(" ", (20-strlen($aNome_supporto) > 0) ? 20-strlen($aNome_supporto) : 0);
		//6 caratteri. Campo a disposizione dell'azienda mittente 
		$campo_a_disposizione = str_repeat(" ", 6); 
		//59 caratteri; blank
		$filler2 = str_repeat(" ", 59); 
		//qualificatore di flusso (6 caratteri) Questo campo è facoltativo. Diventa obbligatorio esclusivamente per operazioni che comportano 
		//regole d’indirizzamento differenti da quelle ordinarie.
		$qualificatore_di_flusso = str_repeat(" ", 7); 
		//blank
		$filler3 = str_repeat(" ", 1);
		//
		$tipo_incasso_rid = $aTipoIncassoRID;
		//Assume il valore fisso “E” (Euro).
		$codice_divisa = "E";
		//blank
		$filler4 = str_repeat(" ", 1);
		//Campo non utilizzabile per l’inserimento di informazioni (5 caratteri)
		$campo_non_disponibile = str_repeat("0", 5);

		return 
			$filler1.
			$tipo_record.
			$mittente.
			$ricevente.
			$data_creazione.
			$nome_supporto.
			$campo_a_disposizione.
			$filler2.
			$qualificatore_di_flusso.
			$filler3.
			$tipo_incasso_rid.
			$codice_divisa.
			$filler4.
			$campo_non_disponibile;
	}

	//record di coda - 120 caratteri
	//@param $nome_supporto: Campo di libera composizione da parte dell'Azienda Mittente; dev'essere univoco nell'ambito della data di creazione e a 
	//						parità di mittente e ricevente (20 caratteri)
	//@param $aTipoIncassoRID: può assumere i seguenti valori: “blank” = RID commerciale (= ordinario), “U” = RID Utenze (= ordinario), “V” = RID Veloce
	//						Questo campo deve essere valorizzato allo stesso modo anche in tutte le disposizioni contenute nel supporto e sui 
	//						record di coda e di testa
	//@param $disposizioni: array contenente le disposizioni 
	function _getRecordEF($aNome_supporto, $aTipoIncassoRID, $disposizioni, $numRecord)
	{
		//blank
		$filler1 = str_repeat(" ", 1); 
		//tipo record
		$tipo_record = "EF";
		//codice assegnato dalla Sia all'azienda mittente (5 caratteri)
		$mittente = Configure::read('CODICE_SIA_MITTENTE'); 
		//codice abi della banca assuntrice a cui devono essere inviate le disposizioni (5 caratteri)
		$ricevente = Configure::read('ABI_BANCA_ASSUNTRICE'); 
		//data nel format GGMMAA
		$data_creazione = date('dmy'); 
		//20 caratteri. 
		$nome_supporto = substr($aNome_supporto, 0, 20).str_repeat(" ", (20-strlen($aNome_supporto) > 0) ? 20-strlen($aNome_supporto) : 0);
		//6 caratteri. Campo a disposizione dell'azienda mittente 
		$campo_a_disposizione = str_repeat(" ", 6);
		//numero di disposizioni (richieste di incasso RID contenute nel flusso) (7 caratteri)
		$numero_disposizioni = sizeof($disposizioni);
		$numero_disposizioni = str_repeat("0", 7-strlen($numero_disposizioni)).$numero_disposizioni;
		//Importo totale – in centesimi di Euro - delle disposizioni contenute nel flusso (15 caratteri)
 		$tot_importi_negativi = 0;
		foreach($disposizioni as $disposizione) $tot_importi_negativi += $this->_getImportoInCentesimi($disposizione['Addebito']['importo']);
		$tot_importi_negativi = str_repeat("0", 15-strlen($tot_importi_negativi)).$tot_importi_negativi;
		
		//Deve essere valorizzato con "zeri" (15 caratteri)
		$tot_importi_positivi = str_repeat("0", 15);
		//Numero dei record che compongono il flusso (comprensivo anche dei record di testa e di coda) (7 caratteri)
		$numero_record = $numRecord+1; //+1 per il record EF
		$numero_record = str_repeat("0", 7-strlen($numero_record)).$numero_record;
		//23 caratteri - blank
		$filler2 = str_repeat(" ", 23); 
		//
		$tipo_incasso_rid = $aTipoIncassoRID;
		//Assume il valore fisso “E” (Euro).
		$codice_divisa = "E";
		//Campo non utilizzabile per l’inserimento di informazioni (6 caratteri)
		$campo_non_disponibile = str_repeat("0", 6);

		return 
			$filler1.
			$tipo_record.
			$mittente.
			$ricevente.
			$data_creazione.
			$nome_supporto.
			$campo_a_disposizione.
			$numero_disposizioni.
			$tot_importi_negativi.
			$tot_importi_positivi.
			$numero_record.
			$filler2.
			$tipo_incasso_rid.
			$codice_divisa.
			$campo_non_disponibile;
	}

	function _getRecordDisposizione($disposizione, $numero_progressivo, $tipo_incasso_rid)
	{	
		return 
			$this->_getRecordCodice10($disposizione, $numero_progressivo, $tipo_incasso_rid).
			$this->_getRecordCodice16($numero_progressivo). //NOTA: facoltativo secondo la documentazione, ma se lo si mette si arrabbia!
			$this->_getRecordCodice17($disposizione, $numero_progressivo, $tipo_incasso_rid).			
			$this->_getRecordCodice20($numero_progressivo).
			$this->_getRecordCodice30($disposizione, $numero_progressivo).
			$this->_getRecordCodice40($disposizione, $numero_progressivo).
			$this->_getRecordCodice50_60($disposizione, $numero_progressivo).
			$this->_getRecordCodice70($disposizione, $numero_progressivo);
	}

	//record fisso codice 10 - 120 caratteri
	function _getRecordCodice10($disposizione, $aNumeroProgressivo, $aTipoIncassoRID)
	{
		//blank
		$filler1 = str_repeat(" ", 1); 
		//tipo record
		$tipo_record = "10";
		//7 caratteri - Numero della disposizione all'interno del flusso. Inizia con 1 ed è progressivo di 1. 
		//Il numero deve essere uguale per tutti i record della stessa ricevuta.
		$numero_progressivo = $aNumeroProgressivo;
		$numero_progressivo = str_repeat("0", 7-strlen($numero_progressivo)).$numero_progressivo;
		//Data di decorrenza della garanzia; deve essere obbligatoriamente valorizzata nelle disposizioni di incasso derivanti da un
		//allineamento elettronico archivi originato da Azienda e contiene la data scadenza della prima rata nel formato GGMMAA
		$data_decorrenza_garanzia = $disposizione['Addebito']['data_decorrenza_garanzia'];
		//blank 6 caratteri
		$filler2 = str_repeat(" ", 6);
		//Data alla quale si richiede l’addebito del conto del debitore nel formato GGMMAA
		$data_scadenza = $disposizione['Addebito']['data_scadenza'];
		//cliente destinatario nel formatoDeve assumere il valore fisso :"50000" = R.I.D - 5 caratteri
		$causale = "50000";	
		//Importo – in centesimi di Euro - della disposizione - 13 caratteri
		$importo = $this->_getImportoInCentesimi($disposizione['Addebito']['importo']);
		$importo = str_repeat("0", 13-strlen($importo)).$importo;
		//assume valore fisso "-"
		$segno = "-";
		
		//coordinate della banca assuntrice ( = creditore)
		
		//Codice ABI della banca assuntrice delle richieste di incasso; deve corrispondere a quello presente sul record di testa
		$abi_banca_assuntrice = Configure::read('ABI_BANCA_ASSUNTRICE');
		//Codice CAB dello sportello della banca
		$cab_banca_assuntrice = Configure::read('CAB_BANCA_ASSUNTRICE');
		//Codice conto corrente del cliente ordinante l'incasso
		$conto_ordinante = Configure::read('CONTO_ORDINANTE');
		
		//coordinate della banca domiciliataria ( = debitore)
		
		//Codice ABI della banca domiciliataria delle richieste di incasso
		$abi_banca_domiciliataria = $disposizione['AutorizzazioneRid']['abi'];
		//Codice CAB dello sportello della banca
		$cab_banca_domiciliataria = $disposizione['AutorizzazioneRid']['cab'];

		//blank - 12 caratteri
		$filler3 = str_repeat(" ", 12);

		//coordinate azienda Creditrice
		
		//5 caratteri
		$codice_azienda = Configure::read('CODICE_SIA_ORDINANTE');
		//deve assumere uno dei seguenti valori:
		//1 - utenza
		//2 - matricola
		//3 - codice fiscale
		//4 - codice cliente
		//5 - codice fornitore
		//6 - portafoglio commerciale
		//9 - altri
		$tipo_codice = 4;
		//codice con il quale il debitore è conosciuto dal creditore - 16 caratteri 
		$codice_cliente_debitore = $disposizione['Addebito']['cliente_id'];
		$codice_cliente_debitore = $codice_cliente_debitore.str_repeat(" ", 16-strlen($codice_cliente_debitore));
		//va valorizzato solo nel caso il debitore sia una Banca e in tal caso deve assumere il valore “B” (il codice ABI è indicato in pos. 70-74)
		$flag_tipo_debitore = " ";	
		//blank - 4 caratteri
		$filler4 = str_repeat(" ", 4);
		//
		$tipo_incasso_rid = $aTipoIncassoRID;
		//Assume il valore fisso “E” (Euro).
		$codice_divisa = "E";

		return 
			$filler1.
			$tipo_record.
			$numero_progressivo.
			$data_decorrenza_garanzia.
			$filler2.
			$data_scadenza.
			$causale.
			$importo.
			$segno.
			$abi_banca_assuntrice.
			$cab_banca_assuntrice.
			$conto_ordinante.
			$abi_banca_domiciliataria.
			$cab_banca_domiciliataria.
			$filler3.
			$codice_azienda.
			$tipo_codice.
			$codice_cliente_debitore.
			$flag_tipo_debitore.
			$filler4.
			$tipo_incasso_rid.
			$codice_divisa;
	}

	//record codice 16 - coordinate ordinante - 120 caratteri
	function _getRecordCodice16($aNumeroProgressivo)
	{
		//blank
		$filler1 = str_repeat(" ", 1); 
		//tipo record
		$tipo_record = "16";
		//7 caratteri - Numero della disposizione all'interno del flusso. Inizia con 1 ed è progressivo di 1. 
		//Il numero deve essere uguale per tutti i record della stessa ricevuta.
		$numero_progressivo = $aNumeroProgressivo;
		$numero_progressivo = str_repeat("0", 7-strlen($numero_progressivo)).$numero_progressivo;
		//Il codice paese deve essere uguale a IT o SM
		$codice_paese = "IT";
		//
		$check_digit = Configure::read('CHECK_DIGIT_IBAN');
		//
		$cin = Configure::read('CIN');
		//
		$codice_ABI = Configure::read('ABI');
		//
		$codice_CAB = Configure::read('CAB');
		//
		$numero_conto = Configure::read('NUMERO_CONTO');
		//blank - 7 caratteri
		$filler2 = str_repeat(" ", 7); 
		// Creditor identifier (23 caratteri)
		$creditor_identifier =  Configure::read("CREDITOR_IDENTIFIER"); 
		
		//blank - 41 caratteri (in realtà ne metto 12 in più perchè il creditor identifier italiano è lungo 23 invece di 35)
		$filler3 = str_repeat(" ", 53);

		return 
			$filler1.
			$tipo_record.
			$numero_progressivo.
			$codice_paese.
			$check_digit.
			$cin.
			$codice_ABI.
			$codice_CAB.
			$numero_conto.
			$filler2.
			$creditor_identifier.
			$filler3;			 
	}

	//record fisso codice 17 - 120 caratteri - PAYER'S COORDINATES
	function _getRecordCodice17($disposizione, $aNumeroProgressivo, $aTipoIncassoRID)
	{
		//blank
		$filler1 = str_repeat(" ", 1); 
		//tipo record
		$tipo_record = "17";
		//7 caratteri - Numero della disposizione all'interno del flusso. Inizia con 1 ed è progressivo di 1. 
		//Il numero deve essere uguale per tutti i record della stessa ricevuta.
		$numero_progressivo = $aNumeroProgressivo; //same as for record 10
		$numero_progressivo = str_repeat("0", 7-strlen($numero_progressivo)).$numero_progressivo;

		$country_code = "IT";

		$check_digit = $disposizione['AutorizzazioneRid']['check_digit'];
		
		$cin = $disposizione['AutorizzazioneRid']['cin'];
		
		$abi = $disposizione['AutorizzazioneRid']['abi'];
		
		$cab = $disposizione['AutorizzazioneRid']['cab'];

		$account_number = $disposizione['AutorizzazioneRid']['conto_corrente'];

		$sequence_type = str_repeat(" ", 4); //da valorizzare in base ad accordi con la banca

		$date_of_signature = date("dmy", strtotime($disposizione['AutorizzazioneRid']['rid_activated']));

		//blank - 73 caratteri
		$filler2 = str_repeat(" ", 73);

		
		return 
			$filler1.
			$tipo_record.
			$numero_progressivo.
			$country_code.
			$check_digit.
			$cin.
			$abi.
			$cab.
			$account_number.
			$sequence_type.
			$date_of_signature.
			$filler2;
	}

	//record fisso codice 20 - 120 caratteri
	function _getRecordCodice20($aNumeroProgressivo)
	{
		//blank
		$filler1 = str_repeat(" ", 1); 
		//tipo record
		$tipo_record = "20";
		//7 caratteri - Numero della disposizione all'interno del flusso. Inizia con 1 ed è progressivo di 1. 
		//Il numero deve essere uguale per tutti i record della stessa ricevuta.
		$numero_progressivo = $aNumeroProgressivo;
		$numero_progressivo = str_repeat("0", 7-strlen($numero_progressivo)).$numero_progressivo;

		//Descrizione del cliente creditore
		$desc_1 = substr(Configure::read('DESC_1'), 0, 30);
		$desc_1 = $desc_1.str_repeat(" ", 30-strlen($desc_1));
		$desc_2 = substr(Configure::read('DESC_2'), 0, 30);
		$desc_2 = $desc_2.str_repeat(" ", 30-strlen($desc_2));
		$desc_3 = substr(Configure::read('DESC_3'), 0, 30);
		$desc_3 = $desc_3.str_repeat(" ", 30-strlen($desc_3));

		$desc = $desc_1.$desc_2.$desc_3;
	
		//blank - 20 caratteri
		$filler2 = str_repeat(" ", 20);

		return 
			$filler1.
			$tipo_record.
			$numero_progressivo.
			$desc.
			$filler2;
	}

	//record fisso codice 30 - 120 caratteri
	function _getRecordCodice30($disposizione, $aNumeroProgressivo)
	{
		//blank
		$filler1 = " "; 
		//tipo record
		$tipo_record = "30";
		//7 caratteri - Numero della disposizione all'interno del flusso. Inizia con 1 ed è progressivo di 1. 
		//Il numero deve essere uguale per tutti i record della stessa ricevuta.
		$numero_progressivo = $aNumeroProgressivo;
		$numero_progressivo = str_repeat("0", 7-strlen($numero_progressivo)).$numero_progressivo;

		//Descrizione del cliente debitore - 3 segmenti (30 caratteri ciascuno)  - 1° obbligatorio - 2° e 3° facoltativo
		$desc_1 = $this->_remove_non_printable_chars( substr($disposizione['Cliente']['id'].' '.$disposizione['Cliente']['COGNOME'].' '.$disposizione['Cliente']['NOME'], 0, 30) );
		$desc_1 = strlen($desc_1) > 30 ? substr($desc_1, 0, 30) : $desc_1.str_repeat(" ", 30-strlen($desc_1));
		$desc_2 =  $this->_remove_non_printable_chars( substr($disposizione['AutorizzazioneRid']['anagrafica_intestatario'], 0, 30) );
		$desc_2 = strlen($desc_2) > 30 ? substr($desc_1, 0, 30) : $desc_2.str_repeat(" ", 30-strlen($desc_2));
		$desc_3 = "";
		$desc_3 = $desc_3.str_repeat(" ", 30-strlen($desc_3));

		$desc = $desc_1.$desc_2.$desc_3;
	
		//blank - 20 caratteri
		$filler2 = str_repeat(" ", 20);

		return 
			$filler1.
			$tipo_record.
			$numero_progressivo.
			$desc.
			$filler2;
	}

	//record fisso codice 40 - 120 caratteri - FACOLTATIVO
	function _getRecordCodice40($disposizione, $aNumeroProgressivo)
	{
		//blank
		$filler1 = " "; 
		//tipo record
		$tipo_record = "40";
		//7 caratteri - Numero della disposizione all'interno del flusso. Inizia con 1 ed è progressivo di 1. 
		//Il numero deve essere uguale per tutti i record della stessa ricevuta.
		$numero_progressivo = $aNumeroProgressivo;
		$numero_progressivo = str_repeat("0", 7-strlen($numero_progressivo)).$numero_progressivo;

		//Indirizzo del cliente debitore
		$ind = $this->_remove_non_printable_chars( substr($disposizione['AutorizzazioneRid']['indirizzo_sottoscrittore'],0, 30) );
		$indirizzo = strlen($ind) > 30 ? substr($ind, 0, 30) : $ind.str_repeat(" ", 30-strlen($ind));		
		$cap = empty($disposizione['AutorizzazioneRid']['cap_sottoscrittore']) ? "00100" : $disposizione['AutorizzazioneRid']['cap_sottoscrittore'];
		$com = $this->_remove_non_printable_chars( substr($disposizione['AutorizzazioneRid']['localita_sottoscrittore'],0, 25) );
		$comune = strlen($com) > 25 ? substr($com, 0, 25) : $com.str_repeat(" ", 25-strlen($com));
		
		//blank - 20 caratteri
		$filler2 = str_repeat(" ", 50);

		return 
			$filler1.
			$tipo_record.
			$numero_progressivo.
			$indirizzo.
			$cap.
			$comune.
			$filler2;
	}

	//record fisso codice 50/60 - 120 caratteri
	//Questi record sono usati in alternativa in base al tipo di descrizione della disposizione d'incasso. Se la
	//descrizione si esaurisce nei 90 caratteri, deve essere usato il record 50; in caso contrario deve essere usato il
	//record 60 (da un minimo di 2 ad un massimo di 5).
	function _getRecordCodice50_60($disposizione, $aNumeroProgressivo)
	{
		$disposizione['Addebito']['descrizione'] = 'Saldo Zolle al 31/'.$disposizione['Addebito']['mese'].'/'.$disposizione['Addebito']['anno'];

		if(strlen($disposizione['Addebito']['descrizione']) > 90) //usa record 60
		{
			$disposizione['Addebito']['descrizione'] = substr($disposizione['Addebito']['descrizione'], 0, 90*5); //massimo 5 recordi di tipo 60
			$disposizione['Addebito']['descrizione'] = $this->_remove_non_printable_chars($disposizione['Addebito']['descrizione']);

			$toReturn = "";
			$num_records = ceil( strlen($disposizione['Addebito']['descrizione'])/90 );
			for($k=0;$k<$num_records;$k++)
			{
				//blank
				$filler1 = str_repeat(" ", 1);
				//tipo record
				$tipo_record = "60";
				//7 caratteri - Numero della disposizione all'interno del flusso. Inizia con 1 ed è progressivo di 1. 
				//Il numero deve essere uguale per tutti i record della stessa ricevuta.
				$numero_progressivo = $aNumeroProgressivo;
				$numero_progressivo = str_repeat("0", 7-strlen($numero_progressivo)).$numero_progressivo;
		
				//riferimenti al debito (è suddiviso in due segmenti di 45 caratteri ciascuno)

				$curr_desc = substr($disposizione['Addebito']['descrizione'], $k*90, $k*90+45);
				//45 caratteri - obbligatorio
				$segmento1 = subtr($curr_desc, 0, 45);
				$segmento1 = $segmento1.str_repeat(" ", 45-strlen($segmento1));

				//45 caratteri - facoltativo
				$segmento2 = subtr($curr_desc, 45);
				$segmento2 = $segmento2.str_repeat(" ", 45-strlen($segmento2));

				//blank - 20 caratteri
				$filler2 = str_repeat(" ", 20);

				$toReturn .=
					$filler1.
					$tipo_record.
					$numero_progressivo.
					$segmento1.
					$segmento2.
					$filler2;
			}

			return $toReturn;			
		}
		else //usa record 50	
		{
			//blank
			$filler1 = str_repeat(" ", 1); 
			//tipo record
			$tipo_record = "50";
			//7 caratteri - Numero della disposizione all'interno del flusso. Inizia con 1 ed è progressivo di 1. 
			//Il numero deve essere uguale per tutti i record della stessa ricevuta.
			$numero_progressivo = $aNumeroProgressivo;
			$numero_progressivo = str_repeat("0", 7-strlen($numero_progressivo)).$numero_progressivo;
		
			//riferimenti al debito (è suddiviso in due segmenti di 45 caratteri ciascuno)

			//45 caratteri - obbligatorio
			$segmento1 = substr($disposizione['Addebito']['descrizione'], 0, 45);
			$segmento1 = $segmento1.str_repeat(" ", 45-strlen($segmento1));

			//45 caratteri - facoltativo
			$segmento2 = subStr($disposizione['Addebito']['descrizione'], 45);
			$segmento2 = $segmento2.str_repeat(" ", 45-strlen($segmento2));

			//blank - 20 caratteri
			$filler2 = str_repeat(" ", 20);

			return
				$filler1.
				$tipo_record.
				$numero_progressivo.
				$segmento1.
				$segmento2.
				$filler2;
		}
	}

	//record fisso codice 70 - 120 caratteri
	function _getRecordCodice70($disposizione, $aNumeroProgressivo)
	{
		//blank
		$filler1 = str_repeat(" ", 1); 
		//tipo record
		$tipo_record = "70";
		//7 caratteri - Numero della disposizione all'interno del flusso. Inizia con 1 ed è progressivo di 1. 
		//Il numero deve essere uguale per tutti i record della stessa ricevuta.
		$numero_progressivo = $aNumeroProgressivo;
		$numero_progressivo = str_repeat("0", 7-strlen($numero_progressivo)).$numero_progressivo;
		//codice attribuito dall’azienda mittente -15 caratteri (reference code)
		$codice_di_riferimento = "ZOLLE-".date("Ymd");
		$codice_di_riferimento = $codice_di_riferimento.str_repeat(" ", 15-strlen($codice_di_riferimento));
		//blank - 69 caratteri	
		$filler2 = str_repeat(" ", 69);
		//Campo a disposizione per eventuali accordi bilaterali tra Cliente e Banca Assuntrice
		$campo_a_disposizione = " "; 
		//Indica la facoltà di rifiuto dell’addebito da parte del debitore; se presente deve
		//assumere uno dei seguenti valori:
		//1= esiste la facoltà di rimborso dopo la scadenza (D+5),
		//2= esiste la facoltà di rimborso alla scadenza (D), 
		//3= non esiste la facoltà di storno per contestazione del debitore,
		//4= non esiste la facoltà di storno per la banca domiciliata ria, 
		//8= esiste il diritto al rimborso (D+8 settimane),
		//9= non esiste la facoltà di rimborso per le previste caratteristiche del mandato (clausola importo prefissato)
		$flag_facolta_storno_di_addebito = $disposizione['AutorizzazioneRid']['opzione_addebito'];
		if($flag_facolta_storno_di_addebito == 0) $flag_facolta_storno_di_addebito = " "; //non valorizzato
		//Campo a disposizione per eventuali accordi bilaterali tra Cliente e Banca Assuntrice
		$campo_a_disposizione2 = " ";
		//blank - 3 caratteri	
		$filler3 = str_repeat(" ", 3);
		//campo a disposizione, valorizzabile dall'Azienda previ accordi diretti con la Banca Assuntrice - 10 caratteri
		$chiavi_di_controllo = str_repeat(" ", 10);
		//blank - 1 carattere	
		$filler4 = " ";
		//campo a disposizione, valorizzabile dall'Azienda previ accordi diretti con la Banca Assuntrice - 9 caratteri
		$chiavi_di_controllo2 = str_repeat(" ", 9);

		return
			$filler1.
			$tipo_record.
			$numero_progressivo.
			$codice_di_riferimento.
			$filler2.
			$campo_a_disposizione.
			$flag_facolta_storno_di_addebito.
			$campo_a_disposizione2.
			$filler3.
			$chiavi_di_controllo.
			$filler4.
			$chiavi_di_controllo2;
	}

	//restituisce $importo (in euro) convertito in centesimi di euro
	function _getImportoInCentesimi($importo)
	{
		$importo = str_replace(",", ".", $importo);
		$importo = number_format($importo, 2); // mandatory !!!
		if(strpos($importo, ".") !== FALSE) {
			$parte_decimale = substr($importo, strpos($importo, ".")+1);
			for($i=0;$i<(2-strlen($parte_decimale));$i++) $parte_decimale .= '0';
		}
		else $parte_decimale = '00';

		return substr($importo, 0, strpos($importo, ".")).$parte_decimale;
	}

	function _remove_non_printable_chars($str) {
		return preg_replace('/[\x00-\x1F\x80-\xFF]/', '', $str);
	}
}
