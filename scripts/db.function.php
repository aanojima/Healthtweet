<?php

require_once '/home/anojima/scripts/const.inc.php';

function getDB() {
    static $db;
    MongoCursor::$timeout = 60000;
    if(!is_object($db)) {
        //$m = new MongoClient(DB_CONNECT_STRING, array("connect" => false, "readPreference" => MongoClient::RP_PRIMARY_PREFERRED));
        $m = new Mongo(DB_CONNECT_STRING); //, array("connect" => false, "readPreference" => MongoClient::RP_PRIMARY_PREFERRED));
        $m->twitter->setSlaveOkay(true);
        $db = $m->twitter;
    }
    return $db;
}

// generate a lookup table assoc array from a collection
function getLookup($collection, $q = array(), $key = "_id", $val = "name") {
    $db = getDB();
    $c = $db->$collection->find($q, array($key, $val));
    while($c->hasNext()) {
        $r = $c->getNext();
        $ret[(string)$r[$key]] = (isset($r[$val]) ? $r[$val] : null);
    }
    return $ret;
}

// unfortunately, with integer IDs (instead of native Mongo IDs) we lose the built-in Mongo upsert capability.

// insert document with an auto-created integer _id, return _id
// if it already exists, update, return _id
// we assume we're working with a single unique record; this function is mainly used for "lookup table" collections such as place, age, source
// returns: the integer ID of the record, or null if failure
function updateOrInsert($collection, $data, $criteria = array()) {
    $db = getDB();
    if(isset($data['_id'])) {
        $db->$collection->update(array('_id' => $data['_id']), $data, array('upsert' => true, 'multiple' => false));
        return $data['_id'];
    } else if(!empty($criteria) && is_array($criteria)) {
        $q = array_intersect_key($data, array_flip($criteria));
        $r = (count($q) ? $db->$collection->findOne($q) : null);
        if(isset($r['_id'])) {
            $db->$collection->update(array('_id' => $r['_id']), $data);
            return $r['_id'];
        } else {
            return insertWithAutoInc($collection, $data);
        }
    } else {
        return insertWithAutoInc($collection, $data);
    }
}

// insert incrementing _id values into a collection
function insertWithAutoInc($collection, $data) {
    $db = getDB();
    $success = false;
    for($j = 0; $j < 10; $j++) {    // should NEVER take more than one or two attempts
        // determine next _id value to try
        $c = $db->$collection->find(array(), array("_id"))->sort(array('_id' => -1))->limit(1);
        if(is_object($c) && $c->hasNext()) {
            $ob = $c->getNext();
            $data['_id'] = $ob['_id'] + 1;
        } else {
            $data['_id'] = 1;
        }
        try {
            $db->$collection->insert($data, array('safe' => true));
        } catch(Exception $err) {
            if($err && $err->getCode()) {
                if($err->getCode() == 11000) { // dup key 
                    continue;
                } else {
                    error_log("unexpected error inserting data: " . print_r($err, true));
                }
            }
        }
        $success = true;
        break;
    }
    if($success) {
        return $data['_id'];
    }
}

?>
