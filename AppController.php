<?php

namespace App\Controller;

use Cake\Controller\Controller;
use Cake\Event\Event;
use Cake\ORM\TableRegistry;
use Cake\Datasource\ConnectionManager;

class AppController extends Controller {

    public function initialize() {
        parent::initialize();
        //Load Models       
        $this->loadModel('Users');
        //Load Components
        $this->loadComponent('RequestHandler');
        $this->loadComponent('Flash');

//        $this->loadComponent('Auth', [
//            'authenticate' => [
//                'Form' => [
//                    'fields' => ['username' => 'email', 'password' => 'password']
//                ]
//            ]
//        ]);


        $this->loadComponent('Auth', [
            'loginRedirect' => [
                'controller' => 'Users',
                'action' => 'index'
            ],
            'logoutRedirect' => [
                'controller' => 'Users',
                'action' => 'home',
            ]
        ]);
    }

    public function beforeFilter(Event $event) {
        //Remember me//
        if (!$this->Auth->user('id') && isset($_COOKIE['rememberme'])) {
            $explodeCookieValue = explode('-', $_COOKIE['rememberme']);
            $user = $this->Users->find()->where(['id' => $explodeCookieValue[0], 'uniq_id' => $explodeCookieValue[1]]);
            if ($user->count() > 0) {
                $this->Auth->setUser($user->first()->toArray());
            }
        }
    }

    public function beforeRender(Event $event) {
        $isLoggedIn = FALSE; //This will to check frontend user login to check for admin use $this->Auth->user('id')
        if (in_array($this->Auth->user('type'), [2, 3, 4])) {
//            $getUserDetail = $this->Users->find()->where(['Users.id' => $this->Auth->user('id')])->select(['id', 'profile_image', 'balance'])->first();
            $conn = ConnectionManager::get('default');
            $sql = "SELECT Users.id , Users.profile_image, Balance.balance,Currencies.name AS curr_name,Currencies.symbol AS curr_symbol FROM users Users LEFT JOIN user_balances Balance ON (Balance.currency_id = Users.currency_id AND Users.id = Balance.user_id) LEFT JOIN currencies Currencies ON (Users.currency_id = Currencies.id) WHERE Users.id = :user_id";
            $stmt = $conn->prepare($sql);
            $stmt->bindValue('user_id', $this->Auth->user('id'), 'integer');
            $stmt->execute();
            $getUserDetail = $stmt->fetch('assoc');
            $userBalances = $this->Users->UserBalances->find('all')->where(['user_id' => $this->Auth->user('id')])->contain(['Currencies']);
            $this->set(compact('getUserDetail', 'userBalances'));
            $isLoggedIn = TRUE;
        }
        $this->set(compact('isLoggedIn'));

        if ($this->request->session()->read('Auth.User.id') == 1) {
            $adminProfileDetails = $this->Users->find('all')->where(['Users.type' => 1], ['Users.is_active' => 1]);
            $adminProfileDetail = $adminProfileDetails->first();
            $this->set(compact('adminProfileDetail'));
        }
        ///General Notification part
        $this->loadModel('Notifications');
        $this->Notifications->belongsTo('Users', [ 'classname' => 'Users', 'foreignKey' => 'notify_from', 'joinType' => 'INNER']);
        $generalNotifications = $this->Notifications->find()->where(['notify_to' => $this->Auth->user('id')])->contain(['Users'])->order(['Notifications.is_read' => 'ASC', 'Notifications.created' => 'DESC'])->limit(10);
        $this->set(compact('generalNotifications'));
        //Message Notification part
        $this->loadModel('MessageThreads');
        $this->loadModel('Messages');
        $conditions = ['OR' => [['employer_id' => $this->Auth->user('id')], ['freelancer_id' => $this->Auth->user('id')]]];
        $messageNotifications = $this->MessageThreads->find('all')->where($conditions)->order(['MessageThreads.id' => 'DESC'])->limit(5);
        foreach ($messageNotifications as $messageNotification) {
            $messageNotification->messages = $this->Messages->find()->where(['message_thread_id' => $messageNotification->id])->limit(1)->order(['id' => 'DESC'])->first();
            $userId = ($messageNotification->freelancer_id == $this->Auth->user('id')) ? $messageNotification->employer_id : $messageNotification->freelancer_id;
            $messageNotification->user = $this->Users->find()->where(['id' => $userId])->select(['id', 'username', 'profile_image'])->first();
        }
        $this->set(compact('messageNotifications'));
        //project feed part
        $this->loadModel('Projects');
        $projectFeedNotifications = $this->Projects->find()->where(['project_bid_count' => 0])->contain(['Categories', 'Currencies'])->order(['Projects.id' => 'DESC']);
        $this->set(compact('projectFeedNotifications'));
    }

}
