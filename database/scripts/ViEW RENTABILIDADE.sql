CREATE OR REPLACE VIEW vw_soma_quantidade_produtos AS
SELECT
  p.id   AS id, -- ID do produto
  p.nome AS nome, -- Nome do produto
  p.tipo AS tipo, -- Tipo do produto

  -- Soma total de quantidade vendida do produto
  COALESCE(SUM(iv.qtd),0) AS total_vendido_qtd,

  -- Soma total do valor vendido do produto
  COALESCE(SUM(iv.sub_total),0) AS total_vendido_valor,

  -- Soma total do custo dos produtos vendidos
  COALESCE(SUM(iv.total_custo_atual),0) AS total_vendido_custo,

  -- Lucro total: valor vendido - custo
  COALESCE(SUM(iv.sub_total),0) - COALESCE(SUM(iv.total_custo_atual),0) AS total_vendido_lucro,

  -- Score de quantidade vendida (normalizado de 0 a 1)
  COALESCE(SUM(iv.qtd),0) / NULLIF((
    SELECT MAX(sq_qtd)
    FROM (
      SELECT SUM(i2.qtd) as sq_qtd
      FROM p_d_v_s i2
      INNER JOIN venda_p_d_v_s v2 ON i2.venda_p_d_v_id = v2.id
      WHERE v2.tipo_registro = 'venda'
      GROUP BY i2.produto_id
    ) t
  ),0) AS score_qtd,

  -- Score de lucro (normalizado de 0 a 1)
  (COALESCE(SUM(iv.sub_total),0) - COALESCE(SUM(iv.total_custo_atual),0)) / NULLIF((
    SELECT MAX(sq_lucro)
    FROM (
      SELECT SUM(i2.sub_total-i2.total_custo_atual) as sq_lucro
      FROM p_d_v_s i2
      INNER JOIN venda_p_d_v_s v2 ON i2.venda_p_d_v_id = v2.id
      WHERE v2.tipo_registro = 'venda'
      GROUP BY i2.produto_id
    ) t
  ),0) AS score_lucro,

  -- Pontuação final de rentabilidade (média dos scores de quantidade e lucro)
  (
    COALESCE(SUM(iv.qtd),0) / NULLIF((
      SELECT MAX(sq_qtd)
      FROM (
        SELECT SUM(i2.qtd) as sq_qtd
        FROM p_d_v_s i2
        INNER JOIN venda_p_d_v_s v2 ON i2.venda_p_d_v_id = v2.id
        WHERE v2.tipo_registro = 'venda'
        GROUP BY i2.produto_id
      ) t
    ),0)
    +
    (COALESCE(SUM(iv.sub_total),0) - COALESCE(SUM(iv.total_custo_atual),0)) / NULLIF((
      SELECT MAX(sq_lucro)
      FROM (
        SELECT SUM(i2.sub_total-i2.total_custo_atual) as sq_lucro
        FROM p_d_v_s i2
        INNER JOIN venda_p_d_v_s v2 ON i2.venda_p_d_v_id = v2.id
        WHERE v2.tipo_registro = 'venda'
        GROUP BY i2.produto_id
      ) t
    ),0)
  ) / 2 AS pontuacao_rentabilidade

FROM produtos p
-- Junta com os itens de venda
LEFT JOIN p_d_v_s iv
  ON p.id = iv.produto_id
-- Junta com o cabeçalho da venda, filtrando apenas vendas (não orçamentos)
LEFT JOIN venda_p_d_v_s v
  ON iv.venda_p_d_v_id = v.id AND v.tipo_registro = 'venda'
-- Garante que só considera itens que pertencem a uma venda
WHERE v.id IS NOT NULL
GROUP BY p.id, p.nome, p.tipo;