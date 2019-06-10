<?php

namespace Tests\Unit;

use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use App\User;
use App\Follow;
use App\Post;
use App\Comment;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Faker\Factory as Faker;


class UnitTest extends TestCase
{

    use DatabaseMigrations;

    public function testCreateUser()
    {
        factory(User::class)->create();   //create faker data
        $this->assertEquals(User::where('id', 1)->count(), 1); 
    }

    public function testDuplicateEmail()
    {
        $this->expectException(\Illuminate\Database\QueryException::class);
        $data = factory(User::class)->create();   //create faker data
        factory(User::class)->create();   //create 2nd faker data
        //try to assign email from id 1 to email for id 2
        User::where('id', 2)->update(['email'=> $data->email]);
    }


    public function testUpdateUserNullEmail()
    {
        $this->expectException(\Illuminate\Database\QueryException::class);
        $data = factory(User::class)->create();   //create faker data
        User::where('id', 1)->update(['email'=> null]); //update faker data to be null
    }

    public function testDeleteZeroUsersByEmail()
    {
        
        factory(User::class, 100)->create();   //create faker data
        $data = factory(User::class)->create();
        /*try to delete email of user generated from $data
        must limit to id < 101 to exclude $data from query*/
        $this->assertEquals(User::where('id','<' ,101)->where('email',$data->email)->delete(), 0); 
    }

    public function testDeleteUserWithPosts()
    {
        $data = factory(User::class)
            ->create()
            ->each(function($user) {
                $user->posts()->save(factory(Post::class)->make());
            });

        //echo "post count: ".Post::count();
        
        //Check user and posts were made
        $this->assertEquals(Post::count(), 1);
        $this->assertEquals(User::count(),  1);

        //Delete user and posts assoicated with user
        User::find($data)->delete();
        Post::where('user_id', $data)->delete();

        //Make sure posts were deleted
        $this->assertEquals(Post::count(), 0);
        $this->assertEquals(User::count(), 0);
        
    }
    
    public function testDeleteUserWithPostsAndComments()
    {
        //Create user with post
        $data = factory(User::class)
            ->create()
            ->each(function($user) {
                $user->posts()->save(factory(Post::class)->make());
            });

        //Create comment from user/post
        $comment = factory(Comment::class)->create();

        //Check user and posts were made
        $this->assertEquals(User::count(),  1);
        $this->assertEquals(Post::count(), 1);
        $this->assertEquals(Comment::count(), 1);

        //Delete user and posts/comments assoicated with user
        User::find($data)->delete();
        Post::where('user_id', $data)->delete();
        Comment::where('user_id', $data)->delete();

        //Make sure posts were deleted
        $this->assertEquals(Post::count(), 0);
        $this->assertEquals(User::count(), 0);
        $this->assertEquals(Comment::count(), 0);
    }

    public function testFollowUser()
    {
        $user1 = factory(User::class)->create();
        $user2 = factory(User::class)->create();
        $follow = factory(Follow::class)->create();

        $this->assertTrue($follow->following_user_id == $user1->id || $follow->following_user_id == $user2->id);
        $this->assertTrue($follow->followed_user_id == $user1->id || $follow->followed_user_id == $user2->id);
        $this->assertEquals(Follow::count(), 1);

    }
 
}
