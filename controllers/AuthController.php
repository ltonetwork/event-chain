<?php

/**
 * Authentication controller
 */
class AuthController extends BaseController
{
    /**
     * Show login page
     */
    public function showLoginAction()
    {
        return $this->view('auth/login');
    }

    /**
     * Perform login with name/password
     */
    public function loginAction()
    {
        $input = $this->getInput();
        $fail = empty($input['email']) || empty($input['password']) ||
            !$this->auth->login($input['email'], $input['password']);

        if ($fail) {
            $this->flash('danger', "User name or password are incorrect.");
            return $this->redirect('/');
        }
         
        $this->flash('success', sprintf("Hi %s, welcome!", (string)$this->auth->user()));

        return $this->redirect('/');
    }

    /**
     * Login with social service
     *
     * @param string $service
     */
    public function loginWithAction($service)
    {
        try {
            $conn = App::getContainer()->get("social:$service");
            $conn->auth(App::config()->$service->scope);
        } catch (Social\AuthException $e) {
            $this->flash('error', "Sorry, there was an error trying to login via " . ucfirst($service));
            return $this->redirect('/login');
        } catch (RuntimeException $e) {
            $this->flash('error', $e->getMessage());
            return $this->redirect('/login');
        }

        $socialUser = $conn->me(['id', 'first_name', 'last_name', 'email']);

        $user = User::fetch([$service . '_id' => $socialUser->getId()])
            ?: User::fetch(['email' => $socialUser->getEmail()])
            ?: User::create();
        
        $user->addSocialNetwork($service, $socialUser);
        
        $validation = $user->validate();
        if ($validation->failed()) {
            return $this->badRequest(join(', ', $validation->getErrors()));
        }
        
        $user->save();
        $this->auth->setUser($user);

        $this->flash('success', sprintf("Hi %s, welcome!", (string)$this->auth->user()));        
        return $this->redirect('/');
    }

    /**
     * Logout
     */
    public function logoutAction()
    {
        $this->auth->logout();
        
        return $this->redirect('/');
    }

    /**
     * Forget password actions
     */
    public function forgotPasswordAction()
    {
        $values = $this->getInput();
        $user = User::fetch(['email' => $values['email']]);

        if (!$user) {
            $this->flash('error', "Email adress is incorrect");
            return $this->redirect('/');
        }

        $this->sendResetPasswordEmail($user);
        
        $this->redirect('/');
    }
    
    /**
     * Show form to set new password
     */    
    public function showResetPasswordAction()
    {
        $hash = $this->getQueryParam('c');
        
        $user = $this->auth->fetchUserForConfirmation($hash, 'reset-password', true);
        if (!$user) {
            return $this->badRequest("This link is no longer valid");
        }

        return $this->view('auth/reset-password', compact('hash', 'user'));
    }
    
    /**
     * Set new password
     */    
    public function resetPasswordAction()
    {
        $hash = $this->getQueryParam('c');

        $user = $this->auth->fetchUserForConfirmation($hash, 'reset-password', true);
        if (!$user) {
            return $this->badRequest("This link is no longer valid");
        }

        $values = array_intersect_key($this->getInput(), array_flip(['password']));
        $values['password'] = $this->auth->hashPassword($values['password']);
        $values['active'] = true;

        $user->setValues($values);
        $validation = $user->validate();

        if (!$validation->isSuccess()) {
            return $this->badRequest($validation->getErrors()[0]);
        }

        $user->save();
        $this->auth->setUser($user);

        $this->flash('success', "Password has been reset successfully");
        $this->redirect('/');        
    }

    /**
     * Send email for password reset
     *
     * @param User $user
     */
    protected function sendResetPasswordEmail(User $user)
    {
        $url = (string)$this->getRequest()->getUri()
            ->withPath('/reset-password')
            ->withQuery('c=' . $this->auth->getConfirmationToken($user, 'reset-password', true))
            ->withPort('');
        
        // try {
            App::email('reset-password')->with(compact('user', 'url'))->sendTo($user->email, $user->getFullName());
            $this->flash('success', "An e-mail with link for reseting password is on it's way");
        // } catch (Exception $e) {
        //     trigger_error($e->getMessage(), E_USER_WARNING);
        //     $this->flash('danger', "Failed to send an e-mail to complete registration");
        // }
    }
}
