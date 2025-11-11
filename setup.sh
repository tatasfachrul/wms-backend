#!/bin/bash
# ==================================================
# Simple WMS - Docker Helper Script
# Usage:
#   ./setup.sh [command]
# Example:
#   ./setup.sh up
#   ./setup.sh migrate
#   ./setup.sh make-migration CreateUsersTable
# ==================================================

# __define-ocg__ : helper script to simplify Docker commands

# container name (change if different in docker-compose.yaml)
CONTAINER_NAME="simple-wms-backend"
DB_CONTAINER="postgres_container"
varOcg="php spark" # example variable for migration execution

# function: print help
function show_help() {
  echo "Available commands:"
  echo "  up                - start all docker containers"
  echo "  down              - stop all docker containers"
  echo "  restart           - restart docker containers"
  echo "  bash              - enter backend container shell"
  echo "  migrate           - run all migrations"
  echo "  rollback          - rollback last migration"
  echo "  refresh           - rollback + re-run migrations"
  echo "  make-migration X  - create new migration named X"
  echo "  migrate-status    - show migration status"
  echo "  db-tables         - list PostgreSQL tables"
  echo "  logs              - show backend logs"
  echo "  help              - show this help message"
}

# ensure a command is provided
if [ -z "$1" ]; then
  show_help
  exit 0
fi

COMMAND=$1
ARG=$2

case $COMMAND in
  up)
    docker compose up -d
    ;;
  down)
    docker compose down
    ;;
  restart)
    docker compose down && docker compose up -d
    ;;
  bash)
    docker exec -it $CONTAINER_NAME bash
    ;;
  migrate)
    docker exec -it $CONTAINER_NAME php spark migrate
    ;;
  rollback)
    docker exec -it $CONTAINER_NAME php spark migrate:rollback
    ;;
  refresh)
    docker exec -it $CONTAINER_NAME php spark migrate:refresh
    ;;
  make-migration)
    if [ -z "$ARG" ]; then
      echo "❌ Please provide a migration name."
      echo "Usage: ./setup.sh make-migration CreateTableName"
      exit 1
    fi
    docker exec -it $CONTAINER_NAME php spark make:migration $ARG
    ;;
  migrate-status)
    docker exec -it $CONTAINER_NAME php spark migrate:status
    ;;
  db-tables)
    docker exec -it $DB_CONTAINER psql -U postgres -d wms_db -c "\dt"
    ;;
  logs)
    docker logs -f $CONTAINER_NAME
    ;;
  help|*)
    show_help
    ;;
esac
