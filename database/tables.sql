DROP TABLE IF EXISTS cities;
DROP TABLE IF EXISTS userHistory;
DROP TABLE IF EXISTS users;
CREATE TABLE cities (id int primary key auto_increment,  weights int, cityAndCountry varchar(255));
CREATE TABLE users (userid varchar(10) primary key, hashedPassword varchar(255), attemptCounter int default 0);
CREATE TABLE userHistory(id int primary key auto_increment, userid varchar(10),suggestion varchar(255),timeAdded timestamp default CURRENT_TIMESTAMP,FOREIGN KEY (userid) REFERENCES users(userid));