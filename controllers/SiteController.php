<?php

namespace app\controllers;

use Yii;
use yii\filters\AccessControl;
use yii\web\Controller;
use yii\web\Response;
use yii\filters\VerbFilter;
use app\models\LoginForm;
use app\models\ContactForm;

class SiteController extends Controller
{

    public function beforeAction($action)
    {
        if (!Yii::$app->session->has('key')){
            Yii::$app->session->set('key', '');
        }
        return parent::beforeAction($action);
    }
    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'only' => ['logout'],
                'rules' => [
                    [
                        'actions' => ['logout'],
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'logout' => ['post'],
                ],
            ],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function actions()
    {
        return [
            'error' => [
                'class' => 'yii\web\ErrorAction',
            ],
            'captcha' => [
                'class' => 'yii\captcha\CaptchaAction',
                'fixedVerifyCode' => YII_ENV_TEST ? 'testme' : null,
            ],
        ];
    }

    /**
     * Displays homepage.
     *
     * @return string
     */
    public function actionIndex()
    {
        return $this->render('index');
    }

    /**
     * Login action.
     *
     * @return Response|string
     */
    public function actionKey()
    {
        /*if (!Yii::$app->user->isGuest) {
            return $this->goHome();
        }

        $model = new LoginForm();
        if ($model->load(Yii::$app->request->post()) && $model->login()) {
            return $this->goBack();
        }

        $model->password = '';
        return $this->render('login', [
            'model' => $model,
        ]);*/
        if (Yii::$app->request->isPost){
            Yii::$app->session->set('key', Yii::$app->request->post('key'));
        }
        return $this->render('key');
    }

    public function actionTables()
    {
        $tables = Yii::$app->db->createCommand("SELECT * FROM INFORMATION_SCHEMA.tables WHERE TABLE_SCHEMA='test'")->queryAll();
        return $this->render('tables', ['data' => $tables]);
    }

    public function actionTable($name = Null)
    {
        if (!is_null($name)){
            $data = [];
            $encryptedColumns = [];
            // ToDo: add filter
            if (Yii::$app->request->isPost){
                $t = "";
                $params = Yii::$app->request->post('columns');
                if (count($params)){
                    foreach ($params as $key => $value) {
                        $t .= ", AES_DECRYPT($key, '".Yii::$app->session->get('key')."') as $key";
                        $encryptedColumns[] = $key;
                    }
                }
                $data = Yii::$app->db->createCommand("SELECT *".$t.", 'Редактировать', 'Удалить' FROM $name LIMIT 10")->queryAll();
            } else{
                $data = Yii::$app->db->createCommand("SELECT *, 'Редактировать', 'Удалить' FROM $name LIMIT 10")->queryAll();
            }
            return $this->render('table', ['data' => $data, 'name' => $name, 'encrypted' => $encryptedColumns]);
        }
    }

    public function actionAdd($name = Null)
    {
        if (!is_null($name)){
            preg_match("/dbname=([\w\d]+)/", Yii::$app->getDb()->dsn, $t);
            $dbname = $t[1];
            $columns = Yii::$app->db->createCommand("SELECT COLUMN_NAME as name FROM information_schema.columns WHERE table_schema='".$dbname."' AND table_name='$name'")->queryAll();
            $values = "(";
            $cols = "(";
            foreach ($columns as $column) {
                if ($column["name"] == "id"){
                    continue;
                }
                $cols .= $column["name"] . ",";
                $values .= "NULL,";
            }
            $cols = substr($cols, 0, -1) . ")";
            $values = substr($values, 0, -1) . ")";
            Yii::$app->db->createCommand("INSERT INTO $name $cols VALUES $values")->execute();
            return $this->redirect("/table/$name");
        }
        return $this->redirect("/tables");
    }

    public function actionDelete($name = Null, $id = 0)
    {
        if (!is_null($name) && is_numeric($id) && ($id > 0)){
            Yii::$app->db->createCommand("DELETE FROM $name WHERE id=$id")->execute();
        } else{

        }
        return $this->redirect("/table/$name");
    }

    public function actionRead($name = Null, $id = 0)
    {
        $data = Yii::$app->db->createCommand("SELECT * FROM $name WHERE id=$id")->queryOne();
        $encryptedColumns = [];
        if (Yii::$app->request->isPost){
            $t = "";
            $params = Yii::$app->request->post('columns');
            if (count($params)){
                foreach ($params as $key => $value) {
                    $t .= ", AES_DECRYPT($key, '".Yii::$app->session->get('key')."') as $key";
                    $encryptedColumns[] = $key;
                }
            }
            $values = "";
            $pars = Yii::$app->request->post();
            unset($pars['columns']);
            unset($pars['_csrf']);
            unset($pars['id']);
            foreach ($pars as $key => $value) {
                if (!empty($params) && !in_array($key, array_keys($params)) || empty($params)){
                    $values .= "$key='$value',";
                } else{
                    $values .= "$key=AES_ENCRYPT('$value', '".Yii::$app->session->get('key')."'),";
                }
            }
            $values = substr($values, 0, -1);
            Yii::$app->db->createCommand("UPDATE $name SET $values WHERE id=$id")->execute();
            return $this->redirect("/table/$name");
        }
        return $this->render("read", ["data" => $data, "name" => $name, "id" => $id, "encrypted" => $encryptedColumns]);
    }

    public function actionDeletetable($name = Null)
    {
        if (!is_null($name)){
            Yii::$app->db->createCommand("DROP TABLE $name")->execute();
        }
        return $this->redirect(['site/tables']);
    }  
    /**
     * Logout action.
     *
     * @return Response
     */
    public function actionLogout()
    {
        Yii::$app->user->logout();

        return $this->goHome();
    }

    /**
     * Displays contact page.
     *
     * @return Response|string
     */
    public function actionContact()
    {
        $model = new ContactForm();
        if ($model->load(Yii::$app->request->post()) && $model->contact(Yii::$app->params['adminEmail'])) {
            Yii::$app->session->setFlash('contactFormSubmitted');

            return $this->refresh();
        }
        return $this->render('contact', [
            'model' => $model,
        ]);
    }

    /**
     * Displays about page.
     *
     * @return string
     */
    public function actionAbout()
    {
        return $this->render('about');
    }
}
