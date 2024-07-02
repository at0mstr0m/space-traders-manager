#!/bin/bash

alias sail='sh $([ -f sail ] && echo sail || echo vendor/bin/sail)'

sail artisan down

sail artisan horizon:terminate --wait

git checkout main
git pull

# update composer packages
sail composer i --no-interaction --no-dev --optimize-autoloader

sail artisan firebase:purge --no-interaction
sail artisan cache:redis-flush-all

sail artisan optimize:clear
sail artisan db:wipe --force
sail artisan migrate --force
sail artisan db:seed --force
sail artisan dispatch-fetch-systems
sail artisan queue:restart

# build frontend
sail bun i --production
sail bun run build

sail artisan up