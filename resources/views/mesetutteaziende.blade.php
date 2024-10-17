<!DOCTYPE html>
<html>
<head>
    <title>Estratto conto mensile aziende </title>
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

<p><span class="rosso">Periodo: {{$array[0]->mese}} / {{$array[0]->anno}}</span></p>

@foreach($array as $aziende)
    @if($aziende->totalemese > 0)
<p><b>{{$aziende->azienda}}</b> 
<span class="rosso">Totale carburanti: <b>@money($aziende->totale_carburanti_mese)</b></span> | Totale generale: <b>@money($aziende->totalemese)</b></p>
<hr>
@foreach($aziende->targhe as $targa)
<p><b>{{ $targa->targa }}</b> - Totale carburanti: <b>@money($targa->carburanti)</b>
@if($targa->olio > 0) | Totale olio: <b>@money($targa->olio)</b>@endif
@if($targa->adblue > 0) | Totale AdBlue: <b>@money($targa->adblue)</b>@endif
@if($targa->accessori > 0) | Totale Accessori: <b>@money($targa->accessori)</b>@endif
<span class="float-right"><b>Totale generale: @money($targa->totale)</b></span></p>
<hr style="border-top: 1px dashed">
@endforeach
<p></p>
<p></p>
@endif
@endforeach
</body>
</html>