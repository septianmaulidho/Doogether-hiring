<?php
namespace app\middleware;

use \Firebase\JWT\JWT;

class middleware
{
    // key for JWT signing and validation, shouldn't be changed
    private $key = "secretSignKey";

    public function genJWT($payload) {
        $payload["exp"]=time() + (60 * 60 * 24 * 30);
        return JWT::encode($payload, $this->key);
    }

    public function validJWT($token) {
        $res = array(false, '');
        // using a try and catch to verify
        $res = array(false, '');
        // using a try and catch to verify
        try {
            //$decoded = JWT::decode($token, $this->key, array('HS256'));
            $decoded = JWT::decode($token, $this->key, array('HS256'));
        } catch (\Exception $e) {
            return $res;
        }
        $res['0'] = true;
        $res['1'] = (array) $decoded;

        return $res;
    }
}