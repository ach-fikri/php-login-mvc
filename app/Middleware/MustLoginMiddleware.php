<?php

namespace AchFikri\Belajar\PHP\MVC\Middleware;

use AchFikri\Belajar\PHP\MVC\App\View;
use AchFikri\Belajar\PHP\MVC\Config\Database;
use AchFikri\Belajar\PHP\MVC\Repository\SessionRepository;
use AchFikri\Belajar\PHP\MVC\Repository\UserRepository;
use AchFikri\Belajar\PHP\MVC\Service\SessionService;

class MustLoginMiddleware implements Middleware
{
    private SessionService $sessionService;

    public function __construct()
    {
        $sessionRepository = new SessionRepository(Database::getConnection());
        $userRepository = new UserRepository(Database::getConnection());
        $this->sessionService = new SessionService($sessionRepository, $userRepository);
    }

    function before(): void
    {
        $user = $this->sessionService->current();
        if ($user == null){
            View::redirect('/users/login');
        }
    }
}