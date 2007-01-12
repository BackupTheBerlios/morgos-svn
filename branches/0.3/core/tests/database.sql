CREATE TABLE {prefix}users (
  userID int(11) auto_increment NOT NULL,
  login varchar(255) NOT NULL,
  email varchar(255) NOT NULL,
  password varchar(32) NOT NULL,
  PRIMARY KEY (userID),
  UNIQUE KEY (login)
);

CREATE TABLE {prefix}groups (
  groupID int(11) auto_increment NOT NULL,
  genericName varchar(255) NOT NULL,
  genericDescription TEXT NOT NULL,
  PRIMARY KEY (groupID),
  UNIQUE KEY (genericName)
);

CREATE TABLE {prefix}translatedGroups (
  translatedGroupID int(11) auto_increment NOT NULL,
  groupID int(11) NOT NULL,
  name varchar(255) NOT NULL,
  description varchar(255) NOT NULL,
  languageCode varchar(5) NOT NULL,
  PRIMARY KEY (translatedGroupID)
);

CREATE TABLE {prefix}group_users (
  groupID int(11) NOT NULL,
  userID int(11) NOT NULL,
  UNIQUE KEY (groupID, userID)
);

CREATE TABLE {prefix}groupPermissions (
  groupID int(11) NOT NULL,
  permissionName varchar(255) NOT NULL,
  enabled ENUM('Y', 'N') NOT NULL,
  UNIQUE KEY (groupID, permissionName)
);

CREATE TABLE {prefix}pages (
  pageID int(11) auto_increment NOT NULL,
  name varchar(255) NOT NULL,
  parentPageID int(11) NOT NULL,
  placeInMenu int(3) NOT NULL,
  action varchar(255),
  pluginID varchar(36),
  PRIMARY KEY (pageID),
  UNIQUE KEY (name)
);

CREATE TABLE {prefix}translatedPages (
  translatedPageID int(11) auto_increment NOT NULL,
  translatedTitle varchar(255) NOT NULL,
  translatedNavTitle varchar(255) NOT NULL,
  translatedContent text,
  pageID int(11) NOT NULL,
  languageCode varchar(5),
  PRIMARY KEY (translatedPageID),
  UNIQUE KEY (pageID, languageCode)
);

INSERT INTO {prefix}users (login, email, password) VALUES ('administrator', 'admin@host.org', 'c54a16ca8fa833f9d23dbba08f617243');
INSERT INTO {prefix}users (login, email, password) VALUES ('normalUser', 'normalUser@host.org', 'c54a16ca8fa833f9d23dbba08f617243');
INSERT INTO {prefix}groups (genericName, genericDescription) VALUES ('administrator', 'This is the administrator group.');
INSERT INTO {prefix}groups (genericName, genericDescription) VALUES ('normalUsers', 'This is the normal users group');

INSERT INTO {prefix}groups (genericName, genericDescription) VALUES ('translatedGroup', 'This is a translated group');
INSERT INTO {prefix}translatedGroups (groupID, name, description, languageCode) VALUES (3, 'NL-NL', 'Netherlands', 'NL-NL');
INSERT INTO {prefix}translatedGroups (groupID, name, description, languageCode) VALUES (3, 'NL', 'Netherlands generic', 'NL');
INSERT INTO {prefix}translatedGroups (groupID, name, description, languageCode) VALUES (3, 'FR-FR', 'French', 'FR-FR');

INSERT INTO {prefix}pages (name, placeInMenu, parentPageID) VALUES ('site',  0, 0);
INSERT INTO {prefix}pages (name, placeInMenu, parentPageID) VALUES ('Home',  1, 1);
INSERT INTO {prefix}pages (name, placeInMenu, parentPageID, action) VALUES ('News',  2, 1, 'newsViewLatestItems');
INSERT INTO {prefix}pages (name, placeInMenu, parentPageID) VALUES ('Packages',  3, 1);
INSERT INTO {prefix}pages (name, placeInMenu, parentPageID) VALUES ('admin', 0, 0);
INSERT INTO {prefix}pages (name, placeInMenu, parentPageID) VALUES ('AdminPage', 1, 5);

INSERT INTO {prefix}pages (name, placeInMenu, parentPageID) VALUES ('TranslatedPage', 0, 2);
INSERT INTO {prefix}translatedPages (translatedTitle, translatedContent, pageID, languageCode) VALUES ('NL-NL', 'This is the dutch (Netherlands) translation. (NL-NL)', 7, 'NL-NL');
INSERT INTO {prefix}translatedPages (translatedTitle, translatedContent, pageID, languageCode) VALUES ('NL', 'This is the dutch (generic) translation. (NL)', 7, 'NL');
INSERT INTO {prefix}translatedPages (translatedTitle, translatedContent, pageID, languageCode) VALUES ('FR-FR', 'This is the french (french) translation. (FR-FR)', 7, 'FR-FR');

INSERT INTO {prefix}pages (name, placeInMenu, parentPageID) VALUES ('ATest', 0, 1);