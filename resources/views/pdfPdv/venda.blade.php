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
      padding: 20px;
    }

    .comprovante {
      max-width: 850px;
      margin: auto;
      background: #fff;
      border-radius: 12px;
      box-shadow: 0 4px 16px rgba(0, 0, 0, 0.08);
      padding: 30px 40px;
    }

    /* Cabeçalho */
    .header {
      display: flex;
      justify-content: space-between;
      align-items: center;
      border-bottom: 2px solid #eee;
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

    /* Título */
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

    /* Linha de dados principais */
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

    /* Tabelas */
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

    /* Resumo */
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

    /* Assinatura */
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

    /* Rodapé */
    .footer {
      text-align: center;
      margin-top: 30px;
      font-size: 0.85rem;
      color: #888;
    }
  </style>
</head>
<body>
  <main class="comprovante">
    <!-- Cabeçalho -->
    <header class="header">
    <table style="width:100%;">
        <tr>
            <td style="width: 80px; vertical-align: middle;">
                <img src="{{ public_path('img/logo.png') }}" alt="logo" style="height: 100px;">
            </td>
            <td style="text-align: right;">
                <div class="header-info">
                    <h2 style="font-size: 1.6rem; margin: 0; color: #777f1a; font-weight: 700;">Max Estética</h2>
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

    <!-- Identificação -->
    <div class="text-center">
    <h2 class="section-title">
      {{ $vendas->tipo_registro == 'orcamento' ? 'Comprovante de Orçamento' : 'Comprovante de Venda' }}
    </h2>
    <span class="badge">
      {{ $vendas->tipo_registro == 'orcamento' ? 'Orçamento Nº ' : 'Venda Nº ' }}{{$vendas->id}}
    </span>
    </div>

      <!-- Dados principais em tabela -->
      <section>
        <table class="info-table" style="width:100%; margin: 18px 0 10px 0; border-radius:10px; overflow:hidden;">
          <thead>
            <tr style="background:#f4f6f9;">
              <th>Cliente</th>
              <th>Vendedor</th>
              <th>Data</th>
              <th>Pagamento</th>
              
            </tr>
          </thead>
          <tbody>
            <tr>
              <td>{{$vendas->cliente->nome ?? '-'}}</td>
              <td>{{$vendas->funcionario->nome ?? '-'}}</td>
              <td>{{ \Carbon\Carbon::parse($vendas->data_venda)->format('d/m/Y') }}</td>
              <td>{{$vendas->formaPgmto->nome ?? '-'}}</td>
              
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
              <td>{{$item->Produto->nome ?? '-'}}</td>
              <td>R$ {{ number_format($item->valor_venda, 2, ',', '.') }}</td>
              <td>{{$item->qtd}}</td>
              <td>R$ {{ number_format($item->sub_total, 2, ',', '.') }}</td>
            </tr>
          @endforeach
        </tbody>
      </table>
    </section>

    <!-- Resumo -->
    <section class="summary">
         @if(!empty($vendas->valor_acres_desc))
            <div class="summary-row">
                <span>Valor Desconto/Acréscimo:</span>
                <span>R$ {{ number_format($vendas->valor_acres_desc, 2, ',', '.') }}</span>
            </div>
        @endif
        @if(!empty($vendas->percent_acres_desc))
            <div class="summary-row">
                <span>Percentual Desconto/Acréscimo:</span>
                <span>{{ $vendas->percent_acres_desc }}%</span>
            </div>
        @endif
        @if(!empty($vendas->valor_total))
            <div class="summary-row">
                <span>Valor Total Produtos:</span>
                <span>R$ {{ number_format($vendas->valor_total, 2, ',', '.') }}</span>
            </div>
        @endif
        @if(!empty($vendas->valor_total_desconto))
            <div class="summary-row">
                <strong>Valor Final:</strong>
                <strong>R$ {{ number_format($vendas->valor_total_desconto, 2, ',', '.') }}</strong>
            </div>
        @endif
    </section>

    <!-- Assinatura -->
    <div class="signature">
      <hr>
      <p>Assinatura do Cliente</p>
    </div>

    <!-- Rodapé -->
    <footer class="footer">
      Documento gerado em {{ date('d/m/Y H:i') }}
    </footer>
  </main>
</body>
</html>
