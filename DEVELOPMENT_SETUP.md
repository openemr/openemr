# OpenEMR Local Development Setup

Step-by-step guide to get a fully running OpenEMR instance with dummy patient data locally, using Docker.

---

## Prerequisites

Install these before anything else:

- **Docker Desktop** — [https://www.docker.com/products/docker-desktop](https://www.docker.com/products/docker-desktop)  
  Make sure Docker Desktop is running (menu bar icon) before proceeding.
- **Git** — ships with Xcode Command Line Tools (`xcode-select --install`)

---

## 1. Clone the Repo

```bash
git clone https://github.com/openemr/openemr.git
cd openemr
```

Or if you already have it locally, just `cd` into the root of the project.

---

## 2. Start the Docker Environment

```bash
cd docker/development-easy
docker compose up --detach --wait
```

`--wait` blocks until all containers pass their health checks — this takes **2–4 minutes** on first run (it installs dependencies inside the container). Subsequent starts are much faster.

To confirm everything is healthy:

```bash
docker compose ps
```

All services should show `Up` or `healthy`. You should see: `openemr`, `mysql`, `selenium`, `phpmyadmin`, `couchdb`, `openldap`, `mailpit`.

---

## 3. Load Dummy Patient Data

OpenEMR ships with two SQL seed files. Load the provider users first (patients reference them), then the patients.

```bash
# Step 1 — provider users
docker compose exec openemr bash -c \
  "mysql -u root -proot openemr < /var/www/localhost/htdocs/openemr/sql/example_patient_users.sql"

# Step 2 — 14 example patients
docker compose exec openemr bash -c \
  "mysql -u root -proot openemr < /var/www/localhost/htdocs/openemr/sql/example_patient_data.sql"
```

The "Deprecated program name" warning from MariaDB is harmless — the commands succeeded.

Verify patients loaded:

```bash
docker compose exec openemr bash -c \
  "mysql -u root -proot openemr -e 'SELECT pid, fname, lname, DOB FROM patient_data ORDER BY pid;'"
```

You should see 14 rows (Farrah Rolle, Ted Shaw, Eduardo Perez, Nora Cohen, and 10 others).

---

## 4. Access the Application

| Service | URL | Credentials |
|---|---|---|
| OpenEMR app (HTTP) | [http://localhost:8300](http://localhost:8300) | `admin` / `pass` |
| OpenEMR app (HTTPS) | [https://localhost:9300](https://localhost:9300) | `admin` / `pass` |
| phpMyAdmin (database UI) | [http://localhost:8310](http://localhost:8310) | server: `mysql`, user: `root`, pass: `root` |
| Mailpit (catch-all email) | [http://localhost:8025](http://localhost:8025) | none |
| Selenium (browser testing) | [http://localhost:4444](http://localhost:4444) | none |
| MariaDB (direct TCP) | `localhost:8320` | user: `root`, pass: `root`, db: `openemr` |

> **Note on HTTPS:** The container uses a self-signed certificate, so browsers will show a "Connection Is Not Private" warning on port 9300. Use HTTP on port 8300 for day-to-day development. If you need HTTPS (e.g. for OAuth), click **Show Details** → **visit this website** (Safari) or type `thisisunsafe` on the page (Chrome) to add a one-time exception.

---

## 5. Browse the Dummy Patients

1. Open [http://localhost:8300](http://localhost:8300) and log in with `admin` / `pass`.
2. In the top navigation bar click **Patient** → **New/Search**.
3. Leave the search box empty and click the blue **Search** button (checkmark icon).
4. A modal lists all 14 patients — you should see **1 - 14 of 14** in the top-right corner.
5. Click any row to open that patient's full chart.

---

## 6. View PHP Logs

```bash
docker compose exec openemr /root/devtools php-log
```

---

## 7. Stop the Environment

```bash
# Stop containers but keep data
docker compose stop

# Restart later
docker compose start
```

---

## 8. Full Reset (wipe all data and start fresh)

This destroys the database volume — you will need to re-run the seed SQL in Step 3.

```bash
docker compose down --volumes
docker compose up --detach --wait
```

Then re-run the two seed commands from Step 3.

---

## 9. Common Issues

**"Workspace still starting" / containers not healthy**  
Wait another minute and re-run `docker compose ps`. First boot is slow.

**Duplicate key error when loading patient SQL**  
You already have patients with those IDs. Either do a full reset (Step 7) first, or load with `INSERT IGNORE`:

```bash
docker compose exec openemr bash -c \
  "sed 's/INSERT INTO/INSERT IGNORE INTO/g' /var/www/localhost/htdocs/openemr/sql/example_patient_data.sql | mysql -u root -proot openemr"
```

**Port already in use**  
Another process is on 8300/8310/etc. You can override ports with environment variables before `docker compose up`:

```bash
WT_HTTP_PORT=8400 WT_PMA_PORT=8410 docker compose up --detach --wait
```

**VPN / network issues**  
If containers fail to pull images, disable your VPN, run `docker compose down --volumes`, then retry from Step 2.

---

## Port Reference

| Variable | Default Port | Service |
|---|---|---|
| `WT_HTTP_PORT` | 8300 | OpenEMR HTTP |
| `WT_HTTPS_PORT` | 9300 | OpenEMR HTTPS |
| `WT_PMA_PORT` | 8310 | phpMyAdmin |
| `WT_MYSQL_PORT` | 8320 | MariaDB (TCP) |
| `WT_SELENIUM_PORT` | 4444 | Selenium Grid |
| `WT_VNC_PORT` | 7900 | Selenium VNC |
| `WT_MAILPIT_UI_PORT` | 8025 | Mailpit web UI |
| `WT_MAILPIT_SMTP_PORT` | 1025 | Mailpit SMTP |
| `WT_COUCHDB_PORT` | 5984 | CouchDB HTTP |
| `WT_COUCHDB_SSL_PORT` | 6984 | CouchDB HTTPS |
