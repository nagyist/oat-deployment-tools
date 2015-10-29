# Deployment-tools

##Installation
* Do `composer install` to get all dependencies
* Add config for queue connection ( see `config/autoload/local.php.dist` for sample )
* Apply DB schema `vendor/bin/doctrine-module orm:schema-tool:update --force`

##Usage
* To start workers `php public/index.php queue doctrine deploy --start`
* To automate worker management [see](https://github.com/juriansluiman/SlmQueue/blob/master/docs/7.WorkerManagement.md)
* To initiate make POST request to `/deploy` with `package_url` and `build_id` parameters
* Directory `data\build` in application root must be writable
* Build log for each package is located in `data\build\<build>\log` 
* General log in `data\log` 
