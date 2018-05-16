# CourseAccessAPI
PHP Course Access RESTful API

## Repository Package Include
* Source Code
* MySQL Database Schema

## Installation Guide
1. Execute SQL schema script on the database
2. Copy api.php to a web server
3. Configure database connection at the top of the file
4. Access endpoints from http connection
    * Examples can be found in source code

## Settings
1. Apache httpd.conf
    * Listen 80 (port can be modified)

## Paths
* http://localhost/api.php/generatedkeys/[id]
    * Example output: ```{"ID":1,"GeneratedPassword":"asfewwerdsf","StartTimeStamp":"2018-05-10 10:52:22","ExpirationTimeStamp":"2018-05-31 00:00:00"}```
* http://localhost/api.php/keycheck/[password]
    * Determines if a key is valid and the current time is within the start/end usage period.
    * Example output: ```{"success":"true"}```
* http://localhost/api.php/post
    * Required fields: "password=XX&startdate=XX&expiredate=XX&courseid=XX&classid=XX&teacherid=XX"
    * cURL Example: ```curl.exe -d "password=thepass&startdate=2018-05-10 08:00&expiredate=2018-05-18 16:00&courseid=12&classid=2&teacherid=21" -X POST http://localhost/si-api/api.php/post```
    * Example output: ```{"success":"true"}```

# Version
- 0.0.2
