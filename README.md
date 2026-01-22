# Project Management Application

Full-stack project management app with PostgreSQL, PHP REST APIs, and DevExtreme frontend.

## Quick Start

### 1. Database Setup

```bash
# Run schema (update host/credentials if needed)
psql -h localhost -U postgres -d project_management -f database/schema.sql
```

### 2. Create User

```bash
php backend/scripts/create_user.php
```

Default credentials: `admin` / `admin123`

### 3. Start Servers

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

Access frontend: http://localhost:8080/index.html

## API Endpoints

Base URL: `http://localhost:8000/api`

- `POST /api/auth` - Login
- `GET /api/tasks` - List tasks
- `GET /api/tasks/{id}` - Get task
- `POST /api/tasks` - Create task
- `PUT /api/tasks/{id}` - Update task
- `DELETE /api/tasks/{id}` - Delete task
- `GET /api/projects` - List projects

All endpoints (except auth) require: `Authorization: Bearer {token}`

**Postman Collection:** http://documenter.getpostman.com/view/18317419/2sBXVkA8vY

## Project Structure

```
backend/
  api/          # REST endpoints
  classes/      # PHP classes
  config/      # Configuration
  scripts/     # Utility scripts
frontend/      # DevExtreme UI
database/      # Schema SQL
scripts/       # Deployment scripts
docs/          # Documentation
```

## Development Workflow

### Git Workflow

```bash
git checkout -b feature/task-name
git add .
git commit -m "feat: description"
git push origin feature/task-name
```

### Jira Integration

Link commits to tickets: `git commit -m "PROJ-123: description"`

See `docs/` folder for detailed workflows.

## Deployment

Run `scripts/deploy.sh` or see `docs/SETUP.md` for instructions.

## Requirements Met

✅ PostgreSQL schema with foreign keys  
✅ RESTful APIs (List, Add, Edit, Delete)  
✅ Modern PHP OOP structure  
✅ Token-based authentication  
✅ Error handling with try-catch  
✅ DevExtreme widgets (DataGrid, Form, Toolbar)  
✅ CRUD operations connected  
✅ Basic styling  
✅ Jira/Git workflow docs  
✅ Deployment script  
