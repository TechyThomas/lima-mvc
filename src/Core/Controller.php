<?php

namespace Lima\Core;

class Controller
{
    public function view($template, $data): bool
    {
        $view = new View();
        return $view->render($template, $data);
    }
}