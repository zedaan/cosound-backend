<?php

namespace App\Contracts;

interface AuthContract
{
    /**
     * Creates the user
     * 
     * @param Array
     * @return \App\Models\User
     */
    public function create($credentials);

    /**
     * Updates the user
     * 
     * @param \App\Models\User $user
     * @param Array $credentials
     * @param string $avatar
     * 
     * @return \App\Models\User
     */
    public function update($user, $credentials);

    /**
     * Send user verification e-mail asynchronously
     * 
     * @param Array
     * @return String
     */
	public function doAsyncVerification($user);

    /**
     * Send user forgot-password mail asynchronously
     * 
     * @param Array
     * @return void
     */
	public function doAsyncForgotPassword($credentials);

    /**
     * Checks the token exist for resetting password
     * 
     * @param String
     * @return Array
     */
	public function getResetPassword($token);

    /**
     * Updates user password against token & email pair
     * 
     * @param Array
     * @return Array
     */
	public function postResetPassword($credentials);
}