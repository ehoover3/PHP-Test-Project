# Project Setup Instructions

To run the project, please follow these steps:

### 1. Add `.env` Variables

Please add the following `.env` variables to **both the root and backend folders**. This is necessary due to pathing issues I encountered with `.env` files in Docker. If I had more time, I would refactor this to avoid duplication.

```bash
DB_CONTAINER_NAME=mysql-container
DB_DATABASE=mydatabase
DB_NAME=mysql
DB_USERNAME=root
DB_PASSWORD=rootpassword
DB_HOST=mysql
DB_PORT=3306

BACKEND_CONTAINER=php-container
```

### 2. Set Up the Project Using CLI Commands

Run the following commands to set up the Docker containers and load the database:

```bash
docker-compose up -d
docker cp backend/data/data-with-null.sql mysql-container:/data-with-null.sql
docker exec -i mysql-container mysql -u root -prootpassword mysql -e "source /data-with-null.sql"
```

### 3. Run the Project

Once the setup is complete, visit the following URLs to run the project:

- http://localhost:8081/updateCommentsInBatchProcess.php
- http://127.0.0.1:5500/frontend/index.html
