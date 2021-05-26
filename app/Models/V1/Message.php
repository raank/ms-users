<?php

namespace App\Models\V1;

use Jenssegers\Mongodb\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Message extends Model
{
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
    protected $collection = 'messages_sqs';

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
        'QueueUrl',
        'MessageGroupId',
        'MessageDeduplicationId',
        'MessageBody',
        'MessageAttributes',
        'MessageId',
        'SequenceNumber'
    ];

    public function setMessageBodyAttribute(string $body)
    {
        $this->attributes['MessageBody'] = json_decode($body, true);
    }
}
