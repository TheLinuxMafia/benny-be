<!DOCTYPE html>
<html>
<head>
    <title>Report gift card </title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css" integrity="sha384-ggOyR0iXCbMQv3Xipma34MD+dH/1fQ784/j6cY/iJTQUOhcWr7x9JvoRxT2MZw1T" crossorigin="anonymous">
    <style>
        * {
            font-size: 10px;
        }

        .rosso {
            color: red;
        }

        .flyleaf {
            page-break-after: always;
        }

        .header, .footer {
            position: fixed;
        }

        .header {
            top: 0;
        }

        footer { position: fixed; bottom: -60px; left: 0px; right: 0px; background-color: lightgrey; height: 50px; }
        header { position: fixed; top: -50px; left: 0px; right: 0px; height: 40px; }
        footer .page-number:after { content: counter(page); }
        @page { margin-bottom: 30px; margin-top: 70px }
        
    </style>
</head>
<body>
<footer>Benny s.r.l. Documento stampato il <?php echo date('d/m/Y') ?> alle ore <?php echo date('H:i') ?> *** Questo documento non è valido ai fini fiscali *** <span class="page-number float-right">Pagina </span></footer>
<header><span class="float-right" style="vertical-align: top"><img src="data:image/png;base64,{{ base64_encode(file_get_contents(public_path('images/benny.png'))) }}" width="100px"></span></header>
<p>Cliente: <b>{{$estratto->azienda->ragsoc}}</b> P. IVA: <b>{{ $estratto->azienda->piva}}</b> <span class="rosso">Periodo: {{ $estratto->azienda->mese}} / {{ $estratto->azienda->anno}}</span></p>

<p>
    Totale carburanti: <b>@money($estratto->azienda->carburanti)</b> |
    Totale AdBlue: <b>@money($estratto->azienda->adblue)</b> |
    Totale Accessori: <b>@money($estratto->azienda->accessori)</b> |
    Totale Olio: <b>@money($estratto->azienda->olio)</b>
    <b>Totale Generale @money($estratto->azienda->totale)</b>
<hr>
@foreach($estratto->targhe as $targa)
<!-- @if ($targa['transazioni']->count() < 1)<p>Targa: <b>{{$targa['targa']}}</b> <b>Nessuna transazione nel periodo selezionato</b><p>@endif -->

@if ($targa['transazioni']->count() > 0)
<p>Targa: <b>{{$targa['targa']}}</b> - Totale mese: <b>@money( $targa['totale_mese'] )</b> - Olio: <b>@money($targa['olio_mese'])</b> - AdBlue: <b>@money($targa['adblue'])</b> - Accessori: <b>@money($targa['accessori_mese'])</b><span class="float-right"><b>Totale carburanti: @money($targa['totale_carburanti'])</b></span><p>
<hr style="border-top: 1px dashed">
@foreach($targa['transazioni'] as $transazione)
<p> Data:  {{ $transazione['created_at']->format("d/m/Y") }} | {{ $transazione['prodotto'] }} : @money($transazione['pr_importo']) 
    @if($transazione['prodotto1']) | {{ $transazione['prodotto1'] }} : @money($transazione['pr1_importo'])@endif
    @if($transazione['adblue']) | AdBlue : @money($transazione['adblue'])@endif
    @if($transazione['olio']) | Olio : @money($transazione['olio'])@endif
    @if($transazione['accessori']) | Accessori : @money($transazione['accessori'])@endif
    <span class="float-right">Totale: @money($transazione['totale'])</span></p>
<hr>
@endforeach
@endif
@endforeach
</body>
</html>