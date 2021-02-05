USE uirs;

SET FOREIGN_KEY_CHECKS=0;

DELETE FROM organisation;
DELETE FROM privilege;
DELETE FROM user;
DELETE FROM incident;
DELETE FROM incident_location;

SET FOREIGN_KEY_CHECKS=1;

INSERT INTO organisation (org_id, org_title, org_created) VALUES (1, "Test Organization", NOW());
INSERT INTO organisation (org_id, org_title, org_created, org_icon) VALUES (2, "Scottish Government", NOW(), "/content/images/scot-gov-logo.svg");

INSERT INTO privilege (priv_id, priv_title) VALUES (1, "UIRS Administrator");
INSERT INTO privilege (priv_id, priv_title) VALUES (2, "Organisation Administrator");
INSERT INTO privilege (priv_id, priv_title) VALUES (3, "Organisation Member");

INSERT INTO user (user_username, user_full_name, user_password, user_created, org_id, priv_id) 
VALUES ("otester01", "Olivia Tester", "$2y$12$oSIhETozRn1q.FbPMPlJdeZnu5PwS5LLUPQQe6YsjYmkIZm6DWpZG",
    NOW(), 2, 1);

-- Password is "password"

-- Incident levels:
-- 0 : Information
-- 1 : Low
-- 2 : Medium
-- 3 : High

INSERT INTO incident (incident_id, incident_title_short, incident_title_long, incident_restrictions,
    incident_description, incident_level, incident_start, incident_end, incident_last_updated, incident_active,
    incident_lat, incident_long, org_id)
VALUES (1, "Incident Title Short", "Incident Title Long", "Incident Restrictions", "Incident Description", 
    0, NOW(), "2022-01-01 01:01:01", NOW(), TRUE, "55.941924", "-3.222138", 1);

INSERT INTO incident (incident_id, incident_title_short, incident_title_long, incident_restrictions,
    incident_description, incident_level, incident_start, incident_end, incident_last_updated, incident_active,
    incident_lat, incident_long, org_id)
