<?php

/**
 * The signup controller
 */
class SignupController extends BaseController
{
    /**
     * Show registration page
     */
    public function showRegisterAction()
    {
        return $this->view('register/register');
    }

    /**
     * Register user
     */
    public function signupAction()
    {
        $values = $this->getInput();
        $values['password'] = $this->auth->hashPassword($values['password']);
        unset($values['password_confirm']);

        $user = User::create()->setValues($values);
        $validation = $user->validate();

        if (!$validation->isSuccess()) {
            return $this->badRequest($validation->getErrors()[0]);
        }

        $user->save();
        $this->auth->setUser($user);        
        $this->sendConfirmationEmail($user);
        
        $this->redirect('/');
    }

    /**
     * Confirm user registration
     */
    public function confirmAction()
    {
        $hash = $this->getQueryParam('c');
        
        $user = $this->auth->fetchUserForConfirmation($hash, 'signup');        
        if (!$user) {
            return $this->badRequest("Confirmation link is no longer valid");
        }
        
        if ($user->active) {
            return $this->back(); // Nothing to do
        }
        
        $user->setValues(['active' => true])->save();
        $this->auth->setUser($user);
        
        $this->flash('success', "Your email is verified");
        $this->redirect('/');
    }

    /**
     * Send email to confirm the registration
     *
     * @param User $user
     */
    protected function sendConfirmationEmail(User $user)
    {
        $hash = $this->auth->getConfirmationToken($user, 'signup');
        $url = $this->getSignupUrl('/signup/confirm', $hash);
        
        try {
            App::email('signup')->with(compact('user', 'url'))->sendTo($user->email, $user->getFullName());

            $this->flash('success', sprintf("Hi, %s, welcom! We've send you an email to complete registration.", (string)$user));
        } catch (Exception $e) {
            trigger_error($e->getMessage(), E_USER_WARNING);

            $this->flash('danger', "There was an error while sending you an email to confirm registration.");
        }
    }
}
