<?php

namespace Lima\Core;

class Controller
{
    public function view($template, $data = []): bool
    {
        $view = System::GetView($data);
        return $view->render($template);
    }
}