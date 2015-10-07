# piTitle

A small raspberry-pi oriented project to display images on HDMI output through a webinterface.

**WARNING :** This app must be used on a private lan and shouldn't be exposed on the internet as it requires privileges escalation 
to be useful. I can't find, atm, a simple way to use fbi from www-data user. If you know how, drop me a line !

## Setup
Setup has been tested on a raspberry pi 2 and a B+ one.

### Requisites

* lighttpd 
* PHP 5.4+
* php_gd module
* Composer
* fbi (may work with fim)

### fbi privileged access
In order to display images on HDMI output, fbi must be run with privileged access. Lots of tutorials exist on giving passwordless sudo to execute commands, check them.

### Installation

Clone the repo anywhere on your PI, run `composer install` to install dependencies.

The `web` folder should be set as document root in lighttpd. 

### Configuration

In `config` folder, copy file config.json.dist to config.json. Edit the file as you need.


