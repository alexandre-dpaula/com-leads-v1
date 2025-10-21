# CRM Leads

Sistema CRM de leads multi-usuário construído em PHP 8.1+ (PDO), MySQL e Tailwind CSS via CDN. Inclui cadastro/login, reset de senha por token, funil Kanban com drag & drop, CRUD de etapas/leads via modais, perfil do usuário e isolamento total por conta.

## Tecnologias

- PHP 8.1+ sem frameworks de back-end (arquitetura MVC leve)
- MySQL 8+ (UTF8MB4) com PDO em modo exceção
- Tailwind CSS via CDN e JavaScript vanilla
- Sessions nativas com cookies HttpOnly/SameSite
- CSRF tokens, prepared statements, validação de entrada e escaping

## Como rodar localmente

1. **Requisitos**  
   - PHP 8.1+ com extensões `pdo_mysql` e `openssl`  
   - MySQL 8+  
   - Servidor web apontando para `public/` (Apache/Nginx) ou `php -S localhost:8000 -t public`

2. **Configuração de ambiente**  
   Copie `.env.example` para `.env` (se desejar) ou configure variáveis de ambiente:

   ```bash
   APP_BASE_URL=""
   DB_DSN="mysql:host=127.0.0.1;dbname=crm_leads;charset=utf8mb4"
   DB_USER="root"
   DB_PASS="secret"
   SESSION_SECURE=false
   SESSION_SAMESITE="Lax"
   ```

3. **Criar banco**  
   - Crie o schema: `CREATE DATABASE crm_leads CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;`
   - importe `sql/schema.sql` ou execute:

     ```bash
     mysql -u root -p crm_leads < sql/schema.sql
     ```

   - Para a base remota fornecida, rode o instalador (usa PDO): `php sql/install_remote.php`

4. **Estrutura de diretórios**

   ```
   public/           # Front controller, assets e JS do Kanban
   app/config/       # Configuração e helpers de ambiente
   app/core/         # Router, Session, Csrf, Validator, etc.
   app/controllers/  # Lógica de rotas (Auth, Dashboard, API)
   app/models/       # Models PDO (User, Stage, Lead, PasswordReset)
   app/views/        # Views Tailwind + parciais
   sql/              # Schema e instalador remoto
   ```

5. **Servir a aplicação**

   ```bash
   php -S localhost:8000 -t public
   ```

   Acesse `http://localhost:8000`.

## Fluxo de autenticação

- Cadastro com validação de senha (>=8), e-mail único e criação automática das etapas padrão.
- Login com rate limit progressivo (delay até 5s).
- Reset de senha com token criptografado (`hash('sha256')`), expiração de 1h e marcação `used_at`.
- Logout via POST com CSRF token.

## Kanban

- Etapas por usuário, CRUD completo + reordenação (arraste o cabeçalho da coluna).
- Leads com drag & drop (persistência via `/api/leads/{id}/move`).
- CRUD em modais com validação (fetch JSON + CSRF header).
- Filtro rápido por nome/e-mail e tag.
- Isolamento por `user_id` em todas as queries.

## Segurança

- Sessions HttpOnly, SameSite configurável e regenerate em login.
- CSRF tokens para formulários e endpoints mutáveis (`X-CSRF-Token`).
- HTML escapado com `htmlspecialchars`, prepared statements em todo o PDO.
- Validação server-side + mensagens em PT-BR.

## Testes manuais sugeridos

1. Registro → login automático → visualizar stages seed.
2. Logout e login novamente (verificar rate limit após tentativas falhas).
3. Reset de senha (solicitar token, usar token exibido em flash de debug).
4. CRUD de stages (criar, renomear, tentar excluir com leads, mover leads e excluir).
5. CRUD de leads (criar/editar/excluir via modais).
6. Drag & drop de lead entre colunas (verificar persistência após reload).
7. Busca e filtro por tag.
8. Edição de perfil (alterar e-mail, senha).
9. Verificar isolamento: criar segundo usuário e garantir que dados não se misturam.

## Deploy

1. Ajuste `.env` com credenciais de produção e `SESSION_SECURE=true`.
2. No servidor, apontar o DocumentRoot para `public/`.
3. Garantir permissões de escrita (se for usar logs/assets adicionais).
4. Remover `sql/install_remote.php` após execução para evitar exposições.

## Autor

- **Nome do Sistema**: CRM Leads  
- **Autor**: Alexandre Dpaula
