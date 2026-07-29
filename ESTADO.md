# Estado do Projeto — Pesqueiro Capela

## Status Atual
- **Fase:** Produção / Deploy Concluído
- **Repositório Remoto:** `https://github.com/MuriloBresciaa/pesqueiro-capela.git`
- **Ambiente Local:** Laragon (`c:\laragon\www\_CLIENTES\pesqueirocapela`)

## O Que Foi Feito
1. **Auditoria de Segurança:**
   - Identificado e removido o Access Token de Produção do Mercado Pago exposto no arquivo `gerar_pix.php`.
   - Refatorado `gerar_pix.php` para carregar `MERCADO_PAGO_ACCESS_TOKEN` via variável de ambiente.
   - Criado `.env.example` para orientação de configuração segura de credenciais.
2. **Governança e Versionamento:**
   - Criado `.gitignore` ignorando `.env`, logs, temporários de SO e IDEs.
   - Criado `README.md` completo com badges de produção, visão geral do case, diferenciais técnicos, stack e instrução de ambiente local.
   - Inicializado repositório Git local na branch `main`.
   - Efetuado commit inicial (`feat: release production code for pesqueiro capela landing page`) e push para a origem remota no GitHub.
3. **Validação:**
   - Sintaxe PHP validada sem erros (`php -l gerar_pix.php`).

## Estrutura de Arquivos
- `index.html`: Landing page principal (Tailwind CSS, Alpine.js, PapaParse, Meta Pixel).
- `gerar_pix.php`: Handler backend em PHP para criação de cobrança via API cURL do Mercado Pago.
- `.env.example`: Template de variáveis de ambiente.
- `.gitignore`: Regras de exclusão do Git.
- `README.md`: Documentação oficial do projeto.
- `ESTADO.md`: Registro de estado e memória contínua.

## Próximos Passos
- Configurar variáveis de ambiente `MERCADO_PAGO_ACCESS_TOKEN` no ambiente do servidor web de produção (ex: Apache VirtualHost / `.env` protegido).
