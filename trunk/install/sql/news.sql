CREATE TABLE %prefix%categories (
  name varchar (50) NOT NULL,
  language varchar (50) NOT NULL,
  description text,
  image varchar (255),
  alternate varchar (255),
  PRIMARY KEY (name,language)
);

CREATE TABLE %prefix%comments (
  id serial NOT NULL,
  message text NOT NULL,
  subject varchar (255) NOT NULL,
  author varchar (50) NOT NULL,
  date int NOT NULL,
  id_news int NOT NULL,
  comment_on_news varchar (3) NOT NULL,
  id_on_comment int NOT NULL,
  PRIMARY KEY (id)
);

CREATE TABLE %prefix%news (
  id serial NOT NULL,
  subject varchar (255) NOT NULL,
  message text NOT NULL,
  language varchar (50) NOT NULL,
  comments int default '0',
  author varchar (50) NOT NULL,
  date int NOT NULL,
  category varchar (50) NOT NULL,
  PRIMARY KEY  (id)
);
