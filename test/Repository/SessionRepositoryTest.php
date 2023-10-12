<?php

namespace AchFikri\Belajar\PHP\MVC\Repository;

use AchFikri\Belajar\PHP\MVC\Config\Database;
use AchFikri\Belajar\PHP\MVC\Domain\Session;
use AchFikri\Belajar\PHP\MVC\Domain\User;
use PHPUnit\Framework\TestCase;

class SessionRepositoryTest extends TestCase
{
    private SessionRepository $sessionRepository;
    private UserRepository $userRepository;

    protected function setUp(): void
    {
        $this->userRepository = new UserRepository(Database::getConnection());
       $this->sessionRepository = new SessionRepository(Database::getConnection());
       $this->sessionRepository->deleteAll();
       $this->userRepository->deleteAll();

       $user = new User();
       $user->id = "fikri";
       $user->name = "Fikri";
       $user->password = "rahasia";
       $this->userRepository->save($user);
    }

    public function testSaveSuccess()
    {
        $session = new Session();
        $session->id = uniqid();
        $session->userId = 'fikri';
        $this->sessionRepository->save($session);
        $result = $this->sessionRepository->findById($session->id);
        self::assertEquals($session->id, $result->id);
        self::assertEquals($session->userId, $result->userId);

    }
    public function testDeleteByIdSuccess()
    {
        $session = new Session();
        $session->id = uniqid();
        $session->userId = 'fikri';
        $this->sessionRepository->save($session);
        $result = $this->sessionRepository->findById($session->id);
        self::assertEquals($session->id, $result->id);
        self::assertEquals($session->userId, $result->userId);

        $this->sessionRepository->deleteById($session->id);
        $result = $this->sessionRepository->findById($session->id);
        self::assertNull($result);

    }

    public function testfindByIdNotFoun()
    {
        $result = $this->sessionRepository->findById("notfound");
        self::assertNull($result);

    }

}
