<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Mailjet\Client;
use Mailjet\Resources;
use Illuminate\Support\Facades\Log;

class SendMailJob implements ShouldQueue
{
    use Queueable;
    private $data;

    /**
     * Create a new job instance.
     */
    public function __construct($data)
    {
        //
        $this->data = $data;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        //
        
        $mj = new Client(
            env('MAILJET_API_KEY'),
            env('MAILJET_API_SECRET'),
            true, // enable call
            ['version' => 'v3.1'] // specify the API version
        );

        $body = [
            'Messages' => [
                [
                    'From' => [
                        'Email' => $this->data["email_from"],
                        'Name'  => $this->data["name_from"]
                    ],
                    'To' => [
                        [
                            'Email' => $this->data["email_recipient"],
                            'Name'  => $this->data["name_recipient"]
                        ]
                    ],
                    'Subject'  => $this->data['subject'],
                    'TemplateID' => $this->data["template_id"],
                    'TemplateLanguage' => true,
                    'Variables' => $this->data["variables"]
                    
                        
                ]
            ]
        ];

        // 3) Send the request via Mailjet
        $response = $mj->post(Resources::$Email, ['body' => $body]);

        $data = $response->getData();

        Log::channel('mailjet')->info('Mail sent', ['data' => $data]);
        
    }
}
