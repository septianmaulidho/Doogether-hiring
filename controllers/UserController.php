<?php


namespace app\controllers;

use app\middleware\middleware;
use app\models\LoginForm;
use app\models\SignupForm;
use app\models\User;
use yii\rest\Controller;

class UserController extends Controller
{
    public $modelClass = User::class;

    public function actionLogin()
    {
        $jwtToken = new middleware();
        $model = new LoginForm();
        $body = \Yii::$app->request->post();
        if ($model->load($body, '') && $model->login()) {
            $user = $model->getUser()->toArray(["ID","name","email"]);
            $user["access_token"]=$jwtToken->genJWT($user);
            return $user;
        }

        \Yii::$app->response->statusCode = 422;
        return [
            'errors' => $model->errors
        ];
    }

    public function actionSignup()
    {
        $jwtToken = new middleware();
        $model = new SignupForm();
        if ($model->load(\Yii::$app->request->post(), '') && $model->signup()) {
            $user = $model->user->toArray(["ID","name","email"]);
            $user["access_token"]=$jwtToken->genJWT($user);
            return $user;
        }

        \Yii::$app->response->statusCode = 422;
        return [
            'errors' => $model->errors
        ];
    }
}