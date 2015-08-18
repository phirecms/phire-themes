--
-- Themes Module SQLite Database for Phire CMS 2.0
--

--  --------------------------------------------------------

--
-- Set database encoding
--

PRAGMA encoding = "UTF-8";
PRAGMA foreign_keys = ON;

-- --------------------------------------------------------

--
-- Table structure for table "themes"
--

CREATE TABLE IF NOT EXISTS "[{prefix}]themes" (
  "id" integer NOT NULL PRIMARY KEY AUTOINCREMENT,
  "name" varchar NOT NULL,
  "file" varchar NOT NULL,
  "folder" varchar NOT NULL,
  "active" integer NOT NULL,
  "assets" text,
  UNIQUE ("id")
) ;

INSERT INTO "sqlite_sequence" ("name", "seq") VALUES ('[{prefix}]themes', 10000);
CREATE INDEX "theme_name" ON "[{prefix}]themes" ("name");
