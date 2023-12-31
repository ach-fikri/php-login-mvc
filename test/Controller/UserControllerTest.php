<?php


namespace AchFikri\Belajar\PHP\MVC\Controller {
    require_once __DIR__.'/../Helper/helper.php';
	use AchFikri\Belajar\PHP\MVC\Config\Database;
    use AchFikri\Belajar\PHP\MVC\Domain\Session;
    use AchFikri\Belajar\PHP\MVC\Domain\User;
    use AchFikri\Belajar\PHP\MVC\Repository\SessionRepository;
    use AchFikri\Belajar\PHP\MVC\Repository\UserRepository;
    use AchFikri\Belajar\PHP\MVC\Service\SessionService;
    use PHPUnit\Framework\TestCase;

	class UserControllerTest extends TestCase
	{
		private UserController $userController;
		private UserRepository $userRepository;
        private SessionRepository $sessionRepository;

		protected function setUp(): void
		{
			$this->userController = new UserController();
            $this->sessionRepository = new SessionRepository(Database::getConnection());
            $this->sessionRepository->deleteAll();
			$this->userRepository = new UserRepository(Database::getConnection());
			$this->userRepository->deleteAll();
			putenv("mode=test");
		}

		public function testRegister()
		{
			$this->userController->register();
			$this->expectOutputRegex("[Register]");
			$this->expectOutputRegex("[Id]");
			$this->expectOutputRegex("[Name]");
			$this->expectOutputRegex("[Password]");
			$this->expectOutputRegex("[Register new User]");
		}

		public function testPostRegisterSucces()
		{
			$_POST['id'] = 'fikri';
			$_POST['name'] = 'Fikri';
			$_POST['password'] = 'rahasia';
			$this->userController->postRegister();
			$this->expectOutputRegex("[Location: /users/login]");
		}

		public function testPostRegisterValidationError()
		{
			$_POST['id'] = '';
			$_POST['name'] = 'Fikri';
			$_POST['password'] = 'rahasia';
			$this->userController->postRegister();

			$this->expectOutputRegex("[Register]");
			$this->expectOutputRegex("[Id]");
			$this->expectOutputRegex("[Name]");
			$this->expectOutputRegex("[Password]");
			$this->expectOutputRegex("[Register new User]");
			$this->expectOutputRegex("[id, name, password can not blank]");

		}

		public function testPostRegisterDuplicate()
		{
			$user = new User();
			$user->id = "fikri";
			$user->name = "Fikri";
			$user->password = "rahasia";
			$this->userRepository->save($user);

			$_POST['id'] = 'fikri';
			$_POST['name'] = 'Fikri';
			$_POST['password'] = 'rahasia';
			$this->userController->postRegister();

			$this->expectOutputRegex("[Register]");
			$this->expectOutputRegex("[Id]");
			$this->expectOutputRegex("[Name]");
			$this->expectOutputRegex("[Password]");
			$this->expectOutputRegex("[Register new User]");
			$this->expectOutputRegex("[user id is already exists]");
		}

		public function testLogin()
		{
			$this->userController->login();
			$this->expectOutputRegex("[Login user]");
			$this->expectOutputRegex("[Id]");
			$this->expectOutputRegex("[Password]");
		}

		public function testLoginSuccess()
		{
			$user = new User();
			$user->id = "fikri";
			$user->name = "Fikri";
			$user->password =password_hash( "rahasia", PASSWORD_BCRYPT);
			$this->userRepository->save($user);

			$_POST['id'] = "fikri";
			$_POST['password'] = 'rahasia';
			$this->userController->postLogin();
			$this->expectOutputRegex("[Location: /]");
            $this->expectOutputRegex("[FKR-SESSION: ]");
		}

		public function testLoginValidationError()
		{
			$_POST['id'] = '';
			$_POST['password'] = '';
			$this->userController->postLogin();
			$this->expectOutputRegex("[Login user]");
			$this->expectOutputRegex("[Id]");
			$this->expectOutputRegex("[Password]");
			$this->expectOutputRegex("[id, password can not blank]");
		}

		public function testLoginNotFound()
		{
			$_POST['id'] = 'notfound';
			$_POST['password'] = 'notfound';
			$this->userController->postLogin();
			$this->expectOutputRegex("[Login user]");
			$this->expectOutputRegex("[Id]");
			$this->expectOutputRegex("[Password]");
			$this->expectOutputRegex("[Id or Password is wrong]");
		}

		public function testLoginWrongPassword()
		{
			$user = new User();
			$user->id = "fikri";
			$user->name = "Fikri";
			$user->password =password_hash( "rahasia", PASSWORD_BCRYPT);
			$this->userRepository->save($user);

			$_POST['id'] = 'fikri';
			$_POST['password'] = 'salah';
			$this->userController->postLogin();
			$this->expectOutputRegex("[Login user]");
			$this->expectOutputRegex("[Id]");
			$this->expectOutputRegex("[Password]");
			$this->expectOutputRegex("[Id or password is wrong]");
		}

        public function testLogout()
        {
            $user = new User();
            $user->id = "fikri";
            $user->name = "Fikri";
            $user->password = "rahasia";
            $this->userRepository->save($user);

            $session = new Session();
            $session->id = uniqid();
            $session->userId = $user->id;
            $this->sessionRepository->save($session);
            $this->userController->logout();

            $this->expectOutputRegex("[Location: /]");
            $this->expectOutputRegex("[FKR-SESSION: ]");
        }

