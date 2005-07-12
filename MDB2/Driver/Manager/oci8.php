<?php
// +----------------------------------------------------------------------+
// | PHP versions 4 and 5                                                 |
// +----------------------------------------------------------------------+
// | Copyright (c) 1998-2004 Manuel Lemos, Tomas V.V.Cox,                 |
// | Stig. S. Bakken, Lukas Smith                                         |
// | All rights reserved.                                                 |
// +----------------------------------------------------------------------+
// | MDB2 is a merge of PEAR DB and Metabases that provides a unified DB  |
// | API as well as database abstraction for PHP applications.            |
// | This LICENSE is in the BSD license style.                            |
// |                                                                      |
// | Redistribution and use in source and binary forms, with or without   |
// | modification, are permitted provided that the following conditions   |
// | are met:                                                             |
// |                                                                      |
// | Redistributions of source code must retain the above copyright       |
// | notice, this list of conditions and the following disclaimer.        |
// |                                                                      |
// | Redistributions in binary form must reproduce the above copyright    |
// | notice, this list of conditions and the following disclaimer in the  |
// | documentation and/or other materials provided with the distribution. |
// |                                                                      |
// | Neither the name of Manuel Lemos, Tomas V.V.Cox, Stig. S. Bakken,    |
// | Lukas Smith nor the names of his contributors may be used to endorse |
// | or promote products derived from this software without specific prior|
// | written permission.                                                  |
// |                                                                      |
// | THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS  |
// | "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT    |
// | LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS    |
// | FOR A PARTICULAR PURPOSE ARE DISCLAIMED.  IN NO EVENT SHALL THE      |
// | REGENTS OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT,          |
// | INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, |
// | BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS|
// |  OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED  |
// | AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT          |
// | LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY|
// | WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE          |
// | POSSIBILITY OF SUCH DAMAGE.                                          |
// +----------------------------------------------------------------------+
// | Author: Lukas Smith <smith@backendmedia.com>                         |
// +----------------------------------------------------------------------+

// $Id$

require_once 'MDB2/Driver/Manager/Common.php';

/**
 * MDB2 oci8 driver for the management modules
 *
 * @package MDB2
 * @category Database
 * @author Lukas Smith <smith@backendmedia.com>
 */
class MDB2_Driver_Manager_oci8 extends MDB2_Driver_Manager_Common
{
    // {{{ createDatabase()

    /**
     * create a new database
     *
     * @param object $db database object that is extended by this class
     * @param string $name name of the database that should be created
     * @return mixed MDB2_OK on success, a MDB2 error on failure
     * @access public
     */
    function createDatabase($name)
    {
        $db =& $this->getDBInstance();
        if (PEAR::isError($db)) {
            return $db;
        }

        $username = $db->options['database_name_prefix'].$name;
        $password = $db->dsn['password'] ? $db->dsn['password'] : $name;
        $tablespace = $db->options['default_tablespace']
            ? ' DEFAULT TABLESPACE '.$db->options['default_tablespace'] : '';

        $query = 'CREATE USER '.$username.' IDENTIFIED BY '.$password.$tablespace;
        $result = $db->standaloneQuery($query);
        if (PEAR::isError($result)) {
            return $result;
        }
        $query = 'GRANT CREATE SESSION, CREATE TABLE, UNLIMITED TABLESPACE, CREATE SEQUENCE TO '.$username;
        $result = $db->standaloneQuery($query);
        if (PEAR::isError($result)) {
            $query = 'DROP USER '.$username.' CASCADE';
            $result2 = $db->standaloneQuery($query);
            if (PEAR::isError($result2)) {
                return $db->raiseError(MDB2_ERROR, null, null,
                    'createDatabase: could not setup the database user ('.$result->getUserinfo().
                        ') and then could drop its records ('.$result2->getUserinfo().')');
            }
            return $result;
        }
        return MDB2_OK;
    }

    // }}}
    // {{{ dropDatabase()

    /**
     * drop an existing database
     *
     * @param object $db database object that is extended by this class
     * @param string $name name of the database that should be dropped
     * @return mixed MDB2_OK on success, a MDB2 error on failure
     * @access public
     */
    function dropDatabase($name)
    {
        $db =& $this->getDBInstance();
        if (PEAR::isError($db)) {
            return $db;
        }

        $username = $db->options['database_name_prefix'].$name;
        return $db->standaloneQuery('DROP USER '.$username.' CASCADE');
    }

