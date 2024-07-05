#!/bin/bash

alias sail='sh $([ -f sail ] && echo sail || echo vendor/bin/sail)'

sail artisan down

sail artisan horizon:terminate --wait

git checkout main
git pull

# update composer packages
sail composer i --no-interaction --no-dev --optimize-autoloader
sail artisan migrate --force
sail artisan optimize:clear
sail artisan firebase:download
sail artisan queue:restart

# build frontend
sail bun i --production
sail bun run build

# trigger tasks
sail artisan trigger-tasks

sail artisan up