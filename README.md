# Symfony 7 Boilerplate

## Containers
- PHP 8.3.2
- PostgreSQL
- MongoDB
- RabbitMQ
- Redis

## Docker compose
```shell
docker compose up -d --build
```

## Doctrine mappings

## Database
```shell
php bin/console doctrine:schema:create
php bin/console doctrine:schema:update
```

## Fixtures

### ORM
```shell
php bin/console doctrine:fixtures:load
```

### ODM
```shell
php bin/console doctrine:mongodb:schema:create

php bin/console doctrine:mongodb:fixtures:load
```
