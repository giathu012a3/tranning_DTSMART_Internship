<?php

namespace app\models\forms;

use Yii;
use yii\base\Model;
use app\models\Category;

class CategoryForm extends Model
{
    private $_category;

    public $name;
    public $status;

    public function __construct(?Category $category = null, $config = [])
    {
        if ($category === null) {
            $category = new Category();
        }
        $this->_category = $category;

        $this->name = $category->name;
        $this->status = $category->status;

        parent::__construct($config);
    }

    public function rules()
    {
        return [
            [['name'], 'required'],
            [['status'], 'integer'],
            [['name'], 'string', 'max' => 255],
            [['name'], 'unique', 'targetClass' => Category::class, 'targetAttribute' => 'name', 'filter' => function ($query) {
                if (!$this->_category->isNewRecord) {
                    $query->andWhere(['not', ['id' => $this->_category->id]]);
                }
            }, 'message' => 'This category name already exists.'],
        ];
    }

    /**
     * @return Category
     */
    public function getCategory()
    {
        return $this->_category;
    }

    public function save()
    {
        if (!$this->validate()) {
            return false;
        }

        $transaction = Yii::$app->db->beginTransaction();
        try {
            $this->_category->name = $this->name;
            $this->_category->status = $this->status ?? 1;

            if (!$this->_category->save()) {
                $this->addErrors($this->_category->getErrors());
                return false;
            }

            $transaction->commit();
            return true;
        } catch (\Exception $e) {
            $transaction->rollBack();
            throw $e;
        }
    }
}
