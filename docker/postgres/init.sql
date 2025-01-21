-- Create a new user with a password
CREATE USER agwenchez WITH PASSWORD 'Agwenchez254';

-- Grant global privileges to the new user
ALTER USER agwenchez WITH SUPERUSER;
ALTER USER agwenchez WITH CREATEDB;
ALTER USER agwenchez WITH CREATEROLE;
ALTER USER agwenchez WITH REPLICATION;
ALTER USER agwenchez WITH BYPASSRLS;

-- Create a new database owned by the new user
CREATE DATABASE newsaggregator OWNER agwenchez;

-- Grant all privileges on the new database to the new user
GRANT ALL PRIVILEGES ON DATABASE newsaggregator TO agwenchez;

-- Keep the postgres user and ensure it retains ownership over its objects
REASSIGN OWNED BY postgres TO postgres;

-- Grant all privileges to the postgres user for the new database
GRANT ALL PRIVILEGES ON DATABASE newsaggregator TO postgres;