    // }}}
    // {{{ alterTable()

    /**
     * alter an existing table
     *
     * @param object $db database object that is extended by this class
     * @param string $name name of the table that is intended to be changed.
     * @param array $changes associative array that contains the details of each type
     *                              of change that is intended to be performed. The types of
     *                              changes that are currently supported are defined as follows:
     *
     *                              name
     *
     *                                 New name for the table.
     *
     *                             added_fields
     *
     *                                 Associative array with the names of fields to be added as
     *                                  indexes of the array. The value of each entry of the array
     *                                  should be set to another associative array with the properties
     *                                  of the fields to be added. The properties of the fields should
     *                                  be the same as defined by the Metabase parser.
     *
     *
     *                             removed_fields
     *
     *                                 Associative array with the names of fields to be removed as indexes
     *                                  of the array. Currently the values assigned to each entry are ignored.
     *                                  An empty array should be used for future compatibility.
     *
     *                             renamed_fields
     *
     *                                 Associative array with the names of fields to be renamed as indexes
     *                                  of the array. The value of each entry of the array should be set to
     *                                  another associative array with the entry named name with the new
     *                                  field name and the entry named Declaration that is expected to contain
     *                                  the portion of the field declaration already in DBMS specific SQL code
     *                                  as it is used in the CREATE TABLE statement.
     *
     *                             changed_fields
     *
     *                                 Associative array with the names of the fields to be changed as indexes
     *                                  of the array. Keep in mind that if it is intended to change either the
     *                                  name of a field and any other properties, the changed_fields array entries
     *                                  should have the new names of the fields as array indexes.
     *
     *                                 The value of each entry of the array should be set to another associative
     *                                  array with the properties of the fields to that are meant to be changed as
     *                                  array entries. These entries should be assigned to the new values of the
     *                                  respective properties. The properties of the fields should be the same
     *                                  as defined by the Metabase parser.
     *
     *                                 If the default property is meant to be added, removed or changed, there
     *                                  should also be an entry with index ChangedDefault assigned to 1. Similarly,
     *                                  if the notnull constraint is to be added or removed, there should also be
     *                                  an entry with index ChangedNotNull assigned to 1.
     *
     *                             Example
     *                                 array(
     *                                     'name' => 'userlist',
     *                                     'added_fields' => array(
     *                                         'quota' => array(
     *                                             'type' => 'integer',
     *                                             'unsigned' => 1
     *                                         )
     *                                     ),
     *                                     'removed_fields' => array(
     *                                         'file_limit' => array(),
     *                                         'time_limit' => array()
     *                                         ),
     *                                     'changed_fields' => array(
     *                                         'gender' => array(
     *                                             'default' => 'M',
     *                                             'change_default' => 1,
     *                                         )
     *                                     ),
     *                                     'renamed_fields' => array(
     *                                         'sex' => array(
     *                                             'name' => 'gender',
     *                                         )
     *                                     )
     *                                 )
     * @param boolean $check indicates whether the function should just check if the DBMS driver
     *                              can perform the requested table alterations if the value is true or
     *                              actually perform them otherwise.
     * @access public
     * @return mixed MDB2_OK on success, a MDB2 error on failure
     */
    function alterTable($name, $changes, $check)
    {
        $db =& $this->getDBInstance();
        if (PEAR::isError($db)) {
            return $db;
        }

        foreach ($changes as $change_name => $change) {
            switch ($change_name) {
            case 'added_fields':
            case 'removed_fields':
            case 'changed_fields':
            case 'name':
                break;
            case 'renamed_fields':
            default:
                return $db->raiseError(MDB2_ERROR, null, null,
                    'alterTable: change type "'.$change_name.'" not yet supported');
            }
        }

        if ($check) {
            return MDB2_OK;
        }

        if (isset($changes['removed_fields'])) {
            $query = ' DROP (';
            $fields = $changes['removed_fields'];
            $skipped_first = false;
            foreach ($fields as $field_name => $field) {
                if ($skipped_first) {
                    $query .= ', ';
                }
                $query .= $field_name;
                $skipped_first = true;
            }
            $query .= ')';
            if (PEAR::isError($result = $db->query("ALTER TABLE $name $query"))) {
                return $result;
            }
            $query = '';
        }

        $query = (isset($changes['name']) ? 'RENAME TO '.$changes['name'] : '');

        if (isset($changes['added_fields'])) {
            $fields = $changes['added_fields'];
            foreach ($fields as $field_name => $field) {
                $query .= ' ADD (' . $db->getDeclaration($field['type'], $field_name, $field) . ')';
            }
        }

        if (isset($changes['changed_fields'])) {
            $fields = $changes['changed_fields'];
            foreach ($fields as $field_name => $field) {
                if (isset($renamed_fields[$field_name])) {
                    $old_field_name = $renamed_fields[$field_name];
                    unset($renamed_fields[$field_name]);
                } else {
                    $old_field_name = $field_name;
                }
                $change = '';
                $change_type = $change_default = false;
                if (isset($field['type'])) {
                    $change_type = $change_default = true;
                }
                if (isset($field['length'])) {
                    $change_type = true;
                }
                if (isset($field['changed_default'])) {
                    $change_default = true;
                }
                if ($change_type) {
                    $db->loadModule('Datatype');
                    $change .= ' '.$db->datatype->getTypeDeclaration($field['definition']);
                }
                if ($change_default) {
                    $default = (isset($field['definition']['default']) ? $field['definition']['default'] : null);
                    $change .= ' DEFAULT '.$db->quote($default, $field['definition']['type']);
                }
                if (isset($field['changed_not_null'])) {
                    $change .= (isset($field['notnull']) ? ' NOT' : '').' NULL';
                }
                if ($change) {
                    $query .= " MODIFY ($old_field_name$change)";
                }
            }
        }

        if (!$query) {
            return MDB2_OK;
        }

        return $db->query("ALTER TABLE $name $query");
    }

