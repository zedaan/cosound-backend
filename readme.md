# What is needed to setup?

1. PHP >=7.1 with extension requirements given as in Laravel's documentation.
2. Node & NPM LTS Stable Release.
3. Composer Stable Release.
4. MySQL.

# How you can install? 

```sh
> composer install;
> cp -fR .env.example .env;
> chmod -fR 777 bootstrap/ storage/;
> php artisan migrate;
> npm install;
```

# How to run the app?

```sh
php artisan serve
```

# How can you see Web routes?

```sh
php artisan route:list
```

# How can you see API routes?

```sh
php artisan api:route
```