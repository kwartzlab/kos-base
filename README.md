# kOS

KwartzlabOS (kOS for short) is a member management and access control system originally designed for Kwartzlab Makerspace in Kitchener, Ontario.

## Local Development ##

### Docker setup ###

#### 0. Pre-requisite: Install Docker

Installation of Docker is required for the Docker setup development path. Please make sure both the `docker` and `docker compose` commands are available as they are used throughout setup.

NOTE: Different installation methods of Docker provide Docker Compose under different names. The following instructions reference Docker Compose as `docker compose`. If however your environment uses `docker-compose`, use that instead.

#### 1. Clone
Begin by navigating to the directory where you want to clone kOS in your terminal, then run the following:

```shell
git clone https://github.com/kwartzlab/kos-base.git
```

This will clone the repository from GitHub. Once cloned, navigate into the new directory:

```shell
cd kos-base
```

#### 2. Build Docker image
Files provided in the repository are pre-configured to build Kos from the application source code previously cloned in the last step. The Kos Docker image is built in steps. Once each step completes, it's stored in a cache for later to speed up following builds.

To build the application image, run:
```shell
docker compose build
```

#### 3. Run Kos
With the application image built, we can now stand up Kos with necessary dependencies. To run Kos, run:
```shell
docker compose up
```
When Kos is ready, you should see a message in your console like this:
> INFO  Server running on [http://0.0.0.0:8000].

Open your browser and navigate to [http://localhost:8000/](http://localhost:8000/). You should be presented with the login page. See details below to login.

To shut down Kos, go back to your terminal and press Control+C. You can remove resources once shut down with:
```shell
docker compose down
```

Once you've tested running Kos for the first time, you can optionally run Kos in the background instead. To run Kos in the background, run:
```shell
docker compose up -d
```

#### Next steps

##### Unit Testing

`phpunit` is available within the container to test the built image. To unit test the image, run the following command in another terminal with the Kos container running.
```shell
docker compose exec app ./vendor/bin/phpunit
```

##### phpMyAdmin
phpMyAdmin is a PHP based web interface for MySQL databases. phpMyAdmin has been pre-configured to connect to and manage the Kos database for local debugging. To access the interface, navigate to [http://localhost:8001/](http://localhost:8001/) with Kos running.

### Application Installation (Manual) ###

#### Install Dependencies ####

kOS requires the following external dependencies:

>  note: this has only been tested on ubuntu, your millage may very

1. php 8.0 or higher https://www.php.net/manual/en/install.php

        sudo apt install php

2. enable php extensions `mbstring xml curl mysql sqlite3`

   - \*nix - install packages (only tested on ubuntu, your mileage may very)

            sudo apt install php-mbstring php-xml php-curl php-mysql php-sqlite3

   - windows - modify php.ini file
     - find php.ini file: `php -r "phpinfo();" | grep php.ini`
     - uncomment the extensions ie. `extension=mbstring`

3. mysql 5.6 or higher https://dev.mysql.com/doc/mysql-installation-excerpt/5.7/en/

        sudo apt install mysql

   1. consider running secure install if this is your first time installing

          sudo mysql_secure_installation utility

4. sqlite3 https://www.sqlite.org/download.html

        sudo apt install sqlite3

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
create database homestead;
CREATE USER 'homestead'@'localhost' IDENTIFIED BY 'secret';
GRANT all on `homestead`.* TO 'homestead'@'localhost';
```

##### 4. create copy of .env file
Next, copy and paste the `.env.example` file to a new `.env` file.
This will contain all the environment information your application needs, such as database credentials.
You will need to determine what these values need to be for your local setup yourself.

##### 5. generate local keys
```shell
php artisan key:generate
```

##### 6. Migarte Database

You should now have a database without tables.
The following command adds tables from the migrations up until now.

```shell
php artisan migrate
```

##### 7. Seed Database

You now have everything you need for the application to run, but your database is woefully empty.
You will need users, forms, etc. for the application to reasonably be used and tested locally.
This will seed everything you need for the application to run locally, such as test users and submission forms.
Run the following command:

```shell
php artisan db:seed
```

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

#### Test Email ####
1. Sign up for mailtrap.io
2. put the smtp credentials in the .env file

Use the following to send the new member email to a specific address:
```
php artisan email:memberapp --email=<members email> --recipient=<your email>
```

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
