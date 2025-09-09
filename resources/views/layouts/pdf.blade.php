<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>@yield('title', 'Relatório PDF')</title>
    <link href="https://fonts.googleapis.com/css2?family=Quicksand:wght@400;500;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Quicksand', sans-serif;
            background: #f5f6fa;
            color: #333;
            margin: 0;
            padding: 20px;
        }
        .report-container {
            max-width: 850px;
            margin: auto;
            background: #fff;
            border-radius: 12px;
            box-shadow: 0 4px 16px rgba(0, 0, 0, 0.08);
            padding: 30px 40px;
        }
        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            /* border-bottom: 2px solid #eee; */
            padding-bottom: 18px;
            margin-bottom: 22px;
        }
        .header img {
            height: 60px;
        }
        .header-info {
            text-align: right;
        }
        .header-info h1 {
            font-size: 1.6rem;
            margin: 0;
            color: #2c3e50;
            font-weight: 700;
        }
        .header-info p {
            margin: 2px 0;
            font-size: 0.9rem;
            color: #666;
        }
        .section-title {
            text-align: center;
            color: #6d6d6d;
            font-size: 1.2rem;
            font-weight: 600;
            margin-bottom: 8px;
        }
        .badge {
            display: inline-block;
            background: #777f1a;
            color: #fff;
            padding: 6px 14px;
            border-radius: 18px;
            font-size: 0.9rem;
            font-weight: 500;
        }
        .info-line {
            display: flex;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 15px;
            font-size: 0.95rem;
            margin: 20px 0;
            padding: 12px 16px;
            background: #f9fafc;
            border: 1px solid #eee;
            border-radius: 10px;
        }
        .info-item strong {
            color: #2c3e50;
            font-weight: 600;
            margin-right: 6px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
            font-size: 0.95rem;
        }
        th, td {
            padding: 10px 12px;
            text-align: left;
        }
        th {
            background: #f4f6f9;
            font-weight: 600;
            color: #2c3e50;
        }
        td {
            background: #fff;
            border-bottom: 1px solid #eee;
        }
        .text-center {
            text-align: center;
        }
        .summary {
            margin-top: 20px;
            font-size: 1rem;
            padding: 15px;
            background: #f9fafc;
            border: 1px solid #eee;
            border-radius: 10px;
        }
        .summary-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 8px;
        }
        .summary-row strong {
            color: #2c3e50;
            font-weight: 600;
        }
        .signature {
            text-align: center;
            margin-top: 40px;
        }
        .signature hr {
            width: 60%;
            margin: 20px auto 10px;
            border: 0;
            border-top: 1px solid #bbb;
        }
        .footer {
            text-align: center;
            margin-top: 30px;
            font-size: 0.85rem;
            color: #888;
        }
    </style>
</head>
<body>
    <div class="report-container">
        <header class="header">
            <table style="width:100%;">
                <tr>
                    <td style="width: 80px; vertical-align: middle;">
                        <img src="{{ public_path('img/logo.png') }}" alt="logo" style="height: 100px;">
                    </td>
                    <td style="text-align: right;">
                        <div class="header-info">
                            <h2 style="font-size: 1.6rem; margin: 0; color: #2563eb; font-weight: 700;">@yield('title', 'Relatório')</h2>
                            <p style="font-size: 10px; color: #aaa;">
                                MAXSAUDE DISTRIBUIDORA DE PRODUTOS ODONTOLOGICOS E HOSPITALARES LTDA<br>
                                CNPJ: 53.322.401/0001-24<br>
                                Endereço: rua buriti 47, centro, Eusébio/Ceará
                            </p>
                            <p style="font-size: 10px; color: #aaa;">
                                Telefones: 85 99168-6536 / 85 99172-5715
                            </p>
                            <p style="font-size: 10px; color: #aaa;">
                                Instagram: @Maxesteticaoficial
                            </p>
                        </div>
                    </td>
                </tr>
            </table>
        </header>
        @yield('content')
        @yield('summary')
        <footer class="footer">
            Documento gerado em {{ date('d/m/Y H:i') }}
        </footer>
    </div>
</body>
</html>
