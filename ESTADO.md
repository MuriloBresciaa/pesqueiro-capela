# Estado do Projeto — Pesqueiro Capela

## Status Atual
- **Fase:** Produção / Arquitetura Modular Implantada e Sincronizada
- **Repositório Remoto:** `https://github.com/MuriloBresciaa/pesqueiro-capela.git`
- **Branch:** `main` (Push realizado com sucesso)
- **Ambiente Local:** Laragon (`c:\laragon\www\_CLIENTES\pesqueirocapela`)

## O Que Foi Feito
1. **Refatoração Arquitetural (De Flat para Modular):**
   - **`api/`**: Isolamento do endpoint backend `gerar_pix.php`.
   - **`assets/img/`**: Agrupamento de todas as imagens e fotografias do site (`aconchegante.jpg`, `cardapio1.jpg`, `cardapio2.jpg`, `deck.jpg`, `estacionamento.jpg`, `index.jpg`, `logo.jpeg`).
   - **`assets/icons/`**: Centralização de favicons e manifesto PWA (`android-chrome-*.png`, `apple-touch-icon.png`, `favicon-*.png`, `favicon.ico`, `site.webmanifest`).
2. **Atualização de Referências:**
   - `index.html`: Atualizados caminhos de favicons (`assets/icons/`), imagens (`assets/img/`) e da rota de API (`./api/gerar_pix.php`).
   - `site.webmanifest`: Atualizados caminhos dos ícones Android para relativos à pasta de ícones.
3. **Validação & Documentação:**
   - Sintaxe PHP em `api/gerar_pix.php` validada sem erros (`php -l api/gerar_pix.php`).
   - `README.md` atualizado com a nova árvore de diretórios.
   - Sincronização enviada para a branch `main` no GitHub remote.

## Estrutura de Arquivos Resultante
```text
pesqueiro-capela/
├── api/
│   └── gerar_pix.php
├── assets/
│   ├── icons/
│   │   ├── android-chrome-192x192.png
│   │   ├── android-chrome-512x512.png
│   │   ├── apple-touch-icon.png
│   │   ├── favicon-16x16.png
│   │   ├── favicon-32x32.png
│   │   ├── favicon.ico
│   │   └── site.webmanifest
│   └── img/
│       ├── aconchegante.jpg
│       ├── cardapio1.jpg
│       ├── cardapio2.jpg
│       ├── deck.jpg
│       ├── estacionamento.jpg
│       ├── index.jpg
│       └── logo.jpeg
├── index.html
├── .env.example
├── .gitignore
├── ESTADO.md
└── README.md
```

## Próximos Passos
- Manter novas APIs dentro de `api/` e novos ativos estáticos dentro de `assets/`.
