<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class GabutController extends Controller
{
    public function page() {
        $payload = [
            'states' => [
                'user_data' => [
                    'id' => 1, 'name' => "Riyan Satria"
                ],
            ],
            'elements' => [
                [
                    'tag' => 'button',
                    'children' => "Click me",
                    'props' => [
                        'onclick' => "doLogin()"
                    ]
                ],
                [
                    'tag' => "img",
                    'props' => [
                        'src' => "https://promociin.com/images/icon.png",
                        'style' => "height: 200px"
                    ]
                ]
            ],
            'functions' => [
                'doLogin' => "fetch('https://broadcast-api.zainzo.com/api/admin/contact', {
                method: 'POST',
            }).then(res => res.json()).then(res => console.log(res));",
            ]
        ];

        return view('page', [
            'payload' => $payload,
        ]);
    }
}
