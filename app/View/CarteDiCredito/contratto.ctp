<?php
	$isRiattivazioneSuAdyen = !empty($carta['signed']) && empty($carta['adyen_psp_reference']);
	
	// 2019-01-04: su richiesta di zolle tolgo il titolo
	//$this->assign('title', $isRiattivazioneSuAdyen ? 'RIATTIVAZIONE CARTA DI CREDITO': 'CONTRATTO CARTA DI CREDITO');
	$this->assign('title', ' '); // importante: devo mettere almeno uno spazio altrimenti scrive il nome del controller!
	
?>

<?php echo $this->Html->script('https://cdnjs.cloudflare.com/ajax/libs/jquery.payment/1.2.3/jquery.payment.min.js',array('inline' => false));?>
<?php echo $this->Html->css('contratto.carta');?>

	<div class="row">
		<div class="col-md-12">
			<h2 class="titolo-profilo">
				<?php if($isRiattivazioneSuAdyen):?>
					<?php echo ucfirst(strtolower(__("Riattivazione dell'autorizzazione all'addebito automatico su carta di credito")));?>
				<?php else:?>
					<?php echo ucfirst(strtolower(__("Contratto per l'autorizzazione all'addebito automatico su carta di credito")));?>
				<?php endif;?>
			</h2>
		</div>
	</div>

<div class="row">
		
	<div class="col-md-6">
		<h4 class="text-orange">Azienda creditrice</h4>
		<div>
			Le Zolle S.r.l. unipersonale
			<br>
			via Giuseppe Belluzzo 55 - 00149 Roma
			<br>
			P.Iva 09848941002
		</div>
	</div>	
	
	<div class="col-md-6">
		<h4 class="text-orange">Intestatario del contratto</h4>
		
		<div class="row">
			<div class="col-xs-4">
				Codice cliente
			</div>
			<div class="col-xs-8">
				<b><?php echo $cliente['id'];?></b>
			</div>
		</div>
		
		<div class="row">
			<div class="col-xs-4">
				Cognome e nome
			</div>
			<div class="col-xs-8">
				<b><?php echo $cliente['displayName'];?></b>
			</div>
		</div>
		
	</div>	
		
</div>

<br/>
<br/>
<br/>

<div class="row">

	<table class="table">
	
		<tr>
			<th><small>Accetto</small></th>
			<th></th>
		</tr>
		
		<tr>
			<td class="text-center">
				<input type="checkbox" class="form-control accettazione" />
			</td>
			<td><small>
				Il cliente, sottoscrivendo il presente contratto di attivazione del SERVIZIO PAGAMENTI RICORRENTI 
				autorizza Le Zolle s.r.l. ad addebitare sulla propria carta di credito le somme dovute per gli acquisti effettuati, 
				pertanto l’importo addebitato può variare di mese in mese
			</small></td>
		</tr>
		
		<tr>
			<td class="text-center">
				<input type="checkbox" class="form-control accettazione" />
			</td>
			<td><small>
				Zolle invierà le  future comunicazioni all'indirizzo Email del cliente che viene confermato portando a 
				termine l'attivazione del SERVIZIO PAGAMENTI RICORRENTI
			</small></td>
		</tr>
		
		<tr>
			<td class="text-center">
				<input type="checkbox" class="form-control accettazione" />
			</td>
			<td><small>
				Ogni volta che il cliente riceve un ordine, entro le ore 24:00 della medesima giornata riceve anche la fattura ad esso riferita con il relativo importo. A fine mese il cliente riceve via email il riepilogo di tutte le fatture relative a quanto acquistato.
			</small></td>
		</tr>
		
		<tr>
			<td class="text-center">
				<input type="checkbox" class="form-control accettazione" />
			</td>
			<td><small>
				L'addebito delle somme dovute avviene su base mensile tra il giorno 2 e il giorno 10 di ogni mese. Ogni addebito si riferisce quindi al totale delle fatture ricevute durante il mese precedente. 
			</small></td>
		</tr>
		
		<tr>
			<td class="text-center">
				<input type="checkbox" class="form-control accettazione" />
			</td>
			<td><small>
				Le somme non addebitate in un determinato mese di esazione vengono addebitate in modo automatico nel mese successivo, unitamente alla somma dovuta per tale mese.  
			</small></td>
		</tr>
		
		<tr>
			<td class="text-center">
				<input type="checkbox" class="form-control accettazione" />
			</td>
			<td><small>
				L'attivazione del SERVIZIO PAGAMENTI RICORRENTI comporta il pagamento di 1 euro. Per il sistema SERVIZIO PAGAMENTI RICORRENTI tale pagamento conferma l'accettazione delle condizioni contenute nel presente documento. La somma di 1 euro sarà scalata in automatico da Le Zolle s.r.l. dal primo importo dovuto.   
			</small></td>
		</tr>
		
		<tr>
			<td class="text-center">
				<input type="checkbox" class="form-control accettazione" />
			</td>
			<td><small>
				Il SERVIZIO PAGAMENTI RICORRENTI è attivo solo dopo il suddetto pagamento di 1 euro.    
			</small></td>
		</tr>
		
		<tr>
			<td class="text-center">
				<input type="checkbox" class="form-control accettazione" />
			</td>
			<td><small>
				Il cliente ha facoltà di recedere dal presente contratto in ogni momento senza penalità e senza spese. 
			</small></td>
		</tr>
		
		<tr>
			<td class="text-center">
				<input type="checkbox" class="form-control accettazione" />
			</td>
			<td><small>
				Qualora il cliente desiderasse cambiare modalità di pagamento, anche solo per un periodo, è sufficiente che ne dia comunicazione scritta a Le Zolle s.r.l. tramite una mail a <a href="mailto:amministrazione@zolle.it">amministrazione@zolle.it</a> . Tale mail deve pervenire entro la fine del mese in cui ha ricevuto i prodotti per i quali desidera cambiare modalità di pagamento.
			</small></td>
		</tr>
	
	</table>

