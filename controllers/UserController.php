<?php

/**
 * The user controller
 */
class UserController extends BaseController
{    
    /**
     * Show edit user screen
     */
    public function showEditAction()
    {
        $user = $this->auth->user();

        return $this->view('users/edit-user', compact('user'));
    }

    /**
     * Edit user data
     */
    public function editAction()
    {
        $user = $this->auth->user();

        $data = array_intersect_key($this->getInput(), array_flip(['first_name', 'last_name', 'email']));
        $user->setValues($data);

        $validation = $user->validate();
        if (!$validation->isSuccess()) {
            return $this->badRequest($validation->getErrors()[0]);
        }

        $user->save();

        $this->flash('success', "User info is saved");
        $this->redirect('/settings');
    }

    /**
     * Show edit user password page
     */
    public function showEditPasswordAction()
    {
        return $this->view('users/edit-password');
    }

    /**
     * Edit user password
     */
    public function editPasswordAction()
    {
        $user = $this->auth->user();

        $data = array_only($this->getInput(), ['password', 'old_password']);
        $data['password'] = $this->auth->hashPassword($data['password']);

        if (!$this->auth->verifyCredentials($user, $data['old_password'])) {
            return $this->badRequest("Current password is not correct");   
        }

        unset($data['old_password']);
        $user->setValues($data);

        $validation = $user->validate();
        if (!$validation->isSuccess()) {
            return $this->badRequest($validation->getErrors()[0]);
        }

        $user->save();

        $this->flash('success', "New password is set");
        $this->redirect('/settings');
    }

    /**
     * Delete user
     */
    public function deleteAction()
    {
        $this->auth->user()->delete();

        $this->auth->logout();
        $_SESSION['deleted'] = true;

        $this->redirect('/good-bye');
    }

    /**
     * Show farewell page after user deletion
     */
    public function goodByeAction()
    {
        if (!isset($_SESSION['deleted'])) {
            return $this->redirect('/');
        }

        unset($_SESSION['deleted']);
        $this->view('users/good-bye');
    }
}
