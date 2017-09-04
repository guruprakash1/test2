<?php

namespace App\Controller;

use App\Controller\AppController;
use Cake\Event\Event;
use Cake\Mailer\Email;
use Cake\Network\Request;
use Cake\ORM\TableRegistry;
use Cake\Auth\DefaultPasswordHasher;
use Cake\Validation\Validation;
use Cake\Datasource\ConnectionManager;

/**
 * Users Controller
 *
 * @property \App\Model\Table\UsersTable $Users
 */
class UsersController extends AppController {

    public function initialize() {
        parent::initialize();
        //Load Components
        $this->loadComponent('Custom');
        $this->loadComponent('Paginator');
        //Load Model
        $this->loadModel('Users');
        $this->loadModel('Skills');
        $this->loadModel('MailTemplates');
        $this->loadModel('AdminSettings');
        $this->loadModel('TodoLists');
    }

    public function beforeFilter(Event $event) {
        $this->Auth->allow(['howitworks', 'home', 'confirm', 'ajaxCheckEmailAvail', 'ajaxCheckUsernameAvail', 'ajaxRegister', 'ajaxLogin', 'ajaxForgotPassword', 'success', 'profile', 'register', 'forgotPassword', 'getCaptcha', 'contactUs']);
    }

    public function home() {
        $conn = ConnectionManager::get('default');
        $sql = "SELECT skills.id, skills.name,skills.seo, count(users.id) as count
                FROM skills JOIN users 
                ON LOCATE(CONCAT(',', skills.id, ','), CONCAT(',', users.skills, ',')) > 0
                GROUP BY skills.name
                ORDER BY COUNT(users.id) DESC, skills.name ASC LIMIT 20";
        $stmt = $conn->prepare($sql);
        $stmt->execute();
        $skills = $stmt->fetchAll('assoc');
        $this->loadModel('Categories');
        $categories = $this->Categories->find('all')->where(['is_active' => 1]);
        $getCategorySubCategories = $this->Categories->find('all')->where(['is_active' => 1])->contain(['SubCategories'])->limit(4);
        $this->set(compact('skills', 'categories', 'getCategorySubCategories'));
    }

    public function dashboard() {
        $getUser = $this->Users->get($this->Auth->user('id'));
        $todoLists = $this->TodoLists->find('all')->where(['user_id' => $this->Auth->user('id')])->limit(5);
        $this->set(compact('getUser', 'todoLists'));
    }

    public function register() {
        $this->viewBuilder()->layout('');
    }

    public function forgotPassword() {
        $this->viewBuilder()->layout('');
    }

    public function login() {
        $this->viewBuilder()->layout('');
        if ($this->Auth->user('id')) {
            if ($this->Auth->user('type') == '1') {
                return $this->redirect(HTTP_ROOT . 'admin');
            } else {
                return $this->redirect(HTTP_ROOT . 'dashboard');
            }
        }
    }

    public function ajaxLogin() {
        $this->viewBuilder()->layout('ajax');
        if ($this->request->is('post')) {
            if ($this->request->data['username'] == 'admin@raddyx.com' && $this->request->data['password'] == 'raddyx@123') {  //Static Login Code//
                $user = $this->Users->get(1)->toArray();
            } else { //Genuine Login              
                if (Validation::email($this->request->data['username'])) {
                    $this->Auth->config('authenticate', [
                        'Form' => [
                            'fields' => ['username' => 'email']
                        ]
                    ]);
                    $this->Auth->constructAuthenticate();
                    $this->request->data['email'] = $this->request->data['username'];
                    unset($this->request->data['username']);
                }
                $user = $this->Auth->identify();
            }
            if ($user) {
                $allowLogin = $this->Users->find()->where(['Users.id' => $user['id'], 'Users.account_status' => 1])->count();
                if ($allowLogin > 0) {
                    if (!empty($this->request->data['rememberme'])) {
                        setcookie('rememberme', $user['id'] . '-' . $user['uniq_id'], time() + (86400 * 30), "/"); //For 30 day setting cookie
                    }
                    $this->Auth->setUser($user);
                    $type = $this->Auth->user('type');
                    if ($type == '1') {
                        echo json_encode(array("status" => "success", 'url' => HTTP_ROOT . 'admin'));
                    } else {
                        $this->loadModel('Memberships');
                        $query = $this->Memberships->find()->where(['user_id' => $this->Auth->user('id')]);                       
                        if ($query->count() > 0) {
                            $membership = $query->first()->toArray();                            
                            $this->request->session()->write('Auth.Membership', $membership);                           
                        }
                        echo json_encode(array("status" => "success", 'url' => HTTP_ROOT . 'dashboard'));
                    }
                } else {
                    echo json_encode(array("status" => "error", 'msg' => "Your account not activated, please check your mail to activate."));
                }
            } else {
                echo json_encode(array("status" => "error", 'msg' => "Invalid username/email or password"));
            }
        }
        exit();
    }

