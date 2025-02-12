# Project Setup Instructions

### 1. Run These Commands on the CLI

```bash
touch .env
echo "DB_CONTAINER_NAME=mysql-container" >> .env
echo "DB_DATABASE=mydatabase" >> .env
echo "DB_NAME=mysql" >> .env
echo "DB_USERNAME=root" >> .env
echo "DB_PASSWORD=rootpassword" >> .env
echo "DB_HOST=mysql" >> .env
echo "DB_PORT=3306" >> .env
touch backend/.env
echo "DB_CONTAINER_NAME=mysql-container" >> backend/.env
echo "DB_DATABASE=mydatabase" >> backend/.env
echo "DB_NAME=mysql" >> backend/.env
echo "DB_USERNAME=root" >> backend/.env
echo "DB_PASSWORD=rootpassword" >> backend/.env
echo "DB_HOST=mysql" >> backend/.env
echo "DB_PORT=3306" >> backend/.env
docker-compose up -d
docker cp backend/data/data-with-null.sql mysql-container:/data-with-null.sql
docker exec -i mysql-container mysql -u root -prootpassword mysql -e "source /data-with-null.sql"
cd frontend
npm i
npm run serve
```

### 2. View the Site

View the site at the url shown in the cli
For example,

App running at:

- Local: http://localhost:8082/
- Network: http://10.0.0.116:8082/
