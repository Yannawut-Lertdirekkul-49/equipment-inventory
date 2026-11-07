# Equipment Inventory

A small web application for keeping track of equipment in rooms (air conditioners, computers, TVs, power outlets, and so on), with user login and role-based permissions. Built as a self-study project to practice full stack basics: an HTML front end, a PHP back end, and a MySQL database.

## Screenshots

| Login | Main page | Edit page |
|---|---|---|
| ![Login page](screenshots/login.png) | ![Main page](screenshots/main.png) | ![Edit page](screenshots/edit.png) |

## Features

- Login and logout with session handling
- Three roles with different permissions (see below)
- View all equipment in a table with its room, type, and quantity
- Add, edit, and delete equipment (delete asks for confirmation)
- Add new rooms (empty and duplicate names are rejected)
- Server-side input validation (required room name, quantity must not be negative)

## Roles and Permissions

| Role | View | Add / edit equipment | Delete equipment | Add rooms |
|---|:---:|:---:|:---:|:---:|
| Guest | yes | no | no | no |
| Staff | yes | yes | no | no |
| Admin | yes | yes | yes | yes |

Permissions are checked on the server for every action, and unavailable buttons are also hidden in the interface.

## Tech Stack

| Layer | Technology |
|---|---|
| Front end | HTML forms, basic CSS |
| Back end | PHP (mysqli, sessions) |
| Database | MySQL / MariaDB |
| Local server | XAMPP (Apache + MySQL) |

## Database Design

Three tables:

- `rooms` (`id`, `name` unique, `floor`)
- `equipment` (`id`, `room_id`, `type`, `quantity`), where `room_id` is a foreign key to `rooms.id`
- `users` (`username`, `password` as a bcrypt hash, `role`)

The full structure and demo data are in `schema.sql`.

## Security Practices

- **Prepared statements** for every query that uses user input, to prevent SQL injection
- **`htmlspecialchars`** when printing database values into the page, to prevent XSS
- **Password hashing** with `password_hash` and `password_verify` (bcrypt), so plain-text passwords are never stored
- **Session handling:** `session_regenerate_id` after login, and pages redirect to the login page when no session exists
- **Role checks on the server**, so permissions cannot be bypassed by sending requests directly
- **Server-side validation**, so rules still apply if the browser checks are bypassed
- **Redirect after POST**, so refreshing the page does not resubmit a form

## How to Run Locally

1. Install [XAMPP](https://www.apachefriends.org/) and start **Apache** and **MySQL** in the XAMPP Control Panel.
2. Copy this folder into `C:\xampp\htdocs\` (for example `C:\xampp\htdocs\equipment-app`).
3. Open `http://localhost/phpmyadmin`, go to the **Import** tab, choose `schema.sql`, and run it. This creates the `equipment_db` database, its tables, and demo data.
4. Open `db.php` and check the connection settings. The port is set to `3307`; change it to `3306` if your MySQL uses the default port.
5. Open `http://localhost/equipment-app/login.php` in your browser and sign in with a demo account.

### Demo Accounts

| Username | Password | Role |
|---|---|---|
| `Admin` | `Admin123` | Admin |
| `S1` | `asd` | Staff |
| `UserInwSa` | `EpicGamerInwSa007` | Guest |

> These accounts and passwords are for local testing only. `db.php` also uses the XAMPP default `root` user with an empty password. Never use these settings on a real server.

## Project Structure

```
equipment-app/
├── login.php     # login page
├── logout.php    # ends the session
├── hello.php     # main page: list, add equipment, add room, delete
├── edit.php      # edit page for one item
├── db.php        # database connection and the list of equipment types
├── schema.sql    # database structure and demo data
├── screenshots/  # images used in this README
└── README.md
```

## Known Limitations

- No CSRF protection on forms
- No limit on repeated failed login attempts
- No page for creating or managing users (accounts are added through SQL)
- Meant for local learning, not production use

## What I Learned

- How a browser, a PHP server, and a database work together in a CRUD application
- Writing SQL queries with joins and connecting them to PHP safely
- Basic web security: SQL injection, XSS, password hashing, sessions, and checking permissions on the server
- Debugging form handling (GET vs POST, duplicate submissions, hidden fields, redirect loops)

## Ideas for Improvement

- Replace the room text field with a dropdown loaded from the database
- Show rooms that have no equipment yet (using a `LEFT JOIN`)
- Add CSRF tokens and a login attempt limit
- Add a page for managing users and changing passwords
