#!/bin/bash

# Create .env file in the root directory
touch .env
echo "DB_CONTAINER_NAME=mysql-container" >> .env
echo "DB_DATABASE=mydatabase" >> .env
echo "DB_NAME=mysql" >> .env
echo "DB_USERNAME=root" >> .env
echo "DB_PASSWORD=rootpassword" >> .env
echo "DB_HOST=mysql" >> .env
echo "DB_PORT=3306" >> .env

# Create .env file in the backend directory
touch backend/.env
echo "DB_CONTAINER_NAME=mysql-container" >> backend/.env
echo "DB_DATABASE=mydatabase" >> backend/.env
echo "DB_NAME=mysql" >> backend/.env
echo "DB_USERNAME=root" >> backend/.env
echo "DB_PASSWORD=rootpassword" >> backend/.env
echo "DB_HOST=mysql" >> backend/.env
echo "DB_PORT=3306" >> backend/.env
sleep 1

# Start Docker containers in detached mode
docker-compose up -d
echo "Waiting for MySQL container to be ready..."
until docker exec mysql-container mysqladmin -u root -prootpassword ping --silent; do
    sleep 2
done
echo "MySQL container is ready!"
sleep 3

# Copy the SQL file into the MySQL container
docker cp backend/data/data-with-null.sql mysql-container:/data-with-null.sql
echo "Waiting for SQL file to be copied into the container..."
until docker exec mysql-container test -f /data-with-null.sql; do
    sleep 2
done
echo "SQL file successfully copied to the container."
sleep 3

# Run the SQL script inside the container
docker exec -i mysql-container mysql -u root -prootpassword mysql -e "source /data-with-null.sql"
echo "Waiting for SQL script execution to complete..."
until docker logs mysql-container 2>&1 | grep -q "Query OK"; do
    sleep 2
done
echo "SQL script executed successfully."

# Install dependencies and start the frontend
cd frontend
npm i
npm run serve
