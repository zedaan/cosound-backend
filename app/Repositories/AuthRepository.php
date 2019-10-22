<?php

namespace App\Repositories;

use DB, Hash, Mail;

use App\Models\{User};
use App\Mails\{VerifyEMail, ResetPassword};
use App\Contracts\AuthContract;

class AuthRepository implements AuthContract
{
	/**
	 * {@inheritdoc}
	 */
	public function create($credentials)
	{
		$data = [];

		$data = [
			'email' => array_get($credentials, 'email'),
			'password' => array_get($credentials, 'password'),
			'first_name' => array_get($credentials, 'first_name'),
			'last_name' => array_get($credentials, 'last_name', NULL),

			'type' => array_get($credentials, 'type'),
			'artist_name' => array_get($credentials, 'artist_name'),

			'dob' => array_get($credentials, 'dob', NULL) !== "null" ? dateFormatter(array_get($credentials, 'dob')) : NULL,
			'address' => array_get($credentials, 'address'),
			'latitude' => array_get($credentials, 'latitude'),
			'longitude' => array_get($credentials, 'longitude'),
			'postal_code' => array_get($credentials, 'postal_code'),
			'social_links' => array_get($credentials, 'social_links', json_encode([])),
			'avatar' => array_get($credentials, 'avatar', NULL),
		];

		$user = new User;
		
		$user->fill($data)->save();

		$genres = array_get($credentials, 'genres', '');
		$genres = json_decode($genres);

		$user->genres()->attach($genres);

		return $user;
	}

	/**
	 * {@inheritdoc}
	 */
	public function update($user, $credentials)
	{
		$data = [];

		$data = [
			'first_name' => array_get($credentials, 'first_name'),
			'last_name' => array_get($credentials, 'last_name'),

			'type' => array_get($credentials, 'type'),
			'artist_name' => array_get($credentials, 'artist_name'),
			'bio' => array_get($credentials, 'bio'),

			'dob' => !is_null(array_get($credentials, 'dob', NULL)) ? dateFormatter(array_get($credentials, 'dob')) : NULL,
				
			'address' => array_get($credentials, 'address'),
			'latitude' => array_get($credentials, 'latitude'),
			'longitude' => array_get($credentials, 'longitude'),
			'postal_code' => array_get($credentials, 'postal_code'),
			'phone_numbers' => json_encode(array_get($credentials, 'phone_numbers', [])),
			'social_links' => json_encode(array_get($credentials, 'social_links', [])),
		];
		
		$user->fill($data)->save();

		$genres = array_get($credentials, 'genres', []);
		
		$user->genres()->sync($genres);

		return $user;
	}

	/**
	 * {@inheritdoc}
	 */
	public function changePassword($user, $data)
	{
		if (! Hash::check($data['old_password'], $user->password)) {
			return ['success' => false, 'message' => "Old password don't match"];
		}

		$user->password = $data['new_password'];
		$user->save();

		return ['success' => true, 'message' => 'Password changed successfully.'];
	}

	/**
	 * {@inheritdoc}
	 */
	public function doAsyncVerification($user)
	{
		$verificationCode = str_random(30);

		$user->confirmation_code = $verificationCode;
		$user->save();

		Mail::to(
			$user->email, 
			$user->first_name
		)->queue((new VerifyEMail($user->id))->onQueue('mail'));

		return $verificationCode;
	}

	/**
	 * {@inheritdoc}
	 */
	public function doAsyncForgotPassword($credentials)
	{
		$user = User::whereEmail($credentials['email'])->first();
		if (! $user) return false;

		$token = Hash::make($credentials['email']);

		DB::table('password_resets')->whereEmail($credentials['email'])->delete();

		DB::table('password_resets')->insert([
			'email' => $credentials['email'],
			'token' => $token,
			'created_at' => date('Y-m-d H:i:s')
		]);

		Mail::to(
			$user->email,
			$user->first_name
		)->queue((new ResetPassword($user->id, $token))->onQueue('mail'));

		return true;
	}

	/**
	 * {@inheritdoc}
	 */
	public function getResetPassword($token)
	{
		$response = ['success' => false, 'message' => 'Invalid Link Followed'];

		$object = DB::table('password_resets')->whereToken($token)->first();
		if (!$object || !Hash::check($object->email, $token)) {
			return $response;
		}

		$user = User::whereEmail($object->email)->first();
		if (! $user || $user->is_blocked == 1) {
			return $response;
		}

		$response['success'] = true;
		return $response;
	}

	/**
	 * {@inheritdoc}
	 */
	public function postResetPassword($credentials)
	{
		$response = ['success' => false, 'message' => 'Invalid Link Followed'];

		$object = DB::table('password_resets')
			->whereEmail(trim($credentials['email']))
			->whereToken(trim($credentials['token']))
			->first();

		if (!$object || !Hash::check($object->email, trim($credentials['token']))) {
			return $response;
		}

		$user = User::whereEmail($object->email)->first();
		if (! $user || $user->is_blocked == 1) {
			return $response;
		}

		$user->password = $credentials['password'];
		$user->save();

		DB::statement("DELETE FROM `password_resets` WHERE `email` like '$credentials[email]' and `token` like '$credentials[token]';");

		return ['success' => true, 'message' => 'Password changed successfully'];
	}
}
