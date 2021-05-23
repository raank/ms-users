<?php

namespace App\Jobs;

use App\Models\V1\User;
use App\Processors\AwsSQS;

class SendMailMailgunSQS extends AwsSQS implements SqsJobEmailInterface
{
    /**
     * The User object to send email.
     *
     * @var string
     */
    protected $user;

    /**
     * The variables of email.
     *
     * @var string
     */
    protected $variables;

    /**
     * The subject of email.
     *
     * @var string
     */
    protected $subject;

    /**
     * The template of email.
     *
     * @var string
     */
    protected $template;

    /**
     * The constructor method.
     *
     * @param User $user
     * @param array $variables
     */
    public function __construct(User $user, array $variables = [])
    {
        $this->user = $user;
        $this->variables = $variables;

        parent::__construct($variables);
    }

    /**
     * @inheritDoc
     */
    public function setSubject(string $subject)
    {
        $this->subject = $subject;

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function setTemplate(string $template)
    {
        $this->template = $template;

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function handle(): array
    {
        $payload = [
            'api_key' => env('MAILGUN_API_KEY'),
            'endpoint' => env('MAILGUN_ENDPOINT'),
            'from' => sprintf(
                '%s <%s>',
                config('mailgun.from.name'),
                config('mailgun.from.address')
            ),

            'to' => sprintf(
                '%s <%s>',
                $this->user->name,
                $this->user->email
            ),

            'template' => $this->template ?? config('mailgun.template'),
            'subject' => $this->subject ?? sprintf(
                'Email submitted from %s',
                env('APP_NAME')
            ),

            'params' => $this->variables
        ];

        return $this->variables;
    }
}