    // }}}
    // {{{ listDatabases()

    /**
     * list all databases
     *
     * @return mixed data array on success, a MDB2 error on failure
     * @access public
     */
    function listDatabases()
    {
        $db =& $this->getDBInstance();
        if (PEAR::isError($db)) {
            return $db;
        }

        if ($db->options['database_name_prefix']) {
            $query = "SELECT SUBSTR(username, "
                .(strlen($db->options['database_name_prefix'])+1)
                .") FROM dba_users WHERE username LIKE '"
                .$db->options['database_name_prefix']."%'";
        } else {
            $query = "SELECT username FROM dba_users WHERE username LIKE '%'";
        }
        $result = $db->standaloneQuery($query);
        if (PEAR::isError($result)) {
            return $result;
        }
        $databases = $result->fetchCol();
        if (PEAR::isError($databases)) {
            return $databases;
        }
        // is it legit to force this to lowercase?
        $databases = array_keys(array_change_key_case(array_flip($databases), CASE_LOWER));
        $result->free();
        return $databases;
    }

        // }}}
    // {{{ listUsers()

    /**
     * list all users in the current database
     *
     * @return mixed data array on success, a MDB2 error on failure
     * @access public
     */
    function listUsers()
    {
        $db =& $this->getDBInstance();
        if (PEAR::isError($db)) {
            return $db;
        }

        $query = 'SELECT username FROM sys.all_users';
        $users = $db->queryCol($query);
        if (PEAR::isError($users)) {
            return $users;
        }
        if ($db->options['portability'] & MDB2_PORTABILITY_LOWERCASE) {
            $users = array_flip($users);
            $users = array_change_key_case($users, CASE_LOWER);
            $users = array_flip($users);
        }
        return $users;
    }
    // }}}
    // {{{ listViews()

    /**
     * list all views in the current database
     *
     * @return mixed data array on success, a MDB2 error on failure
     * @access public
     */
    function listViews()
    {
        $db =& $this->getDBInstance();
        if (PEAR::isError($db)) {
            return $db;
        }

        $query = 'SELECT view_name FROM sys.user_views';
        $views = $db->queryCol($query);
        if (PEAR::isError($views)) {
            return $views;
        }
        if ($db->options['portability'] & MDB2_PORTABILITY_LOWERCASE) {
            $views = array_flip($views);
            $views = array_change_key_case($views, CASE_LOWER);
            $views = array_flip($views);
        }
        return $views;
    }

