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
  pluginID varchar(38),
  PRIMARY KEY (pageID),
  UNIQUE KEY (name)
);

CREATE TABLE {prefix}translatedPages (
  translatedPageID int(11) auto_increment NOT NULL,
  translatedTitle varchar(255) NOT NULL,
  translatedNavTitle varchar(255) NULL,
  translatedContent text,
  pageID int(11) NOT NULL,
  languageCode varchar(5),
  PRIMARY KEY (translatedPageID),
  UNIQUE KEY (pageID, languageCode)
);
