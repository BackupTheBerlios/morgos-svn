CREATE TABLE users (
  userID int(11) auto_increment NOT NULL,
  login varchar(255) NOT NULL,
  email varchar(255) NOT NULL,
  PRIMARY KEY (userID),
  UNIQUE KEY (login)
);

CREATE TABLE groups (
  groupID int(11) auto_increment NOT NULL,
  genericName varchar(255) NOT NULL,
  genericDescription TEXT NOT NULL,
  PRIMARY KEY (groupID),
  UNIQUE KEY (genericName)
);

CREATE TABLE translatedGroups (
  translatedGroupID int(11) auto_increment NOT NULL,
  groupID int(11) NOT NULL,
  name varchar(255) NOT NULL,
  description varchar(255) NOT NULL,
  codeLang varchar(5) NOT NULL,
  PRIMARY KEY (translatedGroupID)
);

CREATE TABLE group_users (
  groupID int(11) NOT NULL,
  userID int(11) NOT NULL
);

CREATE TABLE pages (
  pageID int(11) auto_increment NOT NULL,
  genericName varchar(255) NOT NULL,
  genericContent text,
  parentPageID int(11) NOT NULL,
  placeInMenu int(3) NOT NULL,
  PRIMARY KEY (pageID),
  UNIQUE KEY (genericName)
);

INSERT INTO users (login, email) VALUES ('administrator', 'admin@host.org');
INSERT INTO users (login, email) VALUES ('normalUser', 'normalUser@host.org');
INSERT INTO groups (genericName, genericDescription) VALUES('administrator', 'This is the administrator group.');
INSERT INTO groups (genericName, genericDescription) VALUES('normalUsers', 'This is the normal users group');
INSERT INTO pages (genericName, genericContent, placeInMenu, parentPageID) VALUES('site', '', 0, 0);
INSERT INTO pages (genericName, genericContent, placeInMenu, parentPageID) VALUES('Home', '', 1, 1);
INSERT INTO pages (genericName, genericContent, placeInMenu, parentPageID) VALUES('News', '', 2, 1);
INSERT INTO pages (genericName, genericContent, placeInMenu, parentPageID) VALUES('Packages', '', 3, 1);
