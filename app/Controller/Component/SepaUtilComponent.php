<?php

App::uses('Component', 'Controller');

class SepaUtilComponent extends Component
{ 
	function getFlussoSEPA($nomeDistinta, $dataScadenza, $addebitiFRST, $addebitiRCUR)
	{
		$importoTotale = 0;
		foreach($addebitiFRST as $a) {
			$importoTotale += $a['Addebito']['importo'];
		}
		foreach($addebitiRCUR as $a) {
			$importoTotale += $a['Addebito']['importo'];
		}	
		
		$nomeCreditore = 'Le Zolle s.r.l. - Roma';
		$ibanZolle = array(
			'country_code' => 'IT',
			'check_digit' => '03',
			'cin' => 'X',
			'abi' => '08327',
			'cab' => '03258',
			'conto' => '000000001398'
		);
		$ibanZolleStr = $ibanZolle['country_code'].
						$ibanZolle['check_digit'].
						$ibanZolle['cin'].
						$ibanZolle['abi'].
						$ibanZolle['cab'].
						$ibanZolle['conto'];
		$creditorIdentifier = 'IT74ZZZ0000009848941002';
		
		$codiceCUC = '1409202Y';
		
		/*
		 * $arraySEPA = array(
			'CBISDDReqPhyMsg' => array(
				
				// 1:1 - See General Part for details of application checks
				'CBIHdrTrt' => '',
				
				// 1:1 - See General Part for details of application checks
				'CBIHdrSrv' => '',
				// ----------------------------------------------------------------------------
				// 1:1
				'CBIBdySDDReq'
		 */
		
		
		
		// formato conforme a CBI v00.01.00 (ultima) - se necessario tornare indietro alla precedente è necessario
		// sostituire 00.01.00 con 00.00.06 
		
				// aggiungere xmlns, xmlns:xsi, xsi:schemaLocation via addAttribute in forma valida sembra impossibile,
				// li schianto direttamente nella definizione dell'elemento root
				$distintaSEPA = new SimpleXMLElement('<CBIBdySDDReq xmlns="urn:CBI:xsd:CBIBdySDDReq.00.01.00" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:schemaLocation="urn:CBI:xsd:CBIBdySDDReq.00.00.06 CBIBdySDDReq.00.00.06.xsd"></CBIBdySDDReq>'); // per UBI il flusso parte direttamente da qui, la parte esterna (vedi sopra) non è menzionata
				// la versione sotto con meta informazioni sulla verisone xml non serve perchè uso alla fine dom document per fare il prettify dell'xml)
				/*$distintaSEPA = new SimpleXMLElement('<?xml version="1.0" encoding="UTF-8"?><CBIBdySDDReq xmlns="urn:CBI:xsd:CBIBdySDDReq.00.01.00" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:schemaLocation="urn:CBI:xsd:CBIBdySDDReq.00.00.06 CBIBdySDDReq.00.00.06.xsd"></CBIBdySDDReq>'); // per UBI il flusso parte direttamente da qui, la parte esterna (vedi sopra) non è menzionata*/
				//$distintaSEPA->addAttribute("xmlns", "urn:CBI:xsd:CBIBdySDDReq.00.01.00");
				//$distintaSEPA->addAttribute("xmlns:xsi", "http://www.w3.org/2001/XMLSchema-instance", "urn:CBI:xsd:CBIBdySDDReq.00.01.00");
				//$distintaSEPA->addAttribute("xsi:schemaLocation", "urn:CBI:xsd:CBIBdySDDReq.00.00.06 CBIBdySDDReq.00.00.06.xsd", "http://www.w3.org/2001/XMLSchema-instance");
				
					// 1:1
					$PhyMsgInf = $distintaSEPA->addChild('PhyMsgInf');
						// 1:1 - The value of the field must be consistent with the service name indicated in 
						/* the service header (CBIHdrSrv)
						 INC-SDDC-01 oppure INC-SDDB-01 */
						$PhyMsgInf->addChild('PhyMsgTpCd', 'INC-SDDC-01'); // da doc UBI
						// 1:1 - The value contained in this field Must coincide with the number of CBIEnvelSDDReqLogMsg blocks
						$PhyMsgInf->addChild('NbOfLogMsg', 1);
					// 1:n - CBI logical message envelope, including electronic signature, if applicable
					/* per Zolle ce n'è solo uno */
					$CBIEnvelSDDReqLogMsg = $distintaSEPA->addChild('CBIEnvelSDDReqLogMsg');
						// 1:1 - this is a logical SDD collection request, wrapped in an envelope
						$CBISDDReqLogMsg = $CBIEnvelSDDReqLogMsg->addChild('CBISDDReqLogMsg');
							// 1:1
							$GrpHdr = $CBISDDReqLogMsg->addChild('GrpHdr');
							$GrpHdr->addAttribute("xmlns", "urn:CBI:xsd:CBISDDReqLogMsg.00.01.00");
							
								// 1:1 - message identification
								/* 1..35 - Must be unique */
								$GrpHdr->addChild('MsgId', $nomeDistinta); // UBI: univoco a parità di data creazione e mittente, si raccomanda di usare il medesimo valore per PmtInfId
								// 1:1 - Creation DateTime: data e ora di creazione della richiesta
								/* ISO date time */
								$GrpHdr->addChild('CreDtTm', date('c'));
								// 1:1  - number of transactions: Number of direct debit transactions included in the logical message
								/* 1..15 */ 
								$GrpHdr->addChild('NbOfTxs', sizeof($addebitiFRST) + sizeof($addebitiRCUR));
								// 1:1 - Total amount of all the transactions included in the collection request
								/* decimal number (max total digits: 18, fraction digits:17) */
								/*
								 * UBI: [DecimalNumber  es “180.51”]
									L'importo deve essere compreso tra 0.01 e 999999999999999.99;
									la parte decimale deve essere max di 2 cifre ma può essere
									anche assente; come separatore decimale deve essere
									utilizzato il punto.
								 */
								$GrpHdr->addChild('CtrlSum', number_format($importoTotale, 2, '.', ''));
								// 1:1 - mittente della richiesta di pagamento
								$InitgPty = $GrpHdr->addChild('InitgPty');
									// 0:1 - nome
									/* 1..70 */
									$InitgPty->addChild('Nm', $nomeCreditore);
									// 1:1 - identification
									$Id = $InitgPty->addChild('Id');
										// 1:1 - organisation identification: identificativo soggetto azienda
										$OrgId = $Id->addChild('OrgId');
											// 1:n - 
											/* First occurrence of this field must be used to indicate the CUC of the 
											   Initiating Party 
											   From the second occurrence, it is possible to indicate the Italian fiscal 
											   reference of the Initiating Party; in this case, the Issuer field must take the 
											   value “ADE” and the only acceptable formats for the Identification field are 
											   11 numeric characters or 13 alphanumeric characters of which the first two take 
											   the value "IT" (VAT numbers) or 16 alphanumeric characters (personal Tax Codes) */
											$Othr = $OrgId->addChild('Othr');
												// 1:1
												/* 1..35 - Name or number assigned by a party for the recognition of that party.
												   The first occurrence is represented by the Originator's CUC code. It must be 
												   one of those accepted by CBI.
												 
												   The first occurrence must contain a valid CUC code associated with the 
												   logical sender of the flow, indicated in the Service Header
												   If the Issuer field takes the value “ADE” it must contain either 11 numeric 
												   characters or 13 alphanumeric characters of which the first two take the 
												   value "IT" (VAT numbers) or 16 alphanumeric characters (personal Tax Codes).*/
												/*
												 * UBI: codice CUC - sostituisce il codice SIA
												 */
												$Othr->addChild('Id', $codiceCUC);
												// 1:1
												/* 1..35 - Entity that assigns the identifier
												   If the value of the Id is set to the CUC code, the Issuer is required and 
												   must take the value "CBI"
												   If set to "ADE", commencing from the second occurrence, a specific formal 
												  check (see previous field details) on the identifier input is activated.*/
												$Othr->addChild('Issr', 'CBI'); // è "CBI" anche per UBI
												
												
							
							// 1:n - Obligatory block containing collection information. Represents the "sub-group" of the message and may be repeated
							if( !empty($addebitiFRST) ) {
								$this->_addSottodistinta($CBISDDReqLogMsg, 
													'Sottodistinta 1',
													1,
													'FRST',
													$dataScadenza,
													$nomeCreditore,
													$ibanZolleStr,
													$ibanZolle,
													$creditorIdentifier,
													$addebitiFRST,
													$nomeDistinta,
													0);
							}
								
							if( !empty($addebitiRCUR) ) {						
								$this->_addSottodistinta($CBISDDReqLogMsg, 
													'Sottodistinta 2',
													2,
													'RCUR',
													$dataScadenza,
													$nomeCreditore,
													$ibanZolleStr,
													$ibanZolle,
													$creditorIdentifier,
													$addebitiRCUR,
													$nomeDistinta,
													sizeof($addebitiFRST));
							}
		
		$dom = new DOMDocument('1.0');
		$dom->preserveWhiteSpace = false;
		$dom->formatOutput = true;
		$dom->loadXML($distintaSEPA->asXML());
		return $dom->saveXML();
		
		//return $distintaSEPA->asXML();
	}
	
	
	function _addSottodistinta(&$CBISDDReqLogMsg, 
								$nomeSottodistinta,
								$numeroSottodistinta,
								$tipoIncasso, 
								$dataScadenza, 
								$nomeCreditore, 
								$ibanZolleStr,
								$ibanZolle,
								$creditorIdentifier,
								$addebiti,
								$nomeDistinta,
								$offset) {
	
							$PmtInf = $CBISDDReqLogMsg->addChild('PmtInf');
							$PmtInf->addAttribute("xmlns", "urn:CBI:xsd:CBISDDReqLogMsg.00.01.00");
							
								// 1:1 - payment information identification
								/* 1..35 - Unique identifier assigned by the Initiating Party to identify the collection 
								   information block within the logical message (group)
								   Rule for use: unique within the flow (may be sequential)*/
								$PmtInf->addChild('PmtInfId', $nomeSottodistinta); // stesso valore di UBI
								// 1:1 - metodo di pagamento
								/* 2 - Pre-authorised collection. Amounts and due dates of the transactions may differ.
								   Note: only takes the value "DD"*/
								$PmtInf->addChild('PmtMtd', 'DD');
								
								// 0:1 - accredito cumulativo
								/* Rule: if present and the value is set to "true", batch booking is required; if the value is set 
								   to "false", the individual booking is required.
								   In the absence of specific bilateral agreements on the use of this field, the input of a value 
								   by the customer does not oblige the creditor agent to apply the booking requested.*/
								//'BtchBookg' => null,
		
								// 1:1 - informazioni tipo di pagamento
								$PmtTpInf = $PmtInf->addChild('PmtTpInf');
									// 1:1 - livelli di servizio specifici: regole sull'elaborazione della transazione
									$SvcLvl = $PmtTpInf->addChild('SvcLvl');
										// 1:1 - codifica di servizio
										/* 4 - only takes the value "SEPA" */
										$SvcLvl->addChild('Cd', 'SEPA');
									// 1:1 - local instrument
									$LclInstrm = $PmtTpInf->addChild('LclInstrm');
										// 1:1 - codice strumento
										/* 1..35 - Takes just one of the following values:
											- CORE
											- COR1
											- B2B
											Must be unique within the same logical message (NARR: Local Instrument inconsistent)
											Must be consistent with the Service Name (if the Service Name is INC-SDDC, must be 
											CORE/COR1, if it is INC-SDDB, must be B2B) (NARR: Group type inconsistent with the 
											service requested) */
										$LclInstrm->addChild('Cd', 'CORE'); // da UBI

									// 1:1 - tipo sequenza di incasso
									/* 4 - If the "Amendment Indicator" is "TRUE" and the '‘Original Debtor Agent” is set to ‘SMNDA’, 
									    this field must always be set to "FRST".
								
										Collection frequency/sequence type. Takes just one of the following values:
										- FRST (first in a series of requests)
										- RCUR  (authorisation used for a series of collections at regular intervals)
										- FNAL  (last in a series of requests) 
										- OOFF (one-off, not repeated).
										Rule for use: if, in at least one of the n collection instructions, the "Amendment 
										Indicator" is set to "TRUE" and the '‘Original Debtor Agent” is set to "SMNDA", 
										the Sequence Type tag must be set to "FRST". (AT-21 Transaction/Sequence Type) */
									$PmtTpInf->addChild('SeqTp', $tipoIncasso);
									
									// 1:1 - finalità della transazione
									/*
									 * UBI NON LO USA, ma essendo cardinalità 1:1 lo inserisco ugualmente
									 */
									//$CtgyPurp = $PmtTpInf->addChild('CtgyPurp');
										// 1:1 - code
										// 4 - The list of codes is available at the address 
										// http://www.iso20022.org/external_code_list.page 
										// In case of SDD CORE collection for SEDA remuneration it must be "SEDA"
										//$CtgyPurp->addChild('Cd', 'GDSV'); // GDSV: addebiti diretti relativi a beni e servizi
								
								// 1:1 - data scadenza richiesta dal mittente
								/* ISO date: coincice con la data a cui il conto del debitore deve essere charged */
								$PmtInf->addChild('ReqdColltnDt', $dataScadenza); // in formato AAAA-mm-dd
								// 1:1 - creditore
								$Cdtr = $PmtInf->addChild('Cdtr');
									// 1:1 - nome
									// 1..70
									$Cdtr->addChild('Nm', $nomeCreditore);
									// 0:1 - postal address
									$PstlAdr = $Cdtr->addChild('PstlAdr');
										// 0-1: address type
										/* 4 - Identifies the nature of the address (e.g. head office)
										- "ADDR"Specified address is the complete postal address.
										- "PBOX" Specified address is a PO Box.
										- "HOME" Specified address is that of residence.
										- "BIZZ" Specified address is that of domicile.
										- "MLTO" Specified address is the mailing address for correspondence.
										- "DLVY" Specified address is that for deliveries. */
										$PstlAdr->addChild('AdrTp', 'ADDR');
						
										// 0:1 - divisione
										/* 1..70 */
										//'Dept' => '',
										
										// 0:1 - sotto dipartimento
										/* 1..70 */
										//'SubDept' => '',
									
										// 0:1 - street name
										/* 1..70 */
										$PstlAdr->addChild('StrtNm', 'via Giuseppe Belluzzo');
										// 0:1 - building number
										/* 1..16 */	
										$PstlAdr->addChild('BldgNb', '55');
										// 0:1 - postal code
										/* 1..16 */ 
										$PstlAdr->addChild('PstCd', '00149');
										// 0:1 - town name
										/* 1..35 */
										$PstlAdr->addChild('TwnNm', 'Roma');
										// 0:1 - provincia
										/* 1..35 */
										$PstlAdr->addChild('CtrySubDvsn', 'RM');
										// 0:1 - country code
										/* 2 */
										$PstlAdr->addChild('Ctry', 'IT');
										
										// 0:2 - address line
										/* 1-.70 */
										//'AdrLine',
									
									// 0:1 - identificativo creditore
									/* Unique identifier of an organisation or person.
										Rule: if used, the information must be input in either "Organisation Identification" or 
										"Private Identification". Not in both at the same time */
									//'Id' => array(
										
										// 1:1 OR - organisation identification
									//	'OrgId' => array(
											
											// si usa uno dei campi seguenti - solo uno
											
											// 1:1 XOR
											/* Code assigned to non-financial institutions by the Registration Authority in 
											 * accordance with ISO 9362 */
									//		'BICOrBEI' => '',
											
											// 1:1 XOR
									//		'Othr' => array(
											
												// 1:1
												/* 1..35 - Name or number assigned by a party for the recognition of that party 
												 * (e.g. tax code, VAT number of CUC) */
									//			'Id' => '',
												
												// 0:1 - issuer
												/* 1..35 - Entity that issues the identifier.
												   Note: if the CUC code or the SIA code is set, use of the values "CBI" and 
												   "SIA" respectively is recommended. If Id is set to the Italian Fiscal 
												   Identifier, the Issuer must take the value "ADE". */
									//			'Issr' => '',
									//		)
											
									//	),
										
										// 1:1 OR - private identification
									//	'PrvtId' => array(
											
											// 1:1
									//		'Othr' => array(
												
												// 1:1
												/* 1..35 - Name or number assigned by a party for the recognition of that 
												 * party (e.g. tax code, VAT number of CUC) */
									//			'Id' => '',
												
												// 0:1 - issuer
												/* 1..35 - Entity that issues the identifier.
												   If Id is set to the Italian Fiscal Identifier, the Issuer must take the value 
												   "ADE". */
									//			'Issr' => '',
									//		)
											
									//	)
										
									//)
								
								// 1:1 - conto del creditore
								$CdtrAcct = $PmtInf->addChild('CdtrAcct');
									// 1:1 - id del conto
									$Id = $CdtrAcct->addChild('Id');
										// 1:1
										/* International Bank Account Number (ISO 13616)
										   Format: comprises Country code, check digit, BBAN*/
										/*
										 * UBI: IBAN del conto di accredito 
										 */
										$Id->addChild('IBAN', $ibanZolleStr); 
									
								// 1:1 - Banca Passiva titolare del c/c di accredito
								$CdtrAgt = $PmtInf->addChild('CdtrAgt');									
									// 1:1 - identificativi istituto finanziario
									$FinInstnId = $CdtrAgt->addChild('FinInstnId');
										// 1:1 - ID Istituto nel Sistema di Clearing: ABI Code of the Bank
										$ClrSysMmbId = $FinInstnId->addChild('ClrSysMmbId');
											// 1:1 - member id
											/* 1..35 - Proprietary identifier of a clearing system (ABI code) Must be a valid 
											 * ABI code, in the form of exactly five numeric characters. 
											   Rules for use: must be unique within the entire group/logical message */ 
											$ClrSysMmbId->addChild('MmbId', $ibanZolle['abi']);
								
								// 0:1 - creditore effettivo
								//'UltmtCdtr' => '', /* campo composto, stessi campi di <Cdtr>*/
								
								// 0:1 - tipologia commissioni
								/* 4 - Specifies which party will bear the charges associated with the transaction. 
									Use recommended at payment information level (creditor account block)
									Note: only takes the value "SLEV" */
								//'ChrgBr' => '',
								
								// 0:1 - Account to which the expenses associated with the requested transaction are charged
								//'ChrgsAcct' => array(
									
									// 1:1 - identificativo del conto
									//'Id' => array(
									
										// 1:1
										/* Rules for use: if present, must be different to that of the Creditor Account, 
										 * but relate to the same Creditor Agent (same ABI included in the Creditor Account 
										 * for the transaction) */
									//	'IBAN' => '',
								//	)
								//),
								
								// 1:1 - identificativo schema creditore
								/* Creditor Identification
								   Note: all transactions in the same sub-group are recommended to have the same Creditor 
								   Scheme Identification */
								$CdtrSchmeId = $PmtInf->addChild('CdtrSchmeId');
									// 0:1 - name
									// 1..70
									$CdtrSchmeId->addChild('Nm', $nomeCreditore);
					
									// 0:1 - postal address
									//'PstlAdr' => '', /* struttura come sopra */
									
									// 1:1 - identificativo
									$Id = $CdtrSchmeId->addChild('Id');
										// 1:1 - identificativo soggetto privato
										$PrvtId = $Id->addChild('PrvtId');
											// 1:1 - altro identificativo
											$Othr = $PrvtId->addChild('Othr');
												// 1:1
												/* 1..35 - Identification assigned to a party otherwise without any specific 
												 * identification.
	
												It must be compliant to the "EPC262-08 Creditor Identifier Overview" document

												Format rule for users (see SDD Guidelines):
												• Positions 1 and 2:  ISO country code
												• Positions 3 and 4: check digits
												• Positions from 5 to 7: Creditor Business Code. If unused, set to "ZZZ". 
												Cannot contain spaces.
												• Positions 8 to 23: National identification (Fiscal identification). Must 
												contain five filler zeros starting from the left if the national identification 
												code is a VAT number or a provisional tax code (See ABI technical series circular 
												no. 42 dated 11 August 2009). */
												$Othr->addChild('Id', $creditorIdentifier);
												
												// 0:1 - nome schema
												//'SchmeNm' => array(
												
													// 1:1 XOR
													/* 4 */
												//	'Cd' => '',
													
													// 1:1 XOR
													/* Rule for use: must take the value ‘SEPA’ */
												//	'Prtry' => '',
													
													// 0:1
													/* 1..35 - Entity that issues the identifier.
													   If Id is set to the Fiscal Identifier and the party is resident in Italy, 
													   the Issuer must take the value "ADE". */
												//	'Issr' => '',
												//)
									
									// 0:1 - country code: paese di residenza
									/* 2 */
									//'CtryOfRes' => '',
								
								// 1:n - Informazioni relative alle singole transazioni (disposizioni)
								$i=$offset;
								foreach($addebiti as $addebito) {
									
									if( empty($addebito['AutorizzazioneRid']['id']) ) continue;
									
									$i++;
									
									$DrctDbtTxInf = $PmtInf->addChild('DrctDbtTxInf');
									$DrctDbtTxInf->addAttribute("xmlns", "urn:CBI:xsd:CBISDDReqLogMsg.00.01.00");
									
										// 1:1 - id transazione
										$PmtId = $DrctDbtTxInf->addChild('PmtId');					
											// 1:1
											/* 1..35 - Unique identifier assigned to the instruction by the Initiating Party in 
											   relation to its Bank.
											   May be a sequential number or another identifier used internally by the Initiating 
											   Party.
											   Used for reconciliation purposes */
											$PmtId->addChild('InstrId', $i);
											// 1:1
											/* 1..35 - Must be unique within the group/logical message
													
											   Rule: URI identifier assigned by the Initiating Party, which identifies the 
											   individual collection request throughout the entire chain ending with the Debtor. 
											   Unique within the group/logical message.
											   (AT-10 Creditor’s reference of the direct debit Collection)
											   Note: See document STIN-MO-001 for the related transmission criteria */
											$PmtId->addChild('EndToEndId', $nomeDistinta.'-'.$numeroSottodistinta.'-'.$i); // da esempi di UBI

																
										// 0:1 - informazioni sul tipo di transazione
										//'PmtTpInf' => array(
																	
											// 1:1 - finalità della transazione
											/* Identifies the high level reasons for the request, based on a predetermined set of 
											categories */
										//	'CtgyPurp' => '',
																
												// 1:1
												/* 4 - The list of codes is available at the address http://www.iso20022.org/external_code_list.page */
										//		'Cd' => ''
										//),
																
										// 1:1 - instructed amount: divisa e importo
										/* Value set solely to EUR currency (AM03) and the amount must lie between 0.01 and 
										   999999999.99 
											(maximum of 2 decimal places) (AM09)
																
											Amount of the individual transaction
											Rules for use: 
											(1) only EUR allowed
											(2) amount must lie between 0.01 and 999999999.99
											Format: A maximum of 2 decimal places is allowed, but they may be absent
											(AT-06 Amount of the Collection in Euro) */
										/*
										 * UBI: attribute Ccy=EUR
										 */
										$InstdAmt = $DrctDbtTxInf->addChild('InstdAmt', number_format($addebito['Addebito']['importo'], 2, '.', '') );
										$InstdAmt->addAttribute("Ccy", "EUR");
																
										// 0:1 - tipologia commissioni
										/* 4 - Specifies which party will bear the charges associated with the transaction.  
											Note: only takes the value "SLEV" */
										//'ChrgBr' => '',
																
										// 1:1 - informazioni sul mandato
										$DrctDbtTx = $DrctDbtTxInf->addChild('DrctDbtTx');			
											// 1:1 - informazioni specifiche relative al mandato
											$MndtRltdInf = $DrctDbtTx->addChild('MndtRltdInf');
																	
												// 1:1 - identificativo mandato
												/* Identifier assigned to the mandate signed by the debtor.
													Note: corresponds to AT-01 Unique Mandate Reference in the Rulebook.
													Format: the field is not case sensitive. For example, the identifiers 123AAa45678, 
													123aaA45678, 123aaa45678 and 123AAA45678 must be considered identical.
													Rule: cannot just contain spaces and must only contain characters from the basic 
													Latin set, as specified in the General Part STPG-MO-001 */
												/*
												 * UBI: identificativo unico assegnato al mandato sottoscritto dal debitore
												 */
												/**
												 * Da contatto con BCC il codice deve avere il seguente formato
												 *  IDENTIFICATIVO DEL MANDATO costituito da tre sottocampi :
													Codice SIA : AWPQS
													Tipo codice : 4
													Codice cliente debitore : 4508 (deve essere lo stesso usato nei rid
													quindi è l'id cliente)
													* 
													* Per intero la stringa deve essere : AWPQS44508
												 */
												$MndtId = 'AWPQS4'.$addebito['Addebito']['cliente_id'];
												// 2016-03-01: da successivi contatti con la banca risulta che i sepa
												// sono passati con la precedente implementazione. La banca richiede di
												// mantenere la struttura del codice utilizzata:
												$MndtId = $addebito['Addebito']['rid_id'];
												$MndtRltdInf->addChild('MndtId', $MndtId);
																		
												// 1:1 - data di sottoscrizione: formato YYYY-mm-dd
												/* ISO date Date on which the debtor signed the mandate
													(AT-25 Date of Signing of the Mandate) */
												$MndtRltdInf->addChild('DtOfSgntr', empty($addebito['AutorizzazioneRid']['rid_activated']) ? date('Y-m-d') : date('Y-m-d', strtotime($addebito['AutorizzazioneRid']['rid_activated'])));
																		
												// 0:1 - Indicatore di rettifica/variazione mandato
												/* Corresponds to Rulebook element AT-24. Takes the following values:
													TRUE: modifications made
													FALSE: no modifications with respect to the previous mandate */
												/*
												 * Cardinalità 0:1 ma UBI lo usa con la valorizzazione specificata
												 */
												$MndtRltdInf->addChild('AmdmntInd', 'false');
																		
												// 0:1 - dettagli relativi alle modifiche
												/* Becomes obligatory if the <AmdmntInd> tag is set to TRUE					
												Details of the individual mandate captions that have been modified since the previous
												collection.
												Rule for use: obligatory if Amendment Indicator is set to "TRUE". 
												Note: The coded reason indicated in the Rulebook is specified using one of the 
												sub-elements.
												(AT-24 Reason for Amendment of the Mandate) */
												//'AmdmntInfDtls' => array(
																		
													// 0:1 - identificativo mandato originario
													/* 1..35 - Identifier for the mandate that has been modified.
														Rule: obligatory if the ‘Mandate Identification’ changes, otherwise not used.
														(AT-19 Unique Mandate Reference as given by the Original Creditor who issued the Mandate) */
													//'OrgnlMndtId' => '',
																			
													// 0:1 - identificativo schema creditore originario
													//'OrgnlCdtrSchmeId' => '', /* non lo uso mai - vedi STIN-ST-001-CBISSD-ReqMsg.xls*/
																
													// 0:1 - banca di accredito originaria
													//'OrgnlCdtrAgt' => '', /* non lo uso mai - vedi STIN-ST-001-CBISSD-ReqMsg.xls*/
																		
													// 0:1 - conto del debito originario
													//'OrgnlDbtrAcctt' => '', /* non lo uso mai - vedi STIN-ST-001-CBISSD-ReqMsg.xls*/
																			
													// 0:1 - banca del debitore originaria
													//'OrgnlDbtrAgt' => '', /* non lo uso mai - vedi STIN-ST-001-CBISSD-ReqMsg.xls*/
																	
													// 0:1 - data ultimo incasso originaria
													//'OrgnlFnlColltnDt' => '', /* non lo uso mai - vedi STIN-ST-001-CBISSD-ReqMsg.xls*/
																		
													// 0:1 - frequenza originaria
													/* 4 - Original collection frequency has been modified
														Takes the following values:
														- ADHO Ad hoc:  the event occurs upon request or when necessary
														- DAIL Daily: the event occurs every day
														- INDA IntraDay: the event occurs several times each day
														- MIAN SemiAnnual:  the event occurs every six months or twice each year
														- MNTH Monthly: the event occurs monthly or once each month
														- QURT Quarterly: the event occurs every three months or four times each year
														- WEEK Weekly: the event occurs once each week
														- YEAR Annual:  the event occurs once each year */
													//'OrgnlFrqcy' => '',
																									
																		
												// 0:1 - electronic signature
												/* 1..1025 - Allocation of the electronic signature, usable by reference to the mandate given to 
													the Debtor Bank
													Rules for use:  If the direct debit is based on an electronic mandare, this element 
													must contain the mandate reference contained in the "Mandate Acceptance Report" 
													(Message Id of the mandate validation message). Not allowed if the direct debit is 
													based on a hard-copy mandate. */
												//'ElctrncSgntr' => '',
																		
												// 0:1 - data della prima richiesta di incasso
												//'FrstColltnDt' => '',
																		
												// 0:1 - data dell'ultima richiesta di incasso
												//'FnlColltnDt' => '',
																	
												// 0:1 - frequenza degli incassi
												/* 4 - Frequency with which collections must be created and processed.
													Takes the following values:
													- ADHO Ad hoc:  the event occurs upon request or when necessary
													- DAIL Daily: the event occurs every day
													- INDA IntraDay: the event occurs several times each day
													- MIAN SemiAnnual:  the event occurs every six months or twice each year
													- MNTH Monthly: the event occurs monthly or once each month
													- QURT Quarterly: the event occurs every three months or four times each year
													- WEEK Weekly: the event occurs once each week
													- YEAR Annual:  the event occurs once each year */
												//'Frqcy' => '',
																	
											// 0:1 - identificativo pre-notifica
											/* 1..35 - Unique identifier of the pre-notification sent by the creditor to the debtor ahead of 
												the collection request, and sent separately from that request. */
											//'PreNtfctnId' => '',
																	
											// 0:1 - data pre-notifica
											/* iso date: Date on which the creditor notifies the amount and collection date to the debtor (communication required under the current Rulebook) */
											//'PreNtfctnDt' => '',
																	
										// 0:1 - banca del debitore
										//'DbtrAgt' => array(
																	
											// 1:1 - identificativo istituto finanziario
											//'FinInstnId' => array(
																
												// 1:1 - BIC identifier
												/* 8..11 - Code assigned to financial institutions by the Registration Authority in accordance 
													with ISO 9362.  */
												//'BIC' => '',
																
										// 1:1 - titolare c/c addebito
										$Dbtr = $DrctDbtTxInf->addChild('Dbtr');
											$Dbtr->addChild('Nm', $addebito['Cliente']['displayName']);	
															
										// 1:1 - coordinate bancarie di addebito
										$DbtrAcct = $DrctDbtTxInf->addChild('DbtrAcct');
											// 1:1 - id conto
											$Id = $DbtrAcct->addChild('Id');
																	
												// 1:1
												/* International Bank Account Number (ISO 13616)
													Format: comprises Country code, check digit, BBAN
													Rule: subject to completeness check using the check digit */
												$ar = $addebito['AutorizzazioneRid'];
												$Id->addChild('IBAN', strtoupper('IT'.
																		$ar['check_digit'].
																		$ar['cin'].
																		$ar['abi'].
																		$ar['cab'].
																		$ar['conto_corrente']));
																
										// 0:1 - debitore effettivo
										//'UltmtDbtr' => '', // COME <Dbtr>
																
										// 0:1 - istruzione per banca titolare del c/c di accredito
										/* 1..140 - Further information about the processing of the payment agreed directly between the bank 
											and the customer */
										//'InstrForCdtrAgt' => '',
																
										// 0:1 - causale della transazione
										/*
										 * Cardinalità 0:1 ma usata da UBI
										 */
										$Purp = $DrctDbtTxInf->addChild('Purp');
																
											// 1:1 - causale in forma codificata
											/* 1..35 - Rule for use: subject to validity checks based on external purpose codes (for the 
												related values, see the ISO ExternalPurposeCode table published on the ISO20022 website 
												at http://www.iso20022.org/external_code_list.page) */
											/*
											 * UBI: valore usato "GDSV" (addebiti diretti relativi a beni e servizi)
											 */
											$Purp->addChild('Cd', 'GDSV');
																
										// 0:3 - comunicazioni valutarie
										//'RgltryRptg' => array(
																	
											// 1:1 - indicatore di soggetto cui è a carico l'informazione
											/* 4 - Only takes the value
												"CRED" Information provided solely by the creditor */
											//'DbtCdtRptgInd' => '',
																	
											// 0:1 - dettagli di comunicazioni valutarie
											/*  struttura su doc tecnica */
											//'Dtls' => '',
																
										// 0:1 - informazioni di riconciliazione
										/*
										 * Informazioni sul pagamento comunicate dal creditore al
											debitore (Remittance Information); ad esempio “Quota canone
											mensile”.
										 */
										$RmtInf = $DrctDbtTxInf->addChild('RmtInf');
											$RmtInf->addChild('Ustrd', 'Saldo Zolle al '.$dataScadenza);
								
								} // end loop addebiti
		
	}
	
}
