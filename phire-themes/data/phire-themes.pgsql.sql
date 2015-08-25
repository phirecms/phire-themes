--
-- Themes Module PostgreSQL Database for Phire CMS 2.0
--

-- --------------------------------------------------------

--
-- Table structure for table "themes"
--

CREATE SEQUENCE theme_id_seq START 10001;

CREATE TABLE IF NOT EXISTS "[{prefix}]themes" (
  "id" integer NOT NULL DEFAULT nextval('theme_id_seq'),
  "name" varchar(255) NOT NULL,
  "file" varchar(255) NOT NULL,
  "folder" varchar(255) NOT NULL,
  "active" integer NOT NULL,
  "assets" text,
  PRIMARY KEY ("id")
) ;

ALTER SEQUENCE theme_id_seq OWNED BY "[{prefix}]themes"."id";
CREATE INDEX "theme_name" ON "[{prefix}]themes" ("name");
