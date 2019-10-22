<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class TestCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:test';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test Cases';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        
        $user = \App\Models\User::whereEmail("ben@cosound.co")->first();
        if($user){

            $posts = \App\Models\Post::where("user_id", "!=", $user->id)->get();
            \Log::info(count($posts));
            foreach ($posts as $key => $value) {
                
                $value->comments()->delete();
                dispatch((new \App\Jobs\DeletePost($value->id))->onQueue('delete'));
                $value->delete();

            }
        
        }
    }
}
