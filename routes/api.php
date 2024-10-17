<?php

use Illuminate\Http\Request;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ComuniController;
use App\Http\Controllers\AziendeController;
use App\Http\Controllers\PublicController;
use App\Http\Controllers\PuntiVenditaController;
use App\Http\Controllers\TransazioniController;
use App\Http\Controllers\CampagneController;
use App\Http\Controllers\AdmCarteController;
use App\Http\Controllers\LottiController;
use App\Http\Controllers\LottiGiftController;
use App\Http\Controllers\GiftController;
use App\Http\Controllers\AdmPuntiVenditaController;
use App\Http\Controllers\AdmGiftController;
use App\Http\Controllers\PDFController;
use App\Http\Controllers\Auth\VerificationController;
use App\Http\Controllers\AdmCampagneController;
use App\Http\Controllers\AdmLottiController;
use App\Http\Controllers\GodController;
use App\Http\Controllers\AziendeCarburantiController;
use App\Http\Controllers\ProdottiCarburantiController;
use App\Http\Controllers\TargheCarburantiController;
use App\Http\Controllers\CarteController;
use App\Http\Controllers\StatisticheController;
use App\Http\Controllers\EstratticontoController;
use App\Http\Controllers\VenditeController;

setlocale(LC_TIME, config('app.locale'));

Route::group([

    'middleware' => 'external',
    'prefix' => 'ext'

], function ($router) {

    Route::middleware('auth:external')->group(function() {

        Route::get('extlistuser', [GodController::class, 'godlistusers']);
    });
});


