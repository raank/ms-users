<?php

namespace App\Jobs;

interface SqsJobEmailInterface
{

    /**
     * The subject of message object.
     *
     * @return void
     */
    public function setSubject(string $subject);

    /**
     * The template of message object.
     *
     * @return array
     */
    public function setTemplate(string $template);

    /**
     * The handler object
     *
     * @return array
     */
    public function handle(): array;
}