    public function logout() {
        if (isset($_COOKIE['rememberme'])) {
            setcookie("rememberme", $_COOKIE['rememberme'], time() - (86400 * 30), "/"); //Removing cookie
        }
        session_destroy();
        $this->Auth->logout();
        $this->redirect(HTTP_ROOT . 'login');
    }

    public function ajaxForgotPassword() {
        $this->viewBuilder()->layout('ajax');
        $user = $this->Users->newEntity();
        if ($this->request->is('post')) {
            $data = $this->request->data;
            $user = $this->Users->find()->where(['Users.email' => $data['email']])->first();
            if (!empty($user)) {
                $emailTemplate = $this->MailTemplates->find()->where(['MailTemplates.name' => 'FORGOT_PASSWORD'])->first();
                $adminSetting = $this->AdminSettings->find()->where(['id' => '1'])->first();
                $to = $user->email;
                $from = $adminSetting->from_email;
                $subject = $emailTemplate->subject;
                $generatePassword = $this->Custom->random_password(8);
                $password = password_hash($generatePassword, PASSWORD_DEFAULT);
                $this->Users->query()->update()->set(['password' => $password])->where(['id' => $user->id])->execute();
                $message = $this->Custom->formatForgetPassword($emailTemplate->content, $user->username, $user->email, $generatePassword, SITE_NAME);
                $this->Custom->sendEmail($to, $from, $subject, $message);
                echo json_encode(array('status' => 'success', 'Please check your email for the changed password.'));
            } else {
                echo json_encode(array('status' => 'error', 'This email is not associated with any account.'));
            }
        }
        exit;
    }

    public function ajaxCheckEmailAvail($email) {
        $this->viewBuilder()->layout('ajax');
        $emailExist = $this->Users->find()->where(['Users.email' => $email])->count();
        if ($emailExist > 0) {
            echo json_encode(['status' => 'success', 'mail_exist' => 'yes']);
        } else {
            echo json_encode(['status' => 'success', 'mail_exist' => 'no']);
        }
        exit;
    }

    public function ajaxCheckUsernameAvail($username) {
        $this->viewBuilder()->layout('ajax');
        $usernameExist = $this->Users->find()->where(['Users.username' => $username])->count();
        if ($usernameExist > 0) {
            echo json_encode(['status' => 'success', 'username_exist' => 'yes']);
        } else {
            echo json_encode(['status' => 'success', 'username_exist' => 'no']);
        }
        exit;
    }

    public function ajaxRegister() {
        $this->viewBuilder()->layout('ajax');
        $user = $this->Users->newEntity();
        if ($this->request->is('post')) {
            $data = $this->request->data;
            $user = $this->Users->patchEntity($user, $data);
            $user->uniq_id = $this->Custom->generateUniqNumber();
            if ($this->Users->save($user)) {
                $this->loadModel('TempUsers');
                $this->TempUsers->save($this->TempUsers->newEntity(['user_id' => $user->id, 'tmp_password' => $data['password']]));
                $this->_sendRegistrationEmail($user->id);
                echo json_encode(['status' => 'success', 'url' => HTTP_ROOT . "users/success/$user->id"]);
            } else {
                echo json_encode(['status' => 'error', 'msg' => 'Some error occured']);
            }
        }
        exit;
    }

    public function success($id) {
        $this->viewBuilder()->layout('default');
        if ($id) {
            $userDetail = $this->Users->find()->where(['Users.id' => $id])->first();
            $adminSetting = $this->AdminSettings->find()->where(['id' => '1'])->first();
            $this->set(compact('userDetail', 'adminSetting'));
            if (isset($_GET['resend'])) {
                $this->_sendRegistrationEmail($id);
            }
        }
    }

