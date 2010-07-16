#!/bin/sh

CRONOLOGGER_SERVER=${CRONOLOGGER_SERVER:=localhost}
CRONOLOGGER_DB=${CRONOLOGGER_DB:=cronologger}
URL="http://${CRONOLOGGER_SERVER}:5984/${CRONOLOGGER_DB}/_design/cronview"

REV=`wget -O - -q ${URL} | sed "s/.*rev//g" | awk -F\" '{ print $3}'`

curl -X DELETE "${URL}?rev=${REV}"

curl -s -X PUT -H "text/plain;charset=utf-8" -d @cronview.json $URL
