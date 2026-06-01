<?php

namespace app\models;

class ProductTagModel extends ProductTag
{
    /**
     * @return \yii\db\ActiveQuery
     */
    public function getProduct()
    {
        return $this->hasOne(ProductModel::class, ['id' => 'product_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getTag()
    {
        return $this->hasOne(TagModel::class, ['id' => 'tag_id']);
    }
}
