<?php

namespace App\Models\V1;

use Illuminate\Support\Str;
use Illuminate\Auth\Authenticatable;
use Illuminate\Support\Facades\Hash;
use Laravel\Lumen\Auth\Authorizable;
use Illuminate\Support\Facades\Crypt;
use Jenssegers\Mongodb\Eloquent\Model;
use Tymon\JWTAuth\Contracts\JWTSubject;
use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Contracts\Auth\Access\Authorizable as AuthorizableContract;

/**
 * @OA\Schema(
 *  schema="v1.model_user",
 *  type="object",
 *  description="Response data of user",
 *  @OA\Property(property="_id", type="string", description="Identification of User", example="60aeba949828bb0c57abc123"),
 *  @OA\Property(property="type", type="string", description="Type of User", example="default", enum={"default", "admin"}),
 *  @OA\Property(property="name", type="string", description="Name of User", example="John Doe"),
 *  @OA\Property(property="email", type="string", description="Email of User", example="john@doe.com"),
 *  @OA\Property(property="username", type="string", description="Username", example="john.doe"),
 *  @OA\Property(property="document", type="string", description="Document of User", example="123456789"),
 *  @OA\Property(property="active", type="boolean", description="User is active", example=true, enum={true, false}),
 *  @OA\Property(property="deleted_at", type="string", description="Date of Destroy", example=null),
 *  @OA\Property(property="updated_at", type="string", description="Date of last updated", example="2021-01-01T00:00:00.000000Z"),
 *  @OA\Property(property="created_at", type="string", description="Date of Created", example="2021-01-01T00:00:00.000000Z"),
 * )
 */
class User extends Model implements AuthenticatableContract, AuthorizableContract, JWTSubject
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

    /**
     * Get the identifier that will be stored in the subject claim of the JWT.
     *
     * @return mixed
     */
    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    /**
     * Return a key value array, containing any custom claims to be added to the JWT.
     *
     * @return array
     */
    public function getJWTCustomClaims()
    {
        return [];
    }
}