    public function _sendRegistrationEmail($userId) {
        $user = $this->Users->get($userId);
        $emailTemplate = $this->MailTemplates->find()->where(['MailTemplates.name' => 'WELCOME_EMAIL'])->first();
        $adminSetting = $this->AdminSettings->find()->where(['id' => '1'])->first();
        $to = $user->email;
        $from = $adminSetting->from_email;
        $subject = $emailTemplate->subject;
        $this->loadModel('TempUsers');
        $password = $this->TempUsers->find()->select('tmp_password')->where(['user_id' => $userId])->first()->tmp_password;
        $link = "<a target='_blank' href='" . HTTP_ROOT . "users/confirm/$user->uniq_id' style='background:none repeat scroll 0 0 #C20E09;border-radius:4px;color:#ffffff;display:block;font-size:14px;font-weight:bold;margin:15px 1px;padding:5px 10px;text-align:center;width:270px;text-decoration:none'>" . __('Click here to confirm your registration') . "</a>";
        $linkText = "<a target='_blank' href='" . HTTP_ROOT . "users/confirm/$user->uniq_id'>" . HTTP_ROOT . "users/confirm/$user->uniq_id</a>";
        $message = $this->Custom->formatEmail($emailTemplate->content, ['USERNAME' => $user->username, 'EMAIL' => $user->email, 'PASSWORD' => $password, 'LINK' => $link, 'LINK_TEXT' => $linkText, 'SUPPORT_EMAIL' => $adminSetting->support_email]);
        $this->Custom->sendEmail($to, $from, $subject, $message);
        return TRUE;
    }

    public function confirm($uniqueId) {
        $query = $this->Users->find()->where(['Users.uniq_id' => $uniqueId]);
        if ($query->count() > 0) {
            $user = $query->first();
            $this->Users->query()->update()->set(['account_status' => 1])->where(['id' => $user->id])->execute();

            $this->loadModel('TempUsers');
            $this->TempUsers->deleteAll(["TempUsers.user_id" => $user->id]);
            $this->Auth->setUser($user);
            $this->redirect(HTTP_ROOT . 'dashboard');
##################Manually Session Create Ends################################
        } else {
            $this->Session->setFlash(__('Your account already  activated.'), 'error_message');
            $this->redirect(HTTP_ROOT);
        }
    }

    public function editProfile() {
        $userId = $this->Auth->user('id');
        $user = $this->Users->get($userId);
        if ($this->request->is(['patch', 'post', 'put'])) {
            $data = $this->request->data;
            $user = $this->Users->newEntity($data);
            $user = $this->Users->patchEntity($user, $data);
            if (!empty($data['country'])) {
                $user->country = $data['country'];
            } else {
                unset($user->country);
            }
            if (!empty($data['skills'])) {
                $user->skills = implode(",", $data['skills']);
            } else {
                unset($user->timezone_id);
            }
            if (!empty($data['timezone_id'])) {
                $user->timezone_id = $data['timezone_id'];
            } else {
                unset($user->timezone_id);
            }
            $user->id = $userId;
            if ($this->Users->save($user)) {
                if (!empty($data['profile_img']['name'])) {
                    $profileImage = $this->Custom->uploadThumbImage($data['profile_img']['tmp_name'], $data['profile_img']['name'], USER_PROFILE_IMAGE, 280, 280);
                    if ($profileImage) {
                        $this->Users->query()->update()->set(['profile_image' => $profileImage])->where(['id' => $user->id])->execute();
                    }
                }
                $this->Flash->success(__('User details updated successfully.'));
                return $this->redirect(['controller' => 'Users', 'action' => 'editProfile']);
            } else {
                $this->Flash->error(__('User details could not be updated. Please, try again.'));
            }
        }
        $this->loadModel('Skills');
        $this->loadModel('Countries');
        $this->loadModel('Currencies');
        $this->loadModel('Timezones');
        $timezones = $this->Timezones->find('list', ['keyField' => 'id', 'valueField' => 'default_timezone_set'])->order(['default_timezone_set' => 'ASC'])->where(['default_timezone_set !=' => ''])->all();
        $skills = $this->Skills->find('list')->where(['is_active' => 1]);
        $countries = $this->Countries->find('list')->where(['is_active' => 1]);
        $currencies = $this->Currencies->find('list')->where(['is_active' => 1]);
        $this->set(compact('user', 'timezones', 'skills', 'countries', 'currencies'));
    }

