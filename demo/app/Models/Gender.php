<?php

namespace App\Models;

use App\Models\Base\Gender as BaseGender;

/**
 * Skeleton subclass for representing a row from the 'gender' table.
 *
 *
 *
 * You should add additional methods to this class to meet the
 * application requirements.  This class will only be generated as
 * long as it does not already exist in the output directory.
 *
 */
class Gender extends BaseGender
{
    public function getSomething()
    {
        return ChildQuery::create()->findOne();
    }
}
