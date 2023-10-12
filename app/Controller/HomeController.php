<?php

namespace AchFikri\Belajar\PHP\MVC\Controller;
use AchFikri\Belajar\PHP\MVC\App\View;
use AchFikri\Belajar\PHP\MVC\Config\Database;
use AchFikri\Belajar\PHP\MVC\Repository\SessionRepository;
use AchFikri\Belajar\PHP\MVC\Repository\UserRepository;
use AchFikri\Belajar\PHP\MVC\Service\SessionService;
use function PHPUnit\Framework\identicalTo;

class HomeController
{
    private SessionService $sessionService;

    public function __construct()
    {
        $connection = Database::getConnection();
        $sessionRepository = new SessionRepository($connection);
        $userRepository = new UserRepository($connection);
        $this->sessionService = new SessionService($sessionRepository, $userRepository);
    }

    public function index(){
        $user = $this->sessionService->current();
        if ($user == null){
            View::render('Home/index', [
                "title"=>"PHP Login Management"
            ]);
        }else{
            View::render('Home/dashboard', [
                "title"=>"Dashboard",
                "user" => [
                    "name" => $user->name
                ]
            ]);
        }
	}
}