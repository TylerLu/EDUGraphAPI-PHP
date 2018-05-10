:: 1. Deploy WebJobs
xcopy /y/s "%DEPLOYMENT_SOURCE%\webjobs" "%DEPLOYMENT_TARGET%\App_Data\jobs\triggered\"

:: 2. Create database 
php artisan make:migration
