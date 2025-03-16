<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Post; 

class PostController extends Controller
{
    public function test()
{
    return response()->json([
        'message' => 'Hello from Laravel with PostgreSQL!'
    ]);
}

    public function index(){
       $posts = Post::all();
       return view('posts', compact('posts'));
    }

    public function delete(){
        $post = Post::find(3); //Post::withTrashed()->find(2); $post->restore()
        $post->delete();
        dd('success delete');
    }

    public function update(){
        $post = Post::find(3);
        $post->update([
            'title' => 'updated',
            'content' => 'updated a little'

        ]);

        dd('succes update');
    }

    //firstOrCreate - нужен доп массив для указания по какому полю надо проверять
    //updateOrCreate

    public function create(){
        $posts_arr = [
            [
                'title' => 'hepl',
                'content' => 'asdas',
                'image' => 'asdasd',
                'likes' => 20,
                'published' => 1
            ],
            [
                'title' => 'fdgdfgdfgf',
                'content' => 'df',
                'image' => 'df',
                'likes' => 100,
                'published' => 1
            ]
        ];
        
        foreach($posts_arr as $item){
            Post::create($item);
        }

        dd("success create!!");
        
    }


    public function index5(){
        $posts = Post::where('id', 2)->get(); // get всегда возвращает коллекцию, для получения одного элемента
        // используется метод firts()
        foreach($posts as $post){
            dump($post->title);
        }
        dd('end');
    }

    public function index2(){
        $posts = Post::all();
        foreach($posts as $post){
            dump($post->title);
        }
        dd('end');
    }

    public function index1(){
        $post = Post::find(1);
        dump($post);
        dump($post->title);
    }
}
