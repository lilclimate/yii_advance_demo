<?php
namespace backend\controllers;

use Yii;
use yii\web\Controller;
use yii\filters\VerbFilter;
use yii\filters\AccessControl;
use common\models\LoginForm;
use common\models\User;

/**
 * Site controller
 */
class SiteController extends Controller
{

    private $manifest;

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'rules' => [
                    [
                        'actions' => ['login', 'error'],
                        'allow' => true,
                    ],
                    [
                        'actions' => ['login', 'error'],
                        'allow' => true,
                    ],
                    [
                        'actions' => ['logout', 'index'],
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                    [
                        'actions' => ['test', 'index'],
                        'allow' => true,
                        'roles' => ['?'],
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
     * @inheritdoc
     */
    public function actions()
    {
        return [
            'error' => [
                'class' => 'yii\web\ErrorAction',
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

    public function actionTest() {
        $result = User::findByUsername('test123');
        // sleep(3);
        // $path = '/data/www_self/yii_demo/advanced/backend/runtime/debug/singleDebug.data';
        // $data = unserialize(file_get_contents($path));
        // // $data = file_get_contents($path);
        // echo "<PRE>";print_R($data);exit;
        echo "<PRE>";print_R($this->debug());exit;
    }

    public function debug()
    {
        $searchModel = new \yii\debug\models\search\Debug();
        $dataProvider = $searchModel->search($_GET, $this->getManifest());

        $tags = array_keys($this->getManifest());
        $tag = reset($tags);

        $dataPath = Yii::getAlias('@runtime/debug');
        $maxRetry = 0;
        for ($retry = 0; $retry <= $maxRetry; ++$retry) {
            $manifest = $this->getManifest($retry > 0);
            if (isset($manifest[$tag])) {
                $dataFile = $dataPath . "/$tag.data";
                $data = unserialize(file_get_contents($dataFile));

                return $data;
            }
            sleep(1);
        }

        return ['error_msg' => 'Unable to find debug data tagged with ' . $tag];
    }

    protected function getManifest($forceReload = false)
    {
        if ($this->manifest === null || $forceReload) {
            if ($forceReload) {
                clearstatcache();
            }

            $dataPath = Yii::getAlias('@runtime/debug');
            $indexFile = $dataPath . '/index.data';

            $content = '';
            $fp = @fopen($indexFile, 'r');
            if ($fp !== false) {
                @flock($fp, LOCK_SH);
                $content = fread($fp, filesize($indexFile));
                @flock($fp, LOCK_UN);
                fclose($fp);
            }

            if ($content !== '') {
                $this->manifest = array_reverse(unserialize($content), true);
            } else {
                $this->manifest = [];
            }
        }

        return $this->manifest;
    }

    /**
     * Login action.
     *
     * @return string
     */
    public function actionLogin()
    {
        if (!Yii::$app->user->isGuest) {
            return $this->goHome();
        }
        

        $model = new LoginForm();
        if ($model->load(Yii::$app->request->post()) && $model->login()) {
            return $this->goBack();
        } else {
            return $this->render('login', [
                'model' => $model,
            ]);
        }
    }

    /**
     * Logout action.
     *
     * @return string
     */
    public function actionLogout()
    {
        Yii::$app->user->logout();

        return $this->goHome();
    }
}
