<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class CareerApplication extends Model {
    protected $fillable=['name','email','phone','address','years_of_experience','expected_salary','motivation','attachment_path','attachment_name','attachment_size'];
    protected $hidden=['attachment_path'];
}
