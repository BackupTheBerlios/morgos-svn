CREATE TABLE users (
  userID int(11) auto_increment NOT NULL,
  login varchar(255) NOT NULL,
  email varchar(255) NOT NULL,
  PRIMARY KEY (userID)
);

CREATE TABLE groups (
  groupID int(11) auto_increment NOT NULL,
  genericName varchar(255) NOT NULL,
  genericDescription TEXT NOT NULL,
  PRIMARY KEY (groupID)
);

CREATE TABLE translatedGroups (
  translatedGroupID int(11) auto_increment NOT NULL,
  groupID int(11) NOT NULL,
  name varchar(255) NOT NULL,
  description varchar(255) NOT NULL,
  codeLang varchar(5) NOT NULL,
  PRIMARY KEY (translatedGroupID)
);

CREATE TABLE user_in_group (
  groupID int(11) NOT NULL,
  userID int(11) NOT NULL
);

INSERT INTO groups (genericName, genericDescription) VALUES('administrator', 'This is the administrator group.');
