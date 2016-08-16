CREATE PROCEDURE
`proc_ea_api_get_all_models`()
  BEGIN
    SELECT id, user_id, name, hash, ext, valid, date, time FROM xml_models;
  END $$