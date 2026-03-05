<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class settings extends Controller
{
    public function index(){
        return view("settings");
    }
}
