# WityCMS

WityCMS is a simple Content Management System Model-View-Controller oriented in PHP.

This CMS uses its own templating system, named [WTemplate](https://github.com/Creatiwity/WTemplate), developed as [a separate GitHub project](https://github.com/Creatiwity/WTemplate) but included here as a submodule.

## Installation

### Prerequisites

* An **Apache server** with PHP 5.3+, *mod_rewrite* enabled and .htaccess files allowed;
* A **SQL server**, like *MySQL* or *MariaDB*, with a database available;
* A **FTP client**, like [Filezilla](https://filezilla-project.org/);
* Download the latest version of **WityCMS**: [zip](https://github.com/Creatiwity/WityCMS/archive/0.3.0.zip).

### Let's go

![Installer](https://raw.github.com/Creatiwity/WityCMS/0.4/installer.jpg)

1. **Unzip** and **copy** WityCMS files on your Apache server thanks to Filezilla.
2. Open a navigator and **go to the URL** of your Apache server.
3. Here, the **installation page** should be asking you information about your server, and your admin account. Fill in all the required fields until the big blue button highlights.
4. **Click** on "Let's go".
5. **Congratulations!** WityCMS have just generated its configuration files (in `system/config`), created all its tables in the database and inserted the first user (you!) as an administrator. The system is ready to be used.

## License

The MIT License (MIT)

Copyright (c) 2013 - Julien Blatecky and Johan Dufau

Permission is hereby granted, free of charge, to any person obtaining a copy of
this software and associated documentation files (the "Software"), to deal in
the Software without restriction, including without limitation the rights to
use, copy, modify, merge, publish, distribute, sublicense, and/or sell copies of
the Software, and to permit persons to whom the Software is furnished to do so,
subject to the following conditions:

The above copyright notice and this permission notice shall be included in all
copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, FITNESS
FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR
COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER
IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN
CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