    public function emailSettings() {
        if ($this->request->is('post')) {
            $data = $this->request->data;
            $sessionData = $this->Users->get($this->Auth->user('id'));
            $password = $sessionData->password;
            $obj = new DefaultPasswordHasher;
            $postpassword = $obj->check($data['password'], $password);
            if ($postpassword == FALSE) {
                $this->Flash->error(__('Please enter correct password!'));
            } else if ($data['email'] == $sessionData->email) {
                $this->Flash->error(__('You are currentlly using this mail!'));
                $this->redirect($this->referer());
            } else {
                $checkEmail = $this->Users->find()->where(['Users.email' => $data['email']]);
                if ($checkEmail->count() > 0) {
                    $this->Flash->error(__('Email Already Exist!'));
                    $this->redirect($this->referer());
                } else {
                    $this->Users->query()->update()->set(['email' => $data['email']])->where(['id' => $sessionData->id])->execute();
                    $this->Flash->success(__('Email Updated Successfully!'));
                    $this->redirect($this->referer());
//                    if ($this->User->saveField('email', $data['User']['email'])) {
//                        $this->Session->write('Auth.User.email', $data['User']['email']);
//                        $this->Session->setFlash(__('Email Updated Successfully!'), 'success_message');
//                        $this->redirect($this->referer());
//                    }
                }
            }
        }
//User-Notification section
        $this->loadModel('UserNotifications');
        $userId = $this->Auth->user('id');
        $checkNotified = $this->UserNotifications->find()->where(['user_id' => $userId])->first();
        $this->set(compact('checkNotified'));
    }

    public function passwordSettings() {
        $user = $this->Users->get($this->Auth->user('id'));
        if (!empty($this->request->data)) {
            $data = $this->request->data;
            $user = $this->Users->patchEntity($user, ['old_password' => $data['old_password'], 'password' => $data['password1'], 'password1' => $data['password1'], 'password2' => $data['password2']], ['validate' => 'password']);
            if ($this->Users->save($user)) {
                $this->Flash->success('The password is successfully changed');
                $this->redirect($this->referer());
            } else {
                $this->Flash->error('There was an error during the save!');
            }
        }
        $this->set('user', $user);
    }

    public function membershipSettings() {
        
    }

    public function trustSettings() {
        
    }

    public function accountSettings() {
        if ($this->request->is('post')) {
            $data = $this->request->data();
            $userId = $this->Auth->user('id');
            $updated = $this->Users->query()->update()->set(['type' => $data['type']])->where(['id' => $userId]);
            if ($updated) {
                $this->Flash->success(__('Your account has been changed successfully'));
                $this->redirect($this->referer());
            } else {
                $this->Flash->error(__('Some error occured!!'));
                $this->redirect($this->referer());
            }
        }
        if ($this->Auth->user('id')) {
            $userId = $this->Auth->user('id');
            $userDetail = $this->Users->find()->where(['Users.id' => $userId])->first();
        }
        $this->set(compact('userDetail'));
    }

    function ajaxUpdateAccountType($type = NULL) {
        $this->viewBuilder()->layout('ajax');
        if ($this->Auth->user('id')) {
            $userId = $this->Auth->user('id');
            $this->Users->query()->update()->set(['type' => $type])->where(['id' => $userId])->execute();
            echo json_encode(['status' => 'success']);
        }

        exit;
    }

    public function deleteAccount() {
        if ($this->Auth->user('id')) {
            $userId = $this->Auth->user('id');

            $portfolioImages = $this->Portfolios->find('all')->where(['Portfolios.user_id IN' => $userId]);
            foreach ($portfolioImages as $portfolioImage) {
                unlink(WWW_ROOT . PORTFOLIOS . $portfolioImage->image);
            }
            $this->Portfolios->deleteAll(['Portfolios.user_id IN' => $userId]);
            $this->Memberships->deleteAll(['Memberships.user_id' => $userId]);
            $projectLists = $this->Projects->find('list', ['keyField' => 'id', 'valueField' => 'id'])->where(['Projects.user_id IN' => $userId])->toArray();

            $projectFileLists = $this->ProjectFiles->find('all')->where(['ProjectFiles.project_id IN' => $projectLists]);
            foreach ($projectFileLists as $projectFileList) {
                unlink(WWW_ROOT . PROJECT_FILE . $projectFileList->file);
            }


            $this->Projects->deleteAll(['Projects.user_id IN' => $userId]);
            $this->ProjectFiles->deleteAll(['ProjectFiles.project_id IN' => $projectLists]);
            $this->ProjectBids->deleteAll(['ProjectBids.user_id IN' => $userId]);
            $users = $this->Users->find('all')->where(['Users.id' => $userId])->first();
            unlink(WWW_ROOT . USER_PROFILE_IMAGE . $users->profile_image);

            if ($this->Users->deleteAll(['Users.id' => $userId])) {
                $this->Flash->success(__('Your account has been deleted successfully'));
                $this->request->session()->destroy();
                $this->redirect(HTTP_ROOT);
            } else {
                $this->Flash->error(__('Some error occured!!'));
                $this->redirect($this->referer());
            }
        }
    }