Route::group([

    'middleware' => 'api',
    'prefix' => 'auth'

], function ($router) {

    Route::get('testmail', [AziendeController::class, 'testmail']);
    Route::post('login', [AuthController::class, 'login']);
    Route::post('nfclogin', [AuthController::class, 'nfclogin']);
    Route::post('pinlogin', [AuthController::class, 'pinlogin']);
    Route::post('logout', [AuthController::class, 'logout']);
    Route::post('refresh', [AuthController::class, 'refresh']);
    Route::get('me', [AuthController::class, 'me']);
    /** Ricerca dei comuni */
    Route::get('searchComune/{string}', [ComuniController::class, 'searchComune']);
//    Route::post('addlotto', [LottiController::class, 'addLotto']);
    Route::get('saldopunti/{card}', [PublicController::class, 'saldopunti']);
    Route::get('phonetocard/{phone}', [PublicController::class, 'phonetocard']);
    Route::post('addprinter', [AuthController::class, 'addprinter']);



    Route::middleware('jwt.auth')->group(function() {

Route::get('testmail', [AziendeController::class, 'testmail']);

/** Creaziopne di un utente */
Route::post('reguser', [AziendeController::class, 'reguser']);

/** Controlla se un indirizzo mail esiste per le fidelity card */
Route::get('checkMail/{mail}', [CarteController::class, 'checkMail']);

/** Controlla se un indirizzo mail esiste per le gift card */
Route::get('checkGiftMail/{mail}', [CarteController::class, 'checkGiftMail']);

/** Constrolla se una carte è inserita nei lotti */
Route::post('checkCarta', [CarteController::class, 'checkCarta']);

/** Associa una carta registrata nel sistema ad un utente */
Route::post('nuovacard', [AdmCarteController::class, 'nuovacard']);

/** Ricerca dei comuni */
// Route::get('searchComune/{string}', [ComuniController::class, 'searchComune']);

/** Controlla se la mail inserita per un'azienda esiste */
Route::get('mailAzienda/{mail}', [AziendeController::class, 'mailAzienda']);

/** Controlla se una partita iva è stata già registrata */
Route::get('pivaAzienda/{piva}', [AziendeController::class, 'pivaAzienda']);

/** Inserimento di un'azienda */
Route::post('azienda', [AziendeController::class, 'addAzienda']);

/** Modifica di un'azienda */
Route::put('azienda', [AziendeController::class, 'modAzienda']);

/** Lista di tutte le aziende */
Route::get('azienda', [AziendeController::class, 'getAziende']);

/** Dettaglio di un'azienda */
Route::get('azienda/{id}', [AziendeController::class, 'getAzienda']);

/** Inserimento di un punto vendita */
Route::post('puntovendita', [PuntiVenditaController::class, 'addpv']);

/** Modifica di un punto vendita */
Route::put('puntovendita', [PuntiVenditaController::class, 'modpv']);

/** Lista di tutti i punti vendita */
Route::get('puntovendita', [PuntiVenditaController::class, 'listpv']);

/** Dettaglio di un punto vendita */
Route::get('puntovendita/{id}', [PuntiVenditaController::class, 'getpv']);

/** Attiva o disattiva un punto vendita */
Route::post('actPuntiv', [PuntiVenditaController::class, 'actPuntiv']);

/** Disattiva l'azienda e tutti i suoi punti vendita */
Route::post('actAzienda', [AziendeController::class, 'actAzienda']);

/** Aggiunge una campagna */
Route::post('campagna', [CampagneController::class, 'campagna']);

/** Modifica una campagna */
Route::put('campagna', [CampagneController::class, 'campagna']);

/** Lista di tutte le campagne */
Route::get('campagna', [CampagneController::class, 'campagnaLista']);

/** Modifica il setup di una campagna */
Route::post('setupcampagna', [CampagneController::class, 'setupcampagna']);

/** Dettaglio di una campagna */
Route::get('campagna/{id}', [CampagneController::class, 'campagnaDettaglio']);

/** Aggiunge una promo ad una campagna */
Route::post('addpromocampagna', [CampagneController::class, 'addpromocampagna']);

/** Inserimento nuovo lotto di card */
Route::post('addlotto', [LottiController::class, 'addLotto']);

/** Restituisce tutti i lotti */
Route::get('lotti/{id_azn_anagrafica}', [LottiController::class, 'showLotti']);

/** Restituisce tutti i lotti assegnati ad duna campagna */
Route::get('lotticampagna/{id_campagna}', [LottiController::class, 'lottiCampagna']);

/** Aggiunge un lotto ad una campagna */
Route::post('addlottocampagna', [LottiController::class, 'addLottoCampagna']);

/** Rimuove un lotto ad una campagna */
Route::post('dellottocampagna', [LottiController::class, 'delLottoCampagna']);

/** Attiva o disattiva una campagna */
Route::post('campagnaStatus', [CampagneController::class, 'campagnaStatus']);

/** Restituisce i dati per lo store dell'azienda e del punto vendita dell'utente */
Route::get('aznStore', [AziendeController::class, 'aznStore']);

/** Restituisce i dati per lo store del punto vendita dell'utente */
Route::get('storePv', [PuntiVenditaController::class, 'storePv']);

/** Aggiunge un lotto per le gift card */
Route::post('addlottogift', [LottiGiftController::class, 'addlottogift']);

/** Constrolla se una gift card è inserita nei lotti */
Route::post('checkGift', [GiftController::class, 'checkGift']);

/** Associa una gift registrata nel sistema ad un utente */
Route::post('nuovagift', [GiftController::class, 'nuovagift']);

/** Associa una gift registrata nel sistema ad un utente */
Route::get('giftcardassegnate', [GiftController::class, 'giftcardassegnate']);

/** Associa una gift registrata nel sistema ad un utente */
Route::get('fidelitycardassegnate', [CarteController::class, 'fidelitycardassegnate']);

/** Restituisce tutti i punti vendita di un'azienda valido per inserimento user */
Route::get('puntivenditagod/{id}', [AdmPuntiVenditaController::class, 'puntivenditagod']);

/** Assegna una targa ad una gift card */
Route::post('targatogift', [AdmGiftController::class, 'targatogift']);

/** restiruisce un report in PDF con i movimenti di una gift card per intervallo di date */
Route::post('giftreportdate', [PDFController::class, 'generatePDF']);

/**restituisce report pdf di tutti i movimenti di una gift card */
Route::post('reportGifAll', [PDFController::class, 'reportGifAll']);


/** Restituisce la somma del credito restante sulle gift card */
Route::get('sommacreditogift', [GiftController::class, 'sommacreditogift']);

/**Restituisce tutti i movimenti di una gift card */
Route::post('reportallgift', [GiftController::class, 'reportallgift']);

/** Restituisce i movimenti delle gift card di un giorno */
Route::get('allgifttoday/{data}', [GiftController::class, 'allgifttoday']);



    });
});


