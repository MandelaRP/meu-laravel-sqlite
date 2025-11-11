# 📦 Compilar e Fazer Upload dos Assets

## ⚠️ IMPORTANTE: Assets Não Estão no Git!

Os arquivos compilados (`public/build/`) **NÃO** estão no Git por padrão. Você precisa compilá-los localmente e fazer upload manualmente.

## 🔧 Passo a Passo

### 1️⃣ Compilar Assets Localmente

No seu computador, na raiz do projeto, execute:

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

**Faça upload de TODA a pasta `public/build/` para o servidor.**

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

### 4️⃣ Verificar Permissões

Certifique-se de que a pasta `public/build/` tenha permissão de leitura:

```bash
chmod -R 755 public/build
```

### 5️⃣ Testar

Acesse: `https://auraspay.online`

## 🚨 Se Ainda Estiver em Branco

### Verificar se os Arquivos Existem

1. Acesse diretamente no navegador:
   - `https://auraspay.online/build/assets/app-*.js`
   - `https://auraspay.online/build/assets/app-*.css`

2. Se retornar 404, os arquivos não foram enviados corretamente.

### Verificar o Manifesto

1. Acesse: `https://auraspay.online/build/manifest.json`
2. Verifique se o arquivo existe e contém as referências corretas.

### Limpar Cache do Navegador

- Pressione `Ctrl + Shift + R` (Windows/Linux)
- Ou `Cmd + Shift + R` (Mac)

## 📝 Script Rápido

Crie um arquivo `COMPILAR_E_ENVIAR.bat` (Windows) ou `COMPILAR_E_ENVIAR.sh` (Linux/Mac):

**Windows (COMPILAR_E_ENVIAR.bat):**
```batch
@echo off
echo Compilando assets...
call npm run build
echo.
echo ✅ Assets compilados!
echo.
echo ⚠️ Agora faça upload da pasta public/build/ para o servidor
pause
```

**Linux/Mac (COMPILAR_E_ENVIAR.sh):**
```bash
#!/bin/bash
echo "Compilando assets..."
npm run build
echo ""
echo "✅ Assets compilados!"
echo ""
echo "⚠️ Agora faça upload da pasta public/build/ para o servidor"
```

## 🔄 Atualizar Assets Após Mudanças

Sempre que você modificar arquivos em `resources/js/` ou `resources/css/`:

1. Execute `npm run build` localmente
2. Faça upload da pasta `public/build/` novamente

## ✅ Checklist

- [ ] Executei `npm install` localmente
- [ ] Executei `npm run build` localmente
- [ ] Verifiquei que `public/build/assets/` contém os arquivos
- [ ] Fiz upload de `public/build/` para o servidor
- [ ] Verifiquei permissões da pasta `public/build/`
- [ ] Testei acessando o site
- [ ] Limpei o cache do navegador

