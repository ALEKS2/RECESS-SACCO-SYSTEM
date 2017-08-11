CREATE DATABASE IF NOT EXISTS sacco;
CREATE TABLE IF NOT EXISTS sacco.member(
    id INT(10) NOT NULL AUTO_INCREMENT,
    fname VARCHAR(30) NOT NULL,
    lname VARCHAR(30) NOT NULL,
    username VARCHAR(30) NOT NULL,
    password VARCHAR(30) NOT NULL,
    initial_deposit FLOAT(30) NOT NULL,
    date_of_join DATE NOT NULL,
    position VARCHAR(30) NOT NULL,
    PRIMARY KEY(id)
);
CREATE TABLE IF NOT EXISTS sacco.investment(
	id INT(10) NOT NULL AUTO_INCREMENT,
	idea VARCHAR(50) NOT NULL,
	initial_amount FLOAT(30) NOT NULL,
	date_of_approval DATE NOT NULL,
	profits FLOAT(30) NOT NULL DEFAULT 0,
    losses FLOAT(30) NOT NULL DEFAULT 0,
    username VARCHAR(30) NOT NULL,
    KEY(username),
    PRIMARY KEY(id)
);
CREATE TABLE IF NOT EXISTS sacco.loan(
	id INT(10) NOT NULL AUTO_INCREMENT,
	amount FLOAT(30) NOT NULL,
	date_of_issue DATE NOT NULL,
	balance FLOAT(30) NOT NULL,
	status VARCHAR(30) NOT NULL,
	username VARCHAR(30) NOT NULL,
	KEY(username),
	PRIMARY KEY(id)
);
CREATE TABLE IF NOT EXISTS sacco.contribution(
	id INT(10) NOT NULL AUTO_INCREMENT,
	amount FLOAT(30) NOT NULL,
	date DATE NOT NULL,
	username VARCHAR(30) NOT NULL,
	KEY(username),
	PRIMARY KEY(id)
);
CREATE TABLE IF NOT EXISTS sacco.votes(
	id INT(10) NOT NULL AUTO_INCREMENT,
	approvals VARCHAR(30),
	rejections VARCHAR(30),
	idea VARCHAR(50) NOT NULL,
	KEY(idea),
	PRIMARY KEY(id)
);
CREATE TABLE IF NOT EXISTS sacco.benefits(
	id INT(10) NOT NULL AUTO_INCREMENT,
	date DATE NOT NULL,
	amount FLOAT(30) NOT NULL,
	idea VARCHAR(30) NOT NULL,
	username VARCHAR(30) NOT NULL,
	KEY(idea,username),
	PRIMARY KEY(id)
);
CREATE TABLE IF NOT EXISTS sacco.savings(
	id INT(10) NOT NULL AUTO_INCREMENT,
	date DATE NOT NULL,
	amount FLOAT(30) NOT NULL,
	idea VARCHAR(50) NOT NULL,
	KEY(idea),
	PRIMARY KEY(id)
);
CREATE TABLE IF NOT EXISTS sacco.loan_transactions(
	id INT(10) NOT NULL AUTO_INCREMENT,
	date_of_payement DATE NOT NULL,
	amount FLOAT(30) NOT NULL,
	username VARCHAR(30) NOT NULL,
	KEY(username),
	PRIMARY KEY(id)
);