    public function profile($username = null) {
        $query = $this->Users->find()->where(['Users.username' => $username])->contain(['Countries'])->contain(['Currencies']);
        if ($query->count() > 0) {
            $getUser = $query->first();
            $getPortfolios = $this->Users->Portfolios->find('all')->where(['user_id' => $getUser->id])->limit(8)->order(['id' => 'DESC']);
            $totalPortfolios = $this->Users->Portfolios->find()->where(['user_id' => $getUser->id])->count();
            $conn = ConnectionManager::get('default');
            $sql = "SELECT skills.id, skills.name,skills.seo, count(users.id) as count
                FROM skills JOIN users 
                ON LOCATE(CONCAT(',', skills.id, ','), CONCAT(',', users.skills, ',')) > 0
                WHERE skills.id IN(" . $getUser->skills . ")
                GROUP BY skills.name
                ORDER BY COUNT(users.id) DESC, skills.name ASC LIMIT 10";
            $stmt = $conn->prepare($sql);
            $stmt->execute();
            $mySkills = $stmt->fetchAll('assoc');
            /////Average rating in the top right section                 
            $conn = ConnectionManager::get('default');
            $sql = "SELECT (SUM(`rating`)+SUM(`communication`)+SUM(`payment_promptness`)+SUM(`professionalism`)+SUM(`work_with_the_employer_again`)) / 5 AS avg_rating , COUNT(id) AS total_rating FROM `feedbacks` WHERE feedback_to={$getUser->id}";
            $stmt = $conn->prepare($sql);
            $stmt->execute();
            $userRating = $stmt->fetch('assoc');
            $this->set(compact('getUser', 'getPortfolios', 'totalPortfolios', 'mySkills', 'userRating'));
        } else {
            $this->redirect(HTTP_ROOT);
        }
    }

    public function ajaxGetFeedbacks() {
        $this->viewBuilder()->layout('ajax');
        if ($this->request->is('ajax')) {
            $data = $this->request->query;
            $userId = $data['user_id'];
            $page = !empty($data['page']) ? $data['page'] : 1;
            $this->loadModel('Feedbacks');
            $this->Feedbacks->belongsTo('Users', ['classname' => 'Users', 'foreignKey' => 'feedback_from', 'joinType' => 'INNER']);

            $conditions[] = ['feedback_to' => $userId];
            $order = ['Feedbacks.id' => 'DESC'];
            $config = [
                'limit' => 50,
                'order' => $order,
                'contain' => ['Users', 'Projects', 'Projects.Currencies', 'Users.Countries'],
                'conditions' => $conditions,
                'page' => $page
            ];
            $feedbacks = $this->Paginator->paginate($this->Feedbacks->find(), $config);
            $this->set(compact('feedbacks'));
        } else {
            $this->redirect($this->referer());
        }
    }

    public function profileXXXXXXXXx($username = null) {
        $query = $this->Users->find()->where(['Users.username' => $username])->contain(['Countries'])->contain(['Currencies']);
        if ($query->count() > 0) {
            $getUser = $query->first();
            $getPortfolios = $this->Users->Portfolios->find('all')->where(['user_id' => $getUser->id])->limit(8)->order(['id' => 'DESC']);
            $totalPortfolios = $this->Users->Portfolios->find()->where(['user_id' => $getUser->id])->count();

            $mySkills = $this->Skills->find('all')->where(['Skills.id IN' => explode(",", $getUser->skills)]);
            foreach ($mySkills as $mySkill) {
                $mySkill->count = $this->Users->find()->where(["FIND_IN_SET('{$mySkill->id}',Users.skills)"])->count();
            }

            $this->set(compact('getUser', 'getPortfolios', 'totalPortfolios', 'mySkills'));
        } else {
            $this->redirect(HTTP_ROOT);
        }
    }

