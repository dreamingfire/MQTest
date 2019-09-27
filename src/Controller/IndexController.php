<?php


namespace App\Controller;


use App\Http\Response;

class IndexController
{
    public function index()
    {
        return (new Response())->setContent("Happy coding now !");
    }
}