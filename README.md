# Pedidos-app Setup

Este guia fornece instruções para configurar e executar o projeto **Pedidos-app** utilizando contêineres Docker. 

---

## Requisitos

1. **Docker**
   - Verifique se o Docker está instalado.
2. **Docker Compose**
   - Certifique-se de que a versão do Docker Compose seja superior a **2.20**.
   - Caso necessário, atualize para garantir compatibilidade.

## Clonando o Projeto

```bash
git clone https://github.com/marcelhxc1/Pedidos-app.git
cd Pedidos-app
```

## Configuração do NewRelic

Adicione sua chave do **NewRelic** nos seguintes arquivos:

### Arquivo `newrelic-infra.yml`

Edite o arquivo e insira sua chave **NEW_RELIC_LICENSE_KEY**.

### Arquivo `.env`

Adicione a seguinte linha no arquivo:

```env
NEW_RELIC_LICENSE_KEY=sua_chave_newrelic
```

Não se esqueça de configurar suas credenciais de SMTP para que o disparo de e-mail funcione, a aplicação tem filas de processamento de dados que são disparados e-mails ao final da execução para informar atualização, criação de usuário e detalhamento dos pedidos.

### Arquivo `docker-compose.yml`

Procure pela chave `NEW_RELIC_LICENSE_KEY` e insira sua chave no valor correspondente.

## Subindo os Contêineres

Gere os contêineres com o comando:

```bash
docker-compose up --scale app=3 -d
```

Verifique os contêineres gerados:

```bash
docker ps
```

Anote o ID do contêiner **app** (ou **laravel_app**) e acesse o contêiner:

```bash
docker exec -it containerid bash
```

## Configuração no Contêiner

### Instalar Dependências

Dentro do contêiner, execute:

```bash
composer install
```

### Permissões para Pastas

```bash
sudo chown -R www-data:www-data /var/www/storage
sudo chmod -R 775 /var/www/storage
```

### Gerar Chave JWT

```bash
php artisan jwt:secret
```

### Verificar Conexão com o Banco de Dados

Acesse o shell **Tinker**:

```bash
php artisan tinker
```

No shell, execute:

```php
DB::connection()->getPdo();
```

- Conexão bem-sucedida retorna:
  ```
  PDO instance (mysql:host=127.0.0.1;dbname=seu_banco)
  ```
- Caso falhe:
  ```
  SQLSTATE[HY000] [2002] No such file or directory
  ```
  Revise as configurações de rede e os IPs dos contêineres.

### Migrar Banco de Dados

```bash
php artisan migrate
```

### Rodar Seeders

- Criar usuário administrador:

  ```bash
  php artisan db:seed --class=UsersTableSeeder
  ```

- Criar produtos iniciais:

  ```bash
  php artisan db:seed --class=ProductSeeder
  ```

### Executar Testes

```bash
php artisan test
```

### Limpar Cache

```bash
php artisan optimize
```

## Configuração de Filas (Queue Work)

### Diretamente no Terminal

```bash
php artisan queue:work
```

### Usando Supervisor

Instale o **Supervisor** no contêiner:

```bash
sudo apt-get install supervisor
```

Edite ou crie o arquivo de configuração:

```bash
sudo nano /etc/supervisor/conf.d/laravel-queue.conf
```

Adicione o bloco abaixo no arquivo:

```conf
[program:laravel-queue]
process_name=%(program_name)s
command=php /caminho/para/seu/projeto/artisan queue:work --daemon
autostart=true
autorestart=true
user=www-data
redirect_stderr=true
stdout_logfile=/var/log/laravel-queue.log
stopwaitsecs=3600
```

Ative a automação das filas com os comandos:

```bash
sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl start laravel-queue
```

---

## Postman ##

- A collection completa do postman está nos junto a pasta raiz do projeto, faça o download do aplicativo, importe a collection e estará pronto para uso.

## Stress test ##

Para estressar a aplicação e validar o consumo de recursos das maquinas foi criado o arquivo generate_requests.sh, este arquivo dispara 10000 requisições em 1 segundo e tem por objetivo estressar a aplicação e facilitar a analise via NewRelic de picos de uso da plataforma.

Para executar o arquivo siga o comando abaixo :

```bash
./generate_requests.sh
```

Após seguir essas etapas, o projeto estará configurado e em execução.
