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
            [['name', 'status'], 'validateAnyChange', 'skipOnEmpty' => false],
            [['name'], 'unique', 'targetClass' => Category::class, 'targetAttribute' => 'name', 'filter' => function ($query) {
                $query->andWhere(['deleted_at' => null]);
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

    public function validateAnyChange($attribute, $params)
    {
        if (!$this->_category->isNewRecord) {
            $nameUnchanged = ($this->name === $this->_category->name);
            $statusUnchanged = ((int)$this->status === (int)$this->_category->status);

            if ($nameUnchanged && $statusUnchanged) {
                $this->addError('name', 'No changes detected. Please modify at least one field to update.');
            }
        }
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
