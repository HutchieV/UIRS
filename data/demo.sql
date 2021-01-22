USE uirs;

INSERT INTO organisation
  (
    org_id,
    org_title,
    org_created
  )
VALUES
  (
    1,
    "Test Organization",
    NOW()
  );

INSERT INTO incident 
  (
    incident_id,
    incident_title_short,
    incident_title_long,
    incident_restrictions,
    incident_description,
    incident_start,
    incident_end,
    incident_last_updated,
    incident_active,
    incident_lat,
    incident_long,
    org_id
  )
VALUES
  (
    1,
    "Incident Title Short",
    "Incident Title Long",
    "Incident Restrictions",
    "Incident Description",
    NOW(),
    "2022-01-01 01:01:01",
    NOW(),
    TRUE,
    "55.941924",
    "-3.222138",
    1
  );

INSERT INTO incident_location 
  (
    incident_id,
    pcon_id
  )
VALUES
  (
    1,
    "S14000025"
  )