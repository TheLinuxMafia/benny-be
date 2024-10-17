<!DOCTYPE html>
<html>
<head>
    <title>Estratto conto analitico mese </title>
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
    @foreach($array as $azienda)
    <table class="table table-striped table-bordered table-sm">
    <thead>
        <tr>
            <td colspan="4"><b><span style="color: red;">{{ $azienda->azienda }}</span></b>
            @if ($azienda->olio > 0)
                Tot. Olio: @money($azienda->olio)
            @endif

            @if ($azienda->accessori > 0)
                Tot. Accessori: @money($azienda->accessori)
            @endif

            @if ($azienda->adblue > 0)
                Tot. AdBlue: @money($azienda->adblue)
            @endif
            @foreach($azienda->tot_generate as $k => $v)
                @foreach ($v as $key => $value)
                - Tot {{ $key }}: @money($value)
            @endforeach
            @endforeach
            </td>
        </tr>
    </thead>
    @foreach($azienda->veicoli as $veicolo)
    <tbody>
        <tr>
            <td><b><?php echo $veicolo->targa ?></b></td>
            <td>Olio: @money($veicolo->olio)</td>
            <td>Adblue: @money($veicolo->adblue)</td>
            <td>Accessori: @money($veicolo->accessori)</td>
        </tr>
    @foreach($veicolo->prodotti as $prodotto)
    <tr>
        <td><?php echo $prodotto['prodotto'] ?></td>
        <td>@money($prodotto['importo'])</td>
        <td colspan="2">Lt. <?php echo $prodotto['litri'] ?></td>
    </tr>
    @endforeach
    <tr>
        <td colspan="4"><hr style="border: 0.5px solid black"></td>
    </tr>
    @endforeach
    </tbody>
</table>
    @endforeach
</body>
</html>