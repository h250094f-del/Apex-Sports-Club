# Deploying Apex Sports Club on InfinityFree

This guide provides step-by-step instructions to deploy the Apex Sports Club website and MySQL database on **InfinityFree** (Free Web Hosting).

---

## 1. Create Your Free Account & Website on InfinityFree

1. Go to [InfinityFree](https://www.infinityfree.com/) and register or sign in.
2. In the Client Area, click **+ Create Account**.
3. Choose a free subdomain (for example: `apexsports.infinityfreeapp.com` or `apexclub.epizy.com`).
4. Complete the setup. InfinityFree will create your account and provision your Control Panel (cPanel / vPanel).

---

## 2. Set Up the MySQL Database & Import Data

1. In your InfinityFree Client Area, click **Manage** on your account, then open the **Control Panel** (vPanel).
2. Look for the **Databases** section and click **MySQL Databases**.
3. Under **Create a New Database**, enter a name (e.g. `apex` or `sports`) and click **Create Database**.
4. Take note of the credentials displayed on the MySQL Databases page:
   - **MySQL Hostname**: (e.g. `sql123.infinityfree.com` or `sql200.epizy.com`)
   - **MySQL Database Name**: (e.g. `epiz_12345678_apex`)
   - **MySQL Username**: (e.g. `epiz_12345678`)
   - **MySQL Password**: (Your vPanel account password found in Client Area under Account Details)
   - **MySQL Port**: `3306`

### Import `database-import.sql`:
1. In the Control Panel, click **phpMyAdmin** next to your newly created database.
2. phpMyAdmin will open in a new tab with your database selected.
3. Click the **Import** tab at the top.
4. Click **Choose File** and select [`database-import.sql`](file:///c:/Users/DELL/Desktop/Apex%20Sports%20Club/database-import.sql) from this project folder.
5. Click **Import** (or **Go**) at the bottom.
6. The `members` and `admins` tables will be created, and the default admin user and initial sample records will be imported.

---

## 3. Update `config.php` with Your InfinityFree Credentials

Open [`config.php`](file:///c:/Users/DELL/Desktop/Apex%20Sports%20Club/config.php) and update lines 14–17 with the values from Step 2:

```php
define('DB_HOST', 'sql123.infinityfree.com'); // Your MySQL Hostname
define('DB_NAME', 'epiz_12345678_apex');       // Your Database Name
define('DB_USER', 'epiz_12345678');            // Your MySQL Username
define('DB_PASS', 'YourAccountPassword');      // Your Account Password
```

---

## 4. Upload Files to InfinityFree via File Manager or FTP

You can upload your files using either the **Monsta Online File Manager** or **FileZilla (FTP)**.

### Option A: Using the Online File Manager
1. In the InfinityFree Client Area / Control Panel, click **Online File Manager**.
2. Open the **`htdocs/`** directory.
   *(Important: Do NOT upload files outside `htdocs/`. Delete any default placeholder `index2.html` if present).*
3. Upload all project files and folders into `htdocs/`:
   - `index.html`
   - `about.html`
   - `activities.html`
   - `gallery.html`
   - `join.html`
   - `process.php`
   - `config.php`
   - `admin.php`
   - `admin-login.php`
   - `style.css`
   - `main.js`
   - `assets/` folder (with all images)

### Option B: Using FileZilla (FTP)
1. In Account Details, get your FTP Hostname (`ftpupload.net`), FTP Username, and FTP Password.
2. Connect using FileZilla on port `21`.
3. Open `/htdocs/` and upload all site files directly into it.

---

## 5. Verify the Working Deployment

1. Visit your public website URL (e.g. `http://apexsports.infinityfreeapp.com`).
2. Test navigation across all 5 pages:
   - **Home** (`index.html`)
   - **About Us** (`about.html`)
   - **Activities / Events** (`activities.html`)
   - **Gallery** (`gallery.html`)
   - **Join Us** (`join.html`)
3. **Submit a test membership application** on `join.html`:
   - Ensure the client-side JavaScript validation works.
   - Click submit to run `process.php`.
   - Verify that the receipt page displays your submitted data and confirms database storage.
4. **Log in to the Admin Portal**:
   - Go to `admin-login.php` (or click *Staff & Admin Portal* in the footer).
   - Log in with:
     - **Username**: `admin`
     - **Password**: `Password123!`
   - Verify that your submitted application appears in the table with search, filter, status change, and delete functions.
