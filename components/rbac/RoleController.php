<?php

/**
 * @author      David Rivaldy <davidrivaldy@gmail.com>
 * @copyright   2018 | DRAC
 */

namespace app\components\rbac;

use Yii;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\base\NotSupportedException;
use yii\filters\VerbFilter;
use yii\rbac\Item;
use app\models\rbac\AuthItem;
use app\models\rbac\searchs\AuthItem as AuthItemSearch;

class RoleController extends Controller
{
    public function behaviors()
    {
        return [
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'delete' => ['post'],
                    'assign' => ['post'],
                    'remove' => ['post'],
                ],
            ],
        ];
    }

    public function actionIndex()
    {
        $this->view->title = Yii::t('app', 'role');

        $searchModel = new AuthItemSearch(['type' => $this->type]);
        $dataProvider = $searchModel->search(Yii::$app->request->getQueryParams());

        return $this->render('index', [
            'dataProvider' => $dataProvider,
            'searchModel' => $searchModel,
        ]);
    }

    public function actionView($id)
    {
        $model = $this->findModel($id);

        return $this->render('view', ['model' => $model]);
    }

    public function actionCreate()
    {
        $this->view->title = Yii::t('app', 'create');

        $model = new AuthItem(null);
        
        $model->type = $this->type;
        
        if ($model->load(Yii::$app->getRequest()->post()) && $model->save()) {
            return $this->redirect(['view', 'id' => $model->name]);
        } else {
            return $this->render('create', ['model' => $model]);
        }
    }

    public function actionUpdate($id)
    {
        $this->view->title = Yii::t('app', 'update');

        $model = $this->findModel($id);
        
        if ($model->load(Yii::$app->getRequest()->post()) && $model->save()) {
            return $this->redirect(['view', 'id' => $model->name]);
        }

        return $this->render('update', ['model' => $model]);
    }

    public function actionDelete($id)
    {
        $model = $this->findModel($id);
        
        Configs::authManager()->remove($model->item);
        
        Helper::invalidate();

        return $this->redirect(['index']);
    }

    public function actionAssign($id)
    {
        $items      = Yii::$app->getRequest()->post('items', []);
        $model      = $this->findModel($id);
        $success    = $model->addChildren($items);
        
        Yii::$app->getResponse()->format = 'json';

        return array_merge($model->getItems(), ['success' => $success]);
    }

    public function actionRemove($id)
    {
        $items      = Yii::$app->getRequest()->post('items', []);
        $model      = $this->findModel($id);
        $success    = $model->removeChildren($items);
        
        Yii::$app->getResponse()->format = 'json';

        return array_merge($model->getItems(), ['success' => $success]);
    }

    public function getViewPath()
    {
        return $this->module->getViewPath() . DIRECTORY_SEPARATOR . 'role';
    }

    public function labels()
    {
        throw new NotSupportedException(get_class($this) . ' does not support labels().');
    }

    public function getType()
    {
        
    }

    protected function findModel($id)
    {
        $auth = Configs::authManager();
        $item = $this->type === Item::TYPE_ROLE ? $auth->getRole($id) : $auth->getPermission($id);
        
        if ($item) {
            return new AuthItem($item);
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }
}