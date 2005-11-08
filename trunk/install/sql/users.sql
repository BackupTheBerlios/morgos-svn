CREATE TABLE %prefix%users (
  username varchar (50) NOT NULL,
  password varchar (32) NOT NULL,
  email varchar (100) NOT NULL,
  isadmin varchar (3) NOT NULL,
  language varchar (50) NOT NULL,
  contentlanguage varchar (50) NOT NULL,
  PRIMARY KEY (username)
);