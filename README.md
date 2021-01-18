# **U**nified **I**ncident **R**eporting **S**ystem for Local &amp; Government Organisations (UIRS)

This is the code repository for the UIRS dissertation project, created for [Heriot-Watt University](www.hw.ac.uk)'s BSc. Computer Science degree (2020-21). This is not official.

UIRS's purpose is to provide members of the public with a web portal with which they can view location-specific updates on incidents as posted by local agencies such as councils, health boards and police forces. 

It will allow a member of the public to enter their postcode and receive information on events in their local area (defined using electorial wards). It will also allow the public to subscribe to push notifications for their selected postcodes.

---

## How-To

---

### Setup

Ensure you have MySQL / MariaDB and Apache / httpd installed. To do this on a standard Ubuntu Desktop / Server installation, use the following console commands (you will likely need sudo privileges):

```
apt install apache2 mysql-server mysql-client
```

### Startup Scripts (WSL2 only)

To start Apache (v2) and MySQL / MariaDB on WSL2, execute the following scripts:

```
./scripts/start_apache.sh
./scripts/start_mysql.sh
```

### Resetting the database

To re-create the schema and reset all data, execute the script below and follow the on-screen prompts:

```
./scripts/refresh_schema.sh reset
```

### Refreshing ONS postcode data
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