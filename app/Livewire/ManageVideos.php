<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithFileUploads;
use App\Models\Video;
use Illuminate\Support\Facades\Storage;

class ManageVideos extends Component
{
    use WithFileUploads;

    public $title;
    public $video;
    public $videos;

    protected $rules = [
        'title' => 'required|string|max:255',
        'video' => 'required|file|mimes:mp4,mov,avi|max:204800', // 200 MB
    ];

    public function mount()
    {
        $this->loadVideos();
    }

    public function loadVideos()
    {
        $this->videos = Video::latest()->get();
    }

    public function store()
    {
        $this->validate();

        $path = $this->video->store('videos', 'public');

        Video::create([
            'title' => $this->title,
            'file_path' => $path,
            'size' => $this->video->getSize(),
            'is_active' => true,
        ]);

        $this->reset(['title', 'video']);
        $this->loadVideos();
        session()->flash('message', 'Video subido con éxito.');
    }

    public function toggleStatus($id)
    {
        $video = Video::findOrFail($id);
        $video->is_active = !$video->is_active;
        $video->save();
        $this->loadVideos();
    }

    public function delete($id)
    {
        $video = Video::findOrFail($id);
        Storage::disk('public')->delete($video->file_path);
        $video->delete();
        $this->loadVideos();
        session()->flash('message', 'Video eliminado correctamente.');
    }

    public function updatedVideo()
{
    $this->validateOnly('video');
}


    public function render()
    {
        return view('livewire.manage-videos');
    }
}