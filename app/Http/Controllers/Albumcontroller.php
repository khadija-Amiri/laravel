<?php

namespace App\Http\Controllers;
use File;
use Image;
use App\Gallery;

use Illuminate\Http\Request;

class Albumcontroller extends Controller
{
    public function index()
    {
        $images = Gallery::paginate(10);

        return view('galleries.index', ['images' => $images]);
    }
    public function create()
    {
        //
    }
    public function store(Request $request)
    {
        $this->validate($request, [
            'image' => 'required',
            'image.*' => 'image|mimes:jpeg,jpg,png,gif|max:8000'
        ]);

        if ($request->hasFile('image')) {
            $images = $request->file('image');

            $org_img = $thm_img = true;

            if( ! File::exists('images/gallery/originals/')) {
                $org_img = File::makeDirectory(public_path('images/gallery/originals/'), 0777, true);
            }
            if ( ! File::exists('images/gallery/thumbnails/')) {
                $thm_img = File::makeDirectory(public_path('images/gallery/thumbnails'), 0777, true);
            }

            foreach($images as $key => $image) {

                $gallery = new Gallery;

                $filename = rand(1111,9999).time().'.'.$image->getClientOriginalExtension();

                $org_path = 'images/gallery/originals/' . $filename;
                $thm_path = 'images/gallery/thumbnails/' . $filename;

                $gallery->image     = 'images/gallery/originals/'.$filename;
                $gallery->thumbnail = 'images/gallery/thumbnails/'.$filename;
                $gallery->title     = $request->title;
                $gallery->status    = $request->status;

                if ( ! $gallery->save()) {
                    flash('Gallery could not be updated.')->error()->important();
                    return redirect()->back()->withInput();
                }

               if (($org_img && $thm_img) == true) {
                   Image::make($image)->fit(900, 500, function ($constraint) {
                           $constraint->upsize();
                       })->save($org_path);
                   Image::make($image)->fit(270, 160, function ($constraint) {
                       $constraint->upsize();
                   })->save($thm_path);
               }
            }
        }

        flash('Image uploaded successfully.')->success();
        return redirect()->action('GalleryController@index');
    }
    public function show(Gallery $gallery)
    {
        //
    }
    public function edit(Gallery $gallery)
    {
        //
    }
    public function update(Request $request, Gallery $gallery)
    {
        $image = Gallery::findOrFail($request->id);

        if ($image->status == 1) {
            $image->status = 0;
            $status = 'disabled';
        } else {
            $image->status = 1;
            $status = 'enabled';
        }

        if ( ! $image->save()) {
            flash('Image could not be reverted.')->error();
            return redirect()->route('gallery.index');
        }

        flash('Image has been successfully '.$status)->success();
        return redirect()->route('gallery.index');
    }
    public function destroy($id)
    {
        $post = Gallery::findOrFail($id);

        if ($post->delete()) {
            flash('Image successfully deleted.')->success();
        } else {
            flash('Image could not be deleted.')->error();
        }

        return redirect()->route('gallery.index');
    }

    
}
