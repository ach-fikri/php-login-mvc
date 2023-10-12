<?php

namespace AchFikri\Belajar\PHP\MVC\Service;

use AchFikri\Belajar\PHP\MVC\Config\Database;
use AchFikri\Belajar\PHP\MVC\Domain\User;
use AchFikri\Belajar\PHP\MVC\Exception\ValidationException;
use AchFikri\Belajar\PHP\MVC\Model\UserLoginRequest;
use AchFikri\Belajar\PHP\MVC\Model\UserLoginResponse;
use AchFikri\Belajar\PHP\MVC\Model\UserPasswordUpdateRequest;
use AchFikri\Belajar\PHP\MVC\Model\UserPasswordUpdateResponse;
use AchFikri\Belajar\PHP\MVC\Model\UserProfileUpdateRequest;
use AchFikri\Belajar\PHP\MVC\Model\UserProfileUpdateResponse;
use AchFikri\Belajar\PHP\MVC\Model\UserRegisterRequest;
use AchFikri\Belajar\PHP\MVC\Model\UserRegisterResponse;
use AchFikri\Belajar\PHP\MVC\Repository\UserRepository;
use http\Env\Request;
use PHPUnit\Exception;

class UserService
{
	private UserRepository $userRepository;

	public function __construct(UserRepository $userRepository)
	{
		$this->userRepository = $userRepository;
	}

	public function register(UserRegisterRequest $request): UserRegisterResponse
	{
		$this->validateUserRegistrationRequest($request);
		try {
			Database::beginTransaction();
			$user = $this->userRepository->finById($request->id);
			if ($user != null){
				throw new ValidationException("user id is already exists");
			}
			$user = new User();
			$user->id = $request->id;
			$user->name = $request->name;
			$user->password = password_hash( $request->password, PASSWORD_BCRYPT);
			$this->userRepository->save($user);
			$response = new UserRegisterResponse();
			$response->user = $user;
			Database::commitTransaction();
			return $response;
		}catch (\Exception $exception){
			Database::rollbackTransaction();
			throw $exception;
		}


	}

	private function validateUserRegistrationRequest(UserRegisterRequest $request){
		if ($request->id == null || $request->name == null ||$request->password == null ||
		trim($request->id) == ""||trim($request->name) == ""||trim($request->password)==""){
			throw new ValidationException("id, name, password can not blank ");
		}
	}
	public function login(UserLoginRequest $request) : UserLoginResponse
	{
		$this->validateUserLoginRequest($request);
		$user = $this->userRepository->finById($request->id);
		if ($user == null){
			throw new ValidationException("Id or Password is wrong");
		}
		if (password_verify($request->password, $user->password)){
			$response = new UserLoginResponse();
			$response->user = $user;
			return $response;
		}else{
			throw new ValidationException("Id or password is wrong");
		}

	}
	private function validateUserLoginRequest(UserLoginRequest $request){
		if ($request->id == null ||$request->password == null ||
			trim($request->id) == ""||trim($request->password)==""){
			throw new ValidationException("id, password can not blank ");
		}
	}

    public function updateProfile(UserProfileUpdateRequest $request) : UserProfileUpdateResponse{
        $this->validationUserProfileUpdateRequest($request);

        try {
            Database::beginTransaction();
            $user = $this->userRepository->finById($request->id);
            if ($user == null){
                throw new ValidationException("User Nor Found");
            }
            $user->name = $request->name;
            $this->userRepository->update($user);

            Database::commitTransaction();

            $response = new UserProfileUpdateResponse();
            $response->user = $user;
            return$response;
        }catch (\Exception $exception){
            Database::rollbackTransaction();
            throw $exception;
        }
    }

    private function validationUserProfileUpdateRequest(UserProfileUpdateRequest $request){
        if ($request->id == null ||$request->name == null ||
            trim($request->id) == ""||trim($request->name)==""){
            throw new ValidationException("id, name can not blank ");
        }
    }

    public function updatePassword(UserPasswordUpdateRequest $request): UserPasswordUpdateResponse
    {
        $this->validateUserPasswordUpdateRequest($request);

        try {
            Database::beginTransaction();
            $user = $this->userRepository->finById($request->id);
            if ($user == null){
                throw new ValidationException("User  is not found");
            }

            if (!password_verify($request->oldPassword, $user->password))
            {
                throw new ValidationException("Old Password is wrong");
            }
            $user->password = password_hash($request->newPassword, PASSWORD_BCRYPT);
            $this->userRepository->update($user);

            Database::commitTransaction();

            $response = new UserPasswordUpdateResponse();
            $response->user = $user;
            return $response;
        }catch (Exception $exception){
            Database::rollbackTransaction();
            throw $exception;
        }


    }

    private function validateUserPasswordUpdateRequest(UserPasswordUpdateRequest $request)
    {
        if ($request->id == null || $request->oldPassword == null ||$request->newPassword == null ||
            trim($request->id) == ""||trim($request->oldPassword) == ""||trim($request->newPassword)==""){
            throw new ValidationException("id, Old Password, New password can not blank ");
        }
    }
}


