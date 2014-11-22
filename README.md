# picdump

picdump is a simple image uploader.

# Install

## Requirements

* A webserver with PHP (for example nginx or apache)
* GIT

## Instructions

1. Clone the repo somewhere.
2. Change into the directory.
3. Install the dependencies
 1. Download composer ```curl -sS https://getcomposer.org/installer | php```
 2. Run the installer: ```php composer.phar install```
  - As you don't need a database or a mail server, you can just press return.
4. Delete the Cache ```php app/console cache:clear --env=prod --no-debug```
5. Dump the assets ```php app/console assetic:dump --env=prod --no-debug```
6. At last you need to [configure](http://symfony.com/doc/current/cookbook/configuration/web_server_configuration.html) your webserver.
