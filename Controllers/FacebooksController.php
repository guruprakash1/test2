<?php

namespace App\Controller;

use App\Controller\AppController;
use Cake\Event\Event;
use Cake\Mailer\Email;
use Cake\Network\Request;
use Cake\ORM\TableRegistry;
use Cake\Auth\DefaultPasswordHasher;
use Cake\Validation\Validation;

/////////////////////////FOR FACE BOOK /////////////////////
//use Google_FileCache;
//define('FACEBOOK_SDK_V4_SRC_DIR','../Vendor/fb/src/Facebook/');
define('FACEBOOK_SDK_V4_SRC_DIR', ROOT . DS . 'vendor' . DS . 'fb' . DS . 'src' . DS . 'Facebook/');
require_once(ROOT . DS . 'vendor' . DS . 'fb' . DS . 'autoload.php');

//echo FACEBOOK_SDK_V4_SRC_DIR;exit;

use Facebook\FacebookSession;
use Facebook\FacebookRedirectLoginHelper;
use Facebook\FacebookRequest;
use Facebook\FacebookResponse;
use Facebook\FacebookSDKException;
use Facebook\FacebookRequestException;
use Facebook\FacebookAuthorizationException;
use Facebook\GraphObject;
use Facebook\GraphUser;
use Facebook\GraphSessionInfo;

/**
 * Users Controller
 *
 * @property \App\Model\Table\UsersTable $Users
 */
class FacebooksController extends AppController {

    public function initialize() {
        parent::initialize();
        //Load Components
        $this->loadComponent('Custom');
        //Load Model
        $this->loadModel('Visitors');
    }

    public function beforeFilter(Event $event) {
        $this->Auth->allow();
    }

    /**
     * Facebook Login starts here/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
     */
    public function fblogin() {
        $this->autoRender = false;
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
        FacebookSession::setDefaultApplication(FACEBOOK_APP_ID, FACEBOOK_APP_SECRET);
        $helper = new FacebookRedirectLoginHelper(FACEBOOK_REDIRECT_URI);
        $url = $helper->getLoginUrl(array('email'));
        return $this->redirect($url);
    }

    public function fbreturn() { 
    session_start();
    $app_id = FACEBOOK_APP_ID;
    $app_secret = FACEBOOK_APP_SECRET;
    FacebookSession::setDefaultApplication($app_id, $app_secret);
    $redirect_url = HTTP_ROOT . 'facebooks/fbreturn';
    $helper = new FacebookRedirectLoginHelper($redirect_url);

    try {
    $session = $helper->getSessionFromRedirect();
    } catch (FacebookRequestException $ex) {
    
    } catch (Exception $ex) {
    
    }

    if (isset($session)) {
    $access_token = $session->getToken();
    $appsecret_proof = hash_hmac('sha256', $access_token, $app_secret);
    $request = new FacebookRequest($session, 'GET', '/me?locale=en_US&fields=name,email,gender,age_range,first_name,last_name,link,locale,picture,location', array("appsecret_proof" => $appsecret_proof));
    $response = $request->execute();
    $graph = $response->getGraphObject();

            $user = $graph->asArray(); 
            
            //echo '<pre>'; print_r($user);exit;

            $user['type'] = 4;
            $user['login_type'] = 1;


            //pr($user);exit;
            $query = $this->Visitors->find()->where(['Visitors.social_id' => $user['id'], 'Visitors.login_type' => 1]);
            if ($query->count()) {
                $user['visitor_id'] = $query->first()->id;
                $this->Visitors->query()->update()->set(["lastlogin" => date('Y-m-d H:i:s')])->where(['social_id' => $user['id']])->execute();
            } else {
                $visitor = $this->Visitors->newEntity();
                $visitor->name = $user['name'];
                $visitor->firstname = $user['first_name'];
                $visitor->lastname = $user['last_name'];
                $visitor->email = $user['email'];
                $visitor->login_type = 1;
                $visitor->social_id = $user['id'];
                $visitor->created = date('Y-m-d H:i:s');
                $visitor->modified = date('Y-m-d H:i:s');
                $visitor->lastlogin = date('Y-m-d H:i:s');
                $this->Visitors->save($visitor);
                $user['visitor_id'] = $visitor->id;
            }


            $this->request->session()->write('Auth.User', $user);
            return $this->redirect(HTTP_ROOT . 'customer-quote-requests');


//            $fb_data = $graph->asArray();
//            $id = $graph->getId();
//            $email = $graph->getEmail();
//            $image = "https://graph.facebook.com/" . $id . "/picture?width=100";
//            $this->request->session()->write('fb_data', $fb_data);
        } else {
            return $this->redirect(HTTP_ROOT . 'login');
        }
    }

    /**
     * Facebook Login Ends here/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
     */
}
