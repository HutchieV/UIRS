# **U**nified **I**ncident **R**eporting **S**ystem for Local &amp; Government Organisations (UIRS)

This is the code repository for the UIRS dissertation project, created for [Heriot-Watt University](www.hw.ac.uk)'s BSc. Computer Science degree (2020-21). This is not official.

UIRS's purpose is to provide members of the public with a web portal with which they can view location-specific updates on incidents as posted by local agencies such as councils, health boards and police forces. 

It will allow a member of the public to enter their postcode and receive information on events in their local area (defined using electorial wards). It will also allow the public to subscribe to push notifications for their selected postcodes.

## How-To

### Setup

Ensure you have MySQL / MariaDB and Apache / httpd installed. To do this on a standard Ubuntu Desktop / Server installation, use the following console commands (you will likely need sudo privileges):

```
apt install apache2 mysql-server mysql-client libapache2-mod-php php-mysql
```

You will also have to enable the Apache2's PHP module. UIRS was developed using PHP 7.2, so you may need to change this to your system's version:

```
a2enmod php7.2
```

You may also need to configure Apache to serve files from a custom directory (by default this is `/var/www/html`), do so by first changing the `DocumentRoot` variable to the location of the `UIRS/public` directory, found in the following location:

```
nano /etc/apache2/sites-enabled/000-default.conf
...
    ServerAdmin webmaster@localhost
    DocumentRoot [ your dir here ]
...
```

You will also want to configure the general config file to amend permissions, etc. This is found in:

```
nano /etc/apache2/apache2.conf
```

Where you will want to change `<Directory /var/www/html>` to the location the `UIRS/public`.

### Startup Scripts (WSL2 only)

To start Apache (v2) and MySQL / MariaDB on WSL2, execute the following scripts:

```
./scripts/start_apache.sh
./scripts/start_mysql.sh
```

If required, you may have to execute using sudo, like so:

```
sudo bash ./scripts/[script]
```

### Resetting the database

To re-create the schema and reset all data, execute the script below and follow the on-screen prompts:

```
./scripts/refresh_schema.sh reset
```

### Refreshing the ONS Postcode Directory

First, download the latest ONS Postcode Directory from the ONS website:
https://www.ons.gov.uk/methodology/geography/geographicalproducts/postcodeproducts

Make sure you have the required python dependencies:

```
python3 -m pip install mysql-connector-python
```

Then run the `proc_ons_data.py` script with Python 3.6.9 or above, passing in the ONS postcode directory and parliamentary constituency database files:

```
python3 proc_ons_data.py -d [path_to_postcode_data] -p [path_to_pcon_data]
```

This will insert new constituencies and postcodes, or update existing ones.