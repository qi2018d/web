<?php
/**
 * Created by PhpStorm.
 * User: leo
 * Date: 2018-01-29
 * Time: 오전 10:29
 */

namespace Iot\Model;


use Slimvc\Core\Model;

class DataModel extends Model
{
    public function getUser($id){
        $sql = "SELECT user_id, username, status_public
                FROM user
                WHERE user_id = ?";

    }
}