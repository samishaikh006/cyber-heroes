# Cyber Security Comic Book — CEP Project

PHP + MySQL starter architecture designed for local XAMPP development and later deployment.

## Local setup
1. Copy this folder to `C:/xampp/htdocs/`.
2. Start Apache and MySQL in XAMPP.
3. Open phpMyAdmin and run `sql/schema.sql`.
4. Open `http://localhost/cyber_security_comic_v1/`.

## Database settings
The local defaults are:
- DB_HOST=127.0.0.1
- DB_PORT=3306
- DB_NAME=cyber_comic
- DB_USER=root
- DB_PASSWORD=

Production credentials should be provided as environment variables; do not commit secrets.

## Build order
1. Foundation + index
2. Comic page/image reader + zoom/fullscreen + keyboard navigation
3. English/Hindi story pages
4. Story-specific quizzes
5. Video module
6. Admin panel
7. Render deployment configuration
