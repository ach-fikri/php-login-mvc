<?php

namespace AchFikri\Belajar\PHP\MVC\Service;

use AchFikri\Belajar\PHP\MVC\Config\Database;
use AchFikri\Belajar\PHP\MVC\Domain\User;
use AchFikri\Belajar\PHP\MVC\Exception\ValidationException;
use AchFikri\Belajar\PHP\MVC\Model\UserLoginRequest;
use AchFikri\Belajar\PHP\MVC\Model\UserPasswordUpdateRequest;
use AchFikri\Belajar\PHP\MVC\Model\UserProfileUpdateRequest;
use AchFikri\Belajar\PHP\MVC\Model\UserRegisterRequest;
use AchFikri\Belajar\PHP\MVC\Repository\SessionRepository;
use AchFikri\Belajar\PHP\MVC\Repository\UserRepository;
use PHPUnit\Framework\TestCase;

class UserServiceTest extends TestCase
{
    private UserService $userService;
    private UserRepository $userRepository;
    private SessionRepository $sessionRepository;

    protected function setUp(): void
    {
        $connection = Database::getConnection();
        $this->userRepository = new UserRepository($connection);
        $this->userService = new UserService($this->userRepository);
        $this->sessionRepository = new SessionRepository($connection);
        $this->sessionRepository->deleteAll();
        $this->userRepository->deleteAll();
    }

    public function testRegisterSuccess()
    {
        $request = new UserRegisterRequest();
        $request->id = "fikri";
        $request->name = "Fikri";
        $request->password = password_hash("rahasia", PASSWORD_BCRYPT);
        $response = $this->userService->register($request);
        self::assertEquals($request->id, $response->user->id);
        self::assertEquals($request->name, $response->user->name);
        self::assertNotEquals($request->password, $response->user->password);

        self::assertTrue(password_verify($request->password, $response->user->password));
    }

    public function testRegisterFailed()
    {
        $this->expectException(ValidationException::class);
        $request = new UserRegisterRequest();
        $request->id = "";
        $request->name = "";
        $request->password = "";
        $this->userService->register($request);
    }

    public function testRegisterDuplicate()
    {
        $user = new User();
        $user->id = "fikri";
        $user->name = "Fikri";
        $user->password = "rahasia";
        $this->userRepository->save($user);
        $this->expectException(ValidationException::class);
        $request = new UserRegisterRequest();
        $request->id = "fikri";
        $request->name = "Fikri";
        $request->password = "rahasia";
        $this->userService->register($request);
    }

    public function testLoginNotFound()
    {
        $this->expectException(ValidationException::class);
        $request = new UserLoginRequest();
        $request->id = "fikri";
        $request->password = "rahasia";
        $this->userService->login($request);
    }

    public function testLoginWrongPassword()
    {
        $user = new User();
        $user->id = "fikri";
        $user->password = password_hash("rahasia", PASSWORD_BCRYPT);
        $this->expectException(ValidationException::class);
        $request = new UserLoginRequest();
        $request->id = "fikri";
        $request->password = "salah";
        $this->userService->login($request);
    }

    public function testLoginSucces()
    {
        $user = new User();
        $user->id = "fikri";
        $user->password = password_hash("rahasia", PASSWORD_BCRYPT);
        $this->expectException(ValidationException::class);
        $request = new UserLoginRequest();
        $request->id = "fikri";
        $request->password = "rahasia";
        $response = $this->userService->login($request);
        self::assertEquals($request->id, $response->user->id);
        self::assertTrue(password_verify($request->password, $response->user->password));
    }

    public function testUpdateSuccess()
    {
        $user = new User();
        $user->id = "fikri";
        $user->name = "Fikri";
        $user->password = password_hash("rahasia", PASSWORD_BCRYPT);
        $this->userRepository->save($user);

        $request = new UserProfileUpdateRequest();
        $request->id = "fikri";
        $request->name = "Rina";
        $this->userService->updateProfile($request);

        $result = $this->userRepository->finById($user->id);
        self::assertEquals($request->name, $result->name);

    }

    public function testUpdateValidationError()
    {
        $this->expectException(ValidationException::class);
        $request = new UserProfileUpdateRequest();
        $request->id = "";
        $request->name = "";
        $this->userService->updateProfile($request);

    }

    public function testUpdateNotFound()
    {
        $this->expectException(ValidationException::class);
        $request = new UserProfileUpdateRequest();
        $request->id = "fikri";
        $request->name = "Rina";
        $this->userService->updateProfile($request);
    }

    public function testUpdatePasswordSuccess()
    {
        $user = new User();
        $user->id = "fikri";
        $user->name = "Fikri";
        $user->password = password_hash("rahasia", PASSWORD_BCRYPT);
        $this->userRepository->save($user);

        $request = new UserPasswordUpdateRequest();
        $request->id = "fikri";
        $request->oldPassword = "rahasia";
        $request->newPassword = "new";
        $this->userService->updatePassword($request);

        $result = $this->userRepository->finById($user->id);
        self::assertTrue(password_verify($request->newPassword, $result->password));
    }

    public function testUpdatePasswordValidationError()
    {
        $this->expectException(ValidationException::class);
        $request = new UserPasswordUpdateRequest();
        $request->id = "fikri";
        $request->oldPassword = "";
        $request->newPassword = "";
        $this->userService->updatePassword($request);
    }

    public function testUpdatePasswordWrongOldPassword()
    {
        $this->expectException(ValidationException::class);
        $user = new User();
        $user->id = "fikri";
        $user->name = "Fikri";
        $user->password = password_hash("rahasia", PASSWORD_BCRYPT);
        $this->userRepository->save($user);

        $request = new UserPasswordUpdateRequest();
        $request->id = "fikri";
        $request->oldPassword = "salah";
        $request->newPassword = "new";
        $this->userService->updatePassword($request);
    }

    public function testUpdatePasswordNotFound()
    {
        $this->expectException(ValidationException::class);

        $request = new UserPasswordUpdateRequest();
        $request->id = "eko";
        $request->oldPassword = "eko";
        $request->newPassword = "new";

        $this->userService->updatePassword($request);
    }
}