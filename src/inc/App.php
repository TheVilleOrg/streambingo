<?php

declare (strict_types = 1);

namespace Bingo;

use Bingo\Controller\GameController;
use Bingo\Controller\UserController;
use Bingo\Exception\HttpException;
use Bingo\Page\Page;

/**
 * Provides an interface for the main application.
 */
class App
{
    /**
     * The application version string
     */
    const APP_VERSION = 'v0.2.3';

    /**
     * The client asset version
     */
    const ASSET_VERSION = 7;

    /**
     * The requested route
     *
     * @var string
     */
    private static $route = '';

    /**
     * Runs the main application.
     *
     * @throws \Bingo\Exception\BadRequestException
     * @throws \Bingo\Exception\InternalErrorException
     * @throws \Bingo\Exception\NotFoundException
     * @throws \Bingo\Exception\UnauthorizedException
     */
    public static function run(): void
    {
        \session_set_cookie_params(0, Config::BASE_PATH);
        \session_start();

        \set_exception_handler(['Bingo\App', 'exceptionHandler']);

        $options = [
            'regexp' => '/^[\w\-\/]*$/',
        ];
        self::$route = \filter_input(INPUT_GET, 'page', FILTER_VALIDATE_REGEXP, [ 'options' => $options ]) ?? '';
        $page = empty(self::$route) ? 'index' : self::$route;
        $page = \explode('/', \rtrim($page, '/'));

        Page::route(\array_shift($page), $page);
    }

    /**
     * Handles requests from the command line interface.
     */
    public static function runCli(): void
    {
        global $argc, $argv;
        if (!$argc)
        {
            echo 'usage';
            return;
        }

        $return = [];

        switch ($argv[1])
        {
            case 'getgame':
                $game = GameController::getGameFromToken($argv[2]);
                $return['name'] = $game->getGameName();
                $return['settings'] = [
                    'tts'        => $game->getTts(),
                    'ttsVoice'   => $game->getTtsVoice(),
                    'background' => $game->getBackground(),
                ];
                $return['called'] = $game->getCalled();
                $return['ended'] = $game->getEnded();
                $return['winner'] = $game->getWinnerName();
                break;
            case 'getcard':
                $return = GameController::createCard((int) $argv[2], $argv[3], $argv[4]);
                $return['url'] = Config::BASE_URL . Config::BASE_PATH . 'play';
                break;
            case 'getuser':
                $return['userId'] = UserController::getIdFromGameToken($argv[2]);
                break;
            case 'submitcard':
                $return = GameController::submitCard((int) $argv[2], $argv[3]);
                break;
        }

        echo \json_encode($return);
    }

    /**
     * Handles all uncaught exceptions.
     *
     * @param \Throwable $e The exception
     */
    public static function exceptionHandler(\Throwable $e): void
    {
        $data = [
            'code'    => 500,
            'message' => 'Internal Server Error',
            'details' => '',
        ];

        if ($e instanceof HttpException)
        {
            $data['code'] = $e->getCode();
            $data['message'] = $e->getMessage();
            $data['details'] = $e->getDetails();
        }
        else
        {
            $data['details'] = $e->getMessage();
        }

        \header($_SERVER['SERVER_PROTOCOL'] . ' ' . $data['code'] . ' ' . $data['message']);
        if (\filter_input(INPUT_POST, 'json') || \filter_input(INPUT_GET, 'json'))
        {
            \header('Content-Type: application/json');
            echo \json_encode([
                'error' => $data,
            ]);
        }
        else
        {
            Page::route('error', $data);
        }
    }

    /**
     * @return string The requested route
     */
    public static function getRoute(): string
    {
        return self::$route;
    }
}