Route::group([

    'middleware' => 'api',
    'prefix' => 'admin'

], function ($router) {

    Route::get('saldopunti/{card}', [PublicController::class, 'saldopunti']);

    Route::middleware('jwt.auth')->group(function() {

        Route::post('refresh', [AuthController::class, 'refresh']);

    /** Controlla se la mail di registrazione di un utente esiste */
        Route::get('chekemailuser/{email}', [VerificationController::class, 'chekemailuser']);

    /** Controlla se un indirizzo mail esiste per le fidelity card */
        Route::get('checkMail/{mail}', [CarteController::class, 'checkMail']);

    /** Restituisce l'azienda ed i punti vendita */
        Route::get('azienda', [AziendeController::class, 'azienda']);

    /** Attiva o disattiva una campagna  */
        Route::get('modcampagne/{id}', [AdmCampagneController::class, 'modcampagne']);

    /** Modifica una campagna  */
        Route::post('modcampagna', [AdmCampagneController::class, 'modcampagna']);

    /** Restituisce tutte le campagne di un'azienda */
        Route::get('campagne', [AdmCampagneController::class, 'campagne']);

    /** Restituisce le campagne attive per un'azienda */
        Route::get('campagneattive', [AdmCampagneController::class, 'campagneattive']);

    /** Restitusce i lotti di card registrati per un'azienda */
        Route::get('lotti', [AdmLottiController::class, 'lotti']);

    /** Restituisce il numero di carte di un'azienda */
        Route::get('totalecarte', [AdmCarteController::class, 'totalecarte']);

    /** Restituisce il numero di carte già assegnate ad un user di un'azienda */
        Route::get('carteutilizzate', [AdmCarteController::class, 'carteutilizzate']);

    /** Controlla se una carta è già associata e può essere caricata con i punti */
        Route::post('checkassociata', [AdmCarteController::class, 'checkassociata']);

    /** Controlla se una carta appartiene ad un'azienda */
        Route::post('controllacarta', [AdmCarteController::class, 'controllacarta']);

    /** Controlla se una carta appartiene ad un'azienda */
        Route::get('getcarte', [AdmCarteController::class, 'getcarte']);

    /** Controlla se una carta appartiene ad un'azienda */
        Route::get('lastpoint', [AdmCarteController::class, 'lastpoint']);

    /** Aggiunge punti alla card creando un movimento */
        Route::post('addpoint', [AdmCarteController::class, 'addpoint']);

    /** Restituisce tutti i punti vendita di un'azienda */
        Route::get('puntivendita', [AdmPuntiVenditaController::class, 'puntivendita']);

    /** Aggiunge un punto vendita */
        Route::post('addpuntovendita', [AdmPuntiVenditaController::class, 'addpuntovendita']);

    /** Modifica un punto vendita */
        Route::post('modpuntovendita', [AdmPuntiVenditaController::class, 'modpuntovendita']);

    /** Restituisce il dettaglio di una card */
        Route::get('dettagliocard/{card}', [AdmCarteController::class, 'dettagliocard']);

    /** Disabilita o abilita una card */
        Route::get('cardchangestatus/{card}', [AdmCarteController::class, 'cardchangestatus']);

    /** Associa una carta registrata nel sistema ad un utente */
        Route::post('nuovacard', [AdmCarteController::class, 'nuovacard']);

    /** Modifica i dati utente di una fidelity card registrata */
        Route::post('modcard', [AdmCarteController::class, 'modcard']);

    /** Restituisce i movimenti di una card */
        Route::get('movimenticard/{card}', [AdmCarteController::class, 'movimenticard']);

    /** Funzione per il cambio card in caso di furto o smarrimento */
        Route::post('changecard', [AdmCarteController::class, 'changecard']);

    /** Restituisce i movimenti fatti da un terminale nella data odierna */
        Route::get('getMovimentiTerminale/{card}', [AdmCarteController::class, 'getMovimentiTerminale']);

    /** Restituisce i pagamenti con gift fatti da un terminale nella data odierna */
        Route::get('termgiftmov/{termid}', [AdmGiftController::class, 'getMovimentiGiftTerminale']);

    /** conta le gift totali disponibili per un'azienda  */
        Route::get('totalegift', [AdmGiftController::class, 'totalegift']);

    /** conta le gift già associate ad un utente per un'azienda  */
        Route::get('giftutilizzate', [AdmGiftController::class, 'giftutilizzate']);

    /** Controlla se una gift card è inserita nel sistema ed è associata all'azienda */
        Route::post('controllagift', [AdmGiftController::class, 'controllagift']);

    /** Restituisce le gift associate per un'azienda */
        Route::get('getgift', [AdmGiftController::class, 'getgift']);

    /** Controlla lo stato di una gift card */
        Route::post('giftassociata', [AdmGiftController::class, 'giftassociata']);

    /** Ultimi 5 movimenti delle gift card */
        Route::get('lastgiftmov', [AdmGiftController::class, 'lastgiftmov']);

    /** Ultimi 5 movimenti delle gift card */
        Route::post('nuovagift', [AdmGiftController::class, 'nuovagift']);

    /** ricarica di una gift card */
        Route::post('ricaricagift', [AdmGiftController::class, 'ricaricagift']);

    /** ricarica di una gift card */
        Route::post('pagacongift', [AdmGiftController::class, 'pagacongift']);

    /** Restituisce il dettaglio di una gift card */
        Route::get('dettagliogift/{card}', [AdmGiftController::class, 'dettagliogift']);

    /** Restituisce il dettaglio di una gift card */
        Route::get('giftchangestatus/{card}', [AdmGiftController::class, 'giftchangestatus']);

    /** Restituisce i movimenti di una gift card */
        Route::get('movimentigift/{card}', [AdmGiftController::class, 'movimentigift']);

        Route::get('searchComune/{string}', [ComuniController::class, 'searchComune']);

        });
});

