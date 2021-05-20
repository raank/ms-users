<?php

namespace App\Models;

use Illuminate\Support\Str;
use Illuminate\Auth\Authenticatable;
use Illuminate\Support\Facades\Hash;
use Laravel\Lumen\Auth\Authorizable;
use Illuminate\Support\Facades\Crypt;
use Jenssegers\Mongodb\Eloquent\Model;
use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Contracts\Auth\Access\Authorizable as AuthorizableContract;

class User extends Model implements AuthenticatableContract, AuthorizableContract
{
    use Authenticatable, Authorizable, HasFactory;

    /**
     * Define the connection.
     *
     * @var string
     */
    protected $connection = 'mongodb';

    /**
     * The table name.
     *
     * @var string
     */
    protected $collection = 'users';

    /**
     * The primary key name.
     *
     * @var string
     */
    protected $primaryKey = '_id';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'type',
        'name',
        'email',
        'username',
        'document',
        'active',

        'remember_token',
        'deleted_at',
        'password'
    ];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token'
    ];

    /**
     * Define the username by default.
     *
     * @param string|null $value
     *
     * @return void
     */
    public function setUsernameAttribute(string $value = null)
    {
        if (!isset($value)) {
            $this->attributes['username'] = Str::slug($this->attributes['name']);
        } else {
            $this->attributes['username'] = $value;
        }
    }

    /**
     * Set document encryptation.
     *
     * @param string|null $value
     *
     * @return void
     */
    public function setDocumentAttribute(string $value = null)
    {
        if (isset($value)) {
            $this->attributes['document'] = Crypt::encrypt($value);
        }
    }

    /**
     * Get document decrypted.
     *
     * @return string|null
     */
    public function getDocumentAttribute()
    {
        $value = $this->attributes['document'];

        return Crypt::decrypt($value);
    }

    /**
     * Setting hash to Passwords.
     *
     * @param string $value
     *
     * @return void
     */
    public function setPasswordAttribute(string $value = null)
    {
        if (isset($value)) {
            $this->attributes['password'] = Hash::make($value);
        } else {
            $this->attributes['password'] = null;
        }
    }
}
