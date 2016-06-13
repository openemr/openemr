/* 
 * create a custom table for the form
 *
 * This table NEEDS a UNIQUE name
 */

CREATE TABLE IF NOT EXISTS `form_example` (
    /* these fields are common to all forms and should remain intact */
    id bigint(20) NOT NULL auto_increment,
    date datetime default NULL,
    pid bigint(20) default NULL,
    user varchar(255) default NULL,
    groupname varchar(255) default NULL,
    authorized tinyint(4) default NULL,
    activity tinyint(4) default NULL,

    /* these fields are customized to this form */
    form_date       datetime default NULL,  /* date the form was completed by client */
    name            varchar(255),           /* full name on the form */
    dob             datetime default NULL,  /* date of birth */
    phone           varchar(15),            /* phone number */
    address         varchar(255),           /* home address */
    notes           longtext,               /* free-text notes */
    sig             char(1),                /* Did client sign the paper version of the form? */
    sig_date        datetime default NULL,  /* Date the client signed the form */
    /* end of custom form fields */

    PRIMARY KEY (id)
) ENGINE=InnoDB;
