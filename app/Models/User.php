<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Auth\Authenticatable as AuthenticableTrait;

/**
 * Class User
 * 
 * @property int $gfuser_id
 * @property string $gfuser_alias
 * @property string $gfuser_password
 * @property string $gfuser_fullname
 * @property string $gfuser_sex
 * @property string $gfuser_doctorid
 * @property string|null $gfuser_speciality
 * @property string $gfuser_type
 * @property string|null $gfuser_place
 * @property string|null $gfuser_salut
 * @property string|null $gfuser_doctortype
 * @property string $gfuser_active
 * @property string|null $gfuser_email
 * @property string|null $gfuser_clientid
 * @property string|null $gfuser_passwordhash
 *
 * @package App\Models
 */
class User extends Model implements Authenticatable
{

	use AuthenticableTrait;
	protected $table = 'gfuser';
	protected $primaryKey = 'gfuser_id';
	public $timestamps = false;

	protected $hidden = [
		'gfuser_password'
	];

	protected $fillable = [
		'gfuser_alias',
		'gfuser_password',
		'gfuser_fullname',
		'gfuser_sex',
		'gfuser_doctorid',
		'gfuser_speciality',
		'gfuser_type',
		'gfuser_place',
		'gfuser_salut',
		'gfuser_doctortype',
		'gfuser_active',
		'gfuser_email',
		'gfuser_clientid',
		'gfuser_passwordhash'
	];

	public function getAuthPassword()
	{
		return $this->gfuser_password;
	}
}
