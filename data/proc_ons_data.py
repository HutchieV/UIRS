#!/bin/python3

"""
proc_ons_data.py

This script parses ONS postcode data into the UIRS SQL database.

Based on the Office for National Statistics'
August 2020 Postcode Directory:
https://geoportal.statistics.gov.uk/datasets/a644dd04d18f4592b7d36705f93270d8
Retrieved Jan. 2020

Author:  Ben Milne
Created: Jan. 2021
"""


import csv
import argparse
import mysql.connector
from print_decorator import PrintDecorator


pd = PrintDecorator("PONSD", False)
print = pd.print_decorator(print)

parser = argparse.ArgumentParser(description="""Script to parse ONS postcode
                                data into the UIRS SQL database.""")
parser.add_argument('-d', dest='data', 
                    help='ONS postcode CSV database', required=True)
parser.add_argument('-p', dest='pcon', 
                    help='ONS Westminster Parl. Constituency CSV database', required=True)


if __name__ == "__main__":
    """Entry point of program"""

    args = parser.parse_args()

    db_username = 'uirs_updater'
    db_password = 'uirs_updater_pword'

    try:
        conn = mysql.connector.connect(
            host='localhost',
            user=db_username,
            password=db_password,
            database='uirs'
        )
        cursor = conn.cursor()
        print("Connection to database established")
    except Exception as e:
        print("An error occured when attempting to connect to the database: ")
        print(e)

    try:
        print("Updating PCON data...")

        pcon_update_sql = """INSERT INTO pcon
                              (pcon_id, pcon_name) VALUES (%s, %s)
                            ON DUPLICATE KEY UPDATE
                              pcon_name = %s"""

        pcon_file = open(args.pcon)
        pcon_csv_reader = csv.reader(pcon_file)
        pcon_csv_headers = next(pcon_csv_reader)

        pcon_counter = 1
        pcon_max_len = 1
        for row in pcon_csv_reader:
            if len(row[1]) > pcon_max_len:
                pcon_max_len = len(row[1])
            print("Importing: [#{}] {}".format(pcon_counter, row[1].ljust(pcon_max_len)), end="", start="\r")
            cursor.execute(pcon_update_sql, (row[0], row[1], row[1]))
            pcon_counter += 1
        
        conn.commit()
        pcon_file.close()

        print("PCON data updated", start="\n")

        # cursor.execute("SELECT * FROM pcon")
        # r = cursor.fetchall()
        # for x in r:
        #     print(x)

        print("Updating Postcode Directory...")

        print("DISABLING FOREIGN KEY CHECKS")
        cursor.execute("SET FOREIGN_KEY_CHECKS=0")

        data_update_sql = """INSERT INTO postcode
                              (postcode_id, postcode_lat, postcode_long, pcon_id) VALUES (%s, %s, %s, %s)
                            ON DUPLICATE KEY UPDATE
                              postcode_lat  = %s,
                              postcode_long = %s,
                              pcon_id       = %s"""

        data_file = open(args.data)
        data_csv_reader = csv.reader(data_file)
        data_csv_headers = next(data_csv_reader)

        h_indexes = {"pcd": -1, "pcon": -1, "lat": -1, "long": -1}
        h = 0
        for h in range(len(data_csv_headers)):
            if data_csv_headers[h] == "pcd":
                h_indexes["pcd"]  = h
            if data_csv_headers[h] == "pcon":
                h_indexes["pcon"] = h 
            if data_csv_headers[h] == "lat":
                h_indexes["lat"]  = h
            if data_csv_headers[h] == "long":
                h_indexes["long"] = h 

        data_counter = 1
        data_max_len = 1
        for row in data_csv_reader:
            if len(row[0].replace(' ', '')) > data_max_len:
                data_max_len = len(row[0].replace(' ', ''))
            print("Importing: [#{}] {}".format(str(data_counter), row[0].replace(' ', '').ljust(data_max_len)), end="", start="\r")
            cursor.execute(data_update_sql, (row[h_indexes["pcd"]].replace(' ', ''),
                                             row[h_indexes["lat"]],
                                             row[h_indexes["long"]],
                                             row[h_indexes["pcon"]],
                                             row[h_indexes["lat"]],
                                             row[h_indexes["long"]],
                                             row[h_indexes["pcon"]]))
            data_counter += 1
        
        print("ENABLING FOREIGN KEY CHECKS", start="\n")
        cursor.execute("SET FOREIGN_KEY_CHECKS=1")

        conn.commit()
        data_file.close()

        print("Postcode Directory updated")

    except Exception as e:
        print("An error occured when attempting to parse input data: ", start="\n")
        print(e)

else:
    print("This program must be run directly.")

    
