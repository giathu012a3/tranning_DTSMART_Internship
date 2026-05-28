<?php

namespace app\models\query;

/**
 * This is the ActiveQuery class for [[MembershipLevel]].
 *
 * @see MembershipLevel
 */
class MembershipLevelsQuery extends \yii\db\ActiveQuery
{
    public function active()
    {
        return $this->andWhere(['status' => 1]);
    }

    public function byPoints($points)
    {
        return $this->andWhere(['<=', 'points_required', $points])
            ->orderBy(['points_required' => SORT_DESC]);
    }

    /**
     * {@inheritdoc}
     * @return MembershipLevel[]|array
     */
    public function all($db = null)
    {
        return parent::all($db);
    }

    /**
     * {@inheritdoc}
     * @return MembershipLevel|array|null
     */
    public function one($db = null)
    {
        return parent::one($db);
    }
}
