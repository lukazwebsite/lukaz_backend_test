<?php

namespace App\Models\Admin;

use Illuminate\Database\Eloquent\Model;

class AccessUser extends Model
{
    protected $table = "access_to_users";
    protected $fillable = ['user_id', 'menu_id', 'action_id'];
}
