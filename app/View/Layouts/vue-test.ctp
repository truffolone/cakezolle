<!DOCTYPE html>
<html>
<head>
  <link href="https://fonts.googleapis.com/css?family=Roboto:100,300,400,500,700,900" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/@mdi/font@4.x/css/materialdesignicons.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/vuetify@2.x/dist/vuetify.min.css" rel="stylesheet">
  <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no, minimal-ui">
  <style>
    body {
        font-family: 'Roboto', sans-serif;
    }
  </style>
</head>
<body>
  <div id="app" class="v-application">
    <v-app-bar
      app
      color="light"
    >
      <div class="d-flex align-center">
        <button @click.stop="drawer = !drawer">
          <v-img
          alt="Vuetify Logo"
          class="shrink mr-2"
          contain
          src="https://zolle.it/wp-content/uploads/2019/03/zolle-logo_magenta.png"
          transition="scale-transition"
          width="70"
        />
        </button>

      </div>

      <v-spacer></v-spacer>

      <!--<v-menu offset-y>
      <template v-slot:activator="{ on }">
        <v-btn
          color="primary"
          dark
          v-on="on"
        >
          Dropdown
        </v-btn>
      </template>
      <v-list>
        <v-list-item>
          <v-list-item-title>Profilo</v-list-item-title>
        </v-list-item>
      </v-list>
    </v-menu>-->

      <template v-slot:extension>
      <v-tabs :centered="true"  v-model="tab" show-arrows fixed-tabs>
        <v-tab key="1" color="pink">Prodotti per questa settimana</v-tab>
        <v-tab key="2">Prodotti per la prossima settimana</v-tab>
        <v-tab key="3">Il tuo ordine</v-tab>
        <v-tab key="4">Il tuo profilo</v-tab>
    </v-tabs>
      </template>

    </v-app-bar>

    <v-content :data-app="true"> <!-- l'attributo data-app e' necessario per i modali -->
      
    <v-snackbar
      v-model="newFuncSnackbar"
      :vertical="true"
      :timeout="0"
      color="dark"
    >
      Da oggi puoi modificare la tua spesa autonomamente, escludendo o sostituendo i prodotti che non gradisci in ogni consegna
      <v-btn
        color="orange"
        text
        @click="newFuncSnackbar = false"
      >
        Non mostrare piu'
      </v-btn>
     </v-snackbar>
      
      
    <v-container>
      <v-row>
        <v-col cols="12">

	<template v-for="consegna in consegne">
    <v-card
    class="mx-auto"
  >
    <v-card-text>
      <div><span class="title orange--text">{{consegna.header}}</span></div>
      <v-divider></v-divider>
      <v-list> 
      <v-list-item>
        <v-list-item-content>
          <v-list-item-title class="subtitle">
			  <template v-for="zolla in consegna.zolle">
				<v-chip color="orange">{{zolla.qty}}</v-chip> {{zolla.name}}&nbsp;&nbsp;
			  </template>
        </v-list-item-content>
        <v-list-item-icon>
          <v-btn
            text
            color="deep-purple accent-4"
            @click.stop="consegna.expanded = true"
            v-if="!consegna.expanded"
          >
            Modifica
          </v-btn>
          <v-btn
            text
            color="red accent-4"
            @click.stop="consegna.expanded = false"
            v-if="consegna.expanded"
          >
            Chiudi
          </v-btn>
        </v-list-item-icon>
      </v-list-item>
      <v-divider></v-divider>

	<template v-if="consegna.expanded">
      <v-list-item v-for="prodotto_idx in consegna.spesa" :key="prodotto_idx">
        <v-list-item-avatar style="margin-right:8px">
          <v-img :src="prodotti[prodotto_idx].img"></v-img>
        </v-list-item-avatar>
        <v-list-item-content>
          <v-list-item-title>{{prodotti[prodotto_idx].name}}</v-list-item-title>
          {{prodotti[prodotto_idx].subtitle}}
        </v-list-item-content>
        <v-list-item-icon>
          <v-btn
            text
            color="deep-purple accent-4"
            @click.stop="gestisci(prodotto_idx)"
          >
            Escludi
          </v-btn>
        </v-list-item-icon>
      </v-list-item>
	
      <v-list-item style="background:#ddd" v-if="consegna.esclusioni.length">
        Prodotti che non gradisci in questa consegna
      </v-list-item>
      <v-list-item v-for="prodotto_idx in consegna.esclusioni" :key="prodotto_idx">
        <v-list-item-avatar style="margin-right:8px">
          <v-img :src="prodotti[prodotto_idx].img"></v-img>
        </v-list-item-avatar>
        <v-list-item-content>
          <v-list-item-title>{{prodotti[prodotto_idx].name}}</v-list-item-title>
          {{prodotti[prodotto_idx].subtitle}}
          <div class="red--text font-weight-bold">Rimosso</div>
        </v-list-item-content>
        <v-list-item-icon>
          <v-btn
            text
            color="deep-purple accent-4"
            @click.stop="gestisci(prodotto_idx)"
          >
            Modifica
          </v-btn>
        </v-list-item-icon>
      </v-list-item>

      <v-list-item style="background:#ddd" v-if="consegna.sostituzioni.length">
        Prodotti che hai sostituito in questa consegna
      </v-list-item>
      <v-list-item v-for="sostituzione in consegna.sostituzioni" :key="sostituzione.idx">
        <v-list-item-avatar style="margin-right:8px">
          <v-img :src="prodotti[sostituzione.idx].img"></v-img>
        </v-list-item-avatar>
        <v-list-item-content>
          <v-list-item-title>{{prodotti[sostituzione.idx].name}}</v-list-item-title>
          {{prodotti[sostituzione.idx].subtitle}}
          <div class="green--text font-weight-bold">Sostituisce <v-chip><v-avatar style="margin-right:8px">
          <v-img :src="prodotti[sostituzione.sostituisce].img"></v-img>
        </v-avatar> {{prodotti[sostituzione.sostituisce].name}} {{prodotti[sostituzione.sostituisce].subtitle}}</v-chip></div>
        </v-list-item-content>
        <v-list-item-icon>
          <v-btn
            text
            color="deep-purple accent-4"
            @click.stop="gestisci(sostituzione.sostituisce)"
          >
            Modifica
          </v-btn>
        </v-list-item-icon>
      </v-list-item>
      
      <v-list-item>
		<v-list-item-content></v-list-item-content>
        <v-list-item-icon>
          <v-btn
            text
            color="red accent-4"
            @click.stop="consegna.expanded = false"
            v-if="consegna.expanded"
          >
            Chiudi
          </v-btn>
        </v-list-item-icon>
      </v-list-item>

      <v-divider></v-divider>

    </template>

	<template v-if="consegna.hasMl">
      <v-list-item>
        <v-list-item-content>
          <v-list-item-title class="headline orange--text">MERCATO LIBERO</v-list-item-title>
        </v-list-item-content>
      </v-list-item>

		<v-divider></v-divider>

      <v-list-item v-for="item in articoliMl" :key="item.name">
        <v-list-item-avatar style="margin-right:8px">
          <v-img :src="item.img"></v-img>
        </v-list-item-avatar>
        <v-list-item-content>
          <v-list-item-title>{{item.name}}</v-list-item-title>
          {{item.subtitle}} 
        </v-list-item-content>
        <v-list-item-icon>
        <v-btn-toggle
          rounded
        >
          <v-btn>
            <v-icon>mdi-minus</v-icon>
          </v-btn>
          <v-btn :disabled="true" style="background:#fff !important; color:#222 !important">
            2
          </v-btn>
          <v-btn>
            <v-icon>mdi-plus</v-icon>
          </v-btn>
        </v-btn-toggle>
        
        </v-list-item-icon>
      </v-list-item>

      <v-list-item>
        <v-list-item-content>
          <v-list-item-title class="subtitle-1 orange--text">Totale Mercato Libero</v-list-item-title>
        </v-list-item-content>
        <v-list-item-icon>
          <span class="subtitle-1 orange--text">12,50 €</span>
        </v-list-item-icon>
      </v-list-item>
	</template>

      </v-list>
    </v-card-text>
      
    <v-card-actions v-if="consegna.hasMl">
      <v-btn
        text
        color="deep-purple accent-4"
      >
        Aggiungi prodotti di Mercato Libero
      </v-btn>
    </v-card-actions>
  </v-card>
  <br/>