</div>

<div class="row">
	<div class="col-md-12 text-center">
		<a id="conferma-contratto" href="#" class="btn bkg-orange white btn-block" style="white-space: normal;">Clicca qui per accettare il contratto e procedere all'inserimento dei dati della tua carta di credito</a>
	</div>
</div>

<?php $this->Html->scriptStart(array('inline' => false)); ?>
$(function(){
	$('#conferma-contratto').click(function(e){
		e.preventDefault();
		
		var nonCheckedNum = 0;
		$('.accettazione').each(function(){
			if( !$(this).is(':checked') ) nonCheckedNum++;
		});
		
		if( nonCheckedNum > 0 ) alert('Per procedere è necessario accettare tutte le clausole del contratto');
		else {
			$('#pagamento').show();
			$(this).hide();
		}
	});
	
	/* Fancy restrictive input formatting via jQuery.payment library*/
	// sarebbero interessanti ma lo script di adyen ne impedisce il funzionamento
//$('input[name=cardNumber]').payment('formatCardNumber');
//$('input[name=cardCVC]').payment('formatCardCVC');
//$('input[name=cardExpiry').payment('formatCardExpiry');
	
});
<?php $this->Html->scriptEnd();?>

<br/>
<div class="" id="pagamento" style="display:none">
	
	
	<script type="text/javascript" src="https://live.adyen.com/hpp/cse/js/1114697879461239.shtml"></script>
	<!-- 

	You must encrypt card input fields by annotating them with the data-encrypted-name attribute. Do not use the name attribute.

	This ensures that the call does not send unencrypted card data to your server.

	-->
	<?php
		// adyen richiede un timestamp hidden generato lato server in formato ISO8601 (esempio: 2017-07-17T13:42:40.428+01:00)
		$formattedTimestamp = date('Y-m-d\TH:i:s\.000P');
	?>
	<!-- https://bootsnipp.com/snippets/featured/credit-card-payment-with-stripe -->	
	
		<div class="row">
			<!-- You can make it whatever width you want. I'm making it full width
				 on <= small devices and 4/12 page width on >= medium devices -->
				 
			<div class="col-xs-12 col-md-6 col-md-offset-3">
			
			
				<!-- CREDIT CARD FORM STARTS HERE -->
				<div class="panel panel-default credit-card-box">
					<div class="panel-heading" >
						<div class="row">
							<div class="col-md-6 col-xs-12" style="line-height:40px; vertical-align:middle">
								<h4>Pagamenti ricorrenti</h4>
							</div>
							<div class="col-md-6 col-xs-12 text-right" style="line-height:40px">
								<?php echo $this->Html->image('visa-mastercard.png', array('id' => 'cc-logos'));?>
							</div>
						</div>                    
					</div>
					<div class="panel-body">
						<?php echo $this->Form->create(null, array('id' => 'adyen-encrypted-form', 'url' => array('controller' => 'carte_di_credito', 'action' => 'authorise', $id_contratto)));?>
							<input type="hidden" value="<?php echo $formattedTimestamp;?>" data-encrypted-name="generationtime"/>
							<div class="row">
								<div class="col-xs-12">
									<div class="form-group">
										<label for="cardNumber">INTESTATARIO CARTA</label>
										<div class="input-group">
											<input 
												type="text"
												class="form-control"
												name="titolare"
												placeholder="Cognome e nome"
												required
												data-encrypted-name="holderName" 
											/>
											<span class="input-group-addon"><i class="fa fa-user"></i></span>
										</div>
									</div>                            
								</div>
							</div>
							<div class="row">
								<div class="col-xs-12">
									<div class="form-group">
										<label for="cardNumber">NUMERO DI CARTA</label>
										<div class="input-group">
											<input 
												type="tel"
												class="form-control"
												name="cardNumber"
												placeholder="es. 1111222233334444"
												autocomplete="cc-number"
												required
												data-encrypted-name="number"
											/>
											<span class="input-group-addon"><i class="fa fa-credit-card"></i></span>
										</div>
									</div>                            
								</div>
							</div>
							<div class="row">
								<div class="col-xs-7 col-md-7">
									<div class="form-group">
										<label for="cardExpiry"><span class="">DATA DI SCADENZA</span></label>
										<div class="row">
											<div class="col-md-5 col-xs-5">
												<select data-encrypted-name="expiryMonth" required class="form-control">
													<option value="" selected>MM</option>
													<option value="01">01</option>
													<option value="02">02</option>
													<option value="03">03</option>
													<option value="04">04</option>
													<option value="05">05</option>
													<option value="06">06</option>
													<option value="07">07</option>
													<option value="08">08</option>
													<option value="09">09</option>
													<option value="10">10</option>
													<option value="11">11</option>
													<option value="12">12</option>
												</select>
											</div>
											<div class="col-md-7 col-xs-7">
												<select data-encrypted-name="expiryYear" required class="form-control">
													<option value="" selected>AAAA</option>
													<?php for($year=date('Y');$year<date('Y')+20;$year++):?>
													<option value="<?php echo $year;?>"><?php echo $year;?></option>
													<?php endfor;?>
												</select>
											</div>
										</div>
									</div>
								</div>
								<div class="col-xs-5 col-md-5 pull-right">
									<div class="form-group">
										<label for="cardCVC">CV CODE <button type="button" class="btn btn-default btn-xs" data-toggle="modal" data-target="#cvcModal"><i class="fa fa-question-circle"></i></button></label>
										<input 
											type="tel" 
											class="form-control"
											name="cardCVC"
											placeholder="CVC"
											autocomplete="cc-csc"
											required
											data-encrypted-name="cvc"
										/>
									</div>
								</div>
							</div>
							<div class="row" style="margin-bottom:10px; font-weight:bold">
								<div class="col-xs-12">
									<ul class="nav nav-pills nav-stacked">
										<li class="active"><a href="#"><span class="badge pull-right"><i class="fa fa-eur"></i> 1,00</span> Importo da pagare *</a>
										</li>
									</ul>
								</div>
							</div>
							<div class="row">
								<div class="col-xs-12">
									<button disabled class="subscribe btn btn-success btn-lg btn-block" type="submit" style="white-space:normal">Attiva il servizio pagamenti ricorrenti</button>
								</div>
							</div>
							<div class="alert alert-warning" style="margin-top:10px"><small>* l'importo ti verrà rimborsato con la prima spesa</small></div>
						</form>
					</div>
				</div>            
				<!-- CREDIT CARD FORM ENDS HERE -->   
			</div> <!-- col-12 -->           
		</div> <!-- row -->
	
	
	<script>
		// The form element to encrypt.
		var form = document.getElementById('adyen-encrypted-form');
		// See https://github.com/Adyen/CSE-JS/blob/master/Options.md for details on the options to use.
		var options = {};
		// Bind encryption options to the form.
		adyen.createEncryptedForm(form, options);
	</script>

	
	
</div>


<div class="modal fade" id="cvcModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        <h4 class="modal-title" id="myModalLabel">Codice CVC/CVV/CV2/CID</h4>
      </div>
      <div class="modal-body">
		  <p><u>Si tratta del <b>codice numerico</b> a 3 o 4 cifre stampato sul retro della carta di credito.</u></p>
		  <p><u>Questo codice di sicurezza è utilizzato per verificare l'identità dell'intestatario della carta</u></p>
		  <p><?php echo $this->Html->image('cvc.png', array('class' => 'img-responsive'));?><small>(Fonte immagine: www.creditcards.com)</small></p>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>


