<?php

/**
 * class Note
 * This class offers functionality to store sequential comments/notes about an external object or anything with a unique id.
 * It is not intended that once a note is save it can be editied or changed.
 */

use OpenEMR\Common\ORDataObject\ORDataObject;

class Note extends ORDataObject
{
    /*
    *   Database unique identifier
    *   @var id
    */
    var $id;

    /*
    *   DB unique identifier reference to some other table, this is not unique in the notes table
    *   @var int
    */
    var $foreign_id;

    /*
    *   Narrative comments about whatever object is represented by the foreign id this note is associated with
    *   @var string upto 255 character string
    */
    var $note;

    /*
    *   Foreign key identifier of who initially persisited the note,
    *   potentially ownership could be changed but that would be up to an external non-document object process
    *   @var int
    */
    var $owner;

    /*
    *   Date the note was first persisted
    *   @var date
    */
    var $date;

    /*
    *   Timestamp of the last time the note was changed and persisted, auto maintained by DB, manually change at your own peril
    *   @var int
    */
    var $revision;

    /**
     * Constructor sets all Note attributes to their default value
     * @param int $id optional existing id of a specific note, if omitted a "blank" note is created
     */
    function __construct($id = "")
    {
        //call the parent constructor so we have a _db to work with
        parent::__construct();

        //shore up the most basic ORDataObject bits
        $this->id = $id;
        $this->_table = "notes";

        $this->note = "";
        $this->date = date("Y-m-d H:i:s");

        if ($id != "") {
            $this->populate();
        }
    }

    /**
     * Convenience function to get an array of many document objects
     * For really large numbers of documents there is a way more efficient way to do this by overwriting the populate method
     * @param int $foreign_id optional id use to limit array on to a specific relation, otherwise every document object is returned
     */
    public static function notes_factory($foreign_id = "")
    {
        $notes = array();

        $sqlArray = array();

        if (empty($foreign_id)) {
            $foreign_id_sql = " like '%'";
        } else {
            $foreign_id_sql = " = ?";
            $sqlArray[] = strval($foreign_id);
        }

        $d = new note();
        $sql = "SELECT id FROM " . escape_table_name($d->_table) . " WHERE foreign_id " . $foreign_id_sql . " ORDER BY DATE DESC";
        //echo $sql;
        $result = $d->_db->Execute($sql, $sqlArray);

        while ($result && !$result->EOF) {
            $notes[] = new Note($result->fields['id']);
            $result->MoveNext();
        }

        return $notes;
    }

    public function getOwnerName()
    {
        if (!empty($this->owner)) {
            $user_info = sqlQuery("SELECT `fname`, `lname` FROM `users` where `id`=?", [$this->owner]);
            if (!empty($user_info)) {
                return ($user_info['fname'] . " " . $user_info['lname']);
            }
        }
    }

    /**
     * Convenience function to generate string debug data about the object
     */
    function toString($html = false)
    {
        $string .= "\n"
        . "ID: " . $this->id . "\n"
        . "FID: " . $this->foreign_id . "\n"
        . "note: " . $this->note . "\n"
        . "date: " . $this->date . "\n"
        . "owner: " . $this->owner . "\n"
        . "revision: " . $this->revision . "\n";

        if ($html) {
            return nl2br($string);
        } else {
            return $string;
        }
    }

    /**#@+
    *   Getter/Setter methods used by reflection to affect object in persist/poulate operations
    *   @param mixed new value for given attribute
    */
    function set_id($id)
    {
        $this->id = $id;
    }
    function get_id()
    {
        return $this->id;
    }
    function set_foreign_id($fid)
    {
        $this->foreign_id = $fid;
    }
    function get_foreign_id()
    {
        return $this->foreign_id;
    }
    function set_note($note)
    {
        $this->note = $note;
    }
    function get_note()
    {
        return $this->note;
    }
    function set_date($date)
    {
        $this->date = $date;
    }
    function get_date()
    {
        return $this->date;
    }
    function set_owner($owner)
    {
        $this->owner = $owner;
    }
    function get_owner()
    {
        return $this->owner;
    }
    /*
    *   No getter for revision because it is updated automatically by the DB.
    */
    function set_revision($revision)
    {
        $this->revision = $revision;
    }

    /*
    *   Overridden function to store current object state in the db.
    *   This overide is to allow for a "just in time" foreign id, often this is needed
    *   when the object is never directly exposed and is handled as part of a larger
    *   object hierarchy.
    *   @param int $fid foreign id that should be used so that this note can be related (joined) on it later
    */

    function persist($fid = "")
    {
        if (!empty($fid)) {
            $this->foreign_id = $fid;
        }

        parent::persist();
    }
} // end of Note
