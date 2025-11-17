Maxman Security Website - Setup Instructions
===========================================

1. Prerequisites
----------------
- XAMPP, WAMP, or MAMP installed (for Apache, PHP, and MySQL)
- Web browser

2. Project Structure
--------------------
- index.php          (Main homepage)
- admin-dashboard.php (Admin dashboard - requires login)
- css/               (CSS files: Bootstrap, custom styles)
- js/                (JavaScript: Bootstrap, jQuery, main.js)
- img/               (Images/icons for services, hero background)
- php/               (PHP backend scripts)

3. Database Setup
-----------------
- Open phpMyAdmin (or MySQL CLI).
- Run the SQL script in php/db_setup.sql to create the database and table:

  1. Open phpMyAdmin and select the 'Import' tab, then import 'php/db_setup.sql'.
  2. Or, copy the contents of 'php/db_setup.sql' and run it in a SQL query window.

- By default, the database is named 'security_company_db'.
- The PHP script assumes MySQL user 'root' with no password. Change these in 'php/includes/dbh.inc.php' if needed.

4. PHP Backend Setup
--------------------
- Ensure your web server's document root contains the project files.
- The 'php/request_service.php' script handles form submissions and database insertion.
- Make sure the 'php/' directory is accessible by the server.

5. Running the Website Locally
------------------------------
- Start Apache and MySQL from XAMPP/WAMP/MAMP control panel.
- Place the project folder in the 'htdocs' (XAMPP) or 'www' (WAMP/MAMP) directory.
- Access the site in your browser at: http://localhost/your-folder-name/

6. Customization
----------------
- Update company info in index.php (Contact section).
- Add your own images/icons to the 'img/' folder and update the <img> tags in index.php.
- Adjust color scheme in 'css/style.css' as needed.

7. Security Notes
-----------------
- The PHP script uses prepared statements to prevent SQL injection.
- For production, set a strong MySQL password and update 'php/request_service.php' accordingly.

8. Support
----------
- For questions or issues, contact your web developer or refer to the code comments for guidance. 