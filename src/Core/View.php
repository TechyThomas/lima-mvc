<?php

namespace Lima\Core;

class View
{
    public function render($template, $data = []): bool
    {
        $templateDir = LIMA_ROOT . DIRECTORY_SEPARATOR . 'views';

        if (!empty($_ENV['LIMA_TEMPLATE_DIR'])) {
            $templateDir = $_ENV['LIMA_TEMPLATE_DIR'];
        }

        if (class_exists('\Twig\Loader\FilesystemLoader')) {
            $loader = new \Twig\Loader\FilesystemLoader($templateDir);
            $twig = new \Twig\Environment($loader);

            echo $twig->render($template . '.php', $data);

            return true;
        }

        $templateFile = $templateDir . DIRECTORY_SEPARATOR . $template . '.php';
        if (!file_exists($templateFile)) {
            return false;
        }

        $view = $this;

        ob_start();
        extract(['view' => $view]);
        require($templateFile);
        $html = ob_get_contents();
        ob_end_clean();

        echo $html;

        return true;
    }
}