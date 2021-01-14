-- #####################################
-- schema.sql
-- 
-- This file contains the SQL instructions 
-- to create the UIRS databases.
--
-- Based on schema version v0.5
--
-- Author:  Ben Milne
-- Created: Jan. 2021
-- #####################################

-- NB: BIGINT is used to avoid int overflow

-- ### If you want to reset the database, uncomment the following line
DROP DATABASE IF EXISTS uirs;

-- Create and use database
CREATE DATABASE IF NOT EXISTS uirs;
USE uirs;


-- ### USER / ORG DATA
CREATE TABLE IF NOT EXISTS privilege (
    priv_id                 BIGINT(10) UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    priv_title              VARCHAR(50)
);

CREATE TABLE IF NOT EXISTS organisation (
    org_id                  BIGINT(10) UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    org_title               VARCHAR(200),
    org_created             DATETIME
);

CREATE TABLE IF NOT EXISTS user (
    user_id                 BIGINT(10) UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    user_username           VARCHAR(100),
    user_full_name          VARCHAR(100),
    user_password           VARCHAR(100),
    user_created            DATETIME,
    org_id                  BIGINT(10) UNSIGNED,
    priv_id                 BIGINT(10) UNSIGNED,
    FOREIGN KEY (org_id)    REFERENCES organisation(org_id),
    FOREIGN KEY (priv_id)   REFERENCES privilege(priv_id)
);

CREATE TABLE IF NOT EXISTS token (
    token_id                BIGINT(10) UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    token_value             VARCHAR(255),
    token_created           DATETIME,
    token_valid_from        DATETIME,
    token_valid_to          DATETIME,
    user_id                 BIGINT(10) UNSIGNED,
    FOREIGN KEY (user_id)   REFERENCES user(user_id)
);


-- ### POSTCODE / LOCATION DATA
CREATE TABLE IF NOT EXISTS ward (
    ward_id                 BIGINT(10) UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    ward_name               VARCHAR(150)
);

CREATE TABLE IF NOT EXISTS postcode (
    postcode_id             BIGINT(10) UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    postcode_value          VARCHAR(10),
    ward_id                 BIGINT(10) UNSIGNED,
    FOREIGN KEY (ward_id)   REFERENCES ward(ward_id)
);


-- ### SUBSCRIPTION
CREATE TABLE IF NOT EXISTS subscription (
    sub_id                  BIGINT(10) UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    sub_token               TEXT
);

CREATE TABLE IF NOT EXISTS subscription_postcode (
    sub_id                      BIGINT(10) UNSIGNED,
    postcode_id                 BIGINT(10) UNSIGNED,
    FOREIGN KEY (sub_id)        REFERENCES subscription(sub_id),
    FOREIGN KEY (postcode_id)   REFERENCES postcode(postcode_id)
);


-- ### INCIDENT DATA
CREATE TABLE IF NOT EXISTS incident (
    incident_id                 BIGINT(10) UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    incident_date               DATETIME,
    incident_title_short        VARCHAR(255),
    incident_title_long         VARCHAR(255),
    incident_restrictions       TEXT,
    incident_description        TEXT,
    incident_start              DATETIME,
    incident_end                DATETIME,
    incident_last_updated       DATETIME,
    org_id                      BIGINT(10) UNSIGNED,               
    FOREIGN KEY (org_id)        REFERENCES organisation(org_id)
);

CREATE TABLE IF NOT EXISTS incident_location (
    incident_id                 BIGINT(10) UNSIGNED,
    ward_id                     BIGINT(10) UNSIGNED,
    FOREIGN KEY (incident_id)   REFERENCES incident(incident_id),
    FOREIGN KEY (ward_id)       REFERENCES ward(ward_id)
);