</template>
      </v-col>
    </v-row>
    </v-container>

	<v-dialog v-model="escludiProdottoDialog" persistent :scrollable="true" max-width="600px">
      <v-card>
        <v-card-title>Gestisci&nbsp;<span class="orange--text" v-if="prodottoInEsclusione"> {{prodottoInEsclusione.name}}</span></v-card-title>
        <v-divider></v-divider>
        <v-card-text v-if="prodottoInEsclusione" style="margin:10px 0px">
			<v-list>
			<v-list-item>
				<v-list-item-avatar style="margin-right:8px">
					<v-img :src="prodottoInEsclusione.img"></v-img>
				</v-list-item-avatar>
				<v-list-item-content>
					<v-list-item-title>{{prodottoInEsclusione.name}}</v-list-item-title>
					{{prodottoInEsclusione.subtitle}}
				</v-list-item-content>
			</v-list-item>
			
			<template v-if="!addingRequest">
				<v-list-item v-if="regoleEsclusioni[prodottoInEsclusioneIdx] === undefined || regoleEsclusioni[prodottoInEsclusioneIdx].filter(regola => regola.escludi).length == 0">
					<i>Non e' ancora presente nessuna richiesta di esclusione per questo prodotto</i>
				</v-list-item>
				<template v-if="regoleEsclusioni[prodottoInEsclusioneIdx] !== undefined">
				<div v-for="regola in regoleEsclusioni[prodottoInEsclusioneIdx]" :key="regola.from">
					<v-list-item v-if="regola.escludi">
						<v-list-item-content>
							<v-list-item-title style="display:block">Non ricevo questo prodotto dal <span class="orange--text">{{regola.from}}</span> <template v-if="regola.to != '9999-99-99'">al <span class="orange--text">{{regola.to}}</span></template></v-list-item-title>
								<div style="margin-left:15px">
								<span v-if="regola.sostituisciCon.length">e lo sostituisco con</span>
								<span v-if="regola.sostituisciCon.length == 0">e <span class="red--text">NON</span> lo sostituisco con altri prodotti</span>
									 <template v-for="sostituzione_idx in regola.sostituisciCon">
										<br/>
									 <v-chip style="margin-bottom:3px">
										<v-avatar style="margin-right:8px">
											<v-img :src="prodotti[sostituzione_idx].img"></v-img>
										</v-avatar> {{prodotti[sostituzione_idx].name}} {{prodotti[sostituzione_idx].subtitle}}
									</v-chip>
									</template>
								</div>
						</v-list-item-content>
					</v-list-item>
					<v-divider></v-divider>
				</div>
				</template>
			</template>
			
			<template v-if="addingRequest">
				<v-container>
					<v-row>
						<v-col cols="6">
							  <v-dialog
								ref="dialogFrom"
								v-model="requestFromDialog"
								:return-value.sync="requestFrom"
								persistent
								width="290px"
							  >
								<template v-slot:activator="{ on }">
								  <v-text-field
									v-model="requestFrom"
									label="Dal"
									readonly
									v-on="on"
								  ></v-text-field>
								</template>
								<v-date-picker v-model="requestFrom" scrollable :first-day-of-week="1" locale="it-it">
								  <v-spacer></v-spacer>
								  <v-btn text color="primary" @click="requestFromDialog = false">Cancel</v-btn>
								  <v-btn text color="primary" @click="$refs.dialogFrom.save(requestFrom)">OK</v-btn>
								</v-date-picker>
							  </v-dialog>
						</v-col>
						<v-col cols="6">
							  <v-dialog
								ref="dialogTo"
								v-model="requestToDialog"
								:return-value.sync="requestTo"
								persistent
								width="290px"
							  >
								<template v-slot:activator="{ on }">
								  <v-text-field
									v-model="requestTo"
									label="Al"
									readonly
									v-on="on"
								  ></v-text-field>
								</template>
								<v-date-picker v-model="requestTo" scrollable :first-day-of-week="1" locale="it-it">
								  <v-spacer></v-spacer>
								  <v-btn text color="primary" @click="requestToDialog = false">Cancel</v-btn>
								  <v-btn text color="primary" @click="$refs.dialogTo.save(requestTo)">OK</v-btn>
								</v-date-picker>
							  </v-dialog>
						</v-col>
					</v-row>
					<v-row>
						<v-col cols="12">
							<v-radio-group v-model="requestEscludi">
								<v-radio label="Voglio ricevere questo prodotto" :value="false"></v-radio>
								<v-radio label="NON voglio ricevere questo prodotto" :value="true"></v-radio>
							</v-radio-group>
						</v-col>
					</v-row>
					<v-row v-if="requestEscludi">
						<v-col cols="12">
							<v-radio-group v-model="requestSostituisci">
								<v-radio label="e NON voglio sostituirlo con altri prodotti" :value="false"></v-radio>
								<v-radio label="e voglio sostituirlo con uno o piu' dei seguenti prodotti" :value="true"></v-radio>
							</v-radio-group>
						</v-col>
						<v-col cols="12" v-if="requestSostituisci">
							<v-list-item v-for="p_sostituzione_idx in sostituzioniPossibili[prodottoInEsclusioneIdx]" :key="p_sostituzione_idx">
								<v-list-item-avatar style="margin-right:8px">
									<v-img :src="prodotti[p_sostituzione_idx].img"></v-img>
								</v-list-item-avatar>
								<v-list-item-content>
									<v-list-item-title>{{prodotti[p_sostituzione_idx].name}}</v-list-item-title>
									{{prodotti[p_sostituzione_idx].subtitle}}
								</v-list-item-content>
								<v-list-item-icon style="margin:0">
									<v-checkbox v-model="requestSostituzioni" :value="p_sostituzione_idx"></v-checkbox>
								</v-list-item-icon>
							</v-list-item>
						</v-col>
					</v-row>
				</v-container>
			</template>
			
			</v-list>
        </v-card-text>
        <v-divider></v-divider>
        <v-card-actions>
			<template v-if="addingRequest">
				<v-btn color="blue darken-1" text @click="escludiProdottoDialog = false; addingRequest=false">Chiudi</v-btn>
				<v-btn color="blue darken-1" text @click="addingRequest = false">Indietro</v-btn>
				<v-btn color="blue darken-1" text @click="submitRequest()">Procedi</v-btn>
			</template>
			<template v-if="!addingRequest">
				<v-btn color="blue darken-1" text @click="escludiProdottoDialog = false; addingRequest=false">Chiudi</v-btn>
				<v-btn color="blue darken-1" text @click="addingRequest = true; resetFields();">Aggiungi o cambia richiesta</v-btn>
			</template>
        </v-card-actions>
      </v-card>
    </v-dialog>

    </v-content>
  </div>
  

  <script src="https://cdn.jsdelivr.net/npm/vue@2.x/dist/vue.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/vuetify@2.x/dist/vuetify.js"></script>
  <script>
    new Vue({
      el: '#app',
      vuetify: new Vuetify(),
      data: () => {
        return {
			escludiProdottoDialog: false,
          newFuncSnackbar: true,
          drawer: null,
          tab: 2,
          prodottoInEsclusione: null,
          prodottoInEsclusioneIdx: null,
          addingRequest: false,
          requestFrom: new Date().toISOString().substr(0, 10),
          requestFromDialog: false,
          requestTo: new Date().toISOString().substr(0, 10),
          requestToDialog: false,
          requestEscludi: true,
          requestSostituisci: true,
          requestSostituzioni: [],
          articoliMl: [{
				name: 'Carote',
				subtitle: 'Confezione 600 g circa  -  €3,40/kg',
				img: 'https://mangio.zolle.it/img/areariservata/articoli/resized/box/3533_IMG_6423.jpg'
			}, {
				name: 'Yogurt intero',
				subtitle: 'Confezione 525 g  -  €6,63/kg',
				img: 'https://mangio.zolle.it/img/areariservata/articoli/resized/box/926_IMG_9790.jpg'
			}
          ],
			prodotti: [{
				name: 'Fiordilatte bio',
				subtitle: '0,8 kg',
				img: 'https://mangio.zolle.it/img/areariservata/articoli/resized/box/1296_IMG_7546.jpg'
			}, {
				name: 'Zolletta Frutta',
				subtitle: '4 pz',
				img: 'https://mangio.zolle.it/img/areariservata/articoli/resized/box/398_IMG_5758.jpg'
			}, {
				name: 'Limoni primofiore',
				subtitle: '1 kg',
				img: 'https://mangio.zolle.it/img/areariservata/articoli/resized/box/3391_IMG_0396.jpg'
			}, {
				name: 'Zolletta Arance da tavola',
				subtitle: '2 kg',
				img: 'https://mangio.zolle.it/img/areariservata/articoli/resized/box/3457_IMG_3187.jpg'
			}, {
				name: 'Broccolo romanesco',
				subtitle: '900 g',
				img: 'https://mangio.zolle.it/img/areariservata/articoli/resized/box/4326_broccolo romanescoIMG_3450.jpg'
			}, {
				name: 'Cavolfiore bianco',
				subtitle: '1 kg',
				img: 'https://mangio.zolle.it/img/areariservata/articoli/resized/box/4337_cavolfiore bianco.jpg'
			}, {
				name: 'Cavolo cappuccio',
				subtitle: '1 kg',
				img: 'https://mangio.zolle.it/img/areariservata/articoli/resized/box/4328_cavolo cappuccio.jpg'
			}, {
				name: 'Patate',
				subtitle: '1 kg',
				img: 'https://mangio.zolle.it/img/areariservata/articoli/resized/box/3331_IMG_4910.jpg'
			}, {
				name: 'Porri',
				subtitle: '400 g',
				img: 'https://mangio.zolle.it/img/areariservata/articoli/resized/box/4354_porri_IMG_3461.jpg'
			}, {
				name: 'Spinaci',
				subtitle: '500 g',
				img: 'https://mangio.zolle.it/img/areariservata/articoli/resized/box/4431_spinaci.jpg'
			}, {
				name: 'Limone cedrato',
				subtitle: '300 g',
				img: 'https://mangio.zolle.it/img/areariservata/articoli/resized/box/4381_limone cedrato.jpg'
			}, {
				name: 'Banane',
				subtitle: '0,7 kg',
				img: 'https://mangio.zolle.it/img/areariservata/articoli/resized/box/3395__IMG_9768 - Copia.jpg'
			}],
			sostituzioniPossibili: {
				'0': [4, 5],
				'1': [6, 7],
				'2': [8, 9],
				'3': [10, 11]
			},
			regoleEsclusioni: {
				2: [{
					from: '2020-03-20',
					to: '2020-03-30',
					escludi: true,
					sostituisciCon: [8]
				}, {
					from: '2020-05-01',
					to: '2020-06-01',
					escludi: true,
					sostituisciCon: []
				}]
			},
			consegne: [{
				expanded: false,
				hasMl: true,
				header: 'Venerdi 20 Marzo 2020 - Settimana 12',
				data: '2020-03-20',
				zolle: [{
					qty: 1,
					name: 'Zolla Piccola'
				}, {
					qty: 1,
					name: 'Zolla Single'
				}],
				spesa: [0, 1, 2, 3],
				esclusioni: [],
				sostituzioni: []
			}, {
				expanded: false,
				hasMl: false,
				header: 'Venerdi 27 Marzo 2020 - Settimana 13',
				data: '2020-03-27',
				zolle: [{
					qty: 1,
					name: 'Zolla Piccola'
				}, {
					qty: 1,
					name: 'Zolla Single'
				}],
				spesa: [0, 1, 2, 3],
				esclusioni: [],
				sostituzioni: []
			}, {
				expanded: false,
				hasMl: false,
				header: 'Venerdi 03 Aprile 2020 - Settimana 14',
				data: '2020-04-03',
				zolle: [{
					qty: 1,
					name: 'Zolla Piccola'
				}, {
					qty: 1,
					name: 'Zolla Single'
				}],
				spesa: [0, 1, 2, 3],
				esclusioni: [],
				sostituzioni: []
			}, {
				expanded: false,
				hasMl: false,
				header: 'Venerdi 10 Aprile 2020 - Settimana 15',
				data: '2020-04-10',
				zolle: [{
					qty: 1,
					name: 'Zolla Piccola'
				}, {
					qty: 1,
					name: 'Zolla Single'
				}],
				spesa: [0, 1, 2, 3],
				esclusioni: [],
				sostituzioni: []
			}]
        };
      },
		created: function() {
			this.applicaRegoleAlleConsegne();
		},
		methods: {
			resetFields: function() {
				this.requestFrom= new Date().toISOString().substr(0, 10);
				this.requestFromDialog= false;
				this.requestTo= new Date().toISOString().substr(0, 10);
				this.requestToDialog= false;
				this.requestEscludi = true; // reset del form value
				this.requestSostituisci = true; // reset del form value
				this.requestSostituzioni = []; // reset del form value
			},
			gestisci: function(prodotto_idx) {
				prodotto_idx = parseInt(prodotto_idx);
				this.resetFields();
				this.prodottoInEsclusioneIdx = prodotto_idx;
				this.prodottoInEsclusione = this.prodotti[prodotto_idx];
				this.escludiProdottoDialog = true;
			},
			submitRequest: function() {
				if(this.requestFrom >= this.requestTo) {
					alert('Dal deve essere minore di al');
					return;
				}
				if(this.requestSostitusci && this.requestSostituzioni.length == 0) {
					alert('Devi selezionare almeno un prodotto');
					return;
				}
				this.handleRequest(this.prodottoInEsclusioneIdx, {
					from: this.requestFrom,
					to: this.requestTo,
					escludi: this.requestEscludi,
					sostituisciCon: !this.requestEscludi ? [] : this.requestSostituzioni
				});
				this.addingRequest = false;
			},
			handleRequest: function(prodotto_idx, request) {
				prodotto_idx = parseInt(prodotto_idx);
				if(this.regoleEsclusioni[prodotto_idx] === undefined) {
					this.regoleEsclusioni[prodotto_idx] = [];
				}
				var records = this.regoleEsclusioni[prodotto_idx];
				// estrai tutte le date presenti in records (non posso usare flat() o flatMap() perchè non tutti i browser li supportano)
				var date = records.map(record => record.from);
				date = date.concat( records.map(record => record.to) );
				// aggiungi le date della richiesta
				date = date.concat([request.from, request.to]);
				// rimuovi i duplicati
				date = date.filter((item, pos) => {
					return date.indexOf(item) == pos;
				});
				date.sort();
				// aggiorna i record intervallo per intervallo
				var newRecords = [];
				for(var k=0;k<date.length-1;k++) {
					var currInterval = [date[k], date[k+1]];
					if( request.from <= currInterval[0] && request.to >= currInterval[1] ) {
						// la nuova richiesta sovrascrive la regola esistente (se esiste) in quell'intervallo
						newRecords.push({
							from: currInterval[0],
							to: currInterval[1],
							escludi: request.escludi,
							sostituisciCon: request.sostituisciCon
						});
					}
					else {
						// si applica il record già esistente (ed esiste per costruzione) su quell'intervallo
						// individua il record da applicare nell'intervallo
						for(var j=0;j<records.length;j++) {
							var record = records[j];
							if(record.from <= currInterval[0] && record.to >= currInterval[1]) {
								newRecords.push({
									from: currInterval[0],
									to: currInterval[1],
									escludi: record.escludi,
									sostituisciCon: record.sostituisciCon
								});
								break;
							}
						}
					}
				}
				// fai il merge dei record consecutivi se identici
				records = [];
				var currKey = '';
				for(var h=0;h<newRecords.length;h++) {
					var currRecord = newRecords[h];
					var recordKey = `${currRecord.escludi}-${currRecord.sostituisciCon.join(',')}`;
					if(currKey != recordKey) {
						records.push(currRecord);
						currKey = recordKey;
					}
					else {
						records[records.length-1].to = currRecord.to;
					}
				}
				this.regoleEsclusioni[prodotto_idx] = records;
				this.applicaRegoleAlleConsegne();
			},
			applicaRegoleAlleConsegne: function() {
				for(var j=0;j<this.consegne.length;j++) {
					var consegna = this.consegne[j];
					consegna.spesa = [0,1,2,3];
					consegna.esclusioni = [];
					consegna.sostituzioni = [];
				}
				for(var prodotto_idx in this.regoleEsclusioni) {
					prodotto_idx = parseInt(prodotto_idx);
					var regole = this.regoleEsclusioni[prodotto_idx];
					for(var i=0;i<regole.length;i++) {
						var regola = regole[i];
						for(var j=0;j<this.consegne.length;j++) {
							var consegna = this.consegne[j];
							if(regola.from <= consegna.data && regola.to >= consegna.data) {
								// applica la regola
								if(regola.escludi) {
									// gestisci l'esclusione
									if(consegna.esclusioni.indexOf(prodotto_idx) == -1) {
										consegna.esclusioni.push(prodotto_idx);
									}
									// rimuovi il prodotto dalla spesa
									var idx = consegna.spesa.indexOf(prodotto_idx);
									if(idx != -1) {
										consegna.spesa.splice(idx, 1);
									}
									
									// gestisci le eventuali sostituzioni
									for(var k=0;k<regola.sostituisciCon.length;k++) {
										var sostituzioneIdx = regola.sostituisciCon[k];
										if(consegna.sostituzioni.indexOf(sostituzioneIdx) == -1) {
											consegna.sostituzioni.push({
												idx: sostituzioneIdx,
												sostituisce: prodotto_idx
											});
										}
									} 
								}
								else { // abilita
									var idx = consegna.esclusioni.indexOf(prodotto_idx);
									if(idx != -1) {
										consegna.esclusioni.splice(idx, 1);
									}
									var idx2 = consegna.spesa.indexOf(prodotto_idx);
									if(idx2 == -1) {
										consegna.spesa.push(prodotto_idx);
									}
									// rimuovi le eventuali esclusioni collegate al prodotto ri-abilitato
									for(var k=0;k<regola.sostituisciCon.length;k++) {
										var sostituzioneIdx = regola.sostituisciCon[k];
										var idx3 = consegna.sostituzioni.indexOf(sostituzioneIdx);
										if(idx3 != -1) {
											consegna.sostituzioni.splice(idx3, 1);
										}
									} 
									
								}
							} 
						}
					}
				}
			}
		}
    })
  </script>
</body>
</html> 
