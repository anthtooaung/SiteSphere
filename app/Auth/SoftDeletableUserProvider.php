<?php

namespace App\Auth;

use Illuminate\Auth\EloquentUserProvider;

class SoftDeletableUserProvider extends EloquentUserProvider
{
    /**
     * Retrieve a user by their unique identifier, including soft-deleted users.
     *
     * This allows banned (soft-deleted) users to remain authenticated
     * so they can access the appeal page after login.
     */
    public function retrieveById($identifier)
    {
        $model = $this->createModel();

        return $this->newModelQuery($model)
            ->withTrashed()
            ->where($model->getAuthIdentifierName(), $identifier)
            ->first();
    }
}
