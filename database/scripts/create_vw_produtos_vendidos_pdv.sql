CREATE OR REPLACE VIEW vw_produtos_vendidos_pdv AS
SELECT
    pdv.id AS item_id,
    pdv.venda_p_d_v_id AS venda_id,
    venda.data_venda,
    venda.cliente_id,
    cliente.nome AS cliente_nome,
    venda.funcionario_id,
    funcionario.nome AS funcionario_nome,
    venda.valor_total,
    pdv.produto_id,
    produto.nome AS produto_nome,
    pdv.qtd AS quantidade,
    pdv.valor_venda AS preco_unitario,
    pdv.sub_total
FROM
    p_d_v_s pdv
    INNER JOIN vendas_p_d_v_s venda ON pdv.venda_p_d_v_id = venda.id
    INNER JOIN produtos produto ON pdv.produto_id = produto.id
    LEFT JOIN clientes cliente ON venda.cliente_id = cliente.id
    LEFT JOIN funcionarios funcionario ON venda.funcionario_id = funcionario.id;
