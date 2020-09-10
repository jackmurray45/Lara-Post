<?php

namespace App\Http\Controllers\web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Resources\Post as PostResource;
use App\Post;
use Auth;


class PostController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */

    

    public function __construct()
    {
        $this->middleware('auth', ['except' => ['index', 'show']]);
        $this->middleware('check.following', ['index']);
    }

    public function index(Request $request)
    {

        $posts = !auth()->guest() && $request->following == 1 ? 
            auth()->user()->followingPosts()->paginate(20) : 
            Post::orderBy('created_at', 'DESC')->paginate(20);

        return inertia('Posts', [
            'posts' => $posts
        ]);

    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('post.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {

        $request->validate([
            'content' => "required"
        ]);

        
        $post = new Post;
        $post->user_id = Auth::user()->id;
        $post->content = $request->content;
        $post->save();

        session()->flash('status', 'Post successfully sent!');
        return redirect('/posts');

    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $post = Post::findOrFail($id);

        $comments = $post->comments()->paginate(10);
        return inertia('Post', [
            'post' => $post,
            'comments' => $comments
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $post = Post::findOrFail($id);
        if(isset($post) && $post->user_id == Auth::user()->id)
        {
            $post->delete();
            session()->flash('status', 'Post successfully deleted!');
        }
        $previousPath = explode('/', url()->previous());
        return is_numeric(end($previousPath)) ? redirect('/posts')  : back();
    }

    public function followingPosts()
    {
        $posts = Post::select('posts.*')->join('follows', 'posts.user_id', '=', 'follows.followed_user_id')
        ->where('following_user_id', '=', Auth::user()->id)->orderBy('posts.created_at', 'DESC')->paginate(20);
        return view('post.index')->with('posts', $posts)->with('followingPosts', 1);
    }
}
