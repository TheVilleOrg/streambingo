<?php

declare (strict_types = 1);

namespace Bingo\Page;

use Bingo\Config;
use Bingo\Controller\UserController;
use Bingo\Exception\NotFoundException;

/**
 * Represents a handler for a page, a base unit of the application.
 */
abstract class Page
{
    /**
     * Routes a request to a handler Page.
     *
     * @param string $page The name of the page
     * @param string[] $params The list of parameters to send to the handler
     */
    public static function route(string $page, array $params): void
    {
        $page = empty($page) ? 'index' : $page;
        $className = 'Bingo\\Page\\' . \ucfirst(\strtolower($page)) . 'Page';
        if (\class_exists($className))
        {
            $object = new $className();
            $object->run($params);
        }
        else
        {
            throw new NotFoundException('The page ' . $page . ' could not be found.');
        }
    }

    /**
     * Runs the Page.
     *
     * @param string[] $params The list of parameters
     */
    abstract protected function run(array $params): void;

    /**
     * Shows a template.
     *
     * @param string $template The name of the template
     * @param array $data The data to provide to the template
     */
    final protected function showTemplate(string $template, array $data = []): void
    {
        \extract($data);
        $basePath = Config::BASE_PATH;
        $authUrl = UserController::getAuthUrl();
        $user = [
            'loggedIn' => false,
            'name'     => 'guest',
        ];

        $userModel = UserController::getCurrentUser();
        if ($userModel)
        {
            $user = [
                'loggedIn' => true,
                'name'     => \htmlspecialchars($userModel->getName()),
            ];
        }

        unset($userModel);

        require __DIR__ . '/../views/' . $template . '/index.php';
    }
}
