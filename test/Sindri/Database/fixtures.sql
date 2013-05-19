/**
 * Copyright 2013 Markus Lanz (aka stahlstift)
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *     http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

CREATE TABLE [datetimespecial] (
    [update] VARCHAR(10)  NULL
);

CREATE TABLE [datetimestandard] (
    [update] DATETIME  NULL
);

CREATE TABLE [users] (
    [id] INTEGER  NOT NULL PRIMARY KEY AUTOINCREMENT,
    [username] VARCHAR(100)  UNIQUE NOT NULL,
    [password] VARCHAR(32)  NOT NULL
);

CREATE TABLE [numericdata] (
    [id] INTEGER  NOT NULL PRIMARY KEY AUTOINCREMENT,
    [datafield] FLOAT  NULL
);

INSERT INTO users (username, password) VALUES ('testuser', 'asdfasdfasdfasdfasdfasdfasdfasdf');
INSERT INTO users (username, password) VALUES ('testuser2', 'asdfasdfasdfasdfasdfasdfasdfasdf');
INSERT INTO users (username, password) VALUES ('testuser3', 'asdfasdfasdfasdfasdfasdfasdfasdf');
INSERT INTO users (username, password) VALUES ('testuser4', 'asdfasdfasdfasdfasdfasdfasdfasdf');
INSERT INTO users (username, password) VALUES ('testuser5', 'asdfasdfasdfasdfasdfasdfasdfasdf');
INSERT INTO users (username, password) VALUES ('0', 'asdfasdfasdfasdfasdfasdfasdfasdf');

INSERT INTO numericdata (datafield) VALUES (23.23);
INSERT INTO numericdata (datafield) VALUES (42.42);
INSERT INTO numericdata (datafield) VALUES (13.37);
INSERT INTO numericdata (datafield) VALUES (1.00);
INSERT INTO numericdata (datafield) VALUES (0);
