<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-GLhlTQ8iRABdZLl6O3oVMWSktQOp6b7In1Zl3/Jr59b6EGGoI1aFkw7cmDA6j6gD" crossorigin="anonymous">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js" integrity="sha384-w76AqPfDkMBDXo30jS1Sgez6pr3x5MlQ1ZAGC+nuZB+EYdgRZgiwxhTBTkF7CXvN" crossorigin="anonymous"></script>

    <title>Comprovante de Venda</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
        }

        .header {
            text-align: center;
            margin-bottom: 20px;
        }

        .header img {
            height: 60px;
        }

        .header h1 {
            font-size: 24px;
            font-weight: bold;
            margin: 10px 0;
        }

        .header p {
            font-size: 14px;
            margin: 0;
        }

        .table th, .table td {
            vertical-align: middle;
        }

        .total {
            font-size: 18px;
            font-weight: bold;
            text-align: right;
            margin-top: 20px;
        }

        .signature {
            text-align: center;
            margin-top: 50px;
        }

        .signature hr {
            width: 50%;
            margin: 20px auto;
        }
    </style>
</head>

<body>

    <div class="header">
        <img src="{{ asset('img/logo.png') }}" alt="Logo">
        <h1>Delicaty Acessóriosslllllllll</h1>
        <p>Instagram: @delicatyacessorios - (87)99931-7326</p>
    </div>

    <div class="text-center mb-4">
        <h4>Comprovante de Venda</h4>
    </div>

    <table class="table table-bordered">
        <thead class="table-light text-center">
            <tr>
                <th>Venda</th>
                <th>Cliente</th>
                <th>Data</th>
                <th>Forma de Pagamento</th>
            </tr>
        </thead>
        <tbody class="text-center">
            <tr>
                <td>{{$vendas->id}}</td>
                <td>{{$vendas->cliente->nome}}</td>
                <td>{{ \Carbon\Carbon::parse($vendas->data_venda)->format('d/m/Y') }}</td>
                <td>{{$vendas->formaPgmto->nome}}</td>
            </tr>
        </tbody>
    </table>

    <table class="table table-bordered mt-4">
        <thead class="table-light text-center">
            <tr>
                <th>Produto</th>
                <th>Valor</th>
                <th>Qtd</th>
                <th>Desco/Acres</th>
                <th>SubTotal</th>
            </tr>
        </thead>
        <tbody class="text-center">
            @foreach ($vendas->itensVenda as $itens)
            <tr>
                <td>{{$itens->produto->nome}}</td>
                <td>R$ {{$itens->valor_venda}}</td>
                <td>{{$itens->qtd}}</td>
                <td>R$ {{$itens->acres_desc}}</td>
                <td>R$ {{$itens->sub_total}}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="total">
        VALOR TOTAL: R$ {{$vendas->valor_total}}
    </div>

    <div class="signature">
        <hr>
        <p>Cliente</p>
    </div>

</body>

</html>