Route::group([

    'middleware' => 'api',
    'prefix' => 'god'

], function ($router) {
    Route::middleware('jwt.auth')->group(function() {
/** ################# GOD MODE ######################## */

/** Conta tuttle le carte disponibili nel sistema */
Route::get('godtotalecarte', [GodController::class, 'godtotalecarte']);

/** Conta tuttle le carte associate ad utenti nel sistema */
Route::get('godcarteutilizzate', [GodController::class, 'godcarteutilizzate']);

/** Conta tuttle le carte sospese nel sistema */
Route::get('godcartesospese', [GodController::class, 'godcartesospese']);

/** Conta tuttle le gift disponibili nel sistema */
Route::get('godtotalegift', [GodController::class, 'godtotalegift']);

/** conta le gift già associate per un'azienda */
Route::get('godgiftutilizzate', [GodController::class, 'godgiftutilizzate']);

/** conta le gift sospese */
Route::get('godgiftsospese', [GodController::class, 'godgiftsospese']);

/** Restituisce tutti gli user che hanno accesso al sistema */
Route::get('godlistusers', [GodController::class, 'godlistusers']);

/** Nasconde un user alla visualizzazione del sistema */
Route::get('goddeleteuser/{id}', [GodController::class, 'goddeleteuser']);

/** Blocca l'accsso al sistema ad un utente */
Route::get('godbanuser/{id}', [GodController::class, 'godbanuser']);

/** Generazione api token per un utente */
Route::get('genapi/{id}', [GodController::class, 'genapi']);


    });


});

