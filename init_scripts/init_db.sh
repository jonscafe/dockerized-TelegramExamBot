#!/bin/bash
# init_db.sh

# Wait for MySQL to be ready before executing the SQL script
echo "Waiting for MySQL to be ready..."
until mysql -h db -u root -p$MYSQL_ROOT_PASSWORD -e "SELECT 1;" &>/dev/null; do
  echo "Waiting for MySQL..."
  sleep 2
done

# Execute the SQL file to populate the database
echo "Initializing database with quizbot.sql"
mysql -h db -u root -p$MYSQL_ROOT_PASSWORD quiz_os < /docker-entrypoint-initdb.d/quizbot.sql

echo "Database initialized!"
