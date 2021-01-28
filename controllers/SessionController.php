<?php

namespace app\controllers;

use app\middleware\middleware;
use PHPUnit\Framework\Error\Error;
use Yii;
use app\models\Session;
use app\models\SessionSearch;
use yii\rest\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;

/**
 * SessionController implements the CRUD actions for Session model.
 */
class SessionController extends Controller
{
    public $modelClass = session::class;
    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return [
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'delete' => ['DELETE'],
                ],
            ],
        ];
    }

    /**
     * Lists all Session models.
     * @return mixed
     */

     public function validate($token,$userID){
        $jwtToken = new middleware();
        $tokenValidation = $jwtToken->validJWT($token);
        if($tokenValidation[0]){
            if(!$userID){
                return true;
            }
            else{
                return $userID===$tokenValidation[1]["ID"]?true:false;
            }
        }
        else{
            return false;
        }
    }

    public function actionIndex()
    {
        // define respone and format of respone
        $response = Yii::$app->response;
        $response->format = \yii\web\Response::FORMAT_JSON;

        //define search model & data provider
        $searchModel = new SessionSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $response->data = [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider
        ];
    }

    /**
     * Displays a single Session model.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */

    /**
     * Creates a new Session model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $response = Yii::$app->response;
        $response->format = \yii\web\Response::FORMAT_JSON;
        date_default_timezone_set("Asia/Jakarta");
        $token = explode(" ",\Yii::$app->request->headers["authorization"])[1];
        if(! $this->validate($token,0)){ //Validate user has authorization
            \Yii::$app->response->statusCode=401;
            return $response->data=['errors'=>'Unauthorized'];
        }
        $model = new Session();
        $jwtToken = new middleware();
        $model['userID'] = $jwtToken->validJWT($token)[1]["ID"]; //set userId from decoded token
        $model['created'] = substr(date("Y-m-d H:i:sa"),0,strlen(date("Y-m-d H:i:sa"))-3);
        $model['updated'] = substr(date("Y-m-d H:i:sa"),0,strlen(date("Y-m-d H:i:sa"))-3);

        //load body input to model Session and if true save model
        if ($model->load(Yii::$app->request->post(),'') && $model->save()) {
            return $response->data = $model;
        }
        // If there error send respone coresponding to errors
        Yii::$app->response->statusCode=422;
        return $response->data = [
            'errors' => $model->errors,
        ];
    }

    /**
     * Updates an existing Session model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionUpdate()
    {
        $response = Yii::$app->response;
        $response->format= \yii\web\Response::FORMAT_JSON; //Set respone format
        date_default_timezone_set("Asia/Jakarta");
        $id = Yii::$app->request->queryParams['ID'];
        $model = $this->findModel($id); //Find model to update
        $token = explode(" ",\Yii::$app->request->headers["authorization"])[1];
        if(! $this->validate($token,$model["userID"])){ // Validate if user has authorization
            \Yii::$app->response->statusCode=401;
            return $response->data=['errors'=>'Unauthorized'];
        }
        $model['updated'] = substr(date("Y-m-d H:i:sa"),0,strlen(date("Y-m-d H:i:sa"))-3);

        // load body input and save if no errors
        $bodyUpdate = Yii::$app->request->getBodyParams();
        $this->validateUpdateBody($bodyUpdate);
        if ($model->load($bodyUpdate,'') && $model->save()) {
            return $response->data = ['respon'=>'data updated']; //send message
        }

        // If there error send respone coresponding to errors
        Yii::$app->response->statusCode=422;
        return $response->data = [
            'errors' => $model->errors
        ];
    }

    /**
     * Deletes an existing Session model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionDelete()
    {
        $response = Yii::$app->response;
        $response->format= \yii\web\Response::FORMAT_JSON; //Set respone format
        $id = Yii::$app->request->queryParams['ID'];
        $model = $this->findModel($id); //Find model to update
        $token = explode(" ",\Yii::$app->request->headers["authorization"])[1];

        if(! $this->validate($token,$model["userID"])){ // Validate if user has authorization
            \Yii::$app->response->statusCode=401;
            return $response->data=['errors'=>'Unauthorized'];
        }

        if($model->delete()){ //Delete model
            // If delete success send message success
            return $response->data = ['respone'=> 'Data has been deleted'];
        }

        // If there are errors send errors
        Yii::$app->response->statusCode=422;
        return $response->data = [
            'errors' => $model->errors
        ];
    }

    /**
     * Finds the Session model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return Session the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Session::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }

    public function validateUpdateBody($bodyUpdate){
        $response = Yii::$app->response;
        $response->format= \yii\web\Response::FORMAT_JSON; //Set respone format
        $keysValidate = ['name','description','duration','start'];
        foreach (array_keys($bodyUpdate) as &$value) {
            if(!in_array($value,$keysValidate)){
                throw new Error($value . ' is not allowed!',405,'',0);
            }
        }
    }
}
