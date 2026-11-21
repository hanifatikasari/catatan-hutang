<?php

namespace App\Controllers;

class Home extends BaseController
{
    public function index(): string
    {
        $data = [
            'judul' => 'Catatan Hutang'
        ];

        return view('home', $data);
    }
}
