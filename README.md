# Task 3 — Backend Development & Database Integration

**ApexPlanet Software Pvt. Ltd. — Full Stack Web Development Internship (PHP & MySQL)**
Timeline: Days 25–36

A user management system: registration, session-based login/logout,
role-based access (user/admin), full CRUD on user records, and profile
picture upload — all backed by MySQL through prepared statements. The
UI carries over the code-editor theme from Task 2.

## Setup (XAMPP / WAMP / LAMP)

1. Copy this folder into `htdocs` (XAMPP) or your web root, e.g.
   `C:\xampp\htdocs\task3-user-management`.
2. Start Apache and MySQL from the XAMPP control panel.
3. Open **phpMyAdmin** → *Import* → select `schema.sql`.
   This creates the `apexplanet_task3` database with `roles` and `users` tables.
4. If your MySQL user/password differ from the XAMPP defaults, edit
  `config/db.php`.
5. Visit `http://localhost/task3-user-management/register.php` and
   create an account.
6. Every new account starts as role `user`. To make yourself an
   admin, run in phpMyAdmin's SQL tab:
   ```sql
   UPDATE users SET role_id = 1 WHERE username = 'your_username';
   ```
   Sign out and back in — you'll now see the `users.php` tab.

## Pages

| File               | Purpose                                             | Access        |
|--------------------|------------------------------------------------------|---------------|
| `register.php`     | Create account (hashed password, duplicate check)    | Public        |
| `login.php`        | Session-based login                                   | Public        |
| `logout.php`       | Destroys the session                                  | Logged in     |
| `dashboard.php`    | Landing page after login                              | Logged in     |
| `profile.php`      | Edit email/bio, upload profile picture                | Logged in     |
| `users_list.php`   | Read — table of all users, with search                | Admin only    |
| `users_add.php`    | Create — add a user from the admin panel               | Admin only    |
| `users_edit.php`   | Update — change email, role, or reset password         | Admin only    |
| `users_delete.php` | Delete — POST-only, confirmation popup, CSRF-checked   | Admin only    |

## Database design

`users.role_id` is a foreign key into a separate `roles` lookup table
rather than a repeated string column — normalized so role names live
in exactly one place (3NF). See `database/schema.sql` for the full
schema and an ER-style comment.

## Security

- **Prepared statements** (`mysqli::prepare` + `bind_param`) for every
  query that includes user input — no string-concatenated SQL anywhere.
- **Hashed passwords** via `password_hash()` / `password_verify()`;
  plaintext passwords are never stored.
- **Sessions** for login state, with `session_regenerate_id()` on
  successful login to prevent session fixation.
- **CSRF tokens** on every state-changing form (`includes/csrf.php`).
- **Server-side validation** on every input — required fields, email
  format via `filter_var`, minimum password length — in addition to
  the HTML5 attributes, since client-side checks alone aren't trustworthy.
- **File upload validation** on profile pictures — MIME type sniffed
  with `finfo` (not the client-supplied extension), 2MB size cap, and
  a `.htaccess` in `uploads/profile_pictures/` that blocks execution
  of any script that ends up in that folder.
- **Output escaping** via `htmlspecialchars()` everywhere user data is
  echoed into HTML.

## Deliverable checklist (per the task brief)

- [x] ER-style schema with `users` + `roles`, normalized
- [x] CRUD: add / list / update / delete users
- [x] Registration with hashed passwords
- [x] Login/logout with sessions
- [x] Role-based login (user/admin)
- [x] Prepared statements everywhere
- [x] Server-side validation on all input
- [x] Edit-profile page with picture upload (size + type validated)
- [ ] `README.md` with setup — this file
- [ ] 8-minute demo video — record after import + a walkthrough of
      register → login → profile update → admin CRUD → logout

## Note on continuity with Task 2

The visual language (window chrome, `*.php` tab bar, terminal-style
flash messages) is the same design system as the Task-2 frontend-only
login/register UI, now rendered server-side and wired to real data.