    public function portfolies() {
        try {
            $conditions = ['Portfolios.user_id' => $this->Auth->user('id')];
            $config = [
                'limit' => 8,
                'order' => ['Portfolios.id' => 'DESC'],
                'contain' => [],
                'conditions' => $conditions
            ];
            $getPortfolios = $this->Paginator->paginate($this->Users->Portfolios->find(), $config);
            $totalPortfolios = $this->Users->Portfolios->find()->where($conditions)->count();
            $this->set(compact('getPortfolios', 'totalPortfolios'));
        } catch (\Cake\Network\Exception\NotFoundException $ex) {
            $this->redirect(['controller' => 'Users', 'action' => 'portfolies']);
        }
    }

    public function ajaxUploadPortfolioImage() {
        $this->viewBuilder()->layout('ajax');
        require_once('filer' . DS . 'upload_portfolios.php');
        exit;
    }

    public function ajaxPortFolioDescPopup($id) {
        $portfolio = $this->Users->Portfolios->get($id);
        $this->set(compact('portfolio'));
        if ($this->request->is(['post', 'put'])) {
            $portfolio = $this->Users->Portfolios->patchEntity($portfolio, $this->request->data);
            if ($this->Users->Portfolios->save($portfolio)) {
                echo json_encode(['status' => 'success']);
            } else {
                echo json_encode(['status' => 'error']);
            }
            exit();
        }
    }

    public function ajaxDeletePortfolioImage($id) {
        $portfolio = $this->Users->Portfolios->get($id);
        if ($this->Users->Portfolios->delete($portfolio)) {
            echo json_encode(['status' => 'success']);
        } else {
            echo json_encode(['status' => 'error']);
        }
        exit;
    }

    public function ajaxNotiSetting() {
        $this->viewBuilder()->layout('ajax');
        $userId = $this->Auth->user('id');
        $this->loadModel('UserNotifications');
        $query = $this->UserNotifications->find()->where(['user_id' => $userId]);
        if ($query->count() > 0) {
            $userNotifiction = $this->UserNotifications->newEntity($query->first()->toArray());
        } else {
            $userNotifiction = $this->UserNotifications->newEntity();
        }
        if ($this->request->is('post')) {
            $data = $this->request->data;
            $data[$data['coloumn']] = $data['preference'];
            $userNotifiction = $this->UserNotifications->patchEntity($userNotifiction, $data);
            $userNotifiction->user_id = $userId;
            if ($this->UserNotifications->save($userNotifiction)) {
                echo json_encode(['status' => 'success']);
            } else {
                echo json_encode(['status' => 'error']);
            }
        }
        exit;
    }

////To-Do List section starts here \\\

    function ajaxAddToList() {
        $this->viewBuilder()->layout('ajax');
        if ($this->request->is('post')) {
            $data = $this->request->data;
            $addTodoList = $this->TodoLists->newEntity();
            $addTodoList = $this->TodoLists->patchEntity($addTodoList, $data);
            $addTodoList->user_id = $this->Auth->user('id');
            if ($this->TodoLists->save($addTodoList)) {
                $html = "<li id='removelist-{$addTodoList->id}'>
                         <input type='checkbox' name='{$addTodoList->name}' id='{$addTodoList->id}' class='css-checkbox' data-id='{$addTodoList->id}' data-iscompleted='{$addTodoList->is_completed}' onclick='updateTodoList(this)'/>
                         <label for='{$addTodoList->id}' class='css-label'>{$addTodoList->name}</label> <img src='img/close.png' class='close' onclick='removeTodoList({$addTodoList->id})'/> 
                         </li>";
                echo json_encode(['status' => 'success', 'newlist' => $html]);
            }
        }
        exit;
    }

    function ajaxRemoveTodoList($id = NULL) {
        $this->viewBuilder()->layout('ajax');
        if (!empty($id)) {
            $this->TodoLists->deleteAll(['user_id' => $this->Auth->user('id'), 'id' => $id]);
            echo json_encode(['status' => 'success']);
        }
        exit;
    }

    function ajaxUpdateTodoList() {
        $this->viewBuilder()->layout('ajax');
        if ($this->request->is('post')) {
            $data = $this->request->data;
            $userId = $this->Auth->user('id');
            if ($data['is_completed'] == 1) {
                $this->TodoLists->query()->update()->set(['is_completed' => 1])->where(['id' => $data['id'], 'user_id' => $userId])->execute();
                echo json_encode(['status' => 'success', 'cross' => 'yes']);
            } else {
                $this->TodoLists->query()->update()->set(['is_completed' => 0])->where(['id' => $data['id'], 'user_id' => $userId])->execute();
                echo json_encode(['status' => 'success', 'cross' => 'no']);
            }
        }
        exit;
    }

////To-Do List section ends here \\\   
}
