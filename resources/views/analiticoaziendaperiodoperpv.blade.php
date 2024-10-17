<!DOCTYPE html>
<html>
<head>
    <title>Estratto conto azienda periodo </title>
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
<header>
    <span class="float-right" style="vertical-align: top"><img src="data:image/png;base64,{{ base64_encode(file_get_contents(public_path('images/benny.png'))) }}" width="100px"></span>
        <span class="float-left">Rifornimenti effettuati nel punto vendita di: <span style="color: red">{{ $array->puntovendita}}</span></span>
</header>
<p>
    <b> Azienda: <?php echo $array->azienda ?> - P.IVA: <?php echo  $array->piva ?> Dal {{ date('d/m/Y', strtotime( $array->from)) }} al {{ date('d/m/Y', strtotime( $array->to)) }}</b>
    <span style="color: red">{{ $array->contabilizzato}}</span>
</p>
<p>Rifornimenti effettuati nel punto vendita di: <span style="color: red">{{ $array->puntovendita}}</span></p>

<table class="table table-sm table-bordered">
        <tr style="background-color: #f3f3f3"> 
            <td>Totale generale: @money($array->totale)</td>
            <td>Totale carburanti: @money($array->totale_carburanti)</td>
            @if ($array->totale_olio > 0)
                <td>Totale Olio: @money($array->totale_olio)</td>
            @endif
            @if ($array->totale_adblue > 0)
                <td>Totale AdBlue: @money($array->totale_adblue)</td>
            @endif
            @if ($array->totale_accessori > 0)
                <td>Totale Accessori: @money($array->totale_accessori)</td>
            @endif
        </tr>
    </table>
    
    @foreach($array->targhe as $targa)
        <table class="table table-sm table-bordered">
            <tr style="background-color: #f3f3f3">
                <td colspan="4"><span style="color: red"> {{$targa['targa'] }}</span>
                @if ($targa['olio'] > 0)
                    - Olio: @money($targa['olio'])
                @endif

                @if ($targa['adblue'] > 0)
                    - AdBlue: @money($targa['adblue'])
                @endif

                @if ($targa['accessori'] > 0)
                    - Accessori: @money($targa['accessori'])
                @endif
            </td>
            </tr>
            @foreach($targa['transazioni'] as $transazione)
            @if ($transazione['pr_importo'] > 0)
            <tr>
            <td>{{ date('d/m/Y H:i:s', strtotime($transazione['created_at']))}}</td>
                <td>{{ $transazione['scode'] }}</td>
                <td>{{ $transazione['prodotto'] }}</td>
                <td>@money($transazione['pr_importo'])</td>
            </tr>
            @endif
            @if ($transazione['pr1_importo'] > 0)
            <tr>
                <td>{{ date('d/m/Y H:i:s', strtotime($transazione['created_at']))}}</td>
                <td>{{ $transazione['scode'] }}</td>
                <td>{{ $transazione['prodotto1'] }}</td>
                <td>@money($transazione['pr1_importo'])</td>
            </tr>
            @endif
            @if ($transazione['pr2_importo'] > 0)
            <tr>
            <td>{{ date('d/m/Y H:i:s', strtotime($transazione['created_at']))}}</td>
                <td>{{ $transazione['scode'] }}</td>
                <td>{{ $transazione['prodotto2'] }}</td>
                <td>@money($transazione['pr2_importo'])</td>
            </tr>
            @endif
            @endforeach
            <tr style="background-color: #f3f3f3">
                <td colspan="4" style="text-align:right">
                    @foreach($targa['prodotti'] as $prodotto)
                    Totale {{ $prodotto['prodotto']}}: @money($prodotto['totale']) - Litri: {{ $prodotto['litri']}}
                    @endforeach
                </td>
            </tr>
        </table>
    @endforeach
</body>
</html>