<?php

namespace AchFikri\Belajar\PHP\MVC\Service;
require_once __DIR__ . '/../Helper/helper.php';
use AchFikri\Belajar\PHP\MVC\Config\Database;
use AchFikri\Belajar\PHP\MVC\Domain\Session;
use AchFikri\Belajar\PHP\MVC\Domain\User;
use AchFikri\Belajar\PHP\MVC\Repository\SessionRepository;
use AchFikri\Belajar\PHP\MVC\Repository\UserRepository;
use PHPUnit\Framework\TestCase;



class SessionServiceTest extends TestCase
{
    private SessionService $sessionService;
    private SessionRepository $sessionRepository;
    private UserRepository $userRepository;


    protected function setUp(): void
    {
      $this->sessionRepository = new SessionRepository(Database::getConnection());
      $this->userRepository = new UserRepository(Database::getConnection());
      $this->sessionService = new SessionService($this->sessionRepository, $this->userRepository);

      $this->sessionRepository->deleteAll();
      $this->userRepository->deleteAll();

        $user = new User();
        $user->id = "fikri";
        $user->name = "Fikri";
        $user->password = "rahasia";
        $this->userRepository->save($user);
    }

    public function testCreate()
    {
        $session = $this->sessionService->create("fikri");
        $this->expectOutputRegex("[FKR-SESSION: $session->id]");

        $result = $this->sessionRepository->findById($session->id);
        self::assertEquals("fikri", $result->userId);

    }

    public function testDestroy()
    {
        $session = new Session();
        $session->id = uniqid();
        $session->userId = "fikri";
        $this->sessionRepository->save($session);
        $_COOKIE[SessionService::$COOKIE_NAME] = $session->id;

        $this->sessionService->destroy();
        $this->expectOutputRegex("[FKR-SESSION: ]");

        $result = $this->sessionRepository->findById($session->id);
        self::assertNull($result);

    }

    public function testCurrent()
    {
        $session = new Session();
        $session->id = uniqid();
        $session->userId = "fikri";
        $this->sessionRepository->save($session);
        $_COOKIE[SessionService::$COOKIE_NAME] = $session->id;
        $user = $this->sessionService->current();
        self::assertEquals($session->userId, $user->id);
    }

}
