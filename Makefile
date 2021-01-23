include .env
export

.PHONY: up down start stop prune shell node

default: up

.PHONY: up
up: start
start:
	@echo "Starting up containers for $(PROJECT_NAME)..."
	docker-compose pull
	docker-compose up -d --remove-orphans

.PHONY: down
down: stop
stop:
	@echo "Stopping containers for $(PROJECT_NAME)..."
	@docker-compose stop

.PHONY: prune
prune:
	@echo "Removing containers for $(PROJECT_NAME)..."
	@docker-compose down -v

.PHONY: shell
shell:
	docker exec -ti -e COLUMNS=$(shell tput cols) -e LINES=$(shell tput lines) -u application $(shell docker ps --filter name='$(PROJECT_NAME)_php' --format "{{ .ID }}") bash

.PHONY: node
node:
	docker exec -ti -e COLUMNS=$(shell tput cols) -e LINES=$(shell tput lines) -u node $(shell docker ps --filter name='$(PROJECT_NAME)_node_yarn' --format "{{ .ID }}") bash


# https://stackoverflow.com/a/6273809/1826109
%:
	@: