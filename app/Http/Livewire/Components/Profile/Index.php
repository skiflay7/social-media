<?php

namespace App\Http\Livewire\Components\Profile;

use Livewire\Component;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use App\Models\Profile;
use Jantinnerezo\LivewireAlert\LivewireAlert;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class Index extends Component
{

    use LivewireAlert, WithFileUploads;

    public $nameShort, $bio, $address, $website, $name, $image, $img;
    public $closeModal;

    protected $listeners = [
        'update_profile'
    ];

    public function mount()
    {
        $this->getData();
    }

    public function render()
    {
        return view('livewire.components.profile.index')->extends('layouts.app')->section('content');
    }

    public function getData()
    {
        $data = User::where('id', Auth::user()->id)->with('profiles')->first();
        $data->profiles->name;

        $words = explode(" ", $data->profiles->name);
        $this->nameShort = "";

        foreach ($words as $w) {
            $this->nameShort .= $w[0];
        }

        $this->name = $data->profiles->name;
        $this->bio = $data->profiles->bio;
        $this->address = $data->profiles->address;
        $this->website = $data->profiles->website;
        $this->image = $data->profiles->image;
        $this->user_id = Auth::user()->id;

    }

    public function updatedImg()
    {
        $this->validate([
            'img' => 'file|mimes:png,jpg,jpeg'
        ]);
    }

    public function update_profile()
    {
        $this->getData();
    }

    public function updateProfile($id)
    {

        $newImage = null;

        $this->validate([
            'bio' => 'required',
            'address' => 'required',
            'website' => 'required|url',
            'image' => 'required'
        ]);

       try {

            if (is_null($this->img)) {
                $newImage = $this->img = $this->image;
            }else{
                $newImage = 'photos' . '-' . time(). '.' . $this->img->getClientOriginalExtension();
                File::delete(public_path('storage/images/profile/' . $this->image));
                $this->img->storeAs('public/images/profile', $newImage);
            }


            Profile::updateOrCreate([
                'user_id' => $id
            ],[
                'bio' => $this->bio,
                'address' => $this->address,
                'website' => $this->website,
                'image' => $newImage
            ]);

            $this->alert(
                'success',
                'Succesfully update profile'
            );

            $this->emit('update_profile');

            $this->closeModal = true;
       } catch (\Exception $e) {
           dd($e->getMessage());
       }
    }
}