############################# ROTTE PER GESTIONE CARBURANTI ##########################
Route::group([

    'middleware' => 'api',
    'prefix' => 'carburanti'

], function ($router) {

    Route::middleware('jwt.auth')->group(function() {

    /**
     * Inserimento di una nuova azienda per rifornimento carburante
     */
    Route::post('carburanti/azienda/store', [AziendeCarburantiController::class, 'store']);

    /**
     * Cerca le aziende che corrispondono parzialmente alla stringa
     */
    Route::get('carburanti/azienda/cerca/{string}', [AziendeCarburantiController::class, 'search']);

    /**
     * Restituisce tutte le aziende dei carburanti registrate
     */
    Route::get('carburanti/azienda/all', [AziendeCarburantiController::class, 'all']);

    /**
     * Elimina azienda
     * L'azienda non viene eliminata ma solo disabilitata
     */
    Route::get('carburanti/azienda/delete/{id}', [AziendeCarburantiController::class, 'delete']);

    /**
     * Riattiva azienda
     */
    Route::get('carburanti/azienda/attiva/{id}', [AziendeCarburantiController::class, 'attiva']);

    /**
     * Elimina un centro di costo
     */
    Route::get('carburanti/centrocosto/elimina/{id}', [AziendeCarburantiController::class, 'delete_centro_costo']);

    /**
     * Centri costo di un'azienda
     */
    Route::get('carburanti/centrocosto/azienda/{id}', [AziendeCarburantiController::class, 'aziende_centro_costo']);

    /**
     * Aggiungi un prodotto
     */
    Route::post('carburanti/prodotto/store', [ProdottiCarburantiController::class, 'store']);

    /**
     * Restituisce tutti i prodotti
     */
    Route::get('carburanti/prodotto/all', [ProdottiCarburantiController::class, 'all']);

    /**
     * Elimina un prodotto
     */
    Route::delete('carburanti/prodotto/delete/{id}', [ProdottiCarburantiController::class, 'delete']);

    /**
     * Inserisce una nuova targa
     */
    Route::post('carburanti/targhe/store', [TargheCarburantiController::class, 'store']);

    /**
     * Effettua un controllo sull'esistenza di una targa
     */
    Route::post('carburanti/targhe/checktarga', [TargheCarburantiController::class, 'checktarga']);

    /**
     * Restituisce le targhe per la serach nella transazione
     */
    Route::post('transazione/targhe', [TargheCarburantiController::class, 'targa']);

    /**
     * Restituisce le targhe di un'azienda
     */
    Route::get('targhe/azienda/{id}', [TargheCarburantiController::class, 'targhe_azienda']);

    /**
     * Restituisce tutte le targhe
     */
    Route::get('targhe/carburanti/all', [TargheCarburantiController::class, 'all']);

    /**
     * Cerca in tutte le targhe
     */
    Route::get('cerca/targhe/tutte/{string}', [TargheCarburantiController::class, 'cercatutte']);

    /**
     * Elimina una targa
     */
    Route::get('targhe/elimina/{id}', [TargheCarburantiController::class, 'elimina_targa']);

    /**
     * Cerca fra le targhe di un'azienda
     */
    Route::post('targhe/azienda/search', [TargheCarburantiController::class, 'search']);

    /**
     * Crea una nuova transazione
     */
    Route::post('transazione/nuova', [TransazioniController::class, 'store']);

    /**
     * Ultime 10 Transazioni
     */
    Route::get('transazione/last', [TransazioniController::class, 'last']);

    /**
     * Transazioni del mese di un'azienda
     */
    Route::get('transazione/anno/{id_azienda}', [TransazioniController::class, 'transazioni_anno']);

    /**
     * Restituisce la somma di tutte le transazioni del giorno
     */
    Route::get('transazione/giorno/somma', [TransazioniController::class, 'sum_day']);

    /**
     * Restituisce le transazioni di un'azienda dell'anno e del mese
     * @param: $request
     * @param: id_azienda
     * @param: data
     */
    Route::post('transazione/azienda/mese', [TransazioniController::class, 'transazioni_mese']);

    /**
     * Restituisce le ultime 200 transazioni
     */
    Route::get('transazione/ultime/tutte', [TransazioniController::class, 'ultime200']);

    /**
     * Restituisce le ultime 200 transazioni
     */
    Route::post('transazione/modifica', [TransazioniController::class, 'mod_transazione']);


    /**
     * Flagga una transazione come eliminata
     */
    Route::get('transazione/elimina/{id}', [TransazioniController::class, 'elimina_transazione']);

    /**
     * Ripristina una transazione eliminata
     */
    Route::get('transazione/ripristina/{id}', [TransazioniController::class, 'riattiva_transazione']);

    /**
     * Cerca in tutte le transsazioni dell'anno in corso
     */
    Route::get('transazione/cerca/tutte/{string}', [TransazioniController::class, 'cerca_transazioni']);

    Route::get('chart/transazioni/numero', [TransazioniController::class, 'chartNumeroTransazioni']);

    /** Restituisce i totali per periodo anche divisi per tipologia di prodotto */
    Route::post('chart/transazioni/periodo', [TransazioniController::class, 'totali_periodo']);



    /**
     * Restituisce le statistiche di un'azienda' per un anno, tutti i mesi e tutti i giorni
     * @param: data: data completa
     * @param: id: id azienda
     */
    Route::post('statistiche/azienda/tutte', [StatisticheController::class, 'storica']);


    /**
     * Restituisce le statistiche di un'azienda' per un determinato mese e tutti i giorni
     * @param: data: data completa
     * @param: id: id azienda
     */
    Route::post('statistiche/azienda/mese', [StatisticheController::class, 'mese']);

    /**
     * Restituisce le statistiche di un'azienda' per un determinato giorno
     * @param: data: data completa
     * @param: id: id azienda
     */
    Route::post('statistiche/azienda/giorno', [StatisticheController::class, 'giorno']);


    /**
     * Statistiche storiche per targa
     * @param: targa: targa veicolo
     * @param: id: id dell'azienda
     */
    Route::post('statistiche/targa/storica', [StatisticheController::class, 'targa_storica']);

    /**
     * Ritorna le statistiche del mese una targa e il dettaglio dei giorni
     * @param: data: data completa
     * @param: targa: targa veicolo
     */
    Route::post('statistiche/targa/mese', [StatisticheController::class, 'targa_mese']);

    /**
     * Restituisce le statistiche di una targa per un determinato giorno
     * @param: data: data completa
     * @param: targa: targa veicolo
     */
    Route::post('statistiche/targa/giorno', [StatisticheController::class, 'targa_giorno']);

    /**
     * Estratto conto mensile analitico per targhe di un'azienda
     * @param: data: data completa
     * @param: id id azienda
     * @param: data: data completa
     */
    Route::post('estratti/mese/azienda', [EstratticontoController::class, 'azienda']);

    /**
     * Estratto conto analitico per targa per mese
     * restituisce i totali del mese e il dettaglio dei giorni
     * @param: targa: targa veicolo
     * @param: id: id azienda
     * @param: data: data completa
     */
    Route::post('estratti/mese/targa', [EstratticontoController::class, 'targa_mese']);

    /**
     * Estratto conto mensile di tutte le aziende
     * Visualizza solo i totali senza i dettagli giornalieri
     * @param: data: data completa
     */
    Route::post('estratti/mese/aziende', [EstratticontoController::class, 'aziende']);


    /**
     * Estratto conto giornaliero di tutte le aziende
     * Visualizza solo i totali senza i dettagli delle targhe
     * @param: data: data completa
     */
    Route::post('estratti/giorno/aziende', [EstratticontoController::class, 'aziende_giorno']);


    /**
     * Estratto conto mensile di tutte le aziende
     * Visualizza solo i totali senza i dettagli giornalieri
     * @param: data: data completa
     */
    Route::post('estratto/mese/aziende', [EstratticontoController::class, 'aziende_mese']);

    /**
     * Estratto conto di una targa
     * Visualizza genera un estratto conto di una targa
     * @param: data: data completa
     */
    Route::post('estratto/mese/aziende', [EstratticontoController::class, 'estratto_targa']);


    Route::post('estratto/mese/analitico', [EstratticontoController::class, 'mensile_analitico_aziende']);

    Route::post('estratti/mese/analitico/targhe', [EstratticontoController::class, 'mensile_analitico_targhe']);

    Route::post('estratti/mese/analitico/azienda', [EstratticontoController::class, 'estratto_analitico_azienda_mese']);

    Route::post('estratti/mese/analitico/periodo', [EstratticontoController::class, 'estratto_analitico_azienda_periodo']);

    Route::post('estratti/mese/targhe/periodo', [EstratticontoController::class, 'estratto_targhe_selezionate']);

    Route::get('estratti/all', [EstratticontoController::class, 'all']);

    Route::get('estratti/delete/{id}', [EstratticontoController::class, 'delete']);

    Route::post('estratti/paga', [EstratticontoController::class, 'paga']);

    Route::post('estratti/filtra', [EstratticontoController::class, 'filtra']);

    Route::post('estratti/non_contabilizzate', [EstratticontoController::class, 'non_contabilizzate']);

    /** NON UTILIZZARE */
    Route::get('litri/transazioni/update', [TransazioniController::class, 'litri_transazione']);
    Route::get('litri/transazioni/adblue', [TransazioniController::class, 'update_litri_adblue']);
    /** */




    Route::post('vendite/periodo/prodotto', [VenditeController::class, 'riepilogo_prodotto']);

    });

});



Route::group([

    'middleware' => 'api',
    'prefix' => 'public'

], function ($router) {

    ###### ROTTE NON AUTENTICATE ###########
/** Controlla il token o i dati inseriti dall'utente (card ed email) */
Route::post('checkuserpublic', [PublicController::class, 'checkuserpublic']);
Route::post('signupuserpublic', [PublicController::class, 'signupuserpublic']);


    Route::middleware('jwt.auth')->group(function() {

    });

});
