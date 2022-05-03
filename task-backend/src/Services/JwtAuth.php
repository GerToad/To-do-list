<?php

namespace App\Services;

use Doctrine\Persistence\ManagerRegistry;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use App\Entity\User;

class JwtAuth{

	public $manager;
	public $key;

	public function __construct(ManagerRegistry $doctrine){
		$this->manager = $doctrine;
		$this->key = "Entschuldigung";
	}

	public function signup($email, $password, $getToken = null){
		// Check the user exists
		$user = $this->manager->getRepository(User::class)->findOneBy([
			'email' => $email,
			'password' => $password
		]);

		$signup = false;
		if(is_object($user)){
			$signup = true;
		}

		// If exists, create token
		if($signup){
			$token = [
				'sub' => $user->getId(),
				'name' => $user->getName(),
				'surname' => $user->getSurname(),
				'email' => $user->getEmail(),
				'image' => $user->getImage(),
				'iat' => time(),
				'exp' => time() + (7 * 24 * 60 * 60),
			];

			// Checking the flag getToken condition
			$jwt = JWT::encode($token, $this->key, 'HS256');
			if(!empty($getToken) && $getToken){
				$data = $jwt;
			}else{
				$decoded = JWT::decode($jwt, new Key($this->key, 'HS256'));
				$data = $decoded;
			}
		}else{
			$data = [
				'status' => "error",
				'message' => 'Incorrect login'
			];
		}
		return $data;
	}

	public function checkToken($jwt, $identity = false){
		$auth = false;

		try{
			$decoded = JWT::decode($jwt, new Key($this->key, 'HS256'));
		}catch(\UnexpectedValueException $e){
			$auth = false;
		}catch(\DomainException $e){
			$auth = false;
		}

		if(isset($decoded) && !empty($decoded) && is_object($decoded) && isset($decoded->sub)){
			$auth = true;
		}else{
			$auth = false;
		}

		if($identity){
			return $decoded;
		}else{
			return $auth;
		}
	}
}
