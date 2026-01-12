<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\SafeBox;
use Illuminate\Support\Facades\Crypt;

class SafeBoxController extends Controller
{
public function index()
{
    $data = SafeBox::where('user_id', auth()->id())->get();
    return view('safebox.index', compact('data'));
}


    public function store(Request $request)
    {
        SafeBox::create([
            'user_id' => auth()->id(),
            'title' => $request->title,
            'secret' => Crypt::encryptString($request->secret),
        ]);

        return back();
    }

    public function destroy($id)
    {
        SafeBox::findOrFail($id)->delete();
        return back();
    }

}
