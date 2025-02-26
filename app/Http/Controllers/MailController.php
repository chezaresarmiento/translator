<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Mailjet\Client;
use Mailjet\Resources;


class MailController extends Controller
{
    //
    public function sendMail(Request $request){
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
                        'Email' => 'do_not_reply@appsolution4u.com',
                        'Name'  => 'AppSolution Assistant'
                    ],
                    'To' => [
                        [
                            'Email' => 'cesar@opulence.com',
                            'Name'  => 'Cesar Sarmiento'
                        ]
                    ],
                    'Subject'  => 'Test Email from Mailjet + Laravel',
                    'TemplateID' => 6757865,
                    'TemplateLanguage' => true,
                    'Variables' => [
                        "recipient_name" => "Cesar Sarmiento",
                    ]
                        
                ]
            ]
        ];

        // 3) Send the request via Mailjet
        $response = $mj->post(Resources::$Email, ['body' => $body]);

        // 4) Check response
        if ($response->success()) {
            // success - get data if you want
            $data = $response->getData();
            return response()->json([
                'status' => 'success',
                'mailjet_response' => $data
            ]);
        } else {
            // error
            return response()->json([
                'status' => 'error',
                'mailjet_response' => $response->getData()
            ], 400);
        }


    }
        
}
