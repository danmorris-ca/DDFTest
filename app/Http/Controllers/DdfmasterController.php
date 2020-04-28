<?php

namespace App\Http\Controllers;

use App\ddfmaster;
use Illuminate\Http\Request;

class DdfmasterController extends Controller
{
    //
    public function store()
    {

        ddfmaster::create([
            'ddfid' => 111,
            'lastUpdated' => now(),
        ]);

        return redirect()->route('home');
    }

}
