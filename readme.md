# Monitora Sites

Este projeto de software tem como objetivo monitorar sites e, caso algum esteja fora do ar, registrar os logs do servidor e notificar o usuário via WhatsApp e e-mail.

## Funcionalidades Principais

- **Cadastro e Login**: Permite que novos usuários se cadastrem e acessem o sistema.
- **Gerenciamento de Sites**: O usuário pode adicionar, editar ou remover sites monitorados.
- **Configuração de Endpoints**: Inicialmente, será possível adicionar endpoints aos sites cadastrados.
- **Monitoramento Automático**: O sistema realizará requisições periódicas (a cada 1 minuto) para verificar a disponibilidade dos sites e endpoints configurados.

## Monitoramento e Notificações

- O usuário informará a **URL** e a **frequência de verificação** (inicialmente, apenas intervalos de 1 minuto serão permitidos).
- O sistema registrará o **status do site**, a **data e hora da próxima verificação** e disponibilizará opções para **edição** e consulta de **logs**.
- Caso o site esteja fora do ar, o usuário será notificado por WhatsApp e e-mail.

## Consulta de Logs

- Exibição do **status**, **data/hora (timestamp)** e **resposta (response)** da requisição.
- Avaliação da possibilidade de gerar um **resumo inteligente** do response utilizando **IA** para facilitar a análise dos logs.

## Pacotes Utilizados

- Laravel
- TailwindCSS
- Vue.js
- Laravel Livewire
- Rector - Instant Upgrades and Automated Refactoring (https://github.com/rectorphp/rector)
  -- vendor/bin/rector
  -- vendor/bin/rector dry-run
  -- vendor/bin/rector process --no-cache
  -- vendor/bin/rector process --no-cache --dry-run
  -- vendor/bin/rector process --no-cache --dry-run --no-progress-bar

- iacommit2 - AI Commit Messages (https://github.com/leemunroe/aicommit)
- CaptainHook - Git Hooks (https://github.com/captainhookphp/captainhook)
- PHPStan - Static Analysis Tool (https://github.com/phpstan/phpstan)
- Pint - PHP Code Style Fixer (https://github.com/laravel/pint)
- PHPUnit - Testing Framework (https://github.com/sebastianbergmann/phpunit)
- PHPStan - Static Analysis Tool (https://github.com/phpstan/phpstan)
- Pint - PHP Code Style Fixer (https://github.com/laravel/pint)