VALUES (2, "COVID Lockdown Level 4", "COVID Level 4 measures in place in this region", "
            <table class='pub-i-m-res-table'>
                <tr>
                    <th colspan='2'>
                        Level 4 Measures
                    </th>
                <tr>
                <tr>
                    <th>
                        Socialising
                    </th>
                    <td>
                        No in-home socialising (limited exceptions)<br>
                        <strong>6</strong> people from <strong>2</strong> households outdoors and in public places
                    </td>
                </tr>
                <tr>
                    <th>
                        Hospitality
                    </th>
                    <td>
                        All venues closed
                    </td>
                </tr>
                <tr>
                    <th>
                        Travel
                    </th>
                    <td>
                        Essential travel only to/from level 3 or 4 areas in Scotland, and to/from rest of the UK<br>
                        Follow rules and advice on international travel
                    </td>
                </tr>
                <tr>
                    <th>
                        Transport
                    </th>
                    <td>
                        No use of public transport, except for essential purposes
                    </td>
                </tr>
                <tr>
                    <th>
                        Shopping
                    </th>
                    <td>
                        Non-essential retail closed<br>
                        Click and collect & outdoor retail permitted
                    </td>
                </tr>
                <tr>
                    <th>
                        Close contact services
                    </th>
                    <td>
                        Closed, including mobile close contact services
                    </td>
                </tr>
                <tr>
                    <th>
                        Support Services
                    </th>
                    <td>
                        Essential/online where possible
                    </td>
                </tr>
                <tr>
                    <th>
                        Places of Worship
                    </th>
                    <td>
                        Open<br>
                        Restrict number of attendees: 20
                    </td>
                </tr>
                <tr>
                    <th>
                        Early Learning and Childcare
                    </td>
                    <td>
                        Open, targeted intervention may impact capacity
                    </th>
                </tr>
                <tr>
                    <th>
                        Informal Childcare
                    </th>
                    <td>
                        Essential childcare only (see guidance)
                    </td>
                </tr>
                <tr>
                    <th>
                        Unregulated (children's) Activities
                    </th>
                    <td>
                        Indoors: NO<br>
                        Outdoors: YES
                    </td>
                </tr>
                <tr>
                    <th>
                        Schools
                    </th>
                    <td>
                        Open with enhanced and targeted protective measures
                    </td>
                </tr>
                <tr>
                    <th>
                        Colleges and Universities
                    </th>
                    <td>
                        Restricted, Blended Learning
                    </td>
                </tr>
                <tr>
                    <th>
                        Visitor Attractions
                    </th>
                    <td>
                        Closed
                    </td>
                </tr>
                <tr>
                    <th>
                        Public Services
                    </th>
                    <td>
                        Essential face-to-face (online where possible)
                    </td>
                </tr>
                <tr>
                    <th>
                        Public Buildings
                    </th>
                    <td>
                        Closed
                    </td>
                </tr>
                <tr>
                    <th>
                        Driving Lessons
                    </th>
                    <td>
                        No
                    </td>
                </tr>
                <tr>
                    <th>
                        Offices and Call Centres
                    </th>
                    <td>
                        Essential only/work from home
                    </td>
                </tr>
                <tr>
                    <th>
                        Other Workplaces
                    </th>
                    <td>
                        Essential workplaces<br>
                        Outdoor workplaces<br>
                        Construction<br>
                        Manufacturing
                    </td>
                </tr>
                <tr>
                    <th>
                        Shielding
                    </th>
                    <td>
                        Level 4 shielding rule
                    </td>
                </tr>
                <tr>
                    <th>
                        Sports and Exercise
                    </th>
                    <td>
                        Indoor gyms closed<br>
                        Outdoor non-contact sports only
                    </td>
                </tr>
                <tr>
                    <th>
                        Leisure and Entertainment
                    </th>
                    <td>
                        Closed
                    </td>
                </tr>
                <tr>
                    <th>
                        Life Events
                    </th>
                    <td>
                        Weddings/civil partnerships: capacity of 20<br>
                        Funerals: capacity of 20<br>
                        Post-funeral gatherings: capactiy of 20<br>
                        No receptions
                    </td>
                </tr>
                <tr>
                    <th>
                        Stadia and Events
                    </th>
                    <td>
                        Events not permitted<br>
                        Stadia closed to spectators
                    </td>
                </tr>
                <tr>
                    <th>
                        Accommodation
                    </th>
                    <td>
                        Essential only (no tourism)
                    </td>
                </tr>
            </table>", 
            "Latest updates at <a href='https://www.gov.scot/coronavirus-covid-19/'>https://www.gov.scot/coronavirus-covid-19/</a><br><br>
            Check here for the latest face coverings information: <a href='https://www.gov.scot/publications/coronavirus-covid-19-phase-3-staying-safe-and-protecting-others/pages/face-coverings/'>https://www.gov.scot/publications/coronavirus-covid-19-phase-3-staying-safe-and-protecting-others/pages/face-coverings/</a><br><br>
            These restrictions are indicative and will be updated over time. Exemptions apply for these protective measures. 
            Please see guidance for details. Regulations in relation to each level will be published on <a href='legislation.gov.uk'>legislation.gov.uk</a> and 
            relevant public health advice (such as physical distancing and enhanced hygiene measures) applies. Find relevant 
            guidance on <a href='www.gov.scot'>www.gov.scot</a>. All restrictions will be kept under review to ensure that they remain proportionate and 
            necessary to address the ongoing public health emergency.", 3, NOW(), "2022-01-01 01:01:01", NOW(), 
            TRUE, null, null, 2);

INSERT INTO incident (incident_id, incident_title_short, incident_title_long, incident_restrictions,
    incident_description, incident_level, incident_start, incident_end, incident_last_updated, incident_active,
    incident_lat, incident_long, org_id)
VALUES (3, "COVID Lockdown Level 3", "COVID Level 3 measures in place in this region", "
            <iframe class='pub-i-m-iframe' src='/content/binaries/level-3-measures.pdf'></iframe>", 
            "Latest updates at <a href='https://www.gov.scot/coronavirus-covid-19/'>https://www.gov.scot/coronavirus-covid-19/</a><br><br>
            Check here for the latest face coverings information: <a href='https://www.gov.scot/publications/coronavirus-covid-19-phase-3-staying-safe-and-protecting-others/pages/face-coverings/'>https://www.gov.scot/publications/coronavirus-covid-19-phase-3-staying-safe-and-protecting-others/pages/face-coverings/</a><br><br>
            These restrictions are indicative and will be updated over time. Exemptions apply for these protective measures. 
            Please see guidance for details. Regulations in relation to each level will be published on <a href='legislation.gov.uk'>legislation.gov.uk</a> and 
            relevant public health advice (such as physical distancing and enhanced hygiene measures) applies. Find relevant 
            guidance on <a href='www.gov.scot'>www.gov.scot</a>. All restrictions will be kept under review to ensure that they remain proportionate and 
            necessary to address the ongoing public health emergency.", 3, NOW(), "2022-01-01 01:01:01", NOW(), 
            TRUE, null, null, 2);

INSERT INTO incident_location (incident_id, pcon_id) VALUES (1, "S14000025");
INSERT INTO incident_location (incident_id, pcon_id) VALUES (2, "S14000025");
INSERT INTO incident_location (incident_id, pcon_id) VALUES (3, "E14000720");