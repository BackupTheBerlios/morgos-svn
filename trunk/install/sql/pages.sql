CREATE TABLE %prefix%modules (
  module varchar (50) NOT NULL,
  needauthorized varchar(3) DEFAULT 'no',
  PRIMARY KEY (module)
);

CREATE TABLE %prefix%userpages (
  name varchar (50) NOT NULL,
  language varchar (50) NOT NULL,
  module varchar (50) NOT NULL,
  parent varchar (50),
  link varchar (255) NOT NULL,
  content text,
  PRIMARY KEY (name,language,module)
);
