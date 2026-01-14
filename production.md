untuk auto backup di database admin, Di production, tambahkan cron job:

* * * * * cd /path-to-project && php artisan schedule:run >> /dev/null 2>&1

 Scheduled Posting
* * * * * cd /path-to-your-project && php artisan schedule:run >> /dev/null 2>&1
