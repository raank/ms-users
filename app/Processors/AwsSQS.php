<?php

namespace App\Processors;

use Aws\Result;
use Aws\Sqs\SqsClient;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

class AwsSQS
{
    /**
     * The uuid unique identification.
     *
     * @var string
     */
    private $uuid;

    /**
     * The queue URL parameter.
     *
     * @var string
     */
    protected $queueUrl;

    /**
     * The queue message group id parameter.
     *
     * @var string
     */
    protected $messageGroupId;

    /**
     * The queue message duplication id parameter.
     *
     * @var string
     */
    protected $messageDuplicationId;

    /**
     * The queue message body.
     *
     * @var string
     */
    protected $messageBody;

    /**
     * The queue message attributes
     *
     * @var string
     */
    protected $messageAttributes;

    /**
     * The constructor.
     *
     * @param array $payload
     * @param array $params
     */
    public function __construct()
    {
        $this->uuid = Str::uuid();
    }

    /**
     * The queue url setter.
     *
     * @param string|null $url
     *
     * @return void
     */
    public function onQueue(string $name = null)
    {
        $aws = config('aws');

        $this->queueUrl = sprintf(
            '%s/%s',
            Arr::get($aws, 'sqs.prefix'),
            $name ?? Arr::get($aws, 'sqs.queue')
        );

        return $this;
    }

    /**
     * The queue url getter.
     *
     * @return string
     */
    public function getQueueUrl(): string
    {
        $aws = config('aws');

        if (!isset($this->queueUrl)) {
            return sprintf(
                '%s/%s',
                Arr::get($aws, 'sqs.prefix'),
                Arr::get($aws, 'sqs.queue')
            );
        }

        return $this->queueUrl;
    }

    /**
     * Set the message group id.
     *
     * @param string $id
     *
     * @return void
     */
    public function setMessageGroupId(string $id)
    {
        $this->messageGroupId = $id;

        return $this;
    }

    /**
     * Get the message group id.
     *
     * @return string
     */
    public function getMessageGroupId(): string
    {
        if (!isset($this->messageGroupId)) {
            return config('aws.sqs.messages.group_id', 'default');
        }

        return $this->messageGroupId;
    }

    /**
     * Set message duplication id.
     *
     * @param string $id
     *
     * @return void
     */
    public function setMessageDubplicationId(string $id)
    {
        $this->messageDubplicationId = $id;

        return $this;
    }

    /**
     * Get message duplication id.
     *
     * @return string
     */
    public function getMessageDubplicationId(): string
    {
        if (!isset($this->messageDubplicationId)) {
            return (string) $this->uuid;
        }

        return $this->messageDubplicationId;
    }

    /**
     * Set message body.
     *
     * @param array $body
     *
     * @return void
     */
    public function setMessageBody(array $body)
    {
        $this->messageBody = array_merge(
            ['uuid' => (string) $this->uuid],
            $body
        );

        return $this;
    }

    /**
     * Get message body.
     *
     * @return string
     */
    public function getMessageBody(): string
    {
        return json_encode(
            $this->messageBody
        );
    }

    /**
     * Set message attributes.
     *
     * @param array $items
     *
     * @return void
     */
    public function setMessageAttributes(array $items = [])
    {
        $messages = [];

        foreach ($items as $key => $value) {
            $messages[ucfirst(Str::camel($key))] = [
                'DataType' => is_numeric($value) ? 'Number' : 'String',
                'StringValue' => (string) $value
            ];
        }

        $this->messagesAttribute = array_merge(
            $this->messagesAttribute ?? [],
            $messages
        );

        return $this;
    }

    /**
     * Get message attributes.
     *
     * @return array
     */
    public function getMessageAttributes(): array
    {
        return array_merge(
            [
                'Identification' => [
                    'DataType' => 'String',
                    'StringValue' => (string) $this->uuid
                ]
            ],
            $this->messagesAttribute ?? []
        );
    }

    /**
     * Define the Client.
     *
     * @return SqsClient
     */
    protected function client(): SqsClient
    {
        return new SqsClient([
            'profile' => config('aws.profile'),
            'region' => config('aws.region'),
            'version' => config('aws.sqs.version')
        ]);
    }

    /**
     * Mount array with params.
     *
     * @return array
     */
    public function params(): array
    {
        return [
            'QueueUrl' => $this->getQueueUrl(),
            'MessageGroupId' => $this->getMessageGroupId(),
            'MessageDeduplicationId' => $this->getMessageDubplicationId(),
            'MessageBody' => $this->getMessageBody(),
            'MessageAttributes' => $this->getMessageAttributes(),
        ];
    }

    /**
     * Send message to SQS.
     *
     * @return Result
     */
    public function send(): Result
    {
        return $this->client()
            ->sendMessage(
                $this->params()
            );
    }

    /**
     * Dispatching job.
     *
     * @return Result
     */
    public function dispatch()
    {
        $this->setMessageBody(
            $this->handle()
        );

        /** @var array $params Message SQS Params */
        $params = $this->params();

        /** @var array $message */
        $message = $this->client()
            ->sendMessage($params)
            ->toArray();

        $response = array_merge(
            Arr::only($message, [
                'MessageId',
                'SequenceNumber'
            ]),
            $params
        );

        return $response;
    }
}