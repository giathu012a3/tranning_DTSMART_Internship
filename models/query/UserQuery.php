<?php

namespace app\models\query;

/**
 * This is the ActiveQuery class for [[User]].
 *
 * @see User
 */
class UserQuery extends \yii\db\ActiveQuery
{
    public function active()
    {
        return $this->andWhere(['users.status' => 1]);
    }

    /**
     * {@inheritdoc}
     * @return User[]|array
     */
    public function all($db = null)
    {
        return parent::all($db);
    }

    /**
     * {@inheritdoc}
     * @return User|array|null
     */
    public function one($db = null)
    {
        return parent::one($db);
    }

    public function byId($id)
    {
        return $this->andWhere(['users.id' => $id]);
    }

    public function byEmail($email)
    {
        return $this->andWhere(['users.email' => $email]);
    }

    public function byToken($token)
    {
        return $this->andWhere(['users.access_token' => $token]);
    }

    public function withMembership()
    {
        return $this->with(['membershipLevel']);
    }

    public function withOrders()
    {
        return $this->with(['orders']);
    }

    public function withRoles()
    {
        return $this->with(['roles']);
    }
}
