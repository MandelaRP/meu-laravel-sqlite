# Troubleshooting - Liberpay API

## Problema: API retorna HTML ao invés de JSON

### Sintoma
Ao tentar criar uma venda, a API retorna HTML (página de login) ao invés de JSON.

### Possíveis Causas

1. **Chaves sem permissão para criar vendas**
   - As chaves podem estar configuradas apenas para leitura (consultar saldo)
   - Verifique na dashboard da Liberpay se as chaves têm permissão para criar vendas

2. **Formato de autenticação incorreto**
   - A documentação oficial pode especificar um formato diferente
   - Verifique: https://app.liberpay.pro/docs/sales/create-sale

3. **Endpoint incorreto**
   - O endpoint pode estar diferente do esperado
   - Verifique a documentação oficial

4. **Chaves inválidas ou expiradas**
   - As chaves podem ter expirado
   - Gere novas chaves na dashboard da Liberpay

### Soluções

1. **Verificar documentação oficial:**
   - Acesse: https://app.liberpay.pro/docs/sales/create-sale
   - Verifique o formato exato de autenticação
   - Verifique o endpoint correto
   - Verifique a estrutura do payload

2. **Verificar permissões das chaves:**
   - Acesse a dashboard da Liberpay
   - Verifique se as chaves têm permissão para criar vendas
   - Gere novas chaves se necessário

3. **Contatar suporte:**
   - Email: suporte@liberpay.com
   - Telegram: https://t.me/liberpayoficial

### Endpoints Testados

- ✅ `GET /v1/balance` - Funciona (retorna JSON)
- ❌ `POST /v1/sales` - Retorna HTML
- ❌ `POST /v1/sale` - Retorna HTML
- ❌ `POST /api/v1/sales` - 404
- ❌ `POST /api/sales` - 404
- ❌ `POST /sales` - Retorna HTML

### Formatos de Autenticação Testados

- ❌ `X-API-Key: {chave_publica}`
- ❌ `Authorization: Bearer {chave_publica}`
- ❌ `X-API-Key: {chave_privada}`
- ❌ `Authorization: Bearer {chave_privada}`
- ❌ `X-Public-Key + X-Private-Key`

### Próximos Passos

1. Acesse a documentação oficial e verifique:
   - Formato exato de autenticação
   - Endpoint correto
   - Estrutura do payload
   - Permissões necessárias

2. Verifique na dashboard da Liberpay:
   - Se as chaves estão ativas
   - Se têm permissão para criar vendas
   - Se não expiraram

3. Teste manualmente com curl ou Postman usando a documentação oficial

