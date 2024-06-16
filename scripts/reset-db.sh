#!/bin/bash

alias sail='sh $([ -f sail ] && echo sail || echo vendor/bin/sail)'

sail artisan optimize:clear
sail artisan cache:redis-flush-all
sail artisan db:wipe
sail artisan migrate
sail artisan db:seed
sail artisan firebase:download
