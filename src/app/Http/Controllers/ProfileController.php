<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\ProfileRequest;

class ProfileController extends Controller
{
    public function edit()
    {
        $user = Auth::user();
        return view('profile.edit', compact('user'));
    }

    public function update(ProfileRequest $request)
    {
        $user = auth()->user();
        $v = $request->validated();

        if ($request->hasFile('profile_image')) {
            $path = $request->file('profile_image')->store('public/profile_images');
            $user->profile_image = str_replace('public/', 'storage/', $path);
        }

        $user->name        = $v['name'];
        $user->postal_code = $v['postal_code'];
        $user->address     = $v['address'];
        $user->building    = $v['building'] ?? null;
        $user->save();

        $request->session()->forget('url.intended');

        return redirect()->route('mylist.index', ['tab' => 'mylist'])
            ->with('success', 'プロフィールを更新しました');
    }
}

