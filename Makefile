# Executables
PHP      = php
COMPOSER = composer
SYMFONY  = bin/console

serve:
	symfony server:start -d

install:
	@$(COMPOSER) install

migrate:
	$(SYMFONY) doctrine:migrations:migrate --allow-no-migration --no-interaction;

m-reset:
	$(SYMFONY) doctrine:schema:drop --full-database --force;

m-diff:
	make m-reset;
	make migrate;
	$(SYMFONY) doctrine:migrations:diff;

m-fresh:
	make m-reset;
	make sy args="doctrine:migrations:list";
	make migrate;
	make seed;

clear:
	$(SYMFONY) cache:clear
	$(SYMFONY) cache:warmup

route-list:
	$(SYMFONY) debug:router --show-controllers

seed:
	$(SYMFONY) doctrine:fixtures:load --no-interaction

sy:
	$(SYMFONY) $(args);

comp:
	$(COMPOSER) $(args);

test:
	vendor/bin/phpunit
