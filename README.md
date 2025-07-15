# GM Leads API

## Descrição

Esta API foi desenvolvida para servir como o backend de um sistema de cadastro de leads em eventos para a General Motors. O objetivo principal é capacitar a equipe de marketing a registrar leads de forma eficiente em tablets durante eventos.

A aplicação possui três responsabilidades centrais:

1.  **Geração Dinâmica de Frontend**: Gera webapps (formulários de cadastro) com base em configurações de eventos definidas em um backoffice.
2.  **Captura de Leads**: Recebe e armazena os dados dos leads submetidos através dos formulários gerados.
3.  **Integração com CRM**: Envia os leads capturados para o sistema de CRM da General Motors, com controle para evitar duplicidade de envios.

O backoffice, onde os eventos são criados e as tabelas de leads são definidas, é uma aplicação separada que acessa o mesmo banco de dados.

## Features e Endpoints

A API expõe os seguintes endpoints principais, agrupados sob o prefixo `/api`:

  - `POST /api/generate-event/{tableName}`

      - **Descrição**: Dispara o processo de geração e publicação de um frontend para um evento específico.
      - **Processo**:
        1.  Busca as informações do evento na tabela `briefing` usando o `{tableName}`.
        2.  Obtém o template HTML correspondente ao tipo de evento na tabela `c_tipos`.
        3.  Monta um formulário com base nas colunas da tabela do evento (`{tableName}`).
        4.  Usa a API do GitHub para criar/atualizar um arquivo `index.html` no repositório do frontend, que por sua vez aciona um deploy na Cloudflare Pages.

  - `POST /api/leads`

      - **Descrição**: Endpoint para o qual os formulários gerados enviam os dados dos leads.
      - **Processo**: Recebe um payload JSON com os dados do lead e os insere na tabela de evento correspondente, que é identificada pelo ID do `briefing` no payload.

  - `GET /api/crm/{tableName}`

      - **Descrição**: Envia todos os leads pendentes de um evento específico para o CRM da GM.
      - **Processo**:
        1.  Verifica o último lead enviado para o evento consultando a tabela `crm`.
        2.  Busca todos os leads mais recentes na tabela `{tableName}`.
        3.  Envia os leads para a API do CRM.
        4.  Após o envio bem-sucedido, atualiza a tabela `crm` com o ID do último lead enviado para evitar reenviá-lo no futuro.

  - `GET /api/up`

      - **Descrição**: Endpoint de Health Check para verificar se a aplicação está no ar e conectada ao banco de dados.

## Tecnologias Utilizadas

  - **Backend**: PHP 8.3 com [Slim Framework](https://www.slimframework.com/)
  - **Banco de Dados**: MySQL 8.0
  - **Containerização**: Docker e Docker Compose
  - **Servidor Web Local**: Nginx com PHP-FPM
  - **Dependências PHP**: Guzzle (HTTP Client), Monolog (Logger), PHP-DI (Dependency Injection).
  - **Implantação**: O projeto é projetado para ser implantado no serviço **AWS Elastic Beanstalk (EBS)**. O frontend gerado é hospedado na **Cloudflare Pages**.

## Pré-requisitos

  - Docker
  - Docker Compose
  - Git

## Instalação e Ambiente Local

Siga os passos abaixo para configurar e executar a aplicação em seu ambiente de desenvolvimento local.

1.  **Clone o repositório:**

    ```sh
    git clone <url-do-seu-repositorio>
    cd gmleads-api
    ```

2.  **Configuração de Ambiente:**
    Crie um arquivo `.env` na raiz do projeto. Você pode copiar o `.env.example` (se existir) ou criá-lo do zero com as seguintes variáveis:

    ```env
    # Configuração do Banco de Dados (deve corresponder ao docker-compose.yml)
    DB_HOST=db
    DB_PORT=3306
    DB_DATABASE=gmleads_db
    DB_USERNAME=admin
    DB_PASSWORD=secret

    # Configuração do Ambiente da Aplicação
    APP_ENV=dev
    DISPLAY_ERROR_DETAILS=true
    LOG_ERRORS=true
    LOG_ERRORS_DETAILS=true

    # Credenciais da API do CRM da GM
    CRM_EMAIL="seu-email-crm@example.com"
    CRM_PASSWORD="sua-senha-crm"

    # Credenciais da API do GitHub para deploy do Frontend
    GITHUB_API_TOKEN="seu_personal_access_token_do_github"
    GITHUB_REPO_OWNER="usuario_ou_organizacao_dona_do_repo"
    GITHUB_REPO_NAME="nome_do_repo_frontend"
    ```

3.  **Banco de Dados:**
    A aplicação utiliza o arquivo `gmlead.sql` para popular a estrutura inicial do banco de dados. O container do Docker fará isso automaticamente na primeira inicialização se o volume estiver vazio, mas caso precise fazer manualmente:

    ```sh
    # Certifique-se de que os containers estão de pé antes de executar este comando
    docker-compose exec -T db mysql -uadmin -psecret gmleads_db < gmlead.sql
    ```

4.  **Instale as dependências:**
    Use o Composer através do Docker para instalar as dependências do PHP.

    ```sh
    docker-compose run --rm php composer install
    ```

5.  **Inicie os containers:**

    ```sh
    docker-compose up -d --build
    ```

6.  **Verificação:**
    A API estará disponível em `http://localhost:8080`. Você pode testar o endpoint de health check:

    ```sh
    curl http://localhost:8080/api/up
    ```

    A resposta esperada é um JSON indicando que o serviço está ativo.

## Implantação (Deployment)

A aplicação inclui um script `build.sh` para facilitar a criação de um pacote de implantação para a AWS.

Para gerar o pacote, execute:

```sh
sh build.sh
```

Este comando criará um arquivo zip com timestamp (ex: `20250715143000-build.zip`), contendo todos os arquivos necessários para a produção e excluindo arquivos de desenvolvimento, logs e credenciais. Este arquivo pode ser enviado diretamente para o ambiente do AWS Elastic Beanstalk.
