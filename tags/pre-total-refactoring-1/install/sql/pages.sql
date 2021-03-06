CREATE TABLE %prefix%modules (
  module varchar (50) NOT NULL,
  needauthorized varchar(3) DEFAULT 'no',
  needauthorizedasadmin varchar(3) DEFAULT 'no',
  listedinadmin varchar(3) DEFAULT 'yes',
  parent varchar (50) default '',
  place int ,
  placeinadmin int,
  islink varchar (3),
  extension varchar (21),
  PRIMARY KEY (module)
);

CREATE TABLE %prefix%userpages (
  name varchar (50) NOT NULL,
  language varchar (50) NOT NULL,
  module varchar (50) NOT NULL,
  content text,
  PRIMARY KEY (language,module)
);
