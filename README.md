# Proxy Bundle

## Installation
**Composer**
```bash
composer config repositories.get-repo/proxy-bundle git https://github.com/get-repo/proxy-bundle
composer require get-repo/proxy-bundle
```
**Update your `./app/AppKernel.php`**
```php
$bundles = [
    ...
    new GetRepo\ProxyBundle\ProxyBundle(),
    ...
];
```
or with
```bash
php -r "file_put_contents('./app/AppKernel.php', str_replace('];', \"    new GetRepo\ProxyBundle\ProxyBundle(),\n        ];\", file_get_contents('./app/AppKernel.php')));"
```
