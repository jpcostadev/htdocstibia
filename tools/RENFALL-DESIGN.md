# Renfall: tema e catalogo de personagens

Atualizacao do tema tibiacom, preservando as rotas MyAAC. Verde profundo, dourado, arte e logo Renfall; navegacao, webshop e layout responsivo.

Bazaar: filtros de nome, vocacao (incluindo Monk), nivel, preco, habilidades, item de inventario, boss points, dust e gold no banco; ordenacao, paginacao, favoritos locais, historico, meus leiloes e detalhes. Usa o motor Crystal instalado, sem mudar as regras de custodia e coins.

Instalacao: tools/Update-RenfallDesign.ps1. Exige o motor system/libs/crystal_bazaar.php existente. O script valida PHP, cria backup fora da pasta publica e copia somente a lista explicita de arquivos. Nao altera credenciais, configuracao de pagamentos ou servidor de jogo.

Validacao local: 20 verificacoes de custodia, taxa, lance proprio, saldo, reembolso, entrega, encerramento repetido e filtros; rotas de noticia, conta, cadastro, download, ranking, catalogo, historico, meus leiloes, detalhes e favoritos; login HTTP, CSRF, lance autenticado e redirecionamento contra reenvio. Banco isolado, sem transacoes reais.

Limites: paridade integral com o RubiniOT nao foi certificada; a ficha de referencia ficou limitada por verificacao de seguranca. Favoritos sao deste navegador. O historico exibe dados atuais do personagem (nao snapshots da venda). Outfits/montarias desbloqueados, bestiary e outras secoes avancadas da referencia ainda nao foram implementados. Pagamento real e operacao na VPS precisam ser conferidos apos a instalacao.
