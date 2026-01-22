# Setup & Development Guide

## Database Setup

Run the schema on your PostgreSQL server:

```bash
psql -h localhost -U postgres -d project_management -f database/schema.sql
```

Update `backend/config/database.php` with your credentials if different.

## Create User

```bash
php backend/scripts/create_user.php
```

Default: `admin` / `admin123`

## Start Development Servers

**Backend (Terminal 1):**
```bash
cd backend
php -S localhost:8000
```

**Frontend (Terminal 2):**
```bash
cd frontend
php -S localhost:8080
```

Frontend: http://localhost:8080/index.html

## API Testing

**Postman Collection:** http://documenter.getpostman.com/view/18317419/2sBXVkA8vY

Or use curl:

```bash
# Login
curl -X POST http://localhost:8000/api/auth \
  -H "Content-Type: application/json" \
  -d '{"username":"admin","password":"admin123"}'

# Get tasks (replace YOUR_TOKEN)
curl -X GET http://localhost:8000/api/tasks \
  -H "Authorization: Bearer YOUR_TOKEN"
```

## Git Workflow

```bash
git checkout -b feature/task-name
git add .
git commit -m "feat: description"
git push origin feature/task-name
```

Link to Jira: `git commit -m "PROJ-123: description"`

## Deployment

Edit `scripts/deploy.sh` with your server details, then run:

```bash
./scripts/deploy.sh
```
