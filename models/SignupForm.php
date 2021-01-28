<?php


namespace app\models;


use yii\base\Model;
use yii\helpers\VarDumper;

class SignupForm extends Model
{
    public $name;
    public $email;
    public $password;
    public $password_repeat;
    public $user=null;

    public function rules(){
        return [
            [['name','email','password','password_repeat'],'required'],
            ['email','email'],
            ['password','string','min'=>4,'max'=>16],
            ['password','compare','compareAttribute'=>'password_repeat'],
            ['email', 'unique',
                'targetClass' => '\app\models\User',
                'message' => 'This email has already been taken.'
            ]
        ];
    }

    public function signup(){
        if ($this->validate()){
            date_default_timezone_set("Asia/Jakarta");
            $dateNow=date("Y-m-d H:i:sa");
            $user = new User();
            $user->name=$this->name;
            $user->email=$this->email;
            $user->password=\Yii::$app->security->generatePasswordHash($this->password);
            $user->created=substr($dateNow,0,strlen($dateNow)-3);
            $user->updated=substr($dateNow,0,strlen($dateNow)-3);

            if($user->save()){
                $this->user = $user;
                return true;
            }
        }
        return false;
    }
}