        public function testUpdateProfile()
        {
            $user = new User();
            $user->id = "fikri";
            $user->name = "Fikri";
            $user->password = password_hash("rahasia", PASSWORD_BCRYPT);
            $this->userRepository->save($user);

            $session = new Session();
            $session->id = uniqid();
            $session->userId = $user->id;
            $this->sessionRepository->save($session);

            $_COOKIE[SessionService::$COOKIE_NAME] = $session->id;

            $this->userController->updateProfile();
            $this->expectOutputRegex("[Profile]");
            $this->expectOutputRegex("[Id]");
            $this->expectOutputRegex("[fikri]");
            $this->expectOutputRegex("[Name]");
            $this->expectOutputRegex("[Fikri]");
        }

        public function testPostUpdateProfileSuccess()
        {
            $user = new User();
            $user->id = "fikri";
            $user->name = "Fikri";
            $user->password = "rahasia";
            $this->userRepository->save($user);

            $session = new Session();
            $session->id = uniqid();
            $session->userId = $user->id;
            $this->sessionRepository->save($session);

            $_COOKIE[SessionService::$COOKIE_NAME] = $session->id;
            $_POST['name'] = "budi";
            $this->userController->postUpdateProfile();

            $this->expectOutputRegex("[Location: /]");
            $result = $this->userRepository->finById("fikri");
            self::assertEquals("budi", $result->name);

        }

        public function testUpdateProfileValidasiError()
        {
            $user = new User();
            $user->id = "fikri";
            $user->name = "Fikri";
            $user->password = "rahasia";
            $this->userRepository->save($user);

            $session = new Session();
            $session->id = uniqid();
            $session->userId = $user->id;
            $this->sessionRepository->save($session);

            $_COOKIE[SessionService::$COOKIE_NAME] = $session->id;
            $_POST['name'] = "";
            $this->userController->postUpdateProfile();
            $this->expectOutputRegex("[Profile]");
            $this->expectOutputRegex("[Id]");
            $this->expectOutputRegex("[fikri]");
            $this->expectOutputRegex("[Name]");
            $this->expectOutputRegex("[id, name can not blank]");

        }

        public function testUpdatePassword()
        {
            $user = new User();
            $user->id = "fikri";
            $user->name = "Fikri";
            $user->password = "rahasia";
            $this->userRepository->save($user);

            $session = new Session();
            $session->id = uniqid();
            $session->userId = $user->id;
            $this->sessionRepository->save($session);

            $_COOKIE[SessionService::$COOKIE_NAME] = $session->id;
            $this->userController->updatePassword();

            $this->expectOutputRegex("[Password]");
            $this->expectOutputRegex("[Id]");
            $this->expectOutputRegex("[fikri]");

        }

        public function testPostUpdatePasswordSuccess()
        {
            $user = new User();
            $user->id = "fikri";
            $user->name = "Fikri";
            $user->password = password_hash("rahasia", PASSWORD_BCRYPT);
            $this->userRepository->save($user);

            $session = new Session();
            $session->id = uniqid();
            $session->userId = $user->id;
            $this->sessionRepository->save($session);

            $_COOKIE[SessionService::$COOKIE_NAME] = $session->id;

            $_POST['oldPassword'] = "rahasia";
            $_POST['newPassword'] = "ok";

            $this->userController->postUpdatePassword();

            $this->expectOutputRegex("[Location: /]");

            $result = $this->userRepository->finById($user->id);

            self::assertTrue(password_verify("ok", $result->password));
        }

        public function testPostUpdatePasswordValidationError()
        {
            $user = new User();
            $user->id = "fikri";
            $user->name = "Fikri";
            $user->password = password_hash("rahasia", PASSWORD_BCRYPT);
            $this->userRepository->save($user);

            $session = new Session();
            $session->id = uniqid();
            $session->userId = $user->id;
            $this->sessionRepository->save($session);

            $_COOKIE[SessionService::$COOKIE_NAME] = $session->id;

            $_POST['oldPassword'] = "";
            $_POST['newPassword'] = "";

            $this->userController->postUpdatePassword();

            $this->expectOutputRegex("[Password]");
            $this->expectOutputRegex("[Id]");
            $this->expectOutputRegex("[fikri]");
            $this->expectOutputRegex("[id, Old Password, New password can not blank]");

        }

        public function testPostUpdatePasswordWrongOldPassword()
        {
            $user = new User();
            $user->id = "fikri";
            $user->name = "Fikri";
            $user->password = password_hash("rahasia", PASSWORD_BCRYPT);
            $this->userRepository->save($user);

            $session = new Session();
            $session->id = uniqid();
            $session->userId = $user->id;
            $this->sessionRepository->save($session);

            $_COOKIE[SessionService::$COOKIE_NAME] = $session->id;

            $_POST['oldPassword'] = "salah";
            $_POST['newPassword'] = "ok";

            $this->userController->postUpdatePassword();

            $this->expectOutputRegex("[Password]");
            $this->expectOutputRegex("[Id]");
            $this->expectOutputRegex("[fikri]");
            $this->expectOutputRegex("[Old Password is wrong]");


        }


    }
 }
