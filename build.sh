#!/usr/bin/env bash
php bin/console last:dump --force-override
php bin/console presta:sitemaps:dump --base-url=http://webconsulenza.com
php bin/console app:assets-copy