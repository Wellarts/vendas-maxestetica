<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <title>Comprovante de Venda</title>
    <!-- Fonte suave e delicada -->
    <link href="https://fonts.googleapis.com/css2?family=Quicksand:wght@400;500;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Quicksand', sans-serif;
            background: #f5f6fa;
            color: #333;
            margin: 0;
            padding: 5px;
        }

        .comprovante {
            max-width: 850px;
            margin: auto;
            background: #fff;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.06);
            padding: 10px 12px;
        }

        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding-bottom: 6px;
            margin-bottom: 8px;
        }

        .header img {
            height: 40px;
        }

        .header-info h1,
        .header-info h2 {
            font-size: 1rem;
            margin: 0;
            color: #2c3e50;
            font-weight: 700;
        }

        .header-info p {
            margin: 1px 0;
            font-size: 0.8rem;
            color: #666;
        }

        .section-title {
            text-align: center;
            color: #6d6d6d;
            font-size: 1rem;
            font-weight: 600;
            margin-bottom: 2px;
        }

        .badge {
            display: inline-block;
            background: #777f1a;
            color: #fff;
            padding: 3px 8px;
            border-radius: 14px;
            font-size: 0.8rem;
            font-weight: 500;
        }

        .info-line {
            display: flex;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 8px;
            font-size: 0.85rem;
            margin: 10px 0;
            padding: 6px 8px;
            background: #f9fafc;
            border: 1px solid #eee;
            border-radius: 8px;
        }

        .info-item strong {
            color: #2c3e50;
            font-weight: 600;
            margin-right: 4px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin: 10px 0;
            font-size: 0.75rem;
        }

        th,
        td {
            padding: 5px 6px;
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
            margin-top: 10px;
            font-size: 0.8rem;
            padding: 8px;
            background: #f9fafc;
            border: 1px solid #eee;
            border-radius: 8px;
            text-align: right;
        }

        .summary-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 4px;
        }

        .summary-row strong {
            color: #2c3e50;
            font-weight: 600;
        }

        .signature {
            text-align: center;
            margin-top: 20px;
        }

        .signature hr {
            width: 50%;
            margin: 10px auto 5px;
            border: 0;
            border-top: 1px solid #bbb;
        }

        .footer {
            text-align: center;
            margin-top: 12px;
            font-size: 0.7rem;
            color: #888;
        }
    </style>
</head>

