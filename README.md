# WityCMS

WityCMS is a simple Content Management System Model-View-Controler oriented in PHP. Requires PHP 5.3+. The Apache module mod_rewrite is required too, because the safe-mode without URL rewriting is not yet ready.

This CMS uses its own templating system, named [WTemplate](https://github.com/Creatiwity/WTemplate), developed as a separate GitHub project but included here as a submodule.

## Installation

### Prerequisites

* An **Apache server** with *mod_rewrite* enabled, htaccess files have to be allowed
* An **SQL server**, like *MySQL*, with a database already initialized
* An **FTP client**, like [Filezilla](https://filezilla-project.org/)
* Download the latest version of **WityCMS**: [zip](https://github.com/Creatiwity/WityCMS/archive/0.3.0.zip) or [tar.gz](https://github.com/Creatiwity/WityCMS/archive/0.3.0.tar.gz) version.

### Let's go

![Installer](https://raw.github.com/Creatiwity/WityCMS/0.4/installer.png)

1. **Unzip** and **copy** WityCMS files on your Apache server thanks to Filezilla.
2. Take care to **not copy** or to **remove** all *git files* (`.gitattributes`, `.gitignore` and `.gitmodules`) and the `makedoc.bat` file from the server.
3. Open a navigator and **go to the URL** of your Apache server.
4. Here, the **installation page** should be asking you everything about you, but mainly about your server. Fill in all the required fields unless the blue button on the left highlights.
5. **Click** on "Let's go".
6. **Congratulations !** WityCMS have just generated its configuration files (in `system/config`), created all its tables in the database and inserted the first user (you !) as an administrator.

## How it works ?

Global

### Applications

### Pages

### Templating system

### Users management

## LICENSE

Not yet decided. Soon !
