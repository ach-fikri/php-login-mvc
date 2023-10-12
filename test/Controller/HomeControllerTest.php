<?php
namespace AchFikri\Belajar\PHP\MVC\Controller;

    use AchFikri\Belajar\PHP\MVC\Config\Database;
    use AchFikri\Belajar\PHP\MVC\Domain\Session;
    use AchFikri\Belajar\PHP\MVC\Domain\User;
    use AchFikri\Belajar\PHP\MVC\Repository\SessionRepository;
    use AchFikri\Belajar\PHP\MVC\Repository\UserRepository;
    use AchFikri\Belajar\PHP\MVC\Service\SessionService;
    use PHPUnit\Framework\TestCase;

    class HomeControllerTest extends TestCase
    {
        private HomeController $homeController;
        private UserRepository $userRepository;
        private SessionRepository $sessionRepository;

        protected function setUp(): void
        {
            $this->homeController = new HomeController();
            $this->sessionRepository = new SessionRepository(Database::getConnection());
            $this->userRepository = new UserRepository(Database::getConnection());
            $this->sessionRepository->deleteAll();
            $this->userRepository->deleteAll();
        }

        public function testGuest()
        {
            $this->homeController->index();
            $this->expectOutputRegex("[Login Management]");

        }

        public function testUserLogin()
        {
            $user = new User();
            $user->id = "fikri";
            $user->name = "Fikri";
            $user->password = "rahasia";
            $this->userRepository->save($user);

            $session = new Session();
            $session->id = uniqid();
            $session->userId = "fikri";
            $this->sessionRepository->save($session);
            $_COOKIE[SessionService::$COOKIE_NAME] = $session->id;
            $this->homeController->index();
            $this->expectOutputRegex("[Hello Fikri]");
        }
    }
