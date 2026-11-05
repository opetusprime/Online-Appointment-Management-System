ONLINE APPOINTMENT MANAGEMENT SYSTEM
====================================

Requirements:
- XAMPP
- Apache
- MySQL
- PHP 8.x

INSTALLATION
------------

1. Copy the "online-appointment" folder into:
   C:\xampp\htdocs\

2. Start Apache and MySQL in XAMPP.

3. Open phpMyAdmin:
   http://localhost/phpmyadmin

4. Open the SQL tab and import/run:
   database.sql

5. Open:
   http://localhost/online-appointment/create_admin.php

6. The default admin login is:
   Username: admin
   Password: admin123

7. IMPORTANT:
   Delete create_admin.php after the admin account is created.

8. Open:
   http://localhost/online-appointment/

FEATURES
--------
- Login/logout
- Add appointment
- View appointments
- Edit appointment
- Delete appointment with confirmation
- Search appointments
- Update appointment status
- Admin can view/manage all appointment records
- Regular users see their own records

DATABASE
--------
USER 1:M CLIENT
CLIENT 1:M APPOINTMENT
STATUS 1:M APPOINTMENT
