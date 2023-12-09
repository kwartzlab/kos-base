# kOS

KwartzlabOS (kOS for short) is a member management and access control system originally designed for Kwartzlab Makerspace in Kitchener, Ontario.

## Local Development ##

### Application Installation ###

#### Install Dependencies ####

kOS requires PHP version 8.0 or higher, a MySQL server running 5.6 or higher, and composer.

1. php 8.0 or higher https://www.php.net/manual/en/install.php

2. enable php extensions `mbstring xml curl mysql sqlite3`

   - \*nix - install packages (only tested on ubuntu, your mileage may very)

           sudo apt-get install php-mbstring php-xml php-curl php-mysql php-sqlite3

   - windows - modify php.ini file
     - find php.ini file: `php -r "phpinfo();" | grep php.ini`
     - uncomment the extensions ie. `extension=mbstring`

3. mysql 5.6 or higher https://dev.mysql.com/doc/mysql-installation-excerpt/5.7/en/

   1. consider running secure install if this is your first time installing

          sudo mysql_secure_installation utility

4. sqlite3 https://www.sqlite.org/download.html

5. composer https://getcomposer.org/doc/00-intro.md

#### Download and setup environment ####

##### 1. Clone
Begin by navigating to the directory where you want to clone kOS in your terminal, then run the following:

```shell
git clone https://github.com/kwartzlab/kos-base.git
```

This will clone the repository from GitHub. Once cloned, navigate into the new directory:

```shell
cd kos-base
```

##### 2. Install Composer Dependencies
And then install the package dependencies:

```shell
composer install
```

This will install everything needed to run the application.

> note: if install fails it\'s most likely due to incorrect dependencies.
> Check the dependency section to see if anything was missed.

##### 3. Create mysql database and user

Start mysql as the root user (`sudo mysql` in \*nix).
In the mysql prompt execute the following commands:

```sql
create database homestead
CREATE USER 'homestead'@'localhost' IDENTIFIED BY 'secret';
GRANT all on `homestead`.* TO 'homestead'@'localhost';
```

##### 4. create copy of .env file
Next, copy and paste the `.env.example` file to a new `.env` file. This will contain all the environment information your application needs, such as database credentials. You will need to determine what these values need to be for your local setup yourself. Once you have everything filled in, run the following command:

##### 5. generate local keys
```shell
php artisan key:generate
```

This will generate an application encryption key, which is required for the application to run.

### Database Seeding ###

You now have everything you need for the application to run, but your database is woefully empty. You will need users, forms, etc. for the application to reasonably be used and tested locally. Run the following command:

```shell
php artisan db:seed
```

This will seed everything you need for the application to run locally, such as test users and submission forms.

One user of every role will be created. (see Test Users section)

### Unit Testing ###

The `phpunit.xml` file is configured to run unit tests in an [in-memory sqlite](https://www.servermania.com/kb/articles/install-sqlite) database. Most IDEs have a built-in solution for running tests, but if you choose to run tests through your CLI, you can run the entire test suite by running:

```shell
./vendor/bin/phpunit
```

This will run every test in the application. To run specific tests, you can filter by class name:

```shell
./vendor/bin/phpunit --filter=HttpsProtocolTest
```

### Running the Local Server ###

```shell
php artisan serve
```

#### Test users ####

The following users are created for each role:

* `superuser-dev@kwartzlab.ca` (Superuser role)
* `bod-dev@kwartzlab.ca` (Board of Directors role)
* `kfa-dev@kwartzlab.ca` (Key Fob Assigner role)
* `bookkeeper-dev@kwartzlab.ca` (Bookkeeper role)

Every user is created with the password `secret`.

## Current Features ##

### Management Features ###

* **Membership Management** - From initial application, to hiatus requests, suspensions and withdrawals. 
* **Access Control** - Hardware lockouts that can be used for doors and tools, providing secured access for members with the ability to manage RFID keys and authorizations centrally. 
* **Team System** - Assign members to teams which can administer tools and related training & maintenance requests
* **Key Kiosk** - Browser-based app that facilitates adding new keys to the system and executing management tasks. Originally designed for a Raspberry Pi-based unit with touchscreen and RFID reader.

### User Features ###

* **kOS Dashboard** - Allows members to log in to view use space information such as member traffic, real-time tool use, upcoming events and more
* **Member Profiles** - Allows members to share interests, social media links and relevant certifications for volunteer roles (CPR, etc.)

## Features In Progress ##

* **Training System** - Allows members to sign up for training courses for specific tools or general training (e.g. Health & Safety). Courses will appear as a skill tree to show pre-requisites. Instructors will be able to Approve/Deny access to related tools as needed.
* **Maintenance Requests** - Provides a centralized way to handle tool maintenance and other technical requests
* **Custom Reports** - Provides a robust, customizable way to generate reports for anything from member attendance, tool use, door statistics, team organization, training & maintenance requests

## Compatible Lockout Hardware ##

* **kOS Gatekeeper Project** - Raspberry Pi-based lockout with a custom PCB & enclosures designed for NFC-based key access and tool lockout modes. Two-way communication allows for remote status updates, tool lockout (for maintenance) and door unlock events. Enclosures are 3D printed and PCB is designed with through-hole components for easy assembly.

Communication & key synchronization with kOS is done via an SSL-encrypted API allowing for unlimited custom hardware possibilities.
