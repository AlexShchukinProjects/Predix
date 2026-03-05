<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Models\Crew;
use App\Models\Aircraft as Fleet;
use App\Models\Position as Position;
use Illuminate\Http\Request;
use App\Models;

class DirectoryChangeController extends Controller
{
  public function index($ModelName)
  {


      if ($ModelName=="Position") $variables=Position::all();
      if ($ModelName=="Fleet")    $variables=Fleet::all();

 //     dd($variables);

 return view('Settings.DirectoryChange', compact('ModelName'), compact('variables'));

      }



    public function edit($ModelName, $Name){

//dd($Name);

dd($Name);

        return view('Settings.DirectoryChangeEdit', compact('ModelName'), compact('Name'));


    }


    public function update($ModelName, $Name){

//dd($Name);

        if ($ModelName=="Position") $variables=Position::where('Name',$Name)->update(['Name' => request('Name')]);
        if ($ModelName=="Fleet")    $variables=Fleet::where('Name',$Name)->update(['Name' => request('Name')]);

        if ($ModelName=="Position") $variables=Position::all();
        if ($ModelName=="Fleet")    $variables=Fleet::all();




        return view('Settings.DirectoryChange', compact('ModelName'), compact('variables'));

    }


    public function create($ModelName)
    {


        return view('Settings.DirectoryCreate',compact('ModelName'));
    }


    public function store($ModelName)
    {


        $NewDirectory=\request(['Name' => 'Traveling to Europe']);

        dd($NewDirectory);

        if ($ModelName=="Position") $variables=Position::create($NewDirectory);
        if ($ModelName=="Fleet")    $variables=Fleet::create($NewDirectory);







    }


}
