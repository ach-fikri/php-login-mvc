<?php

namespace AchFikri\Belajar\PHP\MVC\Repository;

use AchFikri\Belajar\PHP\MVC\Config\Database;
use AchFikri\Belajar\PHP\MVC\Domain\User;
use PHPUnit\Framework\TestCase;
class UserRepositoryTest extends TestCase
{
	private UserRepository $userRepository;
    private SessionRepository $sessionRepository;

	protected function setUp(): void
	{
        $this->sessionRepository = new SessionRepository(Database::getConnection());
        $this->sessionRepository->deleteAll();
		$this->userRepository= new UserRepository(Database::getConnection());
		$this->userRepository->deleteAll();
	}

	public function testSaveSuccess()
	{
		$user = new User();
		$user->id = "fikri";
		$user->name = "Fikri";
		$user->password = "rahasia";
		$this->userRepository->save($user);
		$result = $this->userRepository->finById($user->id);
		self::assertEquals($user->id, $result->id);
		self::assertEquals($user->name, $result->name);
		self::assertEquals($user->password, $result->password);
	}

	public function testFindByIdNotFound()
	{
		$user = $this->userRepository->finById("notfound");
		self::assertNull($user);
	}

    public function testUpdate()
    {
        $user = new User();
        $user->id = "fikri";
        $user->name = "Fikri";
        $user->password = "rahasia";
        $this->userRepository->save($user);

        $user->name = "rina";
        $this->userRepository->update($user);
        $result = $this->userRepository->finById($user->id);
        self::assertEquals($user->id, $result->id);
        self::assertEquals($user->name, $result->name);
        self::assertEquals($user->password, $result->password);
    }


}