# Order Travel

## 🚀 Configuração do Projeto

### Pré-requisitos
- Docker
- Docker Compose

### Passos para instalação

1. Abra o diretório do projeto:
   ```bash
   cd order-travel
   ```

2. Suba os containers do projeto:
   ```bash
   docker-compose up -d --build
   ```

3. Copie o arquivo de exemplo `.env`:
   ```bash
   cp .env.example .env
   ```

4. Acesse o container do Laravel:
   ```bash
   docker exec -it laravel bash
   ```

5. Instale as dependências do PHP:
   ```bash
   composer install
   ```

6. Gere as chaves da aplicação e o JWT secret:
   ```bash
   php artisan key:generate
   php artisan jwt:secret
   php artisan l5-swagger:generate
   ```

7. Configure o arquivo `.env`:
    - Descomente as informações do banco de dados e adicione:
      ```env
      DB_CONNECTION=mysql
      DB_HOST=mysql
      DB_PORT=3306
      DB_DATABASE=laravel
      DB_USERNAME=laravel
      DB_PASSWORD=secret
      ```

    - Caso seja necessário o disparo de e-mails, altere no `.env`:
      ```env
      MAIL_ACTIVE=true
      MAIL_MAILER=log
      MAIL_SCHEME=null
      MAIL_HOST=127.0.0.1
      MAIL_PORT=2525
      MAIL_USERNAME=null
      MAIL_PASSWORD=null
      MAIL_FROM_ADDRESS="hello@example.com"
      MAIL_FROM_NAME="${APP_NAME}"
      ```

8. Rode os comandos de otimização e banco de dados:
   ```bash
   composer dump
   php artisan optimize
   php artisan migrate
   php artisan db:seed
   ```

9. Corrija as permissões da pasta `storage/` dentro do container:
   ```bash
   chown -R www-data:www-data storage/
   ```

---

## 📖 Documentação da API

Após subir o projeto, acesse a documentação da API gerada pelo Swagger em:
```
http://localhost:8080/api/documentation
```

---

## 🛠️ Comandos úteis

- Subir containers:
  ```bash
  docker-compose up -d --build
  ```

- Acessar container Laravel:
  ```bash
  docker exec -it laravel bash
  ```

- Derrubar containers:
  ```bash
  docker-compose down
  ```
