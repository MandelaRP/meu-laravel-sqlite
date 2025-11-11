# 🔧 Solução: Página em Branco - Assets Não Carregam

## ❌ Erro

```
GET https://auraspay.online/build/assets/app-*.js net::ERR_ABORTED 404 (Not Found)
GET https://auraspay.online/build/assets/app-*.css net::ERR_ABORTED 404 (Not Found)
```

A página abre em branco porque os arquivos JavaScript e CSS não estão sendo carregados.

## ✅ Solução Completa

### 1️⃣ Compilar Assets Localmente

**No seu computador**, na raiz do projeto, execute:

```bash
npm install
npm run build
```

Isso vai criar os arquivos em `public/build/assets/`.

### 2️⃣ Verificar se os Arquivos Foram Criados

Verifique se existem estes arquivos:

```
public/
└── build/
    ├── assets/
    │   ├── app-*.js      ← Arquivos JavaScript
    │   └── app-*.css     ← Arquivos CSS
    └── manifest.json     ← Manifesto do Vite
```

### 3️⃣ Fazer Upload dos Assets

**⚠️ IMPORTANTE: Os arquivos compilados NÃO estão no Git!**

Faça upload de **TODA a pasta `public/build/`** para o servidor.

A estrutura no servidor deve ficar assim:

```
/ (raiz do servidor)
├── public/
│   └── build/
│       ├── assets/
│       │   ├── app-*.js
│       │   └── app-*.css
│       └── manifest.json
```

### 4️⃣ Verificar no Servidor

1. **Faça upload do arquivo `VERIFICAR_ASSETS.php` para a raiz do servidor**

2. **Acesse no navegador:**
   ```
   https://auraspay.online/VERIFICAR_ASSETS.php
   ```

3. **Siga as instruções que aparecerem**

### 5️⃣ Verificar Permissões

Certifique-se de que a pasta `public/build/` tenha permissão de leitura:

```bash
chmod -R 755 public/build
```

### 6️⃣ Testar

1. **Limpe o cache do navegador:**
   - Pressione `Ctrl + Shift + R` (Windows/Linux)
   - Ou `Cmd + Shift + R` (Mac)

2. **Acesse:** `https://auraspay.online`

## 🔍 Diagnóstico

### Verificar se os Arquivos Existem

1. Acesse diretamente no navegador:
   - `https://auraspay.online/build/manifest.json`
   - Se retornar 404, os arquivos não foram enviados corretamente.

2. Use o script `VERIFICAR_ASSETS.php` para diagnóstico completo.

## 📝 Checklist

- [ ] Executei `npm install` localmente
- [ ] Executei `npm run build` localmente
- [ ] Verifiquei que `public/build/assets/` contém os arquivos
- [ ] Fiz upload de `public/build/` para o servidor
- [ ] Executei `VERIFICAR_ASSETS.php` no servidor
- [ ] Verifiquei permissões da pasta `public/build/`
- [ ] Testei acessando o site
- [ ] Limpei o cache do navegador

## 🔄 Atualizar Assets Após Mudanças

Sempre que você modificar arquivos em `resources/js/` ou `resources/css/`:

1. Execute `npm run build` localmente
2. Faça upload da pasta `public/build/` novamente

## ⚠️ Importante

- Os arquivos de build **NÃO** estão no Git (estão no `.gitignore`)
- Você precisa compilar e fazer upload manualmente
- Após cada mudança no frontend, recompile e faça upload novamente

