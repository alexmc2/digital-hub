<?php

namespace App\Http\Controllers;

use App\Models\Post;
use Cloudinary\Cloudinary;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Http\Resources\PostResource;
use Cloudinary\Api\Upload\UploadApi;

class PostController extends Controller
{
    public function search($term)
    {
        $posts = Post::search($term)->get();
        $posts->load('user:id,username,avatar');
        return $posts;
        //return Post::where('title', 'LIKE', '%' . $term . '%')->orWhere('body', 'LIKE', '%' . $term . '%')->with('user:id,username,avatar')->get();
    }

    public function actuallyUpdate(Post $post, Request $request)
    {
        $incomingFields = $request->validate([
            'title' => 'required',
            'body' => 'required'
        ]);

        $incomingFields['title'] = strip_tags($incomingFields['title']);
        $incomingFields['body'] = strip_tags($incomingFields['body']);

        $post->update($incomingFields);

        return back()->with('success', 'Post successfully updated.');
    }

    public function showEditForm(Post $post)
    {
        return view('edit-post', ['post' => $post]);
    }

    public function delete(Post $post)
    {
        $post->delete();
        return redirect('/profile/' . auth()->user()->username)->with('success', 'Post successfully deleted.');
    }

    // public function deleteApi(Post $post)
    // {
    //     $post->delete();
    //     return 'true';
    // }

    public function viewSinglePost(Post $post)
    {
        $post['body'] = strip_tags(Str::markdown($post->body), '<p><ul><ol><li><strong><em><h3><br>');
        return view('single-post', ['post' => $post]);
    }

    public function storeNewPost(Request $request)
    {
        $incomingFields = $request->validate([
            'title' => 'required',
            'body' => 'required',
            'image' => 'image|max:2048', // Validate the image
        ]);

        $incomingFields['title'] = strip_tags($incomingFields['title']);
        $incomingFields['body'] = strip_tags($incomingFields['body']);
        $incomingFields['user_id'] = auth()->id();

        if ($request->hasFile('image')) {
            $uploadedFile = $request->file('image');
            $cloudinaryUpload = (new UploadApi())->upload($uploadedFile->getRealPath(), [
                'folder' => 'blog_posts'
            ]);
            $incomingFields['image_url'] = $cloudinaryUpload['url']; // Save the Cloudinary URL in the `image_url` field
        }

        $newPost = Post::create($incomingFields);

        return redirect("/post/{$newPost->id}")->with('success', 'New post successfully created.');
    }




    public function showCreateForm()
    {
        return view('create-post');
    }

    public function searchApi($term)
    {
        $posts = Post::search($term)->get();
        $posts->load('user:id,username,avatar');
        return PostResource::collection($posts);
    }

    public function viewSinglePostApi(Post $post)
    {
        return new PostResource($post->load('user:id,username,avatar'));
    }

    public function storeNewPostApi(Request $request)
    {
        $incomingFields = $request->validate([
            'title' => 'required',
            'body' => 'required',
            'image' => 'image|max:2048', // Add image validation
        ]);

        if ($request->hasFile('image')) {
            $uploadedFile = $request->file('image');
            $cloudinaryUpload = (new UploadApi())->upload($uploadedFile->getRealPath(), [
                'folder' => 'blog_posts'
            ]);
            $incomingFields['image_url'] = $cloudinaryUpload['url'];
        }

        $incomingFields['title'] = strip_tags($incomingFields['title']);
        $incomingFields['body'] = strip_tags($incomingFields['body']);
        $incomingFields['user_id'] = auth()->id();

        $newPost = Post::create($incomingFields);

        return (new PostResource($newPost))
            ->response()
            ->setStatusCode(201);
    }

    public function updatePostApi(Post $post, Request $request)
    {
        $incomingFields = $request->validate([
            'title' => 'required',
            'body' => 'required',
            'image' => 'image|max:2048', // Add image validation
        ]);

        if ($request->hasFile('image')) {
            $uploadedFile = $request->file('image');
            $cloudinaryUpload = (new UploadApi())->upload($uploadedFile->getRealPath(), [
                'folder' => 'blog_posts'
            ]);
            $incomingFields['image_url'] = $cloudinaryUpload['url'];
        }

        $incomingFields['title'] = strip_tags($incomingFields['title']);
        $incomingFields['body'] = strip_tags($incomingFields['body']);

        $post->update($incomingFields);

        return new PostResource($post);
    }

    public function deleteApi(Post $post)
    {
        $post->delete();
        return response()->json(['message' => 'Post successfully deleted'], 200);
    }

    public function showPopularPosts()
    {
        $posts = Post::with('user') 
            ->orderBy('popularity_score', 'desc') 
            ->take(10)
            ->get();

        return PostResource::collection($posts);
    }

    public function showNewestPosts()
    {
        $posts = Post::with('user') 
            ->latest()
            ->take(10)
            ->get();

        return PostResource::collection($posts);
    }

    public function showPostsByTopic($topicName)
    {
  
        $posts = Post::with('user')
            ->where('topic', $topicName) 
            ->latest()
            ->take(10)
            ->get();

        return PostResource::collection($posts);
    }
}
