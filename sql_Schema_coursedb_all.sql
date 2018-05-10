# Database
-- drop database CourseAccess_DB;
create database If not exists CourseAccess_DB;

-- database to use
Use CourseAccess_DB;

-- Table for the main table that refer to the courses and classes
create table If not exists CoursePasswords (
	ID bigint auto_increment primary key,
	CourseID bigint, -- Should refer to the KEA database for CourseID
	ClassID bigint,
	TeacherID int -- Should refer to the KEA database for TeacherID
	
);

-- make insert to each key seperately with a start time and a expiration time which will be recived from 
-- mediator
Create table If not exists GeneratedKeys (
	ID bigint auto_increment primary key,
    GeneratedPassword varchar(150) not null,
    StartTimeStamp datetime not null default NOW(),
	ExpirationTimeStamp datetime not null
);

-- middle class to combine the CoursePasswords and GeneratedKeys
Create table If not exists GKCPs (
	CPID bigint,
    GKID bigint,
    primary key(CPID, GKID),
    foreign key(CPID) references CoursePasswords(ID),
    foreign key(GKID) references GeneratedKeys(ID)
);

#users and privelidges
-- user not used
Create USER if not exists 'student'@'localhost' identified by 'student1234';
Grant SELECT on courseaccess_db.* to 'student'@'localhost';

-- not used.
Create USER if not exists 'teacher'@'localhost' identified by 'pv1234';
Grant INSERT on courseaccess_db.* to 'teacher'@'localhost';

-- will be used as a main user for the mediator control.
Create USER if not exists 'mom'@'localhost' identified by 'pv1234';
Grant INSERT, SELECT on courseaccess_db.* to 'mom'@'localhost';

