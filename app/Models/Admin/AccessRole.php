<?php

namespace App\Models\Admin;

use Illuminate\Database\Eloquent\Model;

class AccessRole extends Model
{
    protected $table = "access_to_roles";
    protected $fillable = ['role_id', 'menu_id', 'action_id'];
}
