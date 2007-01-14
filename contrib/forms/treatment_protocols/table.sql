# id is the form ID. Negative form IDs are used for the templates.
# (rownbr, colnbr) start at (0, 0) for cell data.  (-1, -1) is used for the
# template name.
#
# datatype is one of:
# 0 = unused cell
# 1 = static text
# 2 = checkbox
# 3 = text input
#
CREATE TABLE IF NOT EXISTS form_treatment_protocols (
 id                int          NOT NULL,
 rownbr            int          NOT NULL DEFAULT 0,
 colnbr            int          NOT NULL DEFAULT 0,
 datatype          tinyint      NOT NULL DEFAULT 0,
 value             varchar(255) DEFAULT NULL,
 PRIMARY KEY (id, rownbr, colnbr)
) TYPE=MyISAM;
