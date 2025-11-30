web: php artisan optimize && php artisan serve --host=0.0.0.0 --port=$PORT
worker: php artisan queue:listen database --tries=3 --timeout=90 --sleep=3 --max-jobs=1000
scheduler: while true; do php artisan schedule:run --verbose --no-interaction & sleep 60; done
