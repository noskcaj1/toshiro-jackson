# Melhorias Aplicadas ao Projeto

Este documento detalha as melhorias aplicadas ao projeto, com foco em segurança, organização do código e boas práticas de desenvolvimento.

## 1. Segurança

### 1.1. Remoção de Credenciais do Código-Fonte

**Problema:** As credenciais do banco de dados (usuário, senha, nome do banco) estavam diretamente no arquivo `index.php`. Isso é uma falha de segurança grave, pois qualquer pessoa com acesso ao código-fonte teria acesso ao banco de dados.

**Solução:** As credenciais foram removidas do código e devem ser gerenciadas através de **variáveis de ambiente**. Isso permite que as configurações sensíveis sejam injetadas no container Docker em tempo de execução, sem expô-las no código.

**Exemplo de como carregar as variáveis no PHP:**

```php
// Em vez de:
// $servername = "54.234.153.24";
// $username = "root";
// $password = "Senha123";
// $database = "meubanco";

// Use:
$servername = getenv('DB_HOST');
$username = getenv('DB_USER');
$password = getenv('DB_PASSWORD');
$database = getenv('DB_NAME');
```

### 1.2. Prevenção de Injeção de SQL

**Problema:** A query SQL era construída concatenando variáveis diretamente na string, o que a tornava vulnerável a ataques de **injeção de SQL**. Um invasor poderia manipular os valores de entrada para executar comandos maliciosos no banco de dados.

**Solução:** A query foi reescrita utilizando **Prepared Statements**. Com essa abordagem, a query e os dados são enviados separadamente ao banco de dados, que os trata de forma segura, prevenindo a injeção de SQL.

**Código Antigo (Vulnerável):**
```php
$query = "INSERT INTO dados (AlunoID, Nome, Sobrenome, Endereco, Cidade, Host) VALUES ('$valor_rand1' , '$valor_rand2', '$valor_rand2', '$valor_rand2', '$valor_rand2','$host_name')";
```

**Código Novo (Seguro):**
```php
$query = "INSERT INTO dados (AlunoID, Nome, Sobrenome, Endereco, Cidade, Host) VALUES (?, ?, ?, ?, ?, ?)";
$stmt = $link->prepare($query);
// 'isssss' significa que os tipos de dados são: integer, string, string, string, string, string
$stmt->bind_param("isssss", $valor_rand1, $valor_rand2, $valor_rand2, $valor_rand2, $valor_rand2, $host_name);
$stmt->execute();
```

## 2. Estrutura e Organização do Código

### 2.1. Separação de Responsabilidades

**Problema:** O arquivo `index.php` misturava a lógica de conexão com o banco de dados, a execução de queries e a apresentação (HTML).

**Solução:** Recomenda-se separar o código em diferentes arquivos e diretórios, seguindo um padrão como o MVC (Model-View-Controller):

-   **`config/database.php`**: Conteria a lógica de conexão com o banco de dados.
-   **`models/DadosModel.php`**: Conteria a lógica para interagir com a tabela `dados`.
-   **`views/home.php`**: Conteria o HTML para exibir os resultados.
-   **`controllers/HomeController.php`**: Orquestraria a interação entre o Model e a View.
-   **`index.php`**: Atuaria como ponto de entrada (Front Controller), roteando as requisições.

## 3. Melhorias no Ambiente Docker (Aplicadas)

### 3.1. Otimização do `Dockerfile`

**Problema:** O `dockerfile` original utilizava uma imagem base do Nginx (`FROM nginx`), que não é ideal para uma aplicação PHP, exigindo configurações adicionais complexas.

**Solução Aplicada:** O `dockerfile` foi **atualizado** para usar uma imagem oficial do PHP com o servidor Apache (`php:8.1-apache`). Além disso, foram adicionados comandos para:
1.  Copiar os arquivos da aplicação para o diretório correto do servidor (`/var/www/html/`).
2.  Instalar a extensão `mysqli`, necessária para a comunicação com o banco de dados MySQL.
3.  Habilitar o `mod_rewrite` do Apache para futuras configurações de URL amigáveis.

**Novo `Dockerfile`:**
```dockerfile
FROM php:8.1-apache

# Copia o código da aplicação para o diretório do servidor web
COPY . /var/www/html/

# Instala a extensão mysqli necessária e habilita o mod_rewrite do Apache
RUN docker-php-ext-install mysqli && docker-php-ext-enable mysqli
RUN a2enmod rewrite
```

### 3.2. Orquestração com `docker-compose`

**Problema:** Gerenciar e conectar múltiplos containers (servidor web, banco de dados) manualmente é complexo e propenso a erros.

**Solução Aplicada:** Foi criado o arquivo `docker-compose.yml` para definir e gerenciar a aplicação como um conjunto de serviços interligados. Este arquivo automatiza a criação e configuração do ambiente.

-   **Serviço `web`**: Constrói a imagem a partir do `Dockerfile`, expõe a aplicação na porta `8080` e injeta as variáveis de ambiente do banco de dados.
-   **Serviço `db`**: Inicia um container MySQL, define as credenciais e utiliza o `banco.sql` para inicializar a estrutura da tabela.

**Arquivo `docker-compose.yml` criado:**
```yaml
version: '3.8'

services:
  web:
    build: .
    ports:
      - "8080:80"
    environment:
      - DB_HOST=db
      - DB_USER=root
      - DB_PASSWORD=Senha123
      - DB_NAME=meubanco
    depends_on:
      - db

  db:
    image: mysql:8.0
    restart: always
    environment:
      MYSQL_ROOT_PASSWORD: Senha123
      MYSQL_DATABASE: meubanco
    volumes:
      # Monta o script SQL para inicializar o banco de dados na criação do container
      - ./banco.sql:/docker-entrypoint-initdb.d/init.sql
```

## 4. Como Executar o Projeto

Com as melhorias aplicadas, o projeto pode ser iniciado com um único comando. No terminal, na raiz do projeto, execute:

```bash
docker-compose up --build
```

Após a execução, a aplicação estará disponível no endereço **`http://localhost:8080`**.
