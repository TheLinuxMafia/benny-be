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
<p><b>Data {{ $array->info->data }}</b>
<p>Riepilogo giornaliero: Totale: <b>@money($array->info->totale)</b> <span class="rosso">Carburanti: <b>@money($array->info->carburanti)</b></span>  Olio: <b>@money($array->info->olio)</b> Accessori: <b>@money($array->info->accessori)</b> AdBlue: <b>@money($array->info->adblue)</b></p>
<hr>
@foreach($array->aziende as $azienda)
<p><b>{{ $azienda->nome }}</b> Totale: <b>@money($azienda->totale)</b> <span class="rosso">Carburanti: <b>@money($azienda->carburanti)</b></span> <span class="rosso">Litri: <b>{{$azienda->litri}}</b></span> AdBlue: <b>@money($azienda->adblue)</b> Olio: <b>@money($azienda->olio)</b> Accessori: <b>@money($azienda->accessori)</b></p>
<hr style="border-top: 1px dashed">
@endforeach
</body>
</html>