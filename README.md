## Introduction

This is an open source project to initialize any restful symfony project. It is like the symfony skeleton but enriched with some main libraries and functionalities needed in most restful apps. Whether you are creating an API as a backend for a mobile app or a micro-service for a single page application (SPA), this project may be a shortcut to save you time, get inspired by how things are done, or better, clone it, adapt it and start working directly on the core of your app.

## Main functionalities 
- Register a user
- Login
- Login with firebase (google, facebook...)
- Logout
- Show profile

## Tech details
- PHP 7.4 / Symfony 5.1
- Docker / Docker compose
- Make
- JWT
- Validation
- Unit tests
- Custom Normalizer
- Database migrations
- Documentation with Swagger
- PHPCs-fixer

## Getting Started

This is a guide on how to install the project, adapt and run it.
### Prerequisites

Here are the main tools you need to have in order to install the project and get it up and running:

- [Docker CE](https://www.docker.com/community-edition)
- [Docker Compose](https://docs.docker.com/compose/install)
- Openssl

If you want to run the make commands instead of running each command separately you need to install make for linux.

### Install (Mac & Linux)

- Clone the project
- Change any occurrence of `symfony-rest-api-init` in all files to the project name you want to be using (optional)
- Create your `docker-compose.override.yml` file

```bash
cp docker-compose.override.yml.dist docker-compose.override.yml
```
> Notice : Check the ports used in the docker-compose.override.yml and change them if needed (ports already used)

- Build the project and run it

```bash
docker-compose build
docker-compose up -d
```

- In order to create the public and private keys needed to manage the JWT tokens, modify the pass_phrase and token_ttl in `config/packages/lexik_jwt_authentication.yaml` then run the following command
```bash
make jwt
```

If you don't want to use make, run the following commands instead

```bash
mkdir -p config/jwt
php -r "require'vendor/autoload.php';file_put_contents('passphrase.txt',\Symfony\Component\Yaml\Yaml::parse(file_get_contents('config/packages/lexik_jwt_authentication.yaml'))['lexik_jwt_authentication']['pass_phrase']);"
openssl genpkey -out ./config/jwt/private.pem -aes256 -pass file:passphrase.txt -algorithm rsa -pkeyopt rsa_keygen_bits:4096
openssl pkey -in ./config/jwt/private.pem -passin file:passphrase.txt -out config/jwt/public.pem -pubout
rm -f passphrase.txt
chown -R www-data:www-data ./config/jwt
```

- Fix the permissions by running the following

```bash
sudo chmod -R 777 ./var
```

> The config/jwt folder should remain secured but for development purpose where you need to rebuild the project for example you can change its permission too or delete it manually 

- Prepare the database, the following command will delete the database if it exists, recreate it and run the migrations

```bash
make db-reset
```

### Firebase

If you will be using firebase auth you need to uncomment the following line in `config/packages/firebase.yaml`
```yaml
      # credentials: '%kernel.project_dir%/config/firebase/service-account.json'
```

To use the firebase authentication, you need to create a firebase project and import the configuration in this project. Check the following to know how to do it:
- [Documentation](https://firebase.google.com/docs/admin/setup#initialize-sdk)
- Youtube tutorial (coming soon)

Download the service account configuration file, rename it to `service-account.json` and put it in `config/firebase`

The following files are included in the project for test purpose to allow you to get a firebase token:
 - Controller/TestController.php
 - templates/oauth.html.twig
 Once the firebase login tested successfully don't forget to remove that.

Now you need to modify `templates/oauth.html.twig` to your own firebase project configuration. 
To do so, get the firebase configuration from your project settings and replace the existent `firebaseConfig` in  `templates/oauth.html.twig` line 40

Now go to http://localhost:8080/oauth and sign in, then the firebase token will be displayed, copy and use it with the firebase login route.

#### Run the unit tests(optional)

```bash
make unit-test
```

To see the unit tests coverage report run the following command

```bash
make unit-test-coverage
```

The coverage report will be accessible at `public/coverage/index.html`

### Usage

API root URL : [http://localhost:8080](http://localhost:8080)

PhpMyAdmin : [http://localhost:8082](http://localhost:8082)

Swagger documentation URL : [http://localhost:8080/api/doc/v1](http://localhost:8080/api/doc/v1)

Access to a container : `make bash`

### Contact
```php
if( $youHaveAnyQuestion || $youHaveAnySuggestion ){
    $mailer->sendMessageTo('azaiez.nafaa@gmail.com');
}
```