    // }}}
    // {{{ listFunctions()

    /**
     * list all functions in the current database
     *
     * @return mixed data array on success, a MDB2 error on failure
     * @access public
     */
    function listFunctions()
    {
        $db =& $this->getDBInstance();
        if (PEAR::isError($db)) {
            return $db;
        }

        $query = "SELECT name FROM sys.user_source WHERE line = 1 AND type = 'FUNCTION'";
        $functions = $db->queryCol($query);
        if (PEAR::isError($functions)) {
            return $functions;
        }
        if ($db->options['portability'] & MDB2_PORTABILITY_LOWERCASE) {
            $functions = array_flip($functions);
            $functions = array_change_key_case($functions, CASE_LOWER);
            $functions = array_flip($functions);
        }
        return $functions;
    }

    // }}}
    // {{{ listTables()

    /**
     * list all tables in the current database
     *
     * @return mixed data array on success, a MDB error on failure
     * @access public
     **/
    function listTables()
    {
        $db =& $this->getDBInstance();
        if (PEAR::isError($db)) {
            return $db;
        }

        $query = 'SELECT table_name FROM sys.user_tables';
        return $db->queryCol($query);
    }

    // }}}
    // {{{ listTableFields()

    /**
     * list all fields in a tables in the current database
     *
     * @param string $table name of table that should be used in method
     * @return mixed data array on success, a MDB error on failure
     * @access public
     */
    function listTableFields($table)
    {
        $db =& $this->getDBInstance();
        if (PEAR::isError($db)) {
            return $db;
        }

        $table = strtoupper($table);
        $query = "SELECT column_name FROM user_tab_columns WHERE table_name='$table' ORDER BY column_id";
        $fields = $db->queryCol($query);
        if (PEAR::isError($result)) {
            return $result;
        }
        if ($db->options['portability'] & MDB2_PORTABILITY_LOWERCASE) {
            $fields = array_flip(array_change_key_case(array_flip($fields), CASE_LOWER));
        }
        return $fields;
    }

    // }}}
    // {{{ createSequence()

    /**
     * create sequence
     *
     * @param object $db database object that is extended by this class
     * @param string $seq_name name of the sequence to be created
     * @param string $start start value of the sequence; default is 1
     * @return mixed MDB2_OK on success, a MDB2 error on failure
     * @access public
     */
    function createSequence($seq_name, $start = 1)
    {
        $db =& $this->getDBInstance();
        if (PEAR::isError($db)) {
            return $db;
        }

        $sequence_name = $db->getSequenceName($seq_name);
        return $db->query("CREATE SEQUENCE $sequence_name START WITH $start INCREMENT BY 1".
            ($start < 1 ? " MINVALUE $start" : ''));
    }

    // }}}
    // {{{ dropSequence()

    /**
     * drop existing sequence
     *
     * @param object $db database object that is extended by this class
     * @param string $seq_name name of the sequence to be dropped
     * @return mixed MDB2_OK on success, a MDB2 error on failure
     * @access public
     */
    function dropSequence($seq_name)
    {
        $db =& $this->getDBInstance();
        if (PEAR::isError($db)) {
            return $db;
        }

        $sequence_name = $db->getSequenceName($seq_name);
        return $db->query("DROP SEQUENCE $sequence_name");
    }

    // }}}
    // {{{ listSequences()

    /**
     * list all sequences in the current database
     *
     * @return mixed data array on success, a MDB2 error on failure
     * @access public
     */
    function listSequences()
    {
        $db =& $this->getDBInstance();
        if (PEAR::isError($db)) {
            return $db;
        }

        $query = "SELECT sequence_name FROM sys.user_sequences";
        $table_names = $db->queryCol($query);
        if (PEAR::isError($table_names)) {
            return $table_names;
        }
        $sequences = array();
        for ($i = 0, $j = count($table_names); $i < $j; ++$i) {
            if ($sqn = $this->_isSequenceName($table_names[$i]))
                $sequences[] = $sqn;
        }
        if ($db->options['portability'] & MDB2_PORTABILITY_LOWERCASE) {
            $sequences = array_flip($sequences);
            $sequences = array_change_key_case($sequences, CASE_LOWER);
            $sequences = array_flip($sequences);
        }
        return $sequences;
    }}
?>