rm var/log/* -r &&
rm var/cache/* -r &&
rm migrations/*.php -r &&
symfony console doctrine:database:drop --force &&
symfony console doctrine:database:create &&
symfony console make:migration &&
symfony console doctrine:migrations:migrate --no-interaction