# 🎯 LuckPay

Sistema de pagamentos e gestão de checkout.

## 📋 Requisitos

- PHP 8.2+
- MySQL 5.7+ ou MariaDB 10.3+
- Composer
- Node.js 18+ e NPM

## 🚀 Instalação

### 1. Clone o repositório

```bash
git clone https://github.com/seu-usuario/luckpay.git
cd luckpay
```

### 2. Instale as dependências

```bash
composer install
npm install
```

### 3. Configure o ambiente

Copie o arquivo `.env.example` para `.env`:

```bash
cp .env.example .env
```

Configure as variáveis de ambiente, especialmente:

```env
DB_CONNECTION=mysql
DB_HOST=localhost
DB_PORT=3306
DB_DATABASE=seu_banco
DB_USERNAME=seu_usuario
DB_PASSWORD=sua_senha
```

### 4. Gere a chave da aplicação

```bash
php artisan key:generate
```

### 5. Execute as migrações

```bash
php artisan migrate
```

### 6. Compile os assets

```bash
npm run build
```

## 🔧 Comandos Úteis

### Converter SQLite para MySQL

```bash
php artisan db:convert-sqlite-to-mysql --output=database/mysql_export.sql
```

### Limpar cache

```bash
php artisan config:clear
php artisan cache:clear
php artisan view:clear
```

## 📚 Documentação

- [Guia de Deploy](docs/DEPLOY.md)
- [Migração MySQL](docs/MYSQL_MIGRATION.md)
- [Integração Liberpay](docs/liberpay-integration.md)

## 🛠️ Tecnologias

- **Backend:** Laravel 11
- **Frontend:** Vue.js 3 + Inertia.js
- **Banco de Dados:** MySQL
- **Estilização:** Tailwind CSS

## 📝 Licença

Proprietário - Todos os direitos reservados
