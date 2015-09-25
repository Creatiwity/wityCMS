# wityCMS

wityCMS is a simple Content Management System Model-View-Controller oriented in PHP.

This CMS uses its own templating system, named [WTemplate](https://github.com/Creatiwity/WTemplate), developed as [a separate GitHub project](https://github.com/Creatiwity/WTemplate) but included here as a submodule.

## Installation

### Prerequisites

* An **Apache server** with PHP 5.3+, *mod_rewrite* enabled and .htaccess files allowed;
* A **SQL server**, like *MySQL* or *MariaDB*, with a database available;
* A **FTP client**, like [Filezilla](https://filezilla-project.org/);
* Download the latest version of **wityCMS**: [zip](https://github.com/Creatiwity/wityCMS/archive/0.3.0.zip).

### Let's go

![Installer](https://raw.github.com/Creatiwity/wityCMS/0.4/installer.jpg)

1. **Unzip** and **copy** wityCMS files on your Apache server thanks to Filezilla.
2. Open a navigator and **go to the URL** of your Apache server.
3. Here, the **installation page** should be asking you information about your server, and your admin account. Fill in all the required fields until the big blue button highlights.
4. **Click** on "Let's go".
5. **Congratulations!** wityCMS have just generated its configuration files (in `system/config`), created all its tables in the database and inserted the first user (you!) as an administrator. The system is ready to be used.
