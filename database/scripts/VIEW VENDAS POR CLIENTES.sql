CREATE OR REPLACE VIEW vw_total_vendas_por_clientes AS
SELECT 
	c.id AS id,
	c.nome AS cliente_nome,
	COALESCE(SUM(t.total),0) AS valor_total_desconto,
	(CASE WHEN (MAX(t.data_venda) IS NULL) THEN 'Nunca comprou' ELSE DATE_FORMAT(MAX(t.data_venda),'%d/%m/%Y') END) AS ultima_compra
FROM clientes c
LEFT JOIN (
	SELECT 
		venda_p_d_v_s.cliente_id AS id,
		venda_p_d_v_s.valor_total_desconto AS total,
		venda_p_d_v_s.data_venda AS data_venda
	FROM venda_p_d_v_s
	WHERE venda_p_d_v_s.tipo_registro = 'venda'
	UNION ALL
	SELECT 
		vendas.cliente_id AS id,
		vendas.valor_total_desconto AS total,
		vendas.data_venda AS data_venda
	FROM vendas
) t ON c.id = t.id
GROUP BY c.id, c.nome
ORDER BY COALESCE(SUM(t.total),0) DESC;