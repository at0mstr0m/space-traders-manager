sail artisan optimize:clear && sail artisan cache:redis-flush-all && sail artisan db:wipe && sail artisan migrate && sail artisan db:seed
