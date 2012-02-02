<?php

namespace Acme\Hello\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class MainController extends Controller
{
    public function index($name)
    {
        $name = $this->get('hello.name_wrapper')->wrap($name);

        return array('name' => $name);
    }
}