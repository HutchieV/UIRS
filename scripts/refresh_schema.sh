#!/bin/bash

DIR="$( cd "$( dirname "${BASH_SOURCE[0]}" )" >/dev/null 2>&1 && pwd )"

if [ "$1" = "reset" ]
then
	printf "Resetting database! This will erase all data, are you sure? (Y/N): "
	read confirm
fi

if [ "$confirm" = "Y" ] || [ "$confirm" = "y" ]
then
	echo "Proceeding"
	read -p "DBMS Username: " uname

	printf "$pword" | mysql -u "$uname" -p < "$DIR"/../data/schema.sql
	exitc=$?
	echo "Finished with exit code $exitc"
else
	echo "Cancelling"
fi