<body>
    <main class="comprovante">
        <!-- Cabeçalho -->
        <header class="header" style="padding-bottom: 8px; margin-bottom: 12px;">
            <table style="width:100%;">
                <tr>
                    <td style="width: 60px; vertical-align: middle;">
                        <img src="{{ public_path('img/logo.png') }}" alt="logo" style="height: 60px;">
                    </td>
                    <td style="text-align: right;">
                        <div class="header-info">
                            <h2
                                style="font-size: 1.1rem; margin: 0; color: #777f1a; font-weight: 700; line-height: 1.1;">
                                Max Estética</h2>
                            <p style="font-size: 9px; color: #aaa; margin: 1px 0; line-height: 1.1;">
                                MAXSAUDE DISTRIBUIDORA DE PRODUTOS ODONTOLOGICOS E HOSPITALARES LTDA<br>
                                CNPJ: 53.322.401/0001-24<br>
                                Endereço: Rua Buriti 47, Centro, Eusébio/Ceará
                            </p>
                            <p style="font-size: 9px; color: #aaa; margin: 1px 0; line-height: 1.1;">
                                Telefones: 85 99168-6536 / 85 99172-5715
                            </p>
                            <p style="font-size: 9px; color: #aaa; margin: 1px 0; line-height: 1.1;">
                                Instagram: @Maxesteticaoficial
                            </p>
                        </div>
                    </td>
                </tr>
            </table>
            <h2 class="section-title" style="margin-bottom: 0; border: none;">
                {{ $vendas->tipo_registro == 'orcamento' ? 'Comprovante de Orçamento' : 'Comprovante de Venda' }}
            </h2>
            <div style="text-align: center; margin-bottom:8px;">
                <span class="badge">
                    {{ $vendas->tipo_registro == 'orcamento' ? 'Orçamento Nº ' : 'Venda Nº ' }}{{ $vendas->id }}
                </span>
            </div>
        </header>

        <!-- Identificação -->



        <!-- Dados completos do Cliente -->
        <section style="margin: 18px 0 10px 0;">
            <table
                style="width:100%; border-radius:10px; overflow:hidden; background: #f9fafc; border: 1px solid #eee; font-size: 0.80rem; table-layout: fixed;">
                <tr>
                    <td colspan="4" style="padding: 6px 8px; background: #e8ebf0;">
                        <strong>Dados do Cliente</strong>
                    </td>
                </tr>
                <tr>
                    <td style="width: 18%; padding: 6px 8px;"><strong>Nome:</strong></td>
                    <td style="width: 32%; padding: 6px 0px;">{{ $vendas->cliente->nome ?? '-' }}</td>
                    <td style="width: 18%; padding: 6px 8px;"><strong>CPF/CNPJ:</strong></td>
                    <td style="width: 32%; padding: 6px 8px;">{{ $vendas->cliente->cpf_cnpj ?? '-' }}</td>
                </tr>
                <tr>
                    <td style="padding: 6px 8px;"><strong>Telefone:</strong></td>
                    <td style="padding: 6px 8px;">{{ $vendas->cliente->telefone ?? '-' }}</td>
                    <td style="padding: 6px 8px;"><strong>E-mail:</strong></td>
                    <td style="padding: 6px 8px;">{{ $vendas->cliente->email ?? '-' }}</td>
                </tr>
                <tr>
                    <td style="padding: 6px 8px;"><strong>Endereço:</strong></td>
                    <td colspan="3" style="padding: 6px 8px;">{{ $vendas->cliente->endereco ?? '-' }}</td>
                </tr>
                <tr>
                    <td style="width: 18%; padding: 6px 8px;"><strong>Profissão:</strong></td>
                    <td style="width: 32%; padding: 6px 0px;">{{ $vendas->cliente->profissao ?? '-' }}</td>
                    <td style="width: 18%; padding: 6px 8px;"><strong>Nº Conselho:</strong></td>
                    <td style="width: 32%; padding: 6px 8px;">{{ $vendas->cliente->numero_conselho ?? '-' }}</td>
                </tr>


            </table>
        </section>

        <!-- Dados principais em tabela -->
        <section>
            <table class="info-table" style="width:100%; margin: 18px 0 10px 0; border-radius:10px; overflow:hidden;">
                <thead>
                    <tr style="background:#f4f6f9;">
                        <th>Vendedor</th>
                        <th>Data</th>
                        <th>Pagamento</th>

                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>{{ $vendas->funcionario->nome ?? '-' }}</td>
                        <td>{{ \Carbon\Carbon::parse($vendas->data_venda)->format('d/m/Y') }}</td>
                        <td>{{ $vendas->formaPgmto->nome ?? '-' }}</td>

                    </tr>
                </tbody>
            </table>
        </section>

        <!-- Produtos -->
        <section>
            <table>
                <thead>
                    <tr>
                        <th>Produto</th>
                        <th>Valor Unit.</th>
                        <th>Qtd</th>
                        <th>Subtotal</th>
                    </tr>
                </thead>
                <tbody class="text-center">
                    @foreach ($vendas->pdv as $item)
                        <tr>
                            <td>{{ $item->Produto->nome ?? '-' }}</td>
                            <td>R$ {{ number_format($item->valor_venda, 2, ',', '.') }}</td>
                            <td>{{ $item->qtd }}</td>
                            <td>R$ {{ number_format($item->sub_total, 2, ',', '.') }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </section>

        <!-- Resumo -->
        <section class="summary">
            @if ($vendas->tipo_acres_desc == 'Porcentagem')
                <div class="summary-row">
                    <span>Percentual Desconto/Acréscimo:</span>
                    <span>
                        {{ $vendas->percent_acres_desc }}%
                        @php
                            $valorTotal = $vendas->valor_total ?? 0;
                            $valorFinal = $vendas->valor_total_desconto ?? 0;
                            $valorAcresDesc = $valorTotal - $valorFinal;
                        @endphp
                        @if ($valorAcresDesc != 0)
                            (
                            {{ $vendas->percent_acres_desc < 0 ? 'Desconto' : 'Acréscimo' }}:
                            R$ {{ $valorAcresDesc }}
                            )
                        @endif
                    </span>
                </div>
            @elseif($vendas->tipo_acres_desc == 'Valor')
                <div class="summary-row">
                    <span>Valor Desconto/Acréscimo:</span>
                    <span>R$ {{ $vendas->valor_acres_desc }}</span>
                </div>
            @endif
            @if (!empty($vendas->valor_total))
                <div class="summary-row">
                    <span>Valor Total Produtos:</span>
                    <span>R$ {{ number_format($vendas->valor_total, 2, ',', '.') }}</span>
                </div>
            @endif
            @if (!empty($vendas->valor_total_desconto))
                <div class="summary-row">
                    <strong>Valor Final:</strong>
                    <strong>R$ {{ number_format($vendas->valor_total_desconto, 2, ',', '.') }}</strong>
                </div>
            @endif
        </section>

        <!-- Rodapé -->
        <footer class="footer">
            Documento gerado em {{ date('d/m/Y H:i') }}
        </footer>
    </main>
</body>

</html>
