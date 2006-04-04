CREATE TABLE %prefix%users (
  username varchar (50) NOT NULL,
  password varchar (32) NOT NULL,
  email varchar (100) NOT NULL,
  isadmin varchar (3) NOT NULL,
  language varchar (50) NOT NULL default ('english'),
  skin varchar (100) NOT NULL default ('Morgos Default'),
  contentlanguage varchar (50) NOT NULL default ('english'),
  PRIMARY KEY (username)
);