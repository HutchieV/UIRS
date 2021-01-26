USE uirs;

SET FOREIGN_KEY_CHECKS=0;

DELETE FROM organisation;
DELETE FROM privilege;
DELETE FROM user;
DELETE FROM incident;
DELETE FROM incident_location;

SET FOREIGN_KEY_CHECKS=1;

INSERT INTO organisation (org_id, org_title, org_created) VALUES (1, "Test Organization", NOW());

INSERT INTO privilege (priv_id, priv_title) VALUES (1, "UIRS Administrator");
INSERT INTO privilege (priv_id, priv_title) VALUES (2, "Organisation Administrator");
INSERT INTO privilege (priv_id, priv_title) VALUES (3, "Organisation Member");

INSERT INTO user (user_username, user_full_name, user_password, user_created, org_id, priv_id) 
VALUES ("otester01", "Olivia Tester", "$2y$12$oSIhETozRn1q.FbPMPlJdeZnu5PwS5LLUPQQe6YsjYmkIZm6DWpZG",
    NOW(), 1, 1);

-- Password is "password"

INSERT INTO incident (incident_id, incident_title_short, incident_title_long, incident_restrictions,
    incident_description, incident_start, incident_end, incident_last_updated, incident_active,
    incident_lat, incident_long, org_id)
VALUES (1, "Incident Title Short", "Incident Title Long", "Incident Restrictions", "Incident Description", 
    NOW(), "2022-01-01 01:01:01", NOW(), TRUE, "55.941924", "-3.222138", 1);

INSERT INTO incident_location (incident_id, pcon_id) VALUES (1, "S14000025");