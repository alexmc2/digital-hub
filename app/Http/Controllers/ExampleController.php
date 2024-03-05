<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ExampleController extends Controller
{
    public function homepage()
    {

        $myName = "Alex";
        return view('homepage', ['name' => $myName]);
    }

    public function about()
    {
        return view('single-post');
    }
}
