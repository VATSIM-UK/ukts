<?php


namespace VATSIMUK\Auth\Remote\Models;

use VATSIMUK\Auth\Remote\RemoteModel;

class RemoteUser extends RemoteModel
{
    protected static $unguarded = true;
    protected static $singleMethod = "user";
    protected static $manyMethod = "users";

    protected static $defaultFields = [
        "name_first",
        "name_last",
    ];
}
