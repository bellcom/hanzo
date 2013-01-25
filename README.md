# Hanzo - PDL shop v2


## Requirements:

First off, the same requirements as [symfony2](http://symfony.com/doc/2.0/reference/requirements.html), besides this, we have these addons:

1. [redis](http://redis.io/) must be installed and working
2. [cdn](https://github.com/bellcom/hanzo) must be setup (this is the same repos as hanzo see docs/vhost.cdn.conf)
3. Java must be installed (to compile compressed js and css files - we use yuicompressor)
4. Apache must be setup with mod_rewrite
5. Apc for php is also a must-have module.
6. Compass and Sass [compass](http://compass-style.org/install/)
7. [Uglifyjs](https://github.com/mishoo/UglifyJS2)

soon to come:

1. [phpredis](https://github.com/nicolasff/phpredis) nativec extension til at kører sessions i redis, kræver også [NativeSession](https://github.com/drak/NativeSession) til symfony.


## Install:

1. `git clone git@github.com:bellcom/hanzo.git`
2. `cd hanzo`
3. Copy app/config/parameters.ini.dist to app/config/parameters.ini
  1. Change any settings necessary to connect to your database
4. Copy app/config/hanzo.yml.dist to app/config/hanzo.yml
  1. Change cdn and/or other settings
5. `mysql -u xxx -p yyy hanzo < app/propel/sql/default.sql`
6. add fixture data, here we have a "clean" and a "full" version, the full version includes testdata.
  1. `mysql -u xxx -p yyy hanzo < app/propel/fixtures/fixtures.sql`
  2. `mysql -u xxx -p yyy hanzo < app/propel/fixtures/fixtures_all.sql`
7. `php bin/vendors install`
8. Setup apache, see `dosc/vhost.conf` for an example


## Configuration:

To access a dev page, the structure is as follows, {locale} is one of the configured locales: `http://yourdomain.tld/app_dev.php/{locale}` or `http://yourdomain.tld/app_test.php/{locale}` prod is never used, well - in production but else...

At the moment `da_DK`, `sv_SE`, `nb_NO`, `nl_NL` and `en_GB` is configued.


## Misc

Follow the [coding standards](http://symfony.com/doc/current/contributing/code/standards.html) as much as possible.

symfony console:

- `php app/console --help` for help on the cli interface for symfony
- clearing caches:
  - `php app/console cache:clear --env=dev_dk`
  - `php app/console cache:clear --env=test_dk`
  - `php app/console cache:clear --env=prod_dk`
- clearing redis cache:
  - `php app/console hanzo:redis:cache:clear --env=prod_dk`

Note the `env` parameter, this is important, stuff vill break if not ... oh! and run the command as the web user

redis:

- `redis-cli` is the commandline interface for handeling redis related tasks
  redis supports tab-completion
- flushing the cache: `FLUSHALL`
- find a key: `KEYS *xx*`
- help, well: `HELP` or go see the [docs](http://redis.io/documentation), they are great.

## Themes

- [Sass](http://sass-lang.com/)
- [Compass](http://compass-style.org/)

Assets for themes are located under `fx/THEME/`

###Create a new theme with compass:

1. `cd fx`
2. `compass create THEME_NAME --css-dir "css" --javascripts-dir "scripts" --images-dir "images"`

--

Styles are grouped in seperate `.scss` files. e.g. Payment styles are located in `_payment.scss` and importet in `style.scss` (@import "payment"). All sub `.scss` files which should be imported into another instead of being a independet css file, should be prepended with a `_` like `_account.scss`. This way they wont be compiled themself.

The directory of a theme will look like this when built with compass (note that compass only generates the `sass` and `css` folders):

- `fx/THEME/`
  - `css/`
     - `style.scss`
     - `ie.css`
  - `scripts/`
  - `images/`
  - `sass/`
     - `_base.scss`
     - `_header.scss`
     - `_footer.scss`
     - `ie.scss`
     - `style.scss`
  - `config.rb`

Follow the [best practices](http://compass-style.org/help/tutorials/best_practices/)

When editing Sass files be sure to build the `style.scss` file. You can make compass watch by:

1. `cd fx/[theme]/`
2. `compass watch`

--

- `_base.scss` - Includes all globale variables and @imports
- `style.scss` - Main stylesheet which includes _base and others
  - `payment.scss` - Styles for payment
  - ...

#Assetic on locale

To make all assets works locally remember to run this:

`php app/console assetic:dump --env=dev_dk`
