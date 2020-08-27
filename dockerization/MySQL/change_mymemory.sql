-- mysql -uadmin -padmin < change_mymemory.sql

USE `matecat`;

UPDATE engines 
SET name = 'mouse',
    base_url = 'http://mouse:8080'
WHERE id = 1;