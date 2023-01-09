<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Home;

class HomeController extends Controller
{
    public function index () {
        $arr = array(
            'a' => 'a',
            'b' => 'b'
        );

        return response()->json($arr, 200);
